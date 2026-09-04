<?php

namespace Modules\Application\Dtos;

/**
 * 用户辅助类
 */
class User
{
    public function __construct(
        public readonly int $id,                    // 用户ID
        public readonly string $name,               // 姓名
        public readonly string $type,               // 用户类型：'account', 'admin', 'shop'
        public readonly string $email = '',         // 邮箱
        public readonly string $avatar = ''         // 头像
    ) {}

    // 创建用户实例
    public static function create(int $id, string $name, string $type, string $email = '', string $avatar = ''): self
    {
        return new self(
            id: $id,
            name: $name,
            type: $type,
            email: $email,
            avatar: $avatar
        );
    }

    // 转换为数组
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'email' => $this->email,
            'avatar' => $this->avatar
        ];
    }

    // 转换为简化格式
    public function toSimpleFormat(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'email' => $this->email
        ];
    }
}