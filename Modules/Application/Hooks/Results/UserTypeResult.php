<?php

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;
use Modules\Application\Dtos\UserType;

/**
 * 用户类型Hook结果类
 */
class UserTypeResult extends HookResult
{
    public function __construct(
        public readonly array $user_types = [],       // 用户类型列表：[UserType实例]
        public readonly array $metadata = [],         // 元数据：['source_modules' => ['admin', 'shop']]
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $userTypes, array $metadata = []): static
    {
        return new static(
            user_types: $userTypes,
            metadata: $metadata,
            success: true,
            message: '获取用户类型成功'
        );
    }

    public static function failure(array $errors = []): static
    {
        return new static(
            success: false,
            message: '获取用户类型失败',
            metadata: $errors
        );
    }

    // 添加用户类型（链式累积）
    public function addUserType(UserType $userType): self
    {
        $newTypes = $this->user_types;
        $newTypes[] = $userType;

        return new static(
            user_types: $newTypes,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    // 添加简化格式用户类型 - 向后兼容
    public function addUserTypeSimple(string $typeId, string $typeName): self
    {
        $userType = UserType::create($typeId, $typeName);
        return $this->addUserType($userType);
    }

    // 合并用户类型列表
    public function mergeUserTypes(UserTypeResult $other): self
    {
        $mergedTypes = array_merge($this->user_types, $other->user_types);
        $mergedMetadata = array_merge_recursive($this->metadata, $other->metadata);

        return new static(
            user_types: $mergedTypes,
            metadata: $mergedMetadata,
            success: $this->success && $other->success,
            message: $this->message ?: $other->message
        );
    }

    // 获取所有UserType对象
    public function getUserTypes(): array
    {
        return $this->user_types;
    }

    // 获取简化格式类型数组（向后兼容）
    public function getUserTypesSimple(): array
    {
        return array_map(fn($type) => $type->toArray(), $this->user_types);
    }

    // 获取类型选项数组（用于表单）
    public function getTypeOptions(): array
    {
        $options = [];
        foreach ($this->user_types as $type) {
            $options[$type->type_id] = $type->type_name;
        }
        return $options;
    }

    // 检查是否包含特定类型
    public function hasType(string $typeId): bool
    {
        foreach ($this->user_types as $type) {
            if ($type->type_id === $typeId) {
                return true;
            }
        }
        return false;
    }

    // 获取特定类型
    public function getType(string $typeId): ?UserType
    {
        foreach ($this->user_types as $type) {
            if ($type->type_id === $typeId) {
                return $type;
            }
        }
        return null;
    }

    // 获取类型总数
    public function getCount(): int
    {
        return count($this->user_types);
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'errors' => $this->errors,
            'message' => $this->message,
            'user_types' => $this->getUserTypesSimple(),
            'count' => $this->getCount(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * 实现 JsonSerializable 接口
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
