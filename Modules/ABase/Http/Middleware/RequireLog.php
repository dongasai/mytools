<?php

namespace Modules\ABase\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\ABase\Models\SysRequestLog;
use Illuminate\Support\Str;

/**
 * 请求日志中间件
 *
 * 功能：
 * 1. 使用 RUN_UNIQID 为每个请求生成唯一标识
 * 2. 将请求和响应信息存入数据库（sys_request_logs 表）
 * 3. 环境变量控制开关（REQUEST_LOG_ENABLED）
 * 4. 自动清理旧日志，保留最近 100,000 条记录
 * 5. 约 1% 的请求触发清理，避免每次请求都扫描
 */
class RequireLog
{
    /**
     * 日志记录保留数量
     */
    protected const MAX_LOG_RECORDS = 100000;

    /**
     * 存储当前请求的数据
     */
    protected $requestData = [];

    /**
     * 当前请求的唯一ID
     */
    protected $unid;

    /**
     * 请求开始时间（微秒）
     */
    protected $startTime;

    public function handle(Request $request, \Closure $next)
    {
        // 检查是否启用请求日志
        if (!config('module_abase::abase.enabled', false)) {
            return $next($request);
        }

        $this->startTime = microtime(true);

        // 生成唯一请求ID
        $this->unid = defined('RUN_UNIQID') ? RUN_UNIQID : uniqid(date('Ymd_His'));

        // 约 1% 的请求中执行清理
        if (rand(1, 100) === 1) {
            $this->cleanOldLogs();
        }

        $response = $next($request);

        // 在响应后记录，此时认证已完成
        $this->logRequestToDatabase($request);

        // 记录到 Laravel 系统日志
        $this->logToLaravelLog($request, $response);

        return $response;
    }

    /**
     * 清理旧日志记录，只保留最近的 MAX_LOG_RECORDS 条
     */
    protected function cleanOldLogs(): void
    {
        try {
            $total = SysRequestLog::count();

            if ($total <= self::MAX_LOG_RECORDS) {
                return;
            }

            // 计算需要删除的数量
            $deleteCount = $total - self::MAX_LOG_RECORDS;

            // 获取需要保留的最小ID
            $minIdToKeep = SysRequestLog::orderBy('id', 'desc')
                ->offset(self::MAX_LOG_RECORDS - 1)
                ->limit(1)
                ->value('id');

            if ($minIdToKeep) {
                // 删除旧记录
                SysRequestLog::where('id', '<', $minIdToKeep)->delete();

                Log::info("请求日志清理完成", [
                    'deleted_count' => $deleteCount,
                    'remaining_count' => self::MAX_LOG_RECORDS,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("请求日志清理失败", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 记录请求信息到数据库
     */
    protected function logRequestToDatabase(Request $request): void
    {
        try {
            $endTime = microtime(true);
            $runMs = intval(($endTime - $this->startTime) * 1000);

            $headers = $request->headers->all();
            $query = $request->query();
            $postParams = $this->getPostParams($request);
            $filteredPost = $this->filterSensitiveData($postParams);
            $files = $this->getUploadedFilesInfo($request);

            // 提取 token
            $token = null;
            if ($request->hasHeader('Authorization')) {
                $authHeader = $request->header('Authorization');
                if (Str::startsWith($authHeader, 'Bearer ')) {
                    $token = Str::substr($authHeader, 7);
                }
            }

            // 解析模块（从路径中提取）
            $module = null;
            $pathParts = explode('/', $request->path());
            if (isset($pathParts[0]) && $pathParts[0] === 'api' && isset($pathParts[2])) {
                $module = $pathParts[2]; // 如 /api/proto/enterprise/xxx -> enterprise
            }

            $statusCode = 200;
            $content = '';
            $contentType = 'text/html';
            $responseSize = 0;

            // 从全局响应对象获取响应信息
            if (isset($GLOBALS['response']) && $GLOBALS['response']) {
                $response = $GLOBALS['response'];
                $statusCode = $response->getStatusCode();
                $content = $response->getContent();
                $contentType = $response->headers->get('Content-Type') ?? 'text/html';
                $responseSize = strlen($content);

                // 限制响应内容长度
                $maxContentLength = 10000;
                if (strlen($content) > $maxContentLength) {
                    $content = substr($content, 0, $maxContentLength) . '...[truncated]';
                }
            }

            $data = [
                'unid' => $this->unid,
                'request_unid' => $this->unid,
                'run_unid' => defined('RUN_UNIQID') ? RUN_UNIQID : $this->unid,
                'path' => $request->path(),
                'method' => $request->method(),
                'router' => $request->route() ? $request->route()->getName() : null,
                'module' => $module,
                'headers' => json_encode($headers, JSON_UNESCAPED_UNICODE),
                'query' => json_encode($query, JSON_UNESCAPED_UNICODE),
                'post' => json_encode($filteredPost, JSON_UNESCAPED_UNICODE),
                'protobuf_json' => null,
                'files' => json_encode($files, JSON_UNESCAPED_UNICODE),
                'ipaddress' => $request->ip(),
                'host' => $request->getHost(),
                'user_agent' => $request->userAgent(),
                'user_id' => Auth::check() ? Auth::user()->id : 0,
                'token' => $token,
                'response_status' => (string) $statusCode,
                'response_type' => $contentType,
                'response_size' => $responseSize,
                'response' => $content,
                'response_truncated' => strlen($content) > 10000,
                'run_ms' => $runMs,
            ];

            SysRequestLog::create($data);

        } catch (\Exception $e) {
            Log::error("请求日志记录失败", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * 获取 POST 参数（不包括上传的文件）
     */
    protected function getPostParams(Request $request): array
    {
        $params = [];

        // 获取所有输入参数
        $all = $request->all();

        // 过滤掉文件上传
        foreach ($all as $key => $value) {
            if (!$value instanceof \Illuminate\Http\UploadedFile) {
                $params[$key] = $value;
            }
        }

        // 对于 PUT/PATCH/DELETE 请求，PHP 不会自动解析 multipart/form-data
        if (empty($params) && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $contentType = $request->header('Content-Type', '');

            if (strpos($contentType, 'multipart/form-data') !== false) {
                $params = $this->parseMultipartFormData($request);
            } else {
                $params = $request->input();
                if (empty($params)) {
                    $content = $request->getContent();
                    if (!empty($content)) {
                        $jsonData = json_decode($content, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $params = $jsonData;
                        } else {
                            parse_str($content, $params);
                        }
                    }
                }
            }

            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        unset($params[$key]);
                    }
                }
            }
        }

        return is_array($params) ? $params : [];
    }

    /**
     * 手动解析 multipart/form-data 请求体
     */
    protected function parseMultipartFormData(Request $request): array
    {
        $params = [];
        $content = $request->getContent();
        $contentType = $request->header('Content-Type', '');

        if (!preg_match('/boundary=(.*)$/', $contentType, $matches)) {
            return $params;
        }

        $boundary = $matches[1];
        $parts = preg_split('/-+' . preg_quote($boundary, '/') . '/', $content);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part) || $part === '--') {
                continue;
            }

            $partLines = explode("\r\n\r\n", $part, 2);
            if (count($partLines) < 2) {
                continue;
            }

            $headers = $partLines[0];
            $body = trim($partLines[1]);

            if (preg_match('/Content-Disposition:.*?name="([^"]+)"/', $headers, $nameMatches)) {
                $fieldName = $nameMatches[1];
                $params[$fieldName] = $body;
            }
        }

        return $params;
    }

    /**
     * 获取上传的文件信息
     */
    protected function getUploadedFilesInfo(Request $request): array
    {
        $files = [];
        $allFiles = $request->allFiles();

        foreach ($allFiles as $key => $file) {
            if (is_array($file)) {
                $files[$key] = [];
                foreach ($file as $f) {
                    if ($f instanceof \Illuminate\Http\UploadedFile) {
                        $files[$key][] = [
                            'name' => $f->getClientOriginalName(),
                            'size' => $f->getSize(),
                            'type' => $f->getClientMimeType(),
                        ];
                    }
                }
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $files[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        return $files;
    }

    /**
     * 过滤敏感数据
     */
    protected function filterSensitiveData(array $data): array
    {
        // 不过滤,方便调试
        $sensitiveFields = [];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
            foreach ($sensitiveFields as $field) {
                if (stripos($key, $field) !== false) {
                    $value = '***FILTERED***';
                    break;
                }
            }
        });

        return $data;
    }

    /**
     * 记录到 Laravel 系统日志
     */
    protected function logToLaravelLog($request, $response): void
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $maxContentLength = 6000;
        $truncated = false;
        if (strlen($content) > $maxContentLength) {
            $content = substr($content, 0, $maxContentLength) . '...[truncated]';
            $truncated = true;
        }

        $logLevel = $statusCode >= 400 ? 'error' : 'info';
        $logMessage = $statusCode >= 400 ? 'API错误响应' : 'API响应';

        Log::log($logLevel, '字符串:' . $content);
        Log::log($logLevel, $logMessage, [
            'status_code' => $statusCode,
            'response_size' => strlen($response->getContent()),
            'truncated' => $truncated,
            'content_type' => $response->headers->get('Content-Type'),
            'RUN_UNIQID' => $this->unid,
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}