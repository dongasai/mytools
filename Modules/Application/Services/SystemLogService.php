<?php

namespace Modules\Application\Services;

use Modules\Application\Events\SystemLogCreatedEvent;
use Modules\Application\Models\SystemLog;

/**
 * 系统日志服务
 *
 * 提供便捷的系统日志记录接口
 *
 * @example
 * // 记录错误日志
 * SystemLogService::error('ai_analysis', 'AI分析服务异常', [
 *     'request_id' => '...',
 *     'error_code' => 3
 * ]);
 *
 * // 记录信息日志
 * SystemLogService::info('user_action', '用户登录成功', ['user_id' => 20]);
 */
class SystemLogService
{
    /**
     * 日志级别常量
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ALERT = 'alert';
    const LEVEL_EMERGENCY = 'emergency';

    /**
     * 记录调试日志
     *
     * @param  string  $source  来源类型（如：ai_analysis, user_action）
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function debug(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_DEBUG);
    }

    /**
     * 记录信息日志
     *
     * @param  string  $source  来源类型（如：ai_analysis, user_action）
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function info(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_INFO);
    }

    /**
     * 记录通知日志
     *
     * @param  string  $source  来源类型
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function notice(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_NOTICE);
    }

    /**
     * 记录警告日志
     *
     * @param  string  $source  来源类型
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function warning(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_WARNING);
    }

    /**
     * 记录错误日志
     *
     * @param  string  $source  来源类型（如：ai_analysis, payment）
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function error(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_ERROR);
    }

    /**
     * 记录严重错误日志
     *
     * @param  string  $source  来源类型
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function critical(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_CRITICAL);
    }

    /**
     * 记录紧急日志
     *
     * @param  string  $source  来源类型
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function alert(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_ALERT);
    }

    /**
     * 记录最高级别日志
     *
     * @param  string  $source  来源类型
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function emergency(string $source, string $message, array $context = []): SystemLog
    {
        return self::log($source, $message, $context, self::LEVEL_EMERGENCY);
    }

    /**
     * 记录日志（核心方法）
     *
     * @param  string  $source  来源类型
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     * @param  string  $level  日志级别
     * @return SystemLog
     */
    public static function log(string $source, string $message, array $context = [], string $level = self::LEVEL_INFO): SystemLog
    {
        // 准备日志数据
        $data = [
            'level1' => $source,
            'message' => $message,
            'data1' => json_encode(array_merge($context, [
                'level' => $level,
                'timestamp' => date('Y-m-d H:i:s'),
            ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        // 创建日志记录
        $log = SystemLog::create($data);

        // 注意：暂时禁用事件触发，因为 SystemLogCreatedEvent 需要的字段与 SystemLog 模型不匹配
        // 如果需要事件通知功能，建议：
        // 1. 修改 SystemLog 模型添加 type, level, context, user_id 字段
        // 2. 或者重构 SystemLogCreatedEvent 以适配现有模型

        return $log;
    }

    /**
     * 记录 API 响应错误（便捷方法）
     *
     * 专门用于记录外部 API 调用失败的场景
     *
     * @param  string  $source  来源类型
     * @param  string  $message  错误消息
     * @param  array  $apiResponse  API 响应数据
     * @param  array  $extraContext  额外的上下文
     * @return SystemLog
     */
    public static function apiError(string $source, string $message, array $apiResponse, array $extraContext = []): SystemLog
    {
        $context = array_merge([
            'api_response' => $apiResponse,
            'request_time' => date('Y-m-d H:i:s'),
        ], $extraContext);

        return self::error($source, $message, $context);
    }

    /**
     * 记录异常日志（便捷方法）
     *
     * @param  string  $source  来源类型
     * @param  \Throwable  $exception  异常对象
     * @param  array  $context  上下文数据
     * @return SystemLog
     */
    public static function exception(string $source, \Throwable $exception, array $context = []): SystemLog
    {
        $context = array_merge($context, [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile().':'.$exception->getLine(),
            'exception_trace' => $exception->getTraceAsString(),
        ]);

        return self::error($source, '异常: '.$exception->getMessage(), $context);
    }
}