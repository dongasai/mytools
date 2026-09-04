<?php

namespace Modules\FeatureAi\AiProviders\Image;

use Generator;
use Modules\Application\Services\SystemLogService;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ImageContent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Exceptions\HttpException;
use NeuronAI\Exceptions\ProviderException;
use NeuronAI\HttpClient\GuzzleHttpClient;
use NeuronAI\HttpClient\HasHttpClient;
use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\HttpClient\HttpRequest;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Providers\ToolMapperInterface;

use function end;

/**
 * MiniMax图片生成提供商.
 *
 * 实现MiniMax的图片生成API集成.
 */
class MiniMaxImageProvider implements AIProviderInterface
{
    use HasHttpClient;

    /**
     * MiniMax API基础URL.
     */
    protected string $baseUri = 'https://api.minimaxi.com/v1';

    /**
     * 系统指令.
     */
    protected ?string $system = null;

    /**
     * 构造函数.
     *
     * @param string $key API密钥
     * @param string $model 模型名称(image-01/image-01-live)
     * @param string $aspectRatio 图片比例(默认1:1)
     * @param array $parameters 其他参数
     * @param HttpClientInterface|null $httpClient HTTP客户端
     */
    public function __construct(
        protected string $key,
        protected string $model = 'image-01',
        protected string $aspectRatio = '1:1',
        protected array $parameters = [],
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = ($httpClient ?? new GuzzleHttpClient())
            ->withBaseUri($this->baseUri)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->key,
            ]);
    }

    /**
     * 设置系统提示.
     */
    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->system = $prompt;
        return $this;
    }

    /**
     * 生成图片(同步).
     *
     * @param Message ...$messages 用户消息
     *
     * @return Message 包含生成图片的消息
     *
     * @throws HttpException HTTP请求失败
     * @throws ProviderException 图片生成失败
     */
    public function chat(Message ...$messages): Message
    {
        $message = end($messages);

        // 准备请求参数
        $body = [
            'model' => $this->model,
            'prompt' => $message->getContent(),
            'aspect_ratio' => $this->aspectRatio,
            'response_format' => 'base64',
            'n' => $this->parameters['n'] ?? 1,
            'prompt_optimizer' => $this->parameters['prompt_optimizer'] ?? false,
        ];

        // 如果设置了seed参数
        if (isset($this->parameters['seed'])) {
            $body['seed'] = $this->parameters['seed'];
        }

        // 发送请求
        $response = $this->httpClient->request(
            HttpRequest::post(
                uri: 'image_generation',
                body: $body
            )
        )->json();

        // 记录请求和响应详情（用于调试）
        \Illuminate\Support\Facades\Log::debug('MiniMax图片生成API响应', [
            'request_params' => $body,
            'response_keys' => array_keys($response),
            'base_resp' => $response['base_resp'] ?? null,
            'image_count' => count($response['data']['image_base64'] ?? []),
            'data_keys' => array_keys($response['data'] ?? []),
        ]);

        // 检查错误
        if (isset($response['base_resp']['status_code']) && $response['base_resp']['status_code'] !== 0) {
            // 记录系统日志：API调用错误
            SystemLogService::apiError('feature_ai', 'MiniMax图片生成API调用失败', [
                'status_code' => $response['base_resp']['status_code'] ?? null,
                'status_msg' => $response['base_resp']['status_msg'] ?? '',
                'model' => $this->model,
                'aspect_ratio' => $this->aspectRatio,
            ]);

            throw new ProviderException(
                $response['base_resp']['status_msg'] ?? 'MiniMax图片生成失败'
            );
        }

        // 获取base64图片数据数组
        $imageBase64Array = $response['data']['image_base64'] ?? [];

        if (empty($imageBase64Array)) {
            // 记录系统日志：API返回数据为空
            SystemLogService::apiError('feature_ai', 'MiniMax图片生成返回数据为空', [
                'model' => $this->model,
                'aspect_ratio' => $this->aspectRatio,
                'response_keys' => array_keys($response),
            ]);

            throw new ProviderException('MiniMax返回的图片数据为空');
        }

        // 统一处理：总是设置metadata，保持一致性
        $result = new AssistantMessage(
            new ImageContent(
                $imageBase64Array[0],
                SourceType::BASE64,
                'image/jpeg'
            )
        );

        // 存储所有图片数据到消息metadata（包括单张图片的情况）
        $result->setMetadata([
            'images' => $imageBase64Array,
            'count' => count($imageBase64Array),
        ]);

        $result->setUsage(new Usage(0, 0));

        return $result;
    }

    /**
     * 流式生成图片(MiniMax暂不支持).
     *
     * @param Message ...$messages 用户消息
     *
     * @return Generator
     *
     * @throws ProviderException MiniMax不支持流式
     */
    public function stream(Message ...$messages): Generator
    {
        throw new ProviderException('MiniMax图片生成不支持流式模式');
    }

    /**
     * 结构化输出(MiniMax不支持).
     *
     * @param array|Message $messages 消息
     * @param string $class 类名
     * @param array $response_schema 响应schema
     *
     * @return Message
     *
     * @throws ProviderException MiniMax不支持
     */
    public function structured(array|Message $messages, string $class, array $response_schema): Message
    {
        throw new ProviderException('MiniMax图片生成不支持结构化输出');
    }

    /**
     * 消息映射器(MiniMax不需要).
     *
     * @return MessageMapperInterface
     *
     * @throws ProviderException 不支持
     */
    public function messageMapper(): MessageMapperInterface
    {
        throw new ProviderException('MiniMax图片生成不支持消息映射器');
    }

    /**
     * 工具映射器(MiniMax不需要).
     *
     * @return ToolMapperInterface
     *
     * @throws ProviderException 不支持
     */
    public function toolPayloadMapper(): ToolMapperInterface
    {
        throw new ProviderException('MiniMax图片生成不支持工具映射器');
    }

    /**
     * 设置工具(MiniMax不支持).
     *
     * @param array $tools 工具列表
     *
     * @return AIProviderInterface
     */
    public function setTools(array $tools): AIProviderInterface
    {
        return $this;
    }

    /**
     * 转换aspect_ratio为size格式.
     *
     * @param string $aspectRatio 比例(如16:9)
     *
     * @return string 尺寸(如1792x1024)
     */
    public static function convertAspectRatioToSize(string $aspectRatio): string
    {
        // MiniMax支持的aspect_ratio转换为size
        $map = [
            '1:1' => '1024x1024',
            '16:9' => '1792x1024',
            '9:16' => '1024x1792',
            '4:3' => '1365x1024',
            '3:4' => '1024x1365',
            '3:2' => '1536x1024',
            '2:3' => '1024x1536',
            '21:9' => '1792x768',
        ];

        return $map[$aspectRatio] ?? '1024x1024';
    }

    /**
     * 转换size为aspect_ratio格式.
     *
     * @param string $size 尺寸(如1024x1024)
     *
     * @return string 比例(如1:1)
     */
    public static function convertSizeToAspectRatio(string $size): string
    {
        // size转换为aspect_ratio
        $map = [
            '1024x1024' => '1:1',
            '1792x1024' => '16:9',
            '1024x1792' => '9:16',
            '1365x1024' => '4:3',
            '1024x1365' => '3:4',
            '1536x1024' => '3:2',
            '1024x1536' => '2:3',
            '1792x768' => '21:9',
            // 兼容其他尺寸
            '512x512' => '1:1',
            '256x256' => '1:1',
        ];

        return $map[$size] ?? '1:1';
    }
}