<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 通用Map参数类
 *
 * 用于传递键值对参数给Hook处理器
 */
class MapHookParameter extends HookParameter
{
    public function __construct(
        public readonly array $data = []
    ) {
        parent::__construct();
    }

    /**
     * 获取参数数据
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取指定键的值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * 验证参数
     */
    protected function validate(): void
    {
        // 通用参数无需特殊验证
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * JSON序列化
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 创建实例
     */
    public static function create(array $data = []): self
    {
        return new self($data);
    }
}
