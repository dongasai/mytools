<?php

declare(strict_types=1);

namespace Modules\Debug\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\ABase\Models\SysRequestLog;

/**
 * 请求重放命令
 *
 * 根据 unid 重放请求日志中的请求，用于调试和测试
 *
 * @package Modules\Debug\Commands
 * php artisan debug:replay-request {request_id}
 */
class ReplayRequestCommand extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'debug:replay-request {request_id}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '根据 request_id 从数据库重放请求日志';

    /**
     * Guzzle HTTP Client 实例
     *
     * @var Client
     */
    private Client $httpClient;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => false, // 跳过 SSL 验证，用于测试环境
        ]);
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle(): int
    {
        $unid = $this->argument('request_id');

        // 从数据库读取请求日志
        $logRecord = SysRequestLog::where('unid', $unid)->first();

        if (!$logRecord) {
            $this->error("请求日志不存在: {$unid}");
            $this->info("请提供有效的 unid 参数");
            return self::FAILURE;
        }

        $this->info("========== 开始重放请求 ========== ");
        $this->newLine();

        // 显示原始请求信息
        $this->displayOriginalRequest($logRecord);
        $this->newLine();

        // 重放请求
        $replayResponse = $this->replayRequest($logRecord);

        if ($replayResponse === null) {
            $this->error("请求重放失败");
            return self::FAILURE;
        }

        // 显示重放响应信息
        $this->displayReplayResponse($replayResponse);
        $this->newLine();

        // 对比原始响应和重放响应
        $this->displayResponseComparison($logRecord, $replayResponse);

        $this->newLine();
        $this->info("========== 提示 ========== ");
        $this->line("查看详细日志: php artisan debug:request-log --request_id={$unid}");
        $this->line("查看完整响应: php artisan debug:request-log --request_id={$unid} --response");

        return self::SUCCESS;
    }

    /**
     * 显示原始请求信息
     *
     * @param SysRequestLog $logRecord 数据库日志记录
     */
    private function displayOriginalRequest(SysRequestLog $logRecord): void
    {
        $this->info("原始请求信息:");

        $headers = json_decode($logRecord->headers, true) ?? [];
        $query = json_decode($logRecord->query, true) ?? [];
        $post = json_decode($logRecord->post, true) ?? [];

        $data = [
            ['字段', '值'],
            ['UNID', $logRecord->unid],
            ['Method', $logRecord->method],
            ['Path', $logRecord->path],
            ['Host', $logRecord->host],
            ['IP', $logRecord->ipaddress],
            ['User ID', $logRecord->user_id],
            ['Headers 数量', count($headers)],
            ['Query 参数数量', count($query)],
            ['Post 数据数量', count($post)],
            ['运行时间', $logRecord->run_ms . 'ms'],
        ];

        $this->table(['字段', '值'], $data);
    }

    /**
     * 重放请求
     *
     * @param SysRequestLog $logRecord 数据库日志记录
     * @return array|null 重放响应数据
     */
    private function replayRequest(SysRequestLog $logRecord): ?array
    {
        $method = $logRecord->method;
        $url = 'http://' . $logRecord->host . '/' . $logRecord->path;

        $headers = json_decode($logRecord->headers, true) ?? [];
        $preparedHeaders = $this->prepareHeaders($headers);

        $query = json_decode($logRecord->query, true) ?? [];
        $post = json_decode($logRecord->post, true) ?? [];

        $this->info("正在重放请求到: {$url}");

        // 构建请求选项
        $options = [
            'headers' => $preparedHeaders,
            'query' => $query,
        ];

        // 根据请求方法添加 body
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $contentType = $preparedHeaders['Content-Type'] ?? $preparedHeaders['content-type'] ?? '';

            if (strpos($contentType, 'protobuf') !== false) {
                // Protobuf 请求，使用 raw body
                $options['body'] = is_string($post) ? $post : json_encode($post);
            } else {
                // 普通 HTTP 请求
                if (strpos($contentType, 'json') !== false) {
                    $options['json'] = empty($post) ? new \stdClass() : $post;
                } else {
                    if (!empty($post)) {
                        $options['form_params'] = $post;
                    }
                }
            }
        }

        try {
            // 发起请求
            $startTime = microtime(true);
            $response = $this->httpClient->request($method, $url, $options);

            $elapsedTime = round((microtime(true) - $startTime) * 1000, 2);

            // 构建响应数据
            $statusCode = $response->getStatusCode();
            $responseHeaders = $response->getHeaders();
            $responseBody = $response->getBody()->getContents();

            $replayResponse = [
                'status_code' => $statusCode,
                'headers' => $responseHeaders,
                'content' => $responseBody,
                'size' => strlen($responseBody),
                'elapsed_time' => $elapsedTime,
            ];

            $this->info("请求完成，耗时: {$elapsedTime}ms");

            return $replayResponse;

        } catch (\Exception $e) {
            $this->error("请求重放失败: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 准备请求头
     *
     * @param array $headers 原始请求头数组
     * @return array 处理后的请求头
     */
    private function prepareHeaders(array $headers): array
    {
        $preparedHeaders = [];

        // 过滤掉不应该传递的请求头
        $excludeHeaders = [
            'host',
            'content-length',
            'connection',
            'accept-encoding',
        ];

        foreach ($headers as $name => $values) {
            // 跳过排除的头（不区分大小写）
            if (in_array(strtolower($name), $excludeHeaders)) {
                continue;
            }

            // Guzzle 需要单个值或数组
            if (is_array($values)) {
                $preparedHeaders[$name] = $values[0];
            } else {
                $preparedHeaders[$name] = $values;
            }
        }

        return $preparedHeaders;
    }

    /**
     * 显示重放响应信息
     *
     * @param array $responseData 重放响应数据
     */
    private function displayReplayResponse(array $responseData): void
    {
        $this->info("重放响应信息:");

        $data = [
            ['字段', '值'],
            ['Status Code', $responseData['status_code']],
            ['Response Size', $responseData['size'] . ' bytes'],
            ['Headers 数量', count($responseData['headers'])],
            ['耗时', $responseData['elapsed_time'] . 'ms'],
        ];

        $this->table(['字段', '值'], $data);

        // 显示响应内容摘要
        $contentPreview = strlen($responseData['content']) > 200
            ? substr($responseData['content'], 0, 200) . '...'
            : $responseData['content'];

        $this->newLine();
        $this->info("响应内容预览:");
        $this->line($contentPreview);
    }

    /**
     * 显示响应对比
     *
     * @param SysRequestLog $logRecord 原始日志记录
     * @param array $replayResponse 重放响应数据
     */
    private function displayResponseComparison(SysRequestLog $logRecord, array $replayResponse): void
    {
        $this->info("========== 响应对比 ========== ");
        $this->newLine();

        $originalStatus = $logRecord->response_status ?? 'N/A';
        $originalSize = $logRecord->response_size ?? 0;
        $originalContent = $logRecord->response ?? '';

        $comparisonData = [
            ['对比项', '原始响应', '重放响应', '差异'],
            [
                'Status Code',
                $originalStatus,
                $replayResponse['status_code'],
                $originalStatus == $replayResponse['status_code'] ? '相同' : '不同'
            ],
            [
                'Content Size',
                $originalSize . ' bytes',
                $replayResponse['size'] . ' bytes',
                abs($originalSize - $replayResponse['size']) . ' bytes'
            ],
            [
                'Content Type',
                $logRecord->response_type ?? 'N/A',
                $replayResponse['headers']['Content-Type'][0] ?? 'N/A',
                '对比 Content-Type'
            ],
            [
                'Run Time',
                $logRecord->run_ms . 'ms',
                $replayResponse['elapsed_time'] . 'ms',
                round($replayResponse['elapsed_time'] - $logRecord->run_ms, 2) . 'ms'
            ],
        ];

        $this->table(['对比项', '原始响应', '重放响应', '差异'], $comparisonData);

        // 显示完整响应内容对比
        $this->newLine();
        $this->info("原始响应完整内容:");
        $this->line($originalContent);

        $this->newLine();
        $this->info("重放响应完整内容:");
        $this->line($replayResponse['content']);
    }
}