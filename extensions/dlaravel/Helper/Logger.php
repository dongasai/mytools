<?php

namespace DLaravel\Helper;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 日志助手类
 */
class Logger
{
    /**
     * 记录调试日志
     *
     * @param  string  $msg  日志消息
     * @param  array  $data  日志数据
     * @return void
     */
    public static function debug($msg, $data = [])
    {
        Log::debug($msg.' '.json_encode($data));
    }

    /**
     * 错误日志
     *
     * @return void
     */
    public static function error($msg, $data = [])
    {
        Log::error($msg.' '.json_encode($data));
        DB::table('system_logs')->insert([
            'level1' => 'error',
            'message' => $msg,
            'data1' => json_encode($data),
        ]);

    }

    public static function warning($msg, $data = [])
    {
        Log::warning($msg.' '.json_encode($data));
    }

    /**
     * 错误记录
     *
     * @param  string  $msg  日志消息
     * @param  \Throwable  $exception  异常对象
     * @param  array  $data  额外数据
     * @return void
     */
    public static function exception($msg, \Throwable $exception, $data = [])
    {
        // 格式化堆栈跟踪，使其换行显示
        $traceString = $exception->getTraceAsString();
        $formattedTrace = str_replace('#', "\n    #", $traceString);

        // 构建格式化的日志信息
        $logMessage = $msg."\n".
                      '异常信息: '.$exception->getMessage()."\n".
                      '文件位置: '.$exception->getFile().':'.$exception->getLine()."\n".
                      "堆栈跟踪:\n    ".$formattedTrace;

        // 如果有额外数据，添加到日志中
        if (! empty($data)) {
            $logMessage .= "\n额外数据: ".json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        Log::error($logMessage);
    }

    public static function info($msg, $data = [])
    {
        Log::info($msg.' '.json_encode($data));
    }

    public static function clear_log()
    {
        // 获取当前日志文件路径
        $logPath = storage_path('logs');
        $currentDate = date('Y-m-d');

        // Laravel 默认日志文件名格式
        $logFiles = [
            $logPath.'/laravel.log',
            $logPath."/laravel-{$currentDate}.log",
        ];

        $clearedCount = 0;
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile)) {
                // 清空文件内容但保留文件
                file_put_contents($logFile, '');
                $clearedCount++;
            }
        }

        return $clearedCount;
    }
}
