<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FeatureSsh\Enums\ServerStatus;
use Modules\FeatureSsh\Enums\SystemType;

/**
 * SSH服务器模型
 *
 * @property int $id
 * @property string $name 服务器名称
 * @property int|null $group_id 分组ID
 * @property string $host 主机地址
 * @property int $port SSH端口
 * @property SystemType $system_type 系统类型
 * @property array|null $system_info 系统信息
 * @property ServerStatus $status 状态
 * @property \Illuminate\Support\Carbon|null $last_check_at 最后检测时间
 * @property string|null $description 描述
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class Server extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'fssh_servers';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'group_id',
        'host',
        'port',
        'system_type',
        'system_info',
        'status',
        'last_check_at',
        'description',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'group_id' => 'integer',
            'port' => 'integer',
            'system_type' => SystemType::class,
            'system_info' => 'array',
            'status' => ServerStatus::class,
            'last_check_at' => 'datetime',
        ];
    }

    /**
     * 所属分组
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ServerGroup::class, 'group_id');
    }

    /**
     * 服务器的认证方式
     */
    public function authentications(): HasMany
    {
        return $this->hasMany(Authentication::class, 'server_id');
    }

    /**
     * 默认认证方式
     */
    public function defaultAuthentication()
    {
        return $this->authentications()->where('is_default', true)->first();
    }

    /**
     * 命令执行日志
     */
    public function commandLogs(): HasMany
    {
        return $this->hasMany(CommandLog::class, 'server_id');
    }

    /**
     * 获取主机:端口
     */
    public function getHostPort(): string
    {
        return $this->host . ':' . $this->port;
    }

    /**
     * 是否在线
     */
    public function isOnline(): bool
    {
        return $this->status === ServerStatus::ACTIVE;
    }

    /**
     * 更新系统信息
     */
    public function updateSystemInfo(array $info): void
    {
        $this->system_info = $info;
        $this->last_check_at = now();
        $this->save();
    }

    /**
     * 设置为离线
     */
    public function setOffline(): void
    {
        $this->status = ServerStatus::OFFLINE;
        $this->last_check_at = now();
        $this->save();
    }
}