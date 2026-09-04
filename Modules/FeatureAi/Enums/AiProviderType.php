<?php

namespace Modules\FeatureAi\Enums;

use NeuronAI\Providers\AWS\BedrockRuntime;
use NeuronAI\Providers\ElevenLabs\ElevenLabsSpeechToText;
use NeuronAI\Providers\XAI\Grok;

/**
 * AI提供商类型.
 */
enum AiProviderType: string
{
    /**
     * OpenAI.
     */
    case OPENAI = 'openai';

    /**
     * Claude (Anthropic).
     */
    case CLAUDE = 'claude';

    /**
     * Gemini.
     */
    case GEMINI = 'gemini';

    /**
     * Deepseek.
     */
    case DEEPSEEK = 'deepseek';

    /**
     * ZAI (智谱AI GLM).
     */
    case ZAI = 'zai';

    /**
     * MiniMax.
     *
     * 注意：MiniMax 使用 Anthropic 兼容 API
     * API Endpoint: https://api.minimaxi.com/anthropic
     * Driver: Anthropic
     */
    case MINIMAX = 'minimax';

    /**
     * Mistral.
     */
    case MISTRAL = 'mistral';

    /**
     * Ollama (本地模型).
     */
    case OLLAMA = 'ollama';

    /**
     * Cohere.
     */
    case COHERE = 'cohere';

    /**
     * XAI (Grok).
     */
    case XAI = 'xai';

    /**
     * AWS Bedrock.
     */
    case AWS = 'aws';

    /**
     * HuggingFace.
     */
    case HUGGINGFACE = 'huggingface';

    /**
     * ElevenLabs (语音合成).
     */
    case ELEVENLABS = 'elevenlabs';

    /**
     * 自定义提供商.
     */
    case CUSTOM = 'custom';

    /**
     * 获取提供商名称.
     */
    public function getName(): string
    {
        return match ($this) {
            self::OPENAI => 'OpenAI',
            self::CLAUDE => 'Claude',
            self::GEMINI => 'Gemini',
            self::DEEPSEEK => 'Deepseek',
            self::ZAI => '智谱AI (GLM)',
            self::MINIMAX => 'MiniMax(Anthropic)',
            self::MISTRAL => 'Mistral',
            self::OLLAMA => 'Ollama',
            self::COHERE => 'Cohere',
            self::XAI => 'X.AI (Grok)',
            self::AWS => 'AWS Bedrock',
            self::HUGGINGFACE => 'HuggingFace',
            self::ELEVENLABS => 'ElevenLabs',
            self::CUSTOM => '自定义',
        };
    }

    /**
     * 获取 Provider 类名.
     *
     * 返回 NeuronAI 对应的 Provider 类完整命名空间。
     * 优先使用 FeatureAi 模块封装的驱动类。
     *
     * @return string Provider 类名，自定义类型返回空字符串
     */
    public function getProviderClass(): string
    {
        return match ($this) {
            self::OPENAI => \NeuronAI\Providers\OpenAI\OpenAI::class,
            self::CLAUDE => \NeuronAI\Providers\Anthropic\Anthropic::class,
            self::GEMINI => \NeuronAI\Providers\Gemini\Gemini::class,
            self::DEEPSEEK => \NeuronAI\Providers\Deepseek\Deepseek::class,
            self::ZAI => \NeuronAI\Providers\ZAI\ZAI::class,
            // MiniMax 使用独立驱动（Anthropic 兼容 API）
            self::MINIMAX => \Modules\FeatureAi\AiProviders\Chat\MiniMaxChatProvider::class,
            self::MISTRAL => \NeuronAI\Providers\Mistral\Mistral::class,
            self::OLLAMA => \NeuronAI\Providers\Ollama\Ollama::class,
            self::COHERE => \NeuronAI\Providers\Cohere\Cohere::class,
            self::XAI => \NeuronAI\Providers\XAI\Grok::class,
            self::AWS => \NeuronAI\Providers\AWS\BedrockRuntime::class,
            self::HUGGINGFACE => \NeuronAI\Providers\HuggingFace\HuggingFace::class,
            self::ELEVENLABS => \NeuronAI\Providers\ElevenLabs\ElevenLabsSpeechToText::class,
            self::CUSTOM => '', // 自定义类型无固定类
        };
    }
}
