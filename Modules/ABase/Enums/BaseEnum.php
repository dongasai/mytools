<?php

namespace Modules\ABase\Enums;

/**
 * 枚举基类接口
 * 提供枚举的通用方法规范
 */
interface BaseEnum
{
    /**
     * 获取枚举值
     */
    public function value(): string;

    /**
     * 获取显示标签
     */
    public function label(): string;

    /**
     * 获取所有选项（用于下拉框）
     */
    public static function options(): array;

    /**
     * 从值创建枚举实例
     */
    public static function fromValue(string $value): self;
}