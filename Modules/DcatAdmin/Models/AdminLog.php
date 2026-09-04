<?php

namespace Modules\DcatAdmin\Models;

use Modules\Admin\Enums\ADMIN_ACTION_TYPE;
use Illuminate\Database\Eloquent\Model;

/**
 * 管理员日志模型
 *
 * @property int $id
 * @property int|null $admin_id 管理员ID
 * @property string $admin_name 管理员名称
 * @property string $action_type 操作类型
 * @property string $description 操作描述
 * @property string|null $data 操作数据(JSON)
 * @property string $ip_address IP地址
 * @property string|null $user_agent 用户代理
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 */
class AdminLog extends Model
{
    /**
     * 数据表名
     *
     * @var string
     */
    protected $table = 'admin_logs';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'admin_id',
        'admin_name',
        'action_type',
        'description',
        'data',
        'ip_address',
        'user_agent',
    ];

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'admin_id' => 'integer',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取操作类型枚举
     */
    public function getActionTypeEnumAttribute(): ?ADMIN_ACTION_TYPE
    {
        try {
            return ADMIN_ACTION_TYPE::from($this->action_type);
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * 获取操作类型标签
     */
    public function getActionTypeLabelAttribute(): string
    {
        $enum = $this->getActionTypeEnumAttribute();

        return $enum ? $enum->getLabel() : $this->action_type;
    }

    /**
     * 获取操作类型颜色
     */
    public function getActionTypeColorAttribute(): string
    {
        $enum = $this->getActionTypeEnumAttribute();

        return $enum ? $enum->getColor() : 'secondary';
    }

    /**
     * 获取操作类型图标
     */
    public function getActionTypeIconAttribute(): string
    {
        $enum = $this->getActionTypeEnumAttribute();

        return $enum ? $enum->getIcon() : 'fa-question';
    }

    /**
     * 判断是否为危险操作
     */
    public function getIsDangerousAttribute(): bool
    {
        $enum = $this->getActionTypeEnumAttribute();

        return $enum ? $enum->isDangerous() : false;
    }

    /**
     * 获取格式化的数据
     */
    public function getFormattedDataAttribute(): string
    {
        if (empty($this->data)) {
            return '';
        }

        if (is_array($this->data)) {
            return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return (string) $this->data;
    }

    /**
     * 获取简短的用户代理
     */
    public function getShortUserAgentAttribute(): string
    {
        if (empty($this->user_agent)) {
            return '';
        }

        // 提取浏览器信息
        if (preg_match('/Chrome\/[\d.]+/', $this->user_agent, $matches)) {
            return $matches[0];
        }

        if (preg_match('/Firefox\/[\d.]+/', $this->user_agent, $matches)) {
            return $matches[0];
        }

        if (preg_match('/Safari\/[\d.]+/', $this->user_agent, $matches)) {
            return $matches[0];
        }

        if (preg_match('/Edge\/[\d.]+/', $this->user_agent, $matches)) {
            return $matches[0];
        }

        // 如果没有匹配到，返回前50个字符
        return mb_substr($this->user_agent, 0, 50).'...';
    }

    /**
     * 按操作类型查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|ADMIN_ACTION_TYPE  $actionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByActionType($query, $actionType)
    {
        if ($actionType instanceof ADMIN_ACTION_TYPE) {
            $actionType = $actionType->value;
        }

        return $query->where('action_type', $actionType);
    }

    /**
     * 按管理员查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * 按IP地址查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * 按时间范围查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 查询危险操作
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDangerousActions($query)
    {
        $dangerousTypes = [];
        foreach (ADMIN_ACTION_TYPE::cases() as $type) {
            if ($type->isDangerous()) {
                $dangerousTypes[] = $type->value;
            }
        }

        return $query->whereIn('action_type', $dangerousTypes);
    }

    /**
     * 最近的操作
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
