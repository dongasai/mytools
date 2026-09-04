<?php

namespace Modules\Demo5\Dtos;

use Spatie\DataTransferObject\DataTransferObject;

class PostDto extends DataTransferObject
{
    public string $title;

    public string $content;

    public string $status;

    public int $user_id;

    public ?\DateTime $published_at;

    public ?int $id;

    public ?\DateTime $created_at;

    public ?\DateTime $updated_at;

    /**
     * Create PostDto from array
     */
    public static function fromArray(array $data): self
    {
        return new self([
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
            'user_id' => $data['user_id'],
            'published_at' => isset($data['published_at']) ? new \DateTime($data['published_at']) : null,
            'id' => $data['id'] ?? null,
            'created_at' => isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            'updated_at' => isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null,
        ]);
    }

    /**
     * Create PostDto from model
     */
    public static function fromModel($model): self
    {
        return new self([
            'id' => $model->id,
            'title' => $model->title,
            'content' => $model->content,
            'status' => $model->status,
            'user_id' => $model->user_id,
            'published_at' => $model->published_at,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ]);
    }

    /**
     * Convert to array for database operations
     */
    public function toDatabaseArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'published_at' => $this->published_at,
        ];
    }

    /**
     * Validate post data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->title)) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($this->title) > 255) {
            $errors['title'] = 'Title must not exceed 255 characters';
        }

        if (empty($this->content)) {
            $errors['content'] = 'Content is required';
        }

        if (! in_array($this->status, ['published', 'draft', 'archived'])) {
            $errors['status'] = 'Invalid status. Must be published, draft, or archived';
        }

        if ($this->user_id <= 0) {
            $errors['user_id'] = 'Invalid user ID';
        }

        if ($this->status === 'published' && ! $this->published_at) {
            $errors['published_at'] = 'Published date is required for published posts';
        }

        return $errors;
    }

    /**
     * Check if post is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Get post excerpt
     */
    public function getExcerpt(int $length = 150): string
    {
        return str_limit(strip_tags($this->content), $length);
    }

    /**
     * Get status label
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
}
