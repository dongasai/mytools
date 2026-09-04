<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

use JsonSerializable;

/**
 * Hook参数基类
 *
 * 所有Hook参数都必须继承此类，提供统一的参数类型定义和验证机制
 * 每个Hook都有其独特的参数类型，确保类型安全和参数完整性
 */
abstract class HookParameter implements HookParameterInterface, JsonSerializable
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->validate();
    }

    /**
     * 验证参数数据
     * 子类应该重写此方法实现具体的验证逻辑
     */
    protected function validate(): void
    {
        // 基础验证，子类可重写实现具体验证逻辑
    }

    /**
     * 转换为数组
     * 子类应该重写此方法实现具体的数组转换逻辑
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * 转换为JSON字符串
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this, $options);
    }

    /**
     * 实现 JsonSerializable 接口
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
