<?php

namespace Modules\FeatureAi\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FeatureAi\Models\AiProvider;
use Modules\FeatureAi\Models\AiProviderModel;

/**
 * AI提供商示例数据Seeder.
 *
 * 为每种支持的 Provider 类型创建示例数据
 */
class AiProviderSeeder extends Seeder
{
    /**
     * Provider 默认配置.
     */
    protected array $providers = [
        'openai' => [
            'name' => 'OpenAI 官方',
            'endpoint' => 'https://api.openai.com/v1',
            'chat_models' => ['gpt-4o', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'image_models' => ['dall-e-3', 'dall-e-2'],
        ],
        'claude' => [
            'name' => 'Anthropic Claude',
            'endpoint' => 'https://api.anthropic.com',
            'chat_models' => ['claude-3-5-sonnet-20241022', 'claude-3-opus-20240229', 'claude-3-sonnet-20240229'],
            'image_models' => [],
        ],
        'gemini' => [
            'name' => 'Google Gemini',
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta',
            'chat_models' => ['gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-pro'],
            'image_models' => [],
        ],
        'deepseek' => [
            'name' => 'Deepseek',
            'endpoint' => 'https://api.deepseek.com',
            'chat_models' => ['deepseek-chat', 'deepseek-coder'],
            'image_models' => [],
        ],
        'zai' => [
            'name' => '智谱AI GLM',
            'endpoint' => 'https://api.z.ai/api/paas/v4',
            'chat_models' => ['glm-4', 'glm-4-air', 'glm-4-flash'],
            'image_models' => [],
        ],
        'minimax' => [
            'name' => 'MiniMax',
            'endpoint' => 'https://api.minimax.io/v1',
            'chat_models' => ['abab6.5-chat', 'abab5.5-chat'],
            'image_models' => [],
        ],
        'mistral' => [
            'name' => 'Mistral AI',
            'endpoint' => 'https://api.mistral.ai/v1',
            'chat_models' => ['mistral-large-latest', 'mistral-medium', 'mistral-small-latest'],
            'image_models' => [],
        ],
        'ollama' => [
            'name' => 'Ollama (本地)',
            'endpoint' => 'http://localhost:11434',
            'chat_models' => ['llama3.1', 'llama3', 'mistral', 'codellama'],
            'image_models' => [],
        ],
        'cohere' => [
            'name' => 'Cohere',
            'endpoint' => 'https://api.cohere.ai/v1',
            'chat_models' => ['command-r-plus', 'command-r', 'command-light'],
            'image_models' => [],
        ],
        'xai' => [
            'name' => 'X.AI Grok',
            'endpoint' => 'https://api.x.ai/v1',
            'chat_models' => ['grok-beta'],
            'image_models' => [],
        ],
        'aws' => [
            'name' => 'AWS Bedrock',
            'endpoint' => 'https://bedrock-runtime.us-east-1.amazonaws.com',
            'chat_models' => ['anthropic.claude-3-sonnet', 'anthropic.claude-v2'],
            'image_models' => [],
        ],
        'huggingface' => [
            'name' => 'HuggingFace',
            'endpoint' => 'https://api-inference.huggingface.co/models',
            'chat_models' => ['mistralai/Mistral-7B-Instruct-v0.2', 'meta-llama/Llama-2-7b-chat-hf'],
            'image_models' => [],
        ],
        'elevenlabs' => [
            'name' => 'ElevenLabs (语音)',
            'endpoint' => 'https://api.elevenlabs.io/v1',
            'chat_models' => [],
            'image_models' => [],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->providers as $providerType => $config) {
            $this->createProvider($providerType, $config);
        }
    }

    /**
     * 创建提供商及其模型.
     *
     * @param string $providerType 提供商类型
     * @param array $config 提供商配置
     */
    protected function createProvider(string $providerType, array $config): void
    {
        // 幂等性创建提供商
        $provider = AiProvider::firstOrCreate(
            [
                'provider_type' => $providerType,
                'provider_name' => $config['name'],
            ],
            [
                'api_key' => '', // 需要用户手动填写
                'api_endpoint' => $config['endpoint'],
                'is_active' => 0, // 默认禁用，需要用户配置密钥后启用
                'priority' => 0,
                'config_json' => [],
            ]
        );

        // 创建聊天模型
        foreach ($config['chat_models'] as $modelName) {
            $this->createProviderModel($provider, $modelName, 'chat');
        }

        // 创建图像模型
        foreach ($config['image_models'] as $modelName) {
            $this->createProviderModel($provider, $modelName, 'image');
        }

        $this->command->info("创建 Provider: {$config['name']} ({$providerType})");
    }

    /**
     * 创建提供商模型.
     *
     * @param AiProvider $provider 提供商实例
     * @param string $modelName 模型名称
     * @param string $modelType 模型类型（chat/image）
     */
    protected function createProviderModel(AiProvider $provider, string $modelName, string $modelType): void
    {
        $provider->models()->firstOrCreate(
            [
                'model_name' => $modelName,
                'model_type' => $modelType,
            ],
            [
                'max_tokens' => 4096,
                'is_active' => 1,
            ]
        );
    }
}