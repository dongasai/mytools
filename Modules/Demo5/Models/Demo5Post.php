<?php

namespace Modules\Demo5\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Demo5\Database\Factories\Demo5PostFactory;
use Modules\Demo5\Enums\CommentStatus;

class Demo5Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * 获取文章作者ID（不建立模型关联，仅返回ID）
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * 设置文章作者ID
     */
    public function setUserId(int $userId): self
    {
        $this->user_id = $userId;
        return $this;
    }

    /**
     * 获取文章的所有评论
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Demo5Comment::class, 'post_id');
    }

    /**
     * 获取已审核的评论
     */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Demo5Comment::class, 'post_id')
            ->where('status', CommentStatus::Approved->value);
    }

    /**
     * 检查文章是否已发布
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * 检查文章是否为草稿
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * 检查文章是否已归档
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'published' => '已发布',
            'draft' => '草稿',
            'archived' => '已归档',
            default => '未知',
        };
    }

    /**
     * 获取文章摘要
     */
    public function getExcerpt(int $length = 150): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->content), $length);
    }

    /**
     * 创建模型工厂
     */
    protected static function newFactory(): Demo5PostFactory
    {
        return Demo5PostFactory::new();
    }

    // ==================== 模型关系 ====================

    /**
     * 获取文章作者
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Demo5User::class, 'user_id');
    }

    /**
     * 获取文章用户（别名，与author关联相同）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Demo5User::class, 'user_id');
    }

    // ==================== 访问器 ====================

    /**
     * 访问器：获取文章URL
     */
    public function getUrlAttribute(): string
    {
        return url("/posts/{$this->id}");
    }

    /**
     * 访问器：获取SEO友好的slug
     */
    public function getSlugAttribute(): string
    {
        return str()->slug($this->title);
    }

    /**
     * 访问器：获取文章字数
     */
    public function getWordCountAttribute(): int
    {
        return strlen(strip_tags($this->content ?? ''));
    }

    /**
     * 访问器：获取阅读时间（分钟）
     */
    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil($this->word_count / 200));
    }

    // ==================== 修改器 ====================

    /**
     * 修改器：设置标题时自动trim
     */
    public function setTitleAttribute(string $value): void
    {
        $this->attributes['title'] = trim($value);
    }

    /**
     * 修改器：设置内容时清理HTML标签
     */
    public function setContentAttribute(?string $value): void
    {
        if ($value) {
            $allowedTags = '<p><a><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><br><img><blockquote><pre><code>';
            $this->attributes['content'] = strip_tags($value, $allowedTags);
        }
    }
}
