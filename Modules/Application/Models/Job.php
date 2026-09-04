<?php

namespace Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 队列任务模型
 *
 * field start
 *
 * @property int $id
 * @property string $queue
 * @property array $payload
 * @property int $attempts
 * @property int $reserved_at
 * @property int $available_at
 * @property int $created_at
 *                           field end
 */
class Job extends Model
{
    protected $table = 'jobs';

    public $timestamps = false;

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
    ];

    protected $appends = [
        'queue_name',
        'created_at_formatted',
        'available_at_formatted',
        'reserved_at_formatted',
        'status',
        'job_class',
        'job_parameters',
        'job_parameters_short',
    ];

    // attrlist start
    protected $fillable = [
        'id',
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
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
     * 获取格式化的创建时间
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : '';
    }

    /**
     * 获取格式化的可用时间
     */
    public function getAvailableAtFormattedAttribute(): string
    {
        return $this->available_at ? date('Y-m-d H:i:s', $this->available_at) : '';
    }

    /**
     * 获取格式化的保留时间
     */
    public function getReservedAtFormattedAttribute(): string
    {
        return $this->reserved_at ? date('Y-m-d H:i:s', $this->reserved_at) : '';
    }

    /**
     * 获取任务状态
     */
    public function getStatusAttribute(): string
    {
        $now = time();

        if ($this->reserved_at && $this->reserved_at > $now) {
            return '已保留';
        }

        if ($this->available_at > $now) {
            return '延迟中';
        }

        return '待处理';
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
     * 获取任务参数 - 直接反序列化payload原样展示
     */
    public function getJobParametersAttribute(): string
    {
        if (! $this->payload) {
            return '无参数';
        }

        $payload = is_array($this->payload) ? $this->payload : json_decode($this->payload, true);

        if (! is_array($payload)) {
            // 如果JSON解码失败，尝试其他格式
            if (is_string($this->payload)) {
                return $this->payload;
            }

            return '无参数';
        }

        return $this->formatArrayForDisplay($payload);
    }

    /**
     * 格式化数组为显示字符串
     */
    private function formatArrayForDisplay(array $data): string
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[] = $key.': '.json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif (is_object($value)) {
                $result[] = $key.': '.json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif (is_bool($value)) {
                $result[] = $key.': '.($value ? 'true' : 'false');
            } elseif (is_null($value)) {
                $result[] = $key.': null';
            } else {
                $result[] = $key.': '.$value;
            }
        }

        return implode(', ', $result);
    }

    /**
     * 获取简化的任务参数（用于列表显示）
     */
    public function getJobParametersShortAttribute(): string
    {
        $params = $this->job_parameters;
        if (mb_strlen($params) > 50) {
            return mb_substr($params, 0, 50).'...';
        }

        return $params;
    }
}
