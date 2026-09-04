<?php

namespace Modules\FeatureAi\Services;

use Modules\Application\Services\SystemLogService;
use Modules\FeatureAi\Enums\AiProviderType;
use Modules\FeatureAi\Models\AiProvider;
use Modules\FeatureAi\Models\AiServiceMapping;

/**
 * AI提供商服务.
 *
 * 管理AI提供商配置和客户端实例.
 * 数据库优先：优先从 ai_providers 表读取，无记录时回退到文件配置.
 */
class AiProviderService
{
    /**
     * 根据服务类型和服务名字获取提供商配置.
     *
     * 匹配规则：
     * 1. 优先精确匹配 service_type + service_name
     * 2. 无精确匹配时回退到 service_type 的默认供应商（service_name为空）
     *
     * @param string $serviceType 服务类型，如a/b等
     * @param string|null $serviceName 服务名字，如name1/name2等，null表示默认
     *
     * @return array 提供商配置数组
     *
     * @throws \InvalidArgumentException 无匹配映射或提供商不存在时抛出
     */
    public static function getProviderByService(string $serviceType, ?string $serviceName = null): array
    {
        $serviceName = $serviceName ?? '';

        // 优先精确匹配 service_type + service_name
        $mapping = AiServiceMapping::where('service_type', $serviceType)
            ->where('service_name', $serviceName)
            ->where('is_active', 1)
            ->with('provider')
            ->first();

        // 无精确匹配且 serviceName 不为空，尝试回退到默认供应商
        if (!$mapping && $serviceName !== '') {
            $mapping = AiServiceMapping::where('service_type', $serviceType)
                ->where('service_name', '')
                ->where('is_active', 1)
                ->with('provider')
                ->first();
        }

        if (!$mapping || !$mapping->provider) {
            SystemLogService::error('feature_ai', 'AI服务映射不存在', [
                'service_type' => $serviceType,
                'service_name' => $serviceName,
            ]);

            throw new \InvalidArgumentException(
                "AI服务映射不存在: service_type={$serviceType}, service_name={$serviceName}"
            );
        }

        return self::buildProviderConfig($mapping->provider);
    }

    /**
     * 根据提供商ID获取配置.
     *
     * @param int $providerId 提供商ID
     *
     * @return array 提供商配置数组
     *
     * @throws \InvalidArgumentException 提供商不存在时抛出
     */
    public static function getProviderById(int $providerId): array
    {
        $provider = AiProvider::where('id', $providerId)
            ->where('is_active', 1)
            ->with('models')
            ->first();

        if (!$provider) {
            SystemLogService::error('feature_ai', 'AI提供商不存在或已禁用', [
                'provider_id' => $providerId,
            ]);

            throw new \InvalidArgumentException("AI提供商不存在或已禁用: provider_id={$providerId}");
        }

        return self::buildProviderConfig($provider);
    }

    /**
     * 根据提供商类型和名称获取配置.
     *
     * @param string $providerType 提供商类型，如openai/deepseek等
     * @param string $providerName 提供商名称，如one/deepseek2等
     *
     * @return array 提供商配置数组
     *
     * @throws \InvalidArgumentException 提供商不存在时抛出
     */
    public static function getProviderByName(string $providerType, string $providerName): array
    {
        $provider = AiProvider::where('provider_type', $providerType)
            ->where('provider_name', $providerName)
            ->where('is_active', 1)
            ->with('models')
            ->first();

        if (!$provider) {
            SystemLogService::error('feature_ai', 'AI提供商不存在或已禁用', [
                'provider_type' => $providerType,
                'provider_name' => $providerName,
            ]);

            throw new \InvalidArgumentException(
                "AI提供商不存在或已禁用: provider_type={$providerType}, provider_name={$providerName}"
            );
        }

        return self::buildProviderConfig($provider);
    }

    /**
     * 获取提供商配置.
     *
     * 数据库优先：优先从 ai_providers 表读取，无记录时回退到文件配置.
     *
     * 数据库优先：优先从 ai_providers 表读取，无记录时回退到文件配置.
     * 查询该类型下 priority 最高且激活的提供商。
     *
     * @param AiProviderType|string $providerType 提供商类型
     *
     * @return array 提供商配置数组
     *
     * @throws \InvalidArgumentException 提供商不存在时抛出
     */
    public static function getProviderConfig(AiProviderType|string $providerType): array
    {
        $type = is_string($providerType) ? AiProviderType::from($providerType) : $providerType;
        $typeValue = $type->value;

        // 从数据库查询该类型的默认提供商（provider_name为空）
        $provider = AiProvider::where('provider_type', $typeValue)
            ->where('provider_name', '')
            ->where('is_active', 1)
            ->with('models')
            ->orderByDesc('priority')
            ->first();
        // 从数据库查询该类型下 priority 最高的激活提供商
        $provider = AiProvider::where('provider_type', $typeValue)
            ->where('is_active', 1)
            ->with('models')
            ->orderByDesc('priority')
            ->first();

        if (!$provider) {
            SystemLogService::error('feature_ai', 'AI提供商配置不存在', [
                'provider_type' => $typeValue,
            ]);

            throw new \InvalidArgumentException(
                "AI提供商配置不存在: provider_type={$typeValue}"
            );
        }

        return self::buildProviderConfig($provider);
    }

    /**
     * 获取默认提供商类型.
     *
     * 从数据库查询 priority 最高的启用的 provider，无记录时回退到文件配置.
     *
     * @return AiProviderType 默认提供商类型
     *
     * @throws \InvalidArgumentException 无可用提供商时抛出
     */
    public static function getDefaultProviderType(): AiProviderType
    {
        // 从数据库查询 priority 最高的启用提供商
        $provider = AiProvider::where('is_active', 1)
            ->orderByDesc('priority')
            ->first();

        if (!$provider) {
            SystemLogService::error('feature_ai', '默认AI提供商未配置', [
                'reason' => '数据库无可用提供商',
            ]);

            throw new \InvalidArgumentException('默认AI提供商未配置: 数据库无可用提供商');
        }

        return AiProviderType::from($provider->provider_type);
    }

    /**
     * 获取提供商API密钥.
     *
     * @param AiProviderType|string $providerType 提供商类型
     *
     * @return string API密钥
     *
     * @throws \InvalidArgumentException API密钥未配置时抛出
     */
    public static function getApiKey(AiProviderType|string $providerType): string
    {
        $config = self::getProviderConfig($providerType);

        if (empty($config['api_key'])) {
            $type = is_string($providerType) ? $providerType : $providerType->value;
            SystemLogService::error('feature_ai', 'AI提供商API密钥未配置', [
                'provider_type' => $type,
            ]);

            throw new \InvalidArgumentException("AI提供商API密钥未配置: {$type}");
        }

        return $config['api_key'];
    }

    /**
     * 获取提供商默认模型.
     *
     * 从 ai_provider_models 表查询，不再从 config_json 读取 allowed_models.
     *
     * @param AiProviderType|string $providerType 提供商类型
     * @param string $modelType 模型类型(chat/image)
     *
     * @return string 默认模型名称
     *
     * @throws \InvalidArgumentException 无可用模型时抛出
     */
    public static function getDefaultModel(AiProviderType|string $providerType, string $modelType = 'chat'): string
    {
        $config = self::getProviderConfig($providerType);

        // 优先使用 config_json 中的 default_chat_model/default_image_model
        $defaultKey = "default_{$modelType}_model";
        if (!empty($config[$defaultKey])) {
            return $config[$defaultKey];
        }

        // 从数据库关联的 models 中查找该类型的第一个启用模型
        if (!empty($config['models'])) {
            foreach ($config['models'] as $model) {
                if (($model['model_type'] ?? '') === $modelType && ($model['is_active'] ?? 1) === 1) {
                    return $model['model_name'];
                }
            }
        }

        // 回退到文件配置中的模型列表
        if (!empty($config['models'][$modelType][0])) {
            return $config['models'][$modelType][0];
        }

        $type = is_string($providerType) ? $providerType : $providerType->value;
        SystemLogService::error('feature_ai', 'AI提供商无可用模型', [
            'provider_type' => $type,
            'model_type' => $modelType,
        ]);

        throw new \InvalidArgumentException(
            "AI提供商无可用模型: provider_type={$type}, model_type={$modelType}"
        );
    }

    /**
     * 检查提供商是否已配置.
     *
     * 从数据库查询启用的提供商是否存在.
     *
     * @param AiProviderType|string $providerType 提供商类型
     *
     * @return bool 是否已配置
     */
    public static function isProviderConfigured(AiProviderType|string $providerType): bool
    {
        try {
            $config = self::getProviderConfig($providerType);

            return !empty($config['api_key']);
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    /**
     * 获取所有已配置的提供商列表.
     *
     * 从数据库查询所有启用的提供商.
     *
     * @return array 已配置的提供商列表
     */
    public static function getConfiguredProviders(): array
    {
        // 从数据库查询所有启用的提供商
        $providers = AiProvider::where('is_active', 1)
            ->with('models')
            ->orderByDesc('priority')
            ->get();

        $result = [];
        foreach ($providers as $provider) {
            $result[] = self::buildProviderConfig($provider);
        }

        return $result;
    }

    /**
     * 创建带全局配置的 HttpClient 实例.
     *
     * 自动注入 UserAgent 和其他全局请求头。
     *
     * @return \NeuronAI\HttpClient\HttpClientInterface
     */
    public static function createHttpClient(): \NeuronAI\HttpClient\HttpClientInterface
    {
        $headers = [];

        // 从 config/ai.php 读取 UserAgent
        $userAgent = config('ai.user_agent');
        if (!empty($userAgent)) {
            $headers['User-Agent'] = $userAgent;
        }

        // 可选：支持其他全局请求头
        $globalHeaders = config('ai.headers', []);
        if (is_array($globalHeaders)) {
            $headers = array_merge($headers, $globalHeaders);
        }

        return new \NeuronAI\HttpClient\GuzzleHttpClient(
            customHeaders: $headers
        );
    }

    /**
     * 构建提供商配置数组.
     *
     * 将 AiProvider Model 转换为数组格式（兼容旧接口）.
     *
     * @param AiProvider $provider 提供商模型实例
     *
     * @return array 提供商配置数组
     *
     * @throws \InvalidArgumentException 提供商类型无效时抛出
     */
    private static function buildProviderConfig(AiProvider $provider): array
    {
        try {
            $type = AiProviderType::from($provider->provider_type);
            $class = $type->getProviderClass();
        } catch (\ValueError $e) {
            SystemLogService::error('feature_ai', '无效的AI提供商类型', [
                'provider_type' => $provider->provider_type,
                'provider_id' => $provider->id,
            ]);

            throw new \InvalidArgumentException(
                "无效的AI提供商类型: {$provider->provider_type}"
            );
        }

        // 从关联的 models 构建模型列表
        $models = [];
        if ($provider->models) {
            foreach ($provider->models as $model) {
                $models[] = [
                    'id' => $model->id,
                    'model_name' => $model->model_name,
                    'model_type' => $model->model_type,
                    'max_tokens' => $model->max_tokens,
                    'is_active' => $model->is_active,
                ];
            }
        }

        // 处理 config_json 字段（兼容字符串 "[]" 的情况）
        $configJson = $provider->config_json ?? [];
        if (is_string($configJson)) {
            $decoded = json_decode($configJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $configJson = $decoded;
            } else {
                $configJson = [];
            }
        }

        return [
            'id' => $provider->id,
            'provider_type' => $provider->provider_type,
            'provider_name' => $provider->provider_name,
            'api_key' => $provider->api_key,
            'api_endpoint' => $provider->api_endpoint ?? '',
            'is_active' => $provider->is_active,
            'priority' => $provider->priority,
            'config_json' => $configJson,
            'class' => $class,
            'models' => $models,
        ];
    }
}
