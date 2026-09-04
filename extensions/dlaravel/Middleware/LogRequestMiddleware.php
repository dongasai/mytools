<?php

namespace DLaravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


class LogRequestMiddleware
{
    /**
     * 处理传入的请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // 记录请求开始
        Log::debug('Request started', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            "query" => $request->query(),
            'request_id' => $request->header('X-Request-ID', uniqid()),
        ]);

        $response = $next($request);

        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;

        // 准备响应数据
        $responseData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms', // 转换为毫秒
            'memory_used' => $this->formatBytes($memoryUsed),
            'peak_memory' => $this->formatBytes(memory_get_peak_usage(true)),
            'user_id' => auth()->id(),
        ];

        // 如果响应是JSON格式，记录响应内容
        if ($response instanceof \Illuminate\Http\JsonResponse ||
            $response->headers->get('Content-Type') === 'application/json') {
            $content = $response->getContent();
            if ($content) {
                $decodedContent = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // 限制响应内容大小，避免日志过大
                    $responseData['response_data'] = $this->limitDataSize($decodedContent);
                }
            }
        }

        // 记录请求完成
        Log::debug('Request completed', $responseData);

        // 如果响应时间过长，记录警告
        if ($duration > 5.0) {
            Log::warning('Slow request detected', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'duration' => $duration,
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    /**
     * 格式化字节大小为人类可读格式
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * 限制数据大小，避免日志过大
     */
    private function limitDataSize($data, int $maxLength = 1000)
    {
        if (is_array($data)) {
            $limited = [];
            $currentLength = 0;

            foreach ($data as $key => $value) {
                if ($currentLength >= $maxLength) {
                    $limited['...'] = '数据已截断';
                    break;
                }

                if (is_array($value)) {
                    $limited[$key] = $this->limitDataSize($value, 200);
                } else {
                    $strValue = is_string($value) ? $value : json_encode($value);
                    if (strlen($strValue) > 200) {
                        $limited[$key] = substr($strValue, 0, 200) . '...';
                    } else {
                        $limited[$key] = $value;
                    }
                }

                $currentLength += strlen(json_encode([$key => $limited[$key]]));
            }

            return $limited;
        }

        $strData = is_string($data) ? $data : json_encode($data);
        if (strlen($strData) > $maxLength) {
            return substr($strData, 0, $maxLength) . '...';
        }

        return $data;
    }
}
