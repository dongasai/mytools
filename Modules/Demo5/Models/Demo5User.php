<?php

namespace Modules\Demo5\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Demo5\Database\Factories\Demo5UserFactory;

/**
 * Demo5 用户模型
 */
class Demo5User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    /**
     * 获取用户的所有文章
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Demo5Post::class, 'user_id');
    }

    /**
     * 获取用户的所有评论
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Demo5Comment::class, 'user_id');
    }

    /**
     * 检查用户是否活跃
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * 检查用户是否被禁用
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => '活跃',
            'inactive' => '未激活',
            'banned' => '已禁用',
            default => '未知',
        };
    }

    /**
     * 获取用户头像URL
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar ?? 'https://via.placeholder.com/150';
    }

    /**
     * 创建模型工厂
     */
    protected static function newFactory(): Demo5UserFactory
    {
        return Demo5UserFactory::new();
    }
}