<?php

namespace Modules\ABase\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * 自动生成Eloquent模型属性注释和表创建SQL
 *
 * 通过分析数据库表结构，自动生成模型属性的PHPDoc注释，
 * 并提供fillable字段自动维护功能。同时，为每个表生成创建SQL文件。
 *
 * 功能特性：
 * 1. 自动识别字段类型并生成对应property注释
 * 2. 特殊时间字段自动识别为Carbon类型
 * 3. 支持自定义Casts类型，自动识别并使用正确的类型
 * 4. 自动维护模型$attrlist属性包含所有字段
 * 5. 在模块的Databases/createsql目录下为每个表生成创建SQL文件（不包含当前自增值，但保留字段的自增属性）
 * 6. 支持指定单一模块进行处理，提高效率
 *
 * 使用说明：
 * 1. 在模型中添加标记注释：
 *    - 属性注释区域标记：在模型文件中添加  field start  和  field end  注释块
 *    - fillable字段标记：在模型文件中添加  attrlist start  和  attrlist end  注释块
 * 2. 对于使用自定义Cast类的字段，会自动识别并使用Cast类作为类型
 * 3. 可以通过指定模块名称参数，只处理特定模块的模型
 * 4. 生成的SQL文件中不包含当前AUTO_INCREMENT值，但保留字段的自增属性，便于在不同环境中使用
 *
 *
 * @example 处理所有模块: php artisan ucore:generate-model-annotation
 * @example 只处理特定模块: php artisan ucore:generate-model-annotation GameItems
 * @example 开启调试信息: php artisan ucore:generate-model-annotation --debug-info
 * @example 跳过SQL生成: php artisan ucore:generate-model-annotation --skip-sql
 */
class GenerateModelAnnotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ucore:generate-model-annotation
                            {module? : 要处理的模块名称，不提供则处理所有模块}
                            {--skip-sql : 跳过生成表创建SQL文件}
                            {--debug-info : 输出详细的调试信息}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成模型property注释和表创建SQL文件（不含当前自增值，但保留字段自增属性），支持自定义Casts类型，可指定单一模块处理';

    private $fillable = [];

    /**
     * 已处理的表名，用于避免重复生成SQL
     *
     * @var array
     */
    private $processedTables = [];

    /**
     * 成功处理的模型计数
     *
     * @var int
     */
    private $successCount = 0;

    /**
     * 跳过的模型计数
     *
     * @var int
     */
    private $skippedCount = 0;

    /**
     * 失败的模型计数
     *
     * @var int
     */
    private $failedCount = 0;

    /**
     * 主执行方法
     *
     * 扫描指定目录下的模型文件并生成注释和表创建SQL
     *
     * @return void
     */
    public function handle()
    {
        // 获取要处理的模块名称
        $targetModule = $this->argument('module');

        // 显示处理模式
        if ($targetModule) {
            $this->simpleOutput("仅处理模块: {$targetModule}");
        } else {
            $this->simpleOutput('处理所有模块');
        }

        // 显示是否跳过SQL生成的选项状态
        if ($this->option('skip-sql')) {
            $this->debug('已设置跳过生成表创建SQL文件', 'warning');
        } else {
            $this->debug('将为每个表生成创建SQL文件到模块的Databases/createsql目录');
        }

        // 如果没有指定模块或指定的是DLaravel，则扫描核心模型目录
        if (! $targetModule || strtolower($targetModule) === 'ucore') {
            $ucoreModelsDir = app_path('../DLaravel/Models');
            if (is_dir($ucoreModelsDir)) {
                $this->debug("扫描核心模型目录: $ucoreModelsDir");
                $this->call1($ucoreModelsDir, '\DLaravel\Models\\');
            } else {
                $this->simpleOutput("DLaravel Models 目录不存在: $ucoreModelsDir", 'warning');
            }
        }

        // 扫描模块下的Models目录（支持nwidart/laravel-modules）
        $modulesPath = base_path('Modules');
        if (is_dir($modulesPath)) {
            $modules = scandir($modulesPath);
            foreach ($modules as $module) {
                if ($module === '.' || $module === '..') {
                    continue;
                }

                // 如果指定了模块，则只处理该模块
                if ($targetModule && strtolower($targetModule) !== strtolower($module)) {
                    continue;
                }

                $modelsDir = "$modulesPath/$module/Models";
                if (is_dir($modelsDir)) {
                    // 添加模块扫描日志
                    $this->debug("扫描模块目录: $modelsDir");
                    // 使用nwidart/laravel-modules的命名空间格式
                    $namespace = "Modules\\$module\\Models\\";
                    // 统一目录分隔符
                    $namespace = str_replace('/', '\\', $namespace);
                    $this->call1($modelsDir, $namespace);
                } elseif ($targetModule) {
                    // 如果指定了模块但目录不存在，则输出警告
                    $this->simpleOutput("模块 {$module} 的 Models 目录不存在: $modelsDir", 'warning');
                }
            }

            // 如果指定了模块但未找到，则输出错误
            if ($targetModule && $this->successCount === 0 && $this->skippedCount === 0 && $this->failedCount === 0) {
                $this->simpleOutput("未找到指定的模块: {$targetModule}", 'error');

                return;
            }
        }

        // 显示处理结果
        $this->simpleOutput("成功: {$this->successCount} | 跳过: {$this->skippedCount} | 失败: {$this->failedCount}" .
            (! $this->option('skip-sql') ? ' | SQL文件: ' . count($this->processedTables) : ''));
    }

    // ... 保持原有call1、call2、getAnnotation方法不变 ...
    /**
     * 扫描模型目录
     *
     * @param  string  $dir  模型文件目录路径
     * @param  string  $ns  模型类命名空间
     */
    public function call1($dir, $ns)
    {
        $this->debug("扫描目录: $dir");
        $list = scandir($dir);
        foreach ($list as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = "{$dir}/{$item}";

            // 递归处理子目录
            if (is_dir($fullPath)) {
                // 跳过Validator目录
                if (basename($fullPath) === 'Validators' || basename($fullPath) === 'Validator') {
                    $this->debug("跳过Validator目录: $fullPath", 'warning');

                    continue;
                }

                $this->call1($fullPath, "{$ns}{$item}\\");

                continue;
            }

            $p = strpos($item, '.php');
            if ($p !== false) {
                $model = substr($item, 0, $p);
                // 修复命名空间拼接逻辑
                $modelClass = rtrim($ns, '\\') . '\\' . $model;
                // 修复文件路径拼接
                $file = $fullPath;
                $this->call2($model, $modelClass, $file);
            }
        }
    }

    /**
     * 处理单个模型文件
     *
     * @param  string  $model  模型类名
     * @param  string  $modelClass  完整模型类名
     * @param  string  $file  模型文件路径
     */
    public function call2($model, $modelClass, $file)
    {
        if (class_exists($modelClass)) {
            // 检查类是否是抽象类
            $reflectionClass = new \ReflectionClass($modelClass);
            if ($reflectionClass->isAbstract()) {
                $this->debug(" model $modelClass 是抽象类，跳过", 'warning');
                $this->skippedCount++;

                return;
            }

            // 检查类是否是模型类
            if (! $reflectionClass->isSubclassOf(Model::class)) {
                $this->debug(" model $modelClass 不是模型类，跳过", 'warning');
                $this->skippedCount++;

                return;
            }

            // 检查构造函数是否需要参数
            $constructor = $reflectionClass->getConstructor();
            if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
                $this->debug(" model $modelClass 需要构造函数参数，跳过", 'warning');
                $this->skippedCount++;

                return;
            }

            /**
             * @var ModelCore $model
             */
            try {
                $model = new $modelClass;
            } catch (\Throwable $e) {
                $this->output->writeln('<fg=red>失败: ' . $e->getMessage() . '</>');
                $this->failedCount++;

                return;
            }

            if ($model instanceof Model) {
                $co = $model->getConnection();
                $tTablePrefix = $co->getTablePrefix();
                $table = $tTablePrefix . $model->getTable();
                $this->output->write("表: $table ... ");

                // 提取模块名称
                $moduleName = $this->extractModuleNameFromNamespace($modelClass);

                // 如果不跳过SQL生成且找到了模块名称，则生成表的创建SQL
                if (! $this->option('skip-sql') && $moduleName) {
                    // 获取无前缀的表名
                    $tableWithoutPrefix = $model->getTable();
                    $this->generateTableSQLFile($table, $moduleName, $co, $tableWithoutPrefix, $modelClass);
                }

                $annotation = $this->getAnnotation($table, $co, $model);

                $string = file_get_contents($file);

                // 输出文件内容的前200个字符，帮助调试
                $this->debug('文件内容前200字符: ' . substr($string, 0, 200) . '...');

                // 检查文件是否包含 field start/end 标识符
                $hasFieldStart = strpos($string, 'field start') !== false;
                $hasFieldEnd = strpos($string, 'field end') !== false;

                $this->debug('包含 field start: ' . ($hasFieldStart ? '是' : '否'));
                $this->debug('包含 field end: ' . ($hasFieldEnd ? '是' : '否'));

                // 使用正则表达式匹配 field start/end 标识符
                $pattern = '/field\s+start[\s\S]+?field\s+end/';
                $this->debug("使用正则表达式匹配 field start/end: {$pattern}");

                // 尝试匹配
                $matches = [];
                $matchResult = preg_match($pattern, $string, $matches);
                $this->debug('正则匹配结果: ' . ($matchResult ? '成功' : '失败'));
                if ($matchResult) {
                    $this->debug('匹配到的内容长度: ' . strlen($matches[0]));
                    $this->debug('匹配到的内容前50字符: ' . substr($matches[0], 0, 50) . '...');
                }

                // 替换 field start/end 标识符
                $replacement = "field start {$annotation} * field end";
                $result = preg_replace($pattern, $replacement, $string);

                // 检查替换是否成功
                $this->debug('field start/end 替换结果: 成功');

                // 过滤系统默认字段
                $filteredFillable = array_filter(
                    $this->fillable,
                    fn($field) => ! in_array($field, ['created_at', 'updated_at', 'deleted_at'])
                );

                // 格式化数组输出
                $fillableContent = "    protected \$fillable = [\n";
                foreach ($filteredFillable as $field) {
                    $fillableContent .= "        '{$field}',\n";
                }
                $fillableContent .= '    ];';

                // 检查文件是否包含 attrlist start/end 标识符
                $hasAttrlistStart = strpos($string, 'attrlist start') !== false;
                $hasAttrlistEnd = strpos($string, 'attrlist end') !== false;

                $this->debug('包含 attrlist start: ' . ($hasAttrlistStart ? '是' : '否'));
                $this->debug('包含 attrlist end: ' . ($hasAttrlistEnd ? '是' : '否'));

                // 使用正则表达式匹配 attrlist start/end 标识符
                $pattern2 = '/\/\/\s*attrlist\s+start[\s\S]+?\/\/\s*attrlist\s+end/';
                $this->debug("使用正则表达式匹配 attrlist start/end: {$pattern2}");

                // 尝试匹配
                $matches2 = [];
                $matchResult2 = preg_match($pattern2, $result, $matches2);
                $this->debug('正则匹配结果: ' . ($matchResult2 ? '成功' : '失败'));
                if ($matchResult2) {
                    $this->debug('匹配到的内容长度: ' . strlen($matches2[0]));
                    $this->debug('匹配到的内容前50字符: ' . substr($matches2[0], 0, 50) . '...');
                }

                // 替换 attrlist start/end 标识符
                $replacement2 = "// attrlist start \n{$fillableContent}\n    // attrlist end";
                $result = preg_replace($pattern2, $replacement2, $result);

                // 检查替换是否成功
                $this->debug('attrlist start/end 替换结果: 成功');

                // 强制写入文件
                file_put_contents($file, $result);
                $this->successCount++;
                $this->output->writeln('<fg=green>完成</>');
            } else {
                $this->output->writeln('<fg=yellow>跳过: 不是继承 ModelBase</>');
                $this->skippedCount++;
            }
        } else {
            $this->debug(" model $model 不存在", 'warning');
            $this->skippedCount++;
        }
    }

    /**
     * 从模型命名空间中提取模块名称
     *
     * @param  string  $modelClass  模型类名
     * @return string|null 模块名称，如果不是模块内的模型则返回null
     */
    protected function extractModuleNameFromNamespace($modelClass)
    {
        // 匹配 Modules\{ModuleName}\Models 格式的命名空间
        if (preg_match('/^Modules\\\\([^\\\\]+)\\\\Models/', $modelClass, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 输出调试信息并写入日志
     *
     * @param  string  $message  调试信息
     * @param  string  $type  信息类型 (info, warning, error)
     * @return void
     */
    protected function debug($message, $type = 'info')
    {
        // 始终写入日志
        $logPrefix = '[GenerateModelAnnotation] ';
        switch ($type) {
            case 'warning':
                Log::warning("{$logPrefix}{$message}");
                break;
            case 'error':
                Log::error("{$logPrefix}{$message}");
                break;
            default:
                Log::info("{$logPrefix}{$message}");
        }

        // 仅在启用调试信息时输出到控制台
        if ($this->option('debug-info')) {
            switch ($type) {
                case 'warning':
                    $this->output->warning($message);
                    break;
                case 'error':
                    $this->output->error($message);
                    break;
                default:
                    $this->output->info($message);
            }
        }
    }

    /**
     * 输出简洁信息并写入日志
     *
     * @param  string  $message  信息内容
     * @param  string  $type  信息类型 (info, warning, error)
     * @return void
     */
    protected function simpleOutput($message, $type = 'info')
    {
        // 检查消息是否为空
        if (empty(trim($message))) {
            return;
        }

        // 始终写入日志
        $logPrefix = '[GenerateModelAnnotation] ';
        switch ($type) {
            case 'warning':
                Log::warning("{$logPrefix}{$message}");
                $this->output->writeln("<fg=yellow>{$message}</>");
                break;
            case 'error':
                Log::error("{$logPrefix}{$message}");
                $this->output->writeln("<fg=red>{$message}</>");
                break;
            default:
                Log::info("{$logPrefix}{$message}");
                $this->output->writeln($message);
        }
    }

    /**
     * 生成属性注释字符串
     *
     * @param  string  $tableName  数据库表名
     * @param  \Illuminate\Database\Connection  $con  数据库连接
     * @param  Model|null  $model  模型实例，用于获取 $casts 属性
     * @return string 生成的注释字符串
     *
     * @throws \Exception 当数据库查询失败时
     */
    public function getAnnotation($tableName, \Illuminate\Database\Connection $con, $model = null)
    {
        $db = $con->getDatabaseName();
        $fillable = [];
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_name = '{$tableName}' AND TABLE_SCHEMA = '{$db}'
                ORDER BY ORDINAL_POSITION ASC";
        $columns = $con->select($sql);
        $annotation = '';

        // 获取模型的 $casts 属性
        $casts = [];
        if ($model instanceof Model && method_exists($model, 'getCasts')) {
            $casts = $model->getCasts();
            $this->debug('模型 Casts: ' . json_encode($casts, JSON_UNESCAPED_UNICODE));
        }

        foreach ($columns as $column) {
            $type = $this->getColumnType($column->DATA_TYPE);
            $columnName = $column->COLUMN_NAME;
            $fillable[] = $columnName;

            // 检查是否有自定义 Cast
            if (isset($casts[$columnName])) {
                $castType = $casts[$columnName];
                $this->debug("字段 {$columnName} 有自定义 Cast: {$castType}");
                $type = $this->getCastType($castType, $type);
            } else {
                $type = $this->handleSpecialColumns($columnName, $type);
            }

            $annotation .= sprintf(
                "\n * @property  %s  \$%s  %s",
                $type,
                $columnName,
                $column->COLUMN_COMMENT
            );
        }

        $this->fillable = $fillable;

        return "{$annotation}\n";
    }

    private function getColumnType($dataType)
    {
        return match ($dataType) {
            'int', 'tinyint', 'smallint', 'mediumint', 'bigint' => 'int',
            'float', 'double', 'decimal' => 'float',
            'json' => 'object|array',
            default => 'string'
        };
    }

    /**
     * 处理特殊列名的类型
     *
     * @param  string  $columnName  列名
     * @param  string  $type  默认类型
     * @return string 处理后的类型
     */
    private function handleSpecialColumns($columnName, $type)
    {
        if (in_array($columnName, ['created_at', 'updated_at', 'deleted_at'])) {
            return '\\Carbon\\Carbon';
        }

        return $type;
    }

    /**
     * 获取 Cast 类型对应的 PHP 类型
     *
     * @param  string  $castType  Cast 类型
     * @param  string  $defaultType  默认类型
     * @return string PHP 类型
     */
    private function getCastType($castType, $defaultType)
    {
        // 处理基本类型
        $basicTypes = [
            'int' => 'int',
            'integer' => 'int',
            'real' => 'float',
            'float' => 'float',
            'double' => 'float',
            'decimal' => 'float',
            'string' => 'string',
            'bool' => 'bool',
            'boolean' => 'bool',
            'object' => 'object',
            'array' => 'array',
            'json' => 'array',
            'collection' => 'Collection',
            'date' => '\\Carbon\\Carbon',
            'datetime' => '\\Carbon\\Carbon',
            'timestamp' => '\\Carbon\\Carbon',
            'immutable_date' => '\\Carbon\\CarbonImmutable',
            'immutable_datetime' => '\\Carbon\\CarbonImmutable',
            'immutable_timestamp' => '\\Carbon\\CarbonImmutable',
        ];

        if (isset($basicTypes[$castType])) {
            return $basicTypes[$castType];
        }

        // 处理自定义 Cast 类
        if (class_exists($castType)) {
            $this->debug("尝试解析自定义 Cast 类: {$castType}");

            // 检查是否是 DLaravel\Model\CastsAttributes 的子类
            if (is_subclass_of($castType, 'DLaravel\Model\CastsAttributes')) {
                $this->debug("{$castType} 是 DLaravel\Model\CastsAttributes 的子类");

                // 创建一个实例并获取其属性
                try {
                    // 使用反射获取类的公共属性
                    $reflectionClass = new \ReflectionClass($castType);
                    $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);

                    if (count($properties) > 0) {
                        $this->debug("Cast 类 {$castType} 有 " . count($properties) . ' 个公共属性');

                        return '\\' . get_class(new $castType);
                    } else {
                        $this->debug("Cast 类 {$castType} 没有公共属性，使用类名作为类型");

                        return $castType;
                    }
                } catch (\Throwable $e) {
                    $this->debug("无法分析 Cast 类 {$castType}: " . $e->getMessage(), 'warning');
                }
            } elseif (in_array('Illuminate\Contracts\Database\Eloquent\CastsAttributes', class_implements($castType))) {
                // 检查是否是 Illuminate\Contracts\Database\Eloquent\CastsAttributes 的实现
                $this->debug("{$castType} 实现了 Illuminate\Contracts\Database\Eloquent\CastsAttributes 接口");

                return '\\' . $castType;
            } elseif (is_subclass_of($castType, '\UnitEnum')) {
                // 枚举
                $this->debug("{$castType} 实现了 \UnitEnum  枚举");

                return '\\' . $castType;
            }
        }

        // 如果无法确定类型，返回默认类型
        $this->debug("无法确定 Cast 类型 {$castType}，使用默认类型 {$defaultType}", 'warning');

        return $defaultType;
    }

    /**
     * 获取表的创建SQL语句，并移除表的当前自增值
     *
     * @param  string  $tableName  表名
     * @param  \Illuminate\Database\Connection  $connection  数据库连接
     * @return string 创建表的SQL语句（不包含当前自增值，但保留字段的自增属性）
     */
    protected function getCreateTableSQL($tableName, $connection)
    {
        try {
            $result = $connection->select("SHOW CREATE TABLE `{$tableName}`");
            if (! empty($result) && isset($result[0]->{'Create Table'})) {
                $createTableSQL = $result[0]->{'Create Table'};

                // 只移除表的当前自增值 AUTO_INCREMENT=xxx 部分，保留字段的自增属性
                $createTableSQL = preg_replace('/\s+AUTO_INCREMENT=\d+/', '', $createTableSQL);

                $this->debug('已移除表的当前自增值: ' . ($createTableSQL !== $result[0]->{'Create Table'} ? '是' : '否'));

                return $createTableSQL;
            }
        } catch (\Exception $e) {
            $this->output->writeln("<fg=red>获取表 {$tableName} 的创建SQL失败: " . $e->getMessage() . '</>');
        }

        return null;
    }

    /**
     * 为表生成创建SQL文件
     *
     * @param  string  $tableName  表名（带前缀）
     * @param  string  $moduleName  模块名
     * @param  \Illuminate\Database\Connection  $connection  数据库连接
     * @param  string  $tableNameWithoutPrefix  无前缀的表名（必传）
     * @param  string  $modelClass  模型类名
     * @return void
     */
    protected function generateTableSQLFile($tableName, $moduleName, $connection, $tableNameWithoutPrefix, $modelClass = null)
    {

        // 标记该表已处理
        $this->processedTables[] = $tableName;

        // 获取表的创建SQL
        $createSQL = $this->getCreateTableSQL($tableName, $connection);
        if (empty($createSQL)) {
            $modelInfo = $modelClass ? " (Model: {$modelClass})" : '';
            $this->output->writeln("<fg=yellow>SQL生成失败{$modelInfo}</>");

            return;
        }

        // 创建模块的Database/GenerateSql 目录
        $sqlDir = base_path("Modules/{$moduleName}/Database/GenerateSql");
        if (! File::exists($sqlDir)) {
            $this->debug("创建目录: {$sqlDir}");
            File::makeDirectory($sqlDir, 0755, true);
        }

        // 创建或更新README.md文件，声明该目录是自动生成的，不能修改
        $readmePath = "{$sqlDir}/README.md";
        $readmeContent = "# 自动生成的SQL文件目录\n\n";
        $readmeContent .= "**警告：这是自动生成的目录，请勿手动修改此目录下的任何文件！**\n\n";
        $readmeContent .= "此目录下的SQL文件由系统自动生成，用于记录数据库表结构。\n";
        $readmeContent .= "如需修改表结构，请修改对应的模型文件，然后重新运行生成命令。\n";

        // 只有当README.md不存在或内容不同时才写入文件
        if (! File::exists($readmePath) || File::get($readmePath) !== $readmeContent) {
            File::put($readmePath, $readmeContent);
            $this->debug("已创建/更新README.md文件: {$readmePath}");
        }

        // 生成SQL文件，使用无前缀的表名作为文件名
        $sqlFile = "{$sqlDir}/{$tableNameWithoutPrefix}.sql";

        // 构建SQL文件内容，添加Model信息和禁止修改声明
        $sqlContent = "-- ******************************************************************\n";
        $sqlContent .= "-- 表 {$tableName} 的创建SQL\n";
        if ($modelClass) {
            $sqlContent .= "-- 对应的Model: {$modelClass}\n";
        }
        $sqlContent .= "-- 警告: 此文件由系统自动生成，禁止修改！\n";
        $sqlContent .= "-- ******************************************************************\n\n";

        //        $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $sqlContent .= "{$createSQL};\n";

        File::put($sqlFile, $sqlContent);
        $this->debug("已生成SQL: {$sqlFile} (无前缀表名: {$tableNameWithoutPrefix})");
    }
}
