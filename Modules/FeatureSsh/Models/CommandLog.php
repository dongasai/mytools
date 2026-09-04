<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FeatureSsh\Enums\CommandStatus;

/**
 * SSH命令执行日志模型
 *
 * @property int $id
 * @property int $server_id 服务器ID
 * @property int|null $user_id 执行用户ID
 * @property string $command 执行的命令
 * @property int|null $exit_code 退出码
 * @property string|null $output 命令输出
 * @property int $execution_time 执行时间（毫秒）
 * @property CommandStatus $status 执行状态
 * @property \Illuminate\Support\Carbon|null $executed_at 执行时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class CommandLog extends Model
{
    /**
     * 表名
     */
    protected $table = 'fssh_command_logs';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'server_id',
        'user_id',
        'command',
        'exit_code',
        'output',
        'execution_time',
        'status',
        'executed_at',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'server_id' => 'integer',
            'user_id' => 'integer',
            'exit_code' => 'integer',
            'execution_time' => 'integer',
            'status' => CommandStatus::class,
            'executed_at' => 'datetime',
        ];
    }

    /**
     * 所属服务器
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * 执行用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * 是否成功
     */
    public function isSuccess(): bool
    {
        return $this->status === CommandStatus::SUCCESS;
    }

    /**
     * 获取执行时间（秒）
     */
    public function getExecutionTimeSeconds(): float
    {
        return $this->execution_time / 1000;
    }

    /**
     * 获取截断的输出（用于显示）
     */
    public function getTruncatedOutput(int $length = 200): string
    {
        if (!$this->output) {
            return '';
        }

        if (strlen($this->output) <= $length) {
            return $this->output;
        }

        return substr($this->output, 0, $length) . '...';
    }
}