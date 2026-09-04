<?php

namespace Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 失败队列任务模型
 *
 * field start
 *
 * @property int $id
 * @property string $uuid
 * @property string $connection
 * @property string $queue
 * @property array $payload
 * @property string $exception
 * @property \Carbon\Carbon $failed_at
 *                                     field end
 */
class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected $casts = [
        'payload' => 'array',
        'failed_at' => 'datetime',
    ];

    protected $appends = [
        'queue_name',
        'connection_name',
        'job_class',
        'exception_class',
        'exception_message',
        'failed_at_formatted',
        'exception_stack_trace',
    ];

    // attrlist start
    protected $fillable = [
        'id',
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at',
    ];
    // attrlist end

    /**
     * 获取队列名称访问器
     */
    public function getQueueNameAttribute(): string
    {
        return $this->queue ?? 'default';
    }

    /**
     * 获取连接名称访问器
     */
    public function getConnectionNameAttribute(): string
    {
        return $this->connection ?? 'default';
    }

    /**
     * 获取任务类名
     */
    public function getJobClassAttribute(): string
    {
        if (! $this->payload) {
            return '';
        }

        $payload = is_array($this->payload) ? $this->payload : json_decode($this->payload, true);

        return $payload['displayName'] ?? $payload['job'] ?? '';
    }

    /**
     * 获取异常类名
     */
    public function getExceptionClassAttribute(): string
    {
        if (! $this->exception) {
            return '';
        }

        // 从异常信息中提取异常类名
        if (preg_match('/^([^\s:]+)/', $this->exception, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * 获取异常消息
     */
    public function getExceptionMessageAttribute(): string
    {
        if (! $this->exception) {
            return '';
        }

        // 从异常信息中提取异常消息
        $lines = explode("\n", $this->exception);
        if (count($lines) > 0) {
            // 第一行通常包含异常类和消息
            $firstLine = $lines[0];
            if (strpos($firstLine, ':') !== false) {
                return trim(substr($firstLine, strpos($firstLine, ':') + 1));
            }
        }

        return '';
    }

    /**
     * 获取格式化的失败时间
     */
    public function getFailedAtFormattedAttribute(): string
    {
        return $this->failed_at ? $this->failed_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * 获取异常堆栈跟踪（前几行）
     */
    public function getExceptionStackTraceAttribute(): string
    {
        if (! $this->exception) {
            return '';
        }

        $lines = explode("\n", $this->exception);

        // 返回前5行堆栈跟踪
        return implode("\n", array_slice($lines, 0, 5));
    }
}
