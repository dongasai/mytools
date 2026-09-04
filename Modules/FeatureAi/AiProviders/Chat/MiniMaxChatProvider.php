<?php

namespace Modules\FeatureAi\AiProviders\Chat;

use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\HttpClient\GuzzleHttpClient;

/**
 * MiniMax Chat Provider（使用Anthropic API兼容格式）
 *
 * MiniMax提供Anthropic API兼容接口，支持完整的Function Calling功能
 * 接口地址：https://api.minimaxi.com/anthropic
 */
class MiniMaxChatProvider extends Anthropic
{
    /**
     * MiniMax Anthropic兼容接口基础URL（需要带/v1/后缀）
     */
    protected string $baseUri = 'https://api.minimaxi.com/anthropic/v1/';

    /**
     * 构造函数
     */
    public function __construct(
        protected string $key,
        protected string $model = 'MiniMax-M2.7',  // 最新最强模型，204,800上下文
        protected array $parameters = [],
    ) {
        // 先设置baseUri，再调用父类构造函数
        parent::__construct(
            key: $key,
            model: $model,
            parameters: $parameters,
            // 自定义 HTTP 客户端超时时间为 180 秒（章节生成需要较长响应时间）
            httpClient: new GuzzleHttpClient(timeout: 180.0),
        );

        // 父类构造函数会用$this->baseUri初始化httpClient，这里已经生效
    }
}