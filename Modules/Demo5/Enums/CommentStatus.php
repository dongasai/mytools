<?php

namespace Modules\Demo5\Enums;

use Modules\DcatAdmin\Support\Traits\EnumDcat;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    use EnumDcat;

    /**
     * 获取状态标签
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => '待审核',
            self::Approved => '已通过',
            self::Rejected => '已拒绝',
        };
    }

    /**
     * 获取状态颜色
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }


    /**
     * 检查是否可以编辑
     */
    public function canEdit(): bool
    {
        return match ($this) {
            self::Pending, self::Rejected => true,
            self::Approved => false,
        };
    }

    /**
     * 检查是否可以删除
     */
    public function canDelete(): bool
    {
        return match ($this) {
            self::Rejected => true,
            self::Pending, self::Approved => false,
        };
    }

    /**
     * 获取所有状态选项
     */
    public static function getAllOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'color' => $case->getColor(),
        ], self::cases());
    }

    /**
     * 获取用于表单选择的选项数组
     */
    public static function getSelectOptions(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->getLabel();
            return $carry;
        }, []);
    }

    /**
     * 根据值获取枚举实例
     */
    public static function fromValue(string $value): ?self
    {
        return match ($value) {
            'pending' => self::Pending,
            'approved' => self::Approved,
            'rejected' => self::Rejected,
            default => null,
        };
    }

    /**
     * 获取默认状态
     */
    public static function getDefault(): self
    {
        return self::Pending;
    }
}
