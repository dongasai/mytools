<?php

namespace Modules\Application\Dtos;

/**
 * 用户类型辅助类
 */
class UserType
{
    public function __construct(
        public readonly string $type_id,     // 类型标识：'account', 'admin', 'shop'
        public readonly string $type_name,   // 类型中文：'系统用户', '管理员', '商城用户'
    ) {}

    // 创建用户类型实例
    public static function create(string $typeId, string $typeName): self
    {
        return new self(
            type_id: $typeId,
            type_name: $typeName
        );
    }

    // 转换为数组
    public function toArray(): array
    {
        return [
            'type_id' => $this->type_id,
            'type_name' => $this->type_name,
        ];
    }
}