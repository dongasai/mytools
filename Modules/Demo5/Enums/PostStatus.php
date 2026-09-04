<?php

namespace Modules\Demo5\Enums;

enum PostStatus: string
{
    case Published = 'published';
    case Draft = 'draft';
    case Archived = 'archived';

    /**
     * 获取状态标签
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Published => '已发布',
            self::Draft => '草稿',
            self::Archived => '已归档',
        };
    }

    /**
     * 获取状态颜色
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Draft => 'warning',
            self::Archived => 'secondary',
        };
    }

    /**
     * 获取状态徽章HTML
     */
    public function getBadgeHtml(): string
    {
        $color = $this->getColor();
        $label = $this->getLabel();

        return match ($color) {
            'success' => '<span class="badge badge-success">'.$label.'</span>',
            'warning' => '<span class="badge badge-warning">'.$label.'</span>',
            'secondary' => '<span class="badge badge-secondary">'.$label.'</span>',
            default => '<span class="badge badge-secondary">未知</span>',
        };
    }

    /**
     * 检查是否可以编辑
     */
    public function canEdit(): bool
    {
        return match ($this) {
            self::Published, self::Draft => true,
            self::Archived => false,
        };
    }

    /**
     * 检查是否可以删除
     */
    public function canDelete(): bool
    {
        return match ($this) {
            self::Draft, self::Archived => true,
            self::Published => false,
        };
    }

    /**
     * 获取所有状态选项
     */
    public static function getAllOptions(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'color' => $case->getColor(),
        ], self::cases());
    }

    /**
     * 根据值获取枚举实例
     */
    public static function fromValue(string $value): ?self
    {
        return match ($value) {
            'published' => self::Published,
            'draft' => self::Draft,
            'archived' => self::Archived,
            default => null,
        };
    }
}
