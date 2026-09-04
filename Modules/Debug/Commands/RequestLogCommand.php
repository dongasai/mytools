<?php

declare(strict_types=1);

namespace Modules\Debug\Commands;

use Illuminate\Console\Command;
use Modules\ABase\Models\SysRequestLog;

/**
 * 请求日志查看命令
 *
 * 查询和展示请求日志，支持过滤和多种输出格式
 *
 * @package Modules\Debug\Commands
 * @example
 * # 列出最近的请求日志
 * php artisan debug:request-log
 *
 * # 查看指定模块的请求
 * php artisan debug:request-log --module=nt_carbon
 *
 * # 查看单个请求详情
 * php artisan debug:request-log --request_id=xxx
 *
 * # 过滤条件
 * php artisan debug:request-log --method=POST --path=api/proto
 */
class RequestLogCommand extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'debug:request-log
        {--request_id= : 查看指定请求的详细信息}
        {--module= : 按模块过滤}
        {--path= : 按路径关键字过滤}
        {--method= : 按请求方法过滤}
        {--user= : 按用户ID过滤}
        {--limit=20 : 显示记录数量}
        {--format=table : 输出格式（table/json/detail）}
        {--response : 显示完整响应内容}
    ';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '查询和展示请求日志';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle(): int
    {
        $requestId = $this->option('request_id');
        $module = $this->option('module');
        $path = $this->option('path');
        $method = $this->option('method');
        $userId = $this->option('user');
        $limit = (int) $this->option('limit');
        $format = $this->option('format');
        $showResponse = $this->option('response');

        // 如果指定了 request_id，显示单个请求详情
        if ($requestId) {
            return $this->showRequestDetail($requestId, $showResponse);
        }

        // 构建查询
        $query = SysRequestLog::orderBy('created_at', 'desc');

        // 应用过滤条件
        if ($module) {
            $query->where('module', $module);
        }

        if ($path) {
            $query->where('path', 'like', "%{$path}%");
        }

        if ($method) {
            $query->where('method', strtoupper($method));
        }

        if ($userId) {
            $query->where('user_id', (int) $userId);
        }

        // 限制结果数量
        $logs = $query->limit($limit)->get();

        if ($logs->isEmpty()) {
            $this->warn('没有找到符合条件的请求日志');
            $this->info('提示: 尝试放宽过滤条件或增加 --limit 参数');
            return self::SUCCESS;
        }

        // 根据格式输出
        switch ($format) {
            case 'json':
                $this->outputAsJson($logs);
                break;
            case 'detail':
                $this->outputAsDetail($logs, $showResponse);
                break;
            default:
                $this->outputAsTable($logs);
                break;
        }

        return self::SUCCESS;
    }

    /**
     * 显示单个请求详情
     *
     * @param string $requestId 请求唯一ID
     * @param bool $showResponse 是否显示响应内容
     * @return int
     */
    private function showRequestDetail(string $requestId, bool $showResponse): int
    {
        $log = SysRequestLog::where('unid', $requestId)->first();

        if (!$log) {
            $this->error("请求日志不存在: {$requestId}");
            return self::FAILURE;
        }

        $this->info("========== 请求详情 ==========");
        $this->newLine();

        // 基本信息
        $basicInfo = [
            ['字段', '值'],
            ['UNID', $log->unid],
            ['Request UNID', $log->request_unid ?? 'N/A'],
            ['Run UNID', $log->run_unid ?? 'N/A'],
            ['创建时间', $log->created_at?->format('Y-m-d H:i:s') ?? 'N/A'],
            ['模块', $log->module ?? 'N/A'],
            ['路由', $log->router ?? 'N/A'],
            ['方法', $log->method],
            ['路径', $log->path],
            ['主机', $log->host],
            ['IP 地址', $log->ipaddress],
            ['用户 ID', $log->user_id ?? '未登录'],
            ['运行时间', $log->run_ms . 'ms'],
            ['SQL 查询次数', $log->sql_num ?? 0],
        ];

        $this->table(['字段', '值'], $basicInfo);
        $this->newLine();

        // 请求头
        $this->info("请求头:");
        $headers = $log->headers ? json_decode($log->headers, true) : [];
        if (empty($headers)) {
            $this->line('  无');
        } else {
            foreach ($headers as $name => $values) {
                $value = is_array($values) ? implode(', ', $values) : $values;
                $this->line("  {$name}: {$value}");
            }
        }
        $this->newLine();

        // Query 参数
        $query = $log->query ? json_decode($log->query, true) : [];
        if (!empty($query)) {
            $this->info("Query 参数:");
            $this->line(json_encode($query, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
        }

        // POST 数据
        $post = $log->post ? json_decode($log->post, true) : [];
        if (!empty($post)) {
            $this->info("POST 数据:");
            $this->line(json_encode($post, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
        }

        // Protobuf JSON
        $protobufJson = $log->protobuf_json ? json_decode($log->protobuf_json, true) : [];
        if (!empty($protobufJson)) {
            $this->info("Protobuf JSON:");
            $this->line(json_encode($protobufJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
        }

        // 响应信息
        $this->info("响应信息:");
        $responseInfo = [
            ['状态码', $log->response_status ?? 'N/A'],
            ['Content-Type', $log->response_type ?? 'N/A'],
            ['大小', ($log->response_size ?? 0) . ' bytes'],
            ['是否截断', $log->response_truncated ? '是' : '否'],
        ];
        $this->table(['字段', '值'], $responseInfo);
        $this->newLine();

        // 响应内容
        if ($showResponse && $log->response) {
            $this->info("响应内容:");
            $this->line($log->response);
            $this->newLine();
        }

        // 错误信息
        if ($log->error) {
            $this->error("错误信息:");
            $this->line($log->error);
        }

        return self::SUCCESS;
    }

    /**
     * 以表格格式输出
     *
     * @param \Illuminate\Support\Collection $logs 日志集合
     */
    private function outputAsTable($logs): void
    {
        $this->info("共找到 {$logs->count()} 条请求日志");
        $this->newLine();

        $tableData = [
            ['时间', 'UNID', '方法', '路径', '用户', '状态码', '耗时(ms)', '模块'],
        ];

        foreach ($logs as $log) {
            $tableData[] = [
                $log->created_at?->format('m-d H:i:s') ?? 'N/A',
                substr($log->unid, -12),
                $log->method,
                $log->path,
                $log->user_id ?? '-',
                $log->response_status ?? '-',
                $log->run_ms ?? '-',
                $log->module ?? '-',
            ];
        }

        $this->table(['时间', 'UNID', '方法', '路径', '用户', '状态码', '耗时(ms)', '模块'], array_slice($tableData, 1));
    }

    /**
     * 以 JSON 格式输出
     *
     * @param \Illuminate\Support\Collection $logs 日志集合
     */
    private function outputAsJson($logs): void
    {
        $data = $logs->map(function ($log) {
            return [
                'unid' => $log->unid,
                'created_at' => $log->created_at?->toIso8601String(),
                'method' => $log->method,
                'path' => $log->path,
                'module' => $log->module,
                'user_id' => $log->user_id,
                'status' => $log->response_status,
                'run_ms' => $log->run_ms,
            ];
        });

        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 以详细格式输出
     *
     * @param \Illuminate\Support\Collection $logs 日志集合
     * @param bool $showResponse 是否显示响应内容
     */
    private function outputAsDetail($logs, bool $showResponse): void
    {
        foreach ($logs as $index => $log) {
            if ($index > 0) {
                $this->newLine();
                $this->line(str_repeat('-', 80));
                $this->newLine();
            }

            $this->showRequestDetail($log->unid, $showResponse);
        }
    }
}