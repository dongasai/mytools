<?php

namespace Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 队列运行记录模型
 *
 * field start
 *
 * @property int $id
 * @property string $queue
 * @property array $payload
 * @property string $runclass 运行类
 * @property int $attempts
 * @property int $reserved_at
 * @property int $available_at
 * @property int $created_at
 * @property string $status 运行状态
 * @property string $desc 描述信息
 * @property float $runtime 运行时间
 *                          field end
 */
class JobRun extends Model
{
    protected $table = 'job_runs';

    public $timestamps = false;

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
        'runtime' => 'float',
    ];

    protected $appends = [
        'queue_name', 'runtime_formatted',
        'job_class',
        'desc_short',
        'job_parameters',
        'job_parameters_short',
    ];

    // attrlist start
    protected $fillable = [
        'id',
        'queue',
        'payload',
        'runclass',
        'attempts',
        'reserved_at',
        'available_at',
        'status',
        'desc',
        'runtime',
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
     * 获取格式化的运行时间
     */
    public function getRuntimeFormattedAttribute(): string
    {
        if (! $this->runtime) {
            return '';
        }

        if ($this->runtime < 1) {
            return round($this->runtime * 1000, 2).'ms';
        } else {
            return round($this->runtime, 3).'s';
        }
    }

    /**
     * 获取任务类名（从payload中提取）
     */
    public function getJobClassAttribute(): string
    {
        if (! $this->payload) {
            return $this->runclass ?? '';
        }

        $payload = is_array($this->payload) ? $this->payload : json_decode($this->payload, true);

        return $payload['displayName'] ?? $payload['job'] ?? $this->runclass ?? '';
    }

    /**
     * 获取描述信息（截断）
     */
    public function getDescShortAttribute(): string
    {
        if (! $this->desc) {
            return '';
        }

        return mb_strlen($this->desc) > 100 ? mb_substr($this->desc, 0, 100).'...' : $this->desc;
    }

    /**
     * 获取任务参数 - 直接反序列化payload原样展示
     */
    public function getJobParametersAttribute(): string
    {
        if (! $this->payload) {
            return '无参数';
        }

        // 尝试反序列化payload
        $payload = $this->payload;

        // 如果是字符串，尝试反序列化
        if (is_string($payload)) {
            // 去掉外层引号
            $payload = trim($payload, '"');

            // 尝试反序列化PHP数组
            if (strpos($payload, 'a:') === 0) {
                try {
                    $unserialized = unserialize($payload);
                    if (is_array($unserialized)) {
                        return $this->formatArrayForDisplay($unserialized);
                    }
                } catch (\Exception) {
                    // 反序列化失败，返回原始字符串
                    return $payload;
                }
            } else {
                // 尝试JSON解码
                $decoded = json_decode($payload, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->formatArrayForDisplay($decoded);
                }
            }
        }

        // 如果已经是数组，直接格式化
        if (is_array($payload)) {
            return $this->formatArrayForDisplay($payload);
        }

        // 其他情况返回原始内容
        return is_scalar($payload) ? (string) $payload : json_encode($payload);
    }

    /**
     * 格式化数组为显示字符串
     */
    private function formatArrayForDisplay(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
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
