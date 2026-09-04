<?php

namespace Modules\FeatureAi\Services;

use Modules\Application\Services\SystemLogService;
use Modules\FeatureAi\Enums\AiProviderType;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\OpenAILike;
use NeuronAI\Providers\AIProviderInterface;

/**
 * AI文本对话服务.
 *
 * 使用neuron-ai包进行文本对话.
 */
class AiChatService
{
    /**
     * 根据服务映射生成文本.
     *
     * @param string $prompt 提示词
     * @param string $serviceType 服务类型
     * @param string|null $serviceName 服务名字（可选，null表示默认）
     * @param array $parameters 额外参数（model等）
     * @return string 生成的文本
     *
     * @throws \InvalidArgumentException 服务映射不存在时抛出
     */
    public static function generateTextByService(
        string $prompt,
        string $serviceType,
        ?string $serviceName = null,
        array $parameters = []
    ): string {
        // 生成请求ID和开始时间
        $requestId = LlmApiLogService::generateRequestId();
        $startTime = microtime(true);

        // 获取供应商配置
        $providerConfig = AiProviderService::getProviderByService($serviceType, $serviceName);

        // 提取模型名称：优先使用 parameters['model']，否则从配置中查找默认 chat 模型
        $model = $parameters['model'] ?? null;
        if (!$model && !empty($providerConfig['models'])) {
            // 从关联模型中查找第一个启用的 chat 模型
            foreach ($providerConfig['models'] as $modelItem) {
                if (($modelItem['model_type'] ?? '') === 'chat' && ($modelItem['is_active'] ?? 1) === 1) {
                    $model = $modelItem['model_name'];
                    break;
                }
            }
        }

        if (!$model) {
            throw new \InvalidArgumentException(
                "未指定模型且供应商无可用chat模型: service_type={$serviceType}, service_name={$serviceName}"
            );
        }

        // 创建 Chat Provider 实例
        $chatProvider = self::createProvider(
            providerConfig: $providerConfig,
            sceneConfig: ['model' => $model],
            parameters: $parameters
        );

        // 添加服务类型信息到参数
        $parameters['service_type'] = $serviceType;
        $parameters['service_name'] = $serviceName;

        $response = null;
        $errorType = null;
        $errorMessage = null;
        $success = true;

        try {
            // 调用AI生成文本
            $response = $chatProvider->chat(new UserMessage($prompt));

            // 获取文本内容
            return $response->getContent();
        } catch (\Throwable $e) {
            $success = false;
            $errorType = get_class($e);
            $errorMessage = $e->getMessage();

            // 记录系统日志：AI API调用异常
            SystemLogService::exception('feature_ai', $e, [
                'service' => 'generateTextByService',
                'service_type' => $serviceType,
                'service_name' => $serviceName,
                'model' => $model ?? null,
                'provider_type' => $providerConfig['provider_type'] ?? null,
                'request_id' => $requestId,
            ]);

            throw $e;
        } finally {
            // 计算响应时间
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // 记录API调用日志
            LlmApiLogService::logApiCall(
                $requestId,
                $providerConfig,
                $prompt,
                $response,
                $durationMs,
                $parameters,
                $success,
                $errorType,
                $errorMessage
            );
        }
    }

    /**
     * 生成文本回复.
     *
     * @param string $prompt 用户输入文本
     * @param AiProviderType|string|null $providerType 提供商类型(默认OpenAI)
     * @param string|null $model 模型名称(默认gpt-4)
     *
     * @return string AI生成的回复文本
     *
     * @throws \Exception 文本生成失败时抛出
     */
    public static function generateText(
        string $prompt,
        AiProviderType|string|null $providerType = null,
        ?string $model = null
    ): string {
        // 生成请求ID和开始时间
        $requestId = LlmApiLogService::generateRequestId();
        $startTime = microtime(true);

        // 获取默认提供商
        $providerType = $providerType ?? AiProviderService::getDefaultProviderType();
        $type = is_string($providerType) ? AiProviderType::from($providerType) : $providerType;

        // 获取提供商配置
        $apiKey = AiProviderService::getApiKey($type);
        $model = $model ?? AiProviderService::getDefaultModel($type, 'chat');

        // 创建文本对话客户端
        $chatProvider = self::createChatProvider($type, $apiKey, $model);

        // 获取完整的提供商配置（用于日志记录）
        $providerConfig = AiProviderService::getProviderConfig($type);
        $providerConfig['model'] = $model;
        $parameters = [
            'service_type' => 'direct',
            'service_name' => $type->value,
        ];

        $response = null;
        $errorType = null;
        $errorMessage = null;
        $success = true;

        try {
            // 调用AI生成文本
            $response = $chatProvider->chat(new UserMessage($prompt));

            // 获取文本内容
            return $response->getContent();
        } catch (\Throwable $e) {
            $success = false;
            $errorType = get_class($e);
            $errorMessage = $e->getMessage();

            // 记录系统日志：AI API调用异常
            SystemLogService::exception('feature_ai', $e, [
                'service' => 'generateText',
                'provider_type' => $type->value ?? null,
                'model' => $model ?? null,
                'request_id' => $requestId,
            ]);

            throw $e;
        } finally {
            // 计算响应时间
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // 记录API调用日志
            LlmApiLogService::logApiCall(
                $requestId,
                $providerConfig,
                $prompt,
                $response,
                $durationMs,
                $parameters,
                $success,
                $errorType,
                $errorMessage
            );
        }
    }

    /**
     * 根据供应商配置创建 AI Provider 实例（通用工厂）
     *
     * 支持所有 neuron-ai 内置 Provider，根据 class 自动匹配构造模式：
     * - OpenAI 家族（OpenAI/Deepseek/Grok/ZAI/Mistral）: (key, model, parameters)
     * - Anthropic: (key, model, max_tokens, parameters)
     * - Gemini: (key, model, parameters)
     * - Ollama: (url, model, parameters)
     * - OpenAILike: (baseUri, key, model, parameters)
     *
     * @param array $providerConfig 供应商配置 [class, key, model, url?, base_url?, parameters?]
     * @param array $sceneConfig   场景配置 [model?, parameters?]
     * @param array $parameters    调用时额外参数（合并到 parameters）
     * @return AIProviderInterface
     *
     * @throws \InvalidArgumentException 不支持的供应商类
     */
    public static function createProvider(
        array $providerConfig,
        array $sceneConfig = [],
        array $parameters = []
    ): AIProviderInterface {
        $class = $providerConfig['class'];
        $key = $providerConfig['api_key'] ?? '';
        $model = $sceneConfig['model'] ?? $providerConfig['model'] ?? '';

        // 合并参数：供应商级 + 场景级 + 调用时
        $mergedParams = array_merge(
            $providerConfig['parameters'] ?? [],
            $sceneConfig['parameters'] ?? [],
            $parameters,
        );

        // 创建带全局配置的 HttpClient
        $httpClient = AiProviderService::createHttpClient();

        // Anthropic（必须在 OpenAI 之前检查，Anthropic 不是 OpenAI 子类）
        if (is_a($class, Anthropic::class, true)) {
            $provider = new $class(
                key: $key,
                model: $model,
                max_tokens: $mergedParams['max_tokens'] ?? 8192,
                parameters: $mergedParams,
                httpClient: $httpClient,
            );

            // 仅当数据库 endpoint 不为空且 Provider 未自定义 baseUri 时才覆盖
            // MiniMaxChatProvider 等自定义驱动已内置正确的 endpoint，无需覆盖
            if (!empty($providerConfig['api_endpoint']) && $class === Anthropic::class) {
                $customClient = $provider->getHttpClient()->withBaseUri(
                    rtrim($providerConfig['api_endpoint'], '/') . '/'
                );
                $provider->setHttpClient($customClient);
            }

            return $provider;
        }

        // Gemini（不是 OpenAI 子类）
        if (is_a($class, Gemini::class, true)) {
            return new $class(
                key: $key,
                model: $model,
                parameters: $mergedParams,
                httpClient: $httpClient,
            );
        }

        // Ollama（用 url 代替 key，不是 OpenAI 子类）
        if (is_a($class, Ollama::class, true)) {
            return new $class(
                url: $providerConfig['url'] ?? 'http://localhost:11434/api',
                model: $model,
                parameters: $mergedParams,
                httpClient: $httpClient,
            );
        }

        // OpenAILike（baseUri 作为构造参数，继承 OpenAI 但需要额外参数）
        if (is_a($class, OpenAILike::class, true)) {
            return new $class(
                baseUri: $providerConfig['base_url'] ?? '',
                key: $key,
                model: $model,
                parameters: $mergedParams,
                httpClient: $httpClient,
            );
        }

        // OpenAI 家族兜底：OpenAI, Deepseek, Grok, ZAI, Mistral 等（都继承 OpenAI）
        if (is_a($class, OpenAI::class, true)) {
            return new $class(
                key: $key,
                model: $model,
                parameters: $mergedParams,
                httpClient: $httpClient,
            );
        }

        throw new \InvalidArgumentException("不支持的供应商类: {$class}");
    }

    /**
     * 创建文本对话提供商实例（基于 AiProviderType 枚举）
     *
     * @param AiProviderType $type 提供商类型
     * @param string $apiKey API密钥
     * @param string $model 模型名称
     * @param array $parameters 额外参数
     *
     * @return AIProviderInterface 文本对话提供商
     *
     * @throws \InvalidArgumentException 不支持的提供商类型
     */
    protected static function createChatProvider(
        AiProviderType $type,
        string $apiKey,
        string $model,
        array $parameters = []
    ): AIProviderInterface {
        $providerConfig = AiProviderService::getProviderConfig($type);

        return self::createProvider(
            providerConfig: array_merge($providerConfig, [
                'class' => self::getProviderClass($type),
                'key' => $apiKey,
                'model' => $model,
            ]),
            parameters: $parameters,
        );
    }

    /**
     * 根据枚举获取 neuron-ai Provider 类名
     *
     * @param AiProviderType $type 提供商类型
     * @return string Provider 类名
     */
    private static function getProviderClass(AiProviderType $type): string
    {
        return match ($type) {
            AiProviderType::OPENAI => OpenAI::class,
            AiProviderType::CLAUDE => Anthropic::class,
            AiProviderType::GEMINI => Gemini::class,
            AiProviderType::MINIMAX => Anthropic::class,
            AiProviderType::DEEPSEEK => \NeuronAI\Providers\Deepseek\Deepseek::class,
            default => throw new \InvalidArgumentException("不支持的提供商: {$type->value}"),
        };
    }
}
