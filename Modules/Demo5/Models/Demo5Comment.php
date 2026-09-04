<?php

namespace Modules\Demo5\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Demo5\Enums\CommentStatus;

/**
 * 文章评论模型
 *
 * @property int $id
 * @property string $content 评论内容
 * @property enum $status 评论状态: pending, approved, rejected
 * @property int $post_id 文章ID
 * @property int $user_id 评论者ID
 * @property int|null $parent_id 父评论ID
 * @property string|null $ip_address IP地址
 * @property string|null $user_agent 用户代理
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Demo5Post $post
 * @property-read User $user
 * @property-read Demo5Comment|null $parent
 * @property-read \Illuminate\Database\EloquentCollection<Demo5Comment> $replies
 */
class Demo5Comment extends Model
{
    use HasFactory;

    /**
     * 数据表名
     */
    protected $table = 'demo5_comments';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'content',
        'status',
        'post_id',
        'user_id',
        'parent_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * 属性类型转换
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }

    /**
     * 获取所属文章
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Demo5Post::class, 'post_id');
    }

    /**
     * 获取评论者ID（不建立模型关联，仅返回ID）
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * 设置评论者ID
     */
    public function setUserId(int $userId): self
    {
        $this->user_id = $userId;
        return $this;
    }

    /**
     * 获取父评论
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Demo5Comment::class, 'parent_id');
    }

    /**
     * 获取子评论
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Demo5Comment::class, 'parent_id');
    }

    /**
     * 获取已审核的子评论
     */
    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', CommentStatus::Approved->value);
    }

    /**
     * 检查评论是否已审核通过
     */
    public function isApproved(): bool
    {
        return $this->status === CommentStatus::Approved;
    }

    /**
     * 检查评论是否待审核
     */
    public function isPending(): bool
    {
        return $this->status === CommentStatus::Pending;
    }

    /**
     * 检查评论是否被拒绝
     */
    public function isRejected(): bool
    {
        return $this->status === CommentStatus::Rejected;
    }

    /**
     * 检查是否为顶级评论
     */
    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * 检查是否为回复评论
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabel(): string
    {
        return $this->status ? $this->status->getLabel() : '未知';
    }

    /**
     * 获取内容摘要
     */
    public function getExcerpt(int $length = 50): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->content), $length);
    }

    /**
     * 审核通过评论
     */
    public function approve(): void
    {
        $this->update(['status' => CommentStatus::Approved->value]);
    }

    /**
     * 拒绝评论
     */
    public function reject(): void
    {
        $this->update(['status' => CommentStatus::Rejected->value]);
    }

    /**
     * 提交审核
     */
    public function submitForReview(): void
    {
        $this->update(['status' => CommentStatus::Pending->value]);
    }

    /**
     * 查询作用域：按状态筛选
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 查询作用域：已审核通过的评论
     */
    public function scopeApproved($query)
    {
        return $query->where('status', CommentStatus::Approved->value);
    }

    /**
     * 查询作用域：待审核的评论
     */
    public function scopePending($query)
    {
        return $query->where('status', CommentStatus::Pending->value);
    }

    /**
     * 查询作用域：被拒绝的评论
     */
    public function scopeRejected($query)
    {
        return $query->where('status', CommentStatus::Rejected->value);
    }

    /**
     * 查询作用域：顶级评论
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * 查询作用域：回复评论
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * 查询作用域：最近的评论
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 创建模型工厂
     */
    protected static function newFactory(): \Modules\Demo5\Database\Factories\Demo5CommentFactory
    {
        return \Modules\Demo5\Database\Factories\Demo5CommentFactory::new();
    }
}