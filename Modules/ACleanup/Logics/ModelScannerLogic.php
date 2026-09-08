<?php

namespace Modules\AClean\Logics;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Modules\AClean\Enums\DATA_CATEGORY;
use Modules\AClean\Models\CleanupConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

/**
 * Model扫描逻辑类
 *
 * 负责扫描系统中的Model类并生成清理配置
 */
class ModelScannerLogic
{
    /**
     * 扫描所有Model类
     *
     * @param  bool  $forceRefresh  是否强制刷新
     * @return array 扫描结果
     */
    public static function scanAllModels(bool $forceRefresh = false): array
    {
        $startTime = microtime(true);

        // 获取所有Model类
        $models = static::getAllModelClasses();

        $result = [
            'total_models' => count($models),
            'scanned_models' => 0,
            'new_models' => 0,
            'updated_models' => 0,
            'models' => [],
            'scan_time' => 0,
        ];

        foreach ($models as $modelClass) {
            try {
                $modelInfo = static::scanModel($modelClass, $forceRefresh);
                if ($modelInfo) {
                    $result['models'][] = $modelInfo;
                    $result['scanned_models']++;

                    if ($modelInfo['is_new']) {
                        $result['new_models']++;
                    } elseif ($modelInfo['is_updated']) {
                        $result['updated_models']++;
                    }
                }
            } catch (\Exception|\Error $e) {
                Log::error("扫描Model {$modelClass} 失败: ".$e->getMessage());
            }
        }

        $result['scan_time'] = round(microtime(true) - $startTime, 3);

        return $result;
    }

    /**
     * 扫描单个Model
     *
     * @param  string  $modelClass  Model类名
     * @param  bool  $forceRefresh  是否强制刷新
     * @return array Model信息
     */
    public static function scanModel(string $modelClass, bool $forceRefresh = false): ?array
    {
        try {
            // 检查是否已存在配置
            $existingConfig = CleanupConfig::where('model_class', $modelClass)->first();
            $isNew = ! $existingConfig;
            $isUpdated = false;

            // 获取Model信息
            $modelInfo = static::getModelInfo($modelClass);

            // 如果获取Model信息失败，跳过
            if (isset($modelInfo['error'])) {
                Log::warning("跳过有问题的Model: {$modelClass} - ".$modelInfo['error']);

                return null;
            }

            // 自动识别数据分类
            $dataCategory = static::detectDataCategory($modelClass, $modelInfo);

            // 识别模块名称
            $moduleName = static::extractModuleName($modelClass);

            // 生成默认清理配置
            $defaultConfig = static::generateDefaultConfig($dataCategory, $modelInfo);

            if ($isNew || $forceRefresh) {
                // 创建或更新配置
                $configData = [
                    'model_class' => $modelClass,
                    'table_name' => $modelInfo['table_name'], // 保持兼容性
                    'model_info' => $modelInfo,
                    'module_name' => $moduleName,
                    'data_category' => $dataCategory->value,
                    'default_cleanup_type' => $defaultConfig['cleanup_type'],
                    'default_conditions' => $defaultConfig['conditions'],
                    'is_enabled' => $defaultConfig['is_enabled'],
                    'priority' => $defaultConfig['priority'],
                    'batch_size' => $defaultConfig['batch_size'],
                    'description' => $defaultConfig['description'],
                ];

                if ($isNew) {
                    CleanupConfig::create($configData);
                } else {
                    $existingConfig->update($configData);
                    $isUpdated = true;
                }
            }

            return [
                'model_class' => $modelClass,
                'table_name' => $modelInfo['table_name'],
                'module_name' => $moduleName,
                'data_category' => $dataCategory,
                'model_info' => $modelInfo,
                'is_new' => $isNew,
                'is_updated' => $isUpdated,
            ];
        } catch (\Exception|\Error $e) {
            Log::error("扫描Model失败: {$modelClass} - ".$e->getMessage());

            return null;
        }
    }

    /**
     * 获取所有可用的Model类列表（供表单选择使用）
     *
     * 扫描所有模块的 Models 目录，返回有效的 Model 类名映射
     *
     * @return array<string, string> Model类名 => Model类名 的映射
     */
    public static function getAvailableModels(): array
    {
        $modelList = [];
        $models = static::getAllModelClasses();

        foreach ($models as $modelClass) {
            $modelList[$modelClass] = $modelClass;
        }

        ksort($modelList);

        return $modelList;
    }

    /**
     * 获取所有Model类
     *
     * @return array Model类名列表
     */
    private static function getAllModelClasses(): array
    {
        $models = [];
        $modulePaths = glob(app_path('Module/*/Models'));

        foreach ($modulePaths as $modulePath) {
            $modelFiles = glob($modulePath.'/*.php');

            foreach ($modelFiles as $modelFile) {
                $modelClass = static::getModelClassFromFile($modelFile);
                if ($modelClass && static::isValidModel($modelClass)) {
                    $models[] = $modelClass;
                }
            }
        }

        return $models;
    }

    /**
     * 从文件路径获取Model类名
     *
     * @param  string  $filePath  文件路径
     * @return string|null Model类名
     */
    private static function getModelClassFromFile(string $filePath): ?string
    {
        $relativePath = str_replace(app_path(), '', $filePath);
        $relativePath = str_replace('.php', '', $relativePath);
        $relativePath = str_replace('/', '\\', $relativePath);

        return 'App'.$relativePath;
    }

    /**
     * 验证是否为有效的Model类
     *
     * @param  string  $modelClass  Model类名
     * @return bool 是否有效
     */
    private static function isValidModel(string $modelClass): bool
    {
        if (! class_exists($modelClass)) {
            return false;
        }

        try {
            $reflection = new \ReflectionClass($modelClass);

            // 检查是否继承自Eloquent
            $isValidModel = $reflection->isSubclassOf(Model::class);

            // 排除抽象类
            if ($reflection->isAbstract()) {
                return false;
            }

            return $isValidModel;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取Model信息
     *
     * @param  string  $modelClass  Model类名
     * @return array Model信息
     */
    private static function getModelInfo(string $modelClass): array
    {
        try {
            $model = new $modelClass;

            return [
                'class_name' => $modelClass,
                'table_name' => $model->getTable(),
                'primary_key' => $model->getKeyName(),
                'timestamps' => $model->timestamps,
                'soft_deletes' => static::hasSoftDeletes($model),
                'fillable' => $model->getFillable(),
                'guarded' => $model->getGuarded(),
                'casts' => $model->getCasts(),
                'connection' => $model->getConnectionName(),
                'relations' => static::getModelRelations($model),
            ];
        } catch (\Exception|\Error $e) {
            Log::warning("获取Model信息失败: {$modelClass} - ".$e->getMessage());

            // 返回基本信息
            return [
                'class_name' => $modelClass,
                'table_name' => 'unknown',
                'primary_key' => 'id',
                'timestamps' => true,
                'soft_deletes' => false,
                'fillable' => [],
                'guarded' => [],
                'casts' => [],
                'connection' => null,
                'relations' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查Model是否支持软删除
     *
     * @param  Model  $model  Model实例
     * @return bool 是否支持软删除
     */
    private static function hasSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }

    /**
     * 获取Model的关系定义
     *
     * @param  Model  $model  Model实例
     * @return array 关系列表
     */
    private static function getModelRelations(Model $model): array
    {
        $relations = [];
        $reflection = new \ReflectionClass($model);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === get_class($model) &&
                ! $method->isStatic() &&
                $method->getNumberOfParameters() === 0) {

                $methodName = $method->getName();

                // 跳过一些已知的非关系方法
                if (in_array($methodName, ['getTable', 'getKeyName', 'getFillable', 'getGuarded', 'getCasts'])) {
                    continue;
                }

                try {
                    $result = $model->$methodName();
                    if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                        $relations[] = [
                            'name' => $methodName,
                            'type' => class_basename(get_class($result)),
                            'related' => get_class($result->getRelated()),
                        ];
                    }
                } catch (\Exception|\Error $e) {
                    // 忽略调用失败的方法
                    Log::debug("获取Model关系失败: {$methodName} - ".$e->getMessage());
                }
            }
        }

        return $relations;
    }

    /**
     * 从Model类名提取模块名称
     *
     * @param  string  $modelClass  Model类名
     * @return string 模块名称
     */
    private static function extractModuleName(string $modelClass): string
    {
        // App\Module\Farm\Models\FarmUser -> Farm
        if (preg_match('/App\\\\Module\\\\([^\\\\]+)\\\\Models\\\\/', $modelClass, $matches)) {
            return $matches[1];
        }

        return 'Unknown';
    }

    /**
     * 检测数据分类
     *
     * @param  string  $modelClass  Model类名
     * @param  array  $modelInfo  Model信息
     * @return DATA_CATEGORY 数据分类
     */
    private static function detectDataCategory(string $modelClass, array $modelInfo): DATA_CATEGORY
    {
        $tableName = $modelInfo['table_name'];
        $className = class_basename($modelClass);

        // 根据表名和类名模式识别数据分类
        if (str_contains($tableName, '_logs') || str_contains($className, 'Log')) {
            return DATA_CATEGORY::LOG_DATA;
        }

        if (str_contains($tableName, '_cache') || str_contains($tableName, '_sessions') ||
            str_contains($className, 'Cache') || str_contains($className, 'Session')) {
            return DATA_CATEGORY::CACHE_DATA;
        }

        if (str_contains($tableName, '_configs') || str_contains($tableName, '_settings') ||
            str_contains($className, 'Config') || str_contains($className, 'Setting')) {
            return DATA_CATEGORY::CONFIG_DATA;
        }

        if (str_contains($tableName, '_orders') || str_contains($tableName, '_payments') ||
            str_contains($tableName, '_transactions') || str_contains($className, 'Order') ||
            str_contains($className, 'Payment') || str_contains($className, 'Transaction')) {
            return DATA_CATEGORY::TRANSACTION_DATA;
        }

        // 默认为用户数据
        return DATA_CATEGORY::USER_DATA;
    }

    /**
     * 生成默认清理配置
     *
     * @param  DATA_CATEGORY  $dataCategory  数据分类
     * @param  array  $modelInfo  Model信息
     * @return array 默认配置
     */
    private static function generateDefaultConfig(DATA_CATEGORY $dataCategory, array $modelInfo): array
    {
        $defaultCleanupType = $dataCategory->getDefaultCleanupType();
        $defaultPriority = $dataCategory->getDefaultPriority();
        $isEnabled = $dataCategory->isDefaultEnabled();

        // 根据Model特性调整配置
        $conditions = [];
        if ($defaultCleanupType === CLEANUP_TYPE::DELETE_BY_TIME) {
            $timeField = 'created_at';
            if ($modelInfo['timestamps']) {
                $conditions = [
                    'time_field' => $timeField,
                    'before' => '30_days_ago',
                ];
            }
        }

        return [
            'cleanup_type' => $defaultCleanupType->value,
            'conditions' => $conditions,
            'is_enabled' => $isEnabled,
            'priority' => $defaultPriority,
            'batch_size' => 1000,
            'description' => "自动生成的{$dataCategory->getDescription()}清理配置",
        ];
    }
}
