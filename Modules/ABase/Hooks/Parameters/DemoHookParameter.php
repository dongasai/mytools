<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * DemoHook参数类
 *
 * 用于演示Hook系统的参数传递和处理
 */
class DemoHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $name = '',
        public readonly int $value = 0,
        public readonly array $options = [],
        public readonly bool $enabled = true,
        public readonly ?string $description = null
    ) {
        parent::__construct();
    }

    /**
     * 获取名称
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取值
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * 获取选项
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 是否启用
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * 获取描述
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * 验证参数
     */
    protected function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('DemoHook name cannot be empty');
        }

        if ($this->value < 0) {
            throw new \InvalidArgumentException('DemoHook value must be non-negative');
        }
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'options' => $this->options,
            'enabled' => $this->enabled,
            'description' => $this->description,
        ];
    }

    /**
     * JSON序列化
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 创建DemoHook参数实例
     */
    public static function create(
        string $name,
        int $value = 0,
        array $options = [],
        bool $enabled = true,
        ?string $description = null
    ): self {
        return new self($name, $value, $options, $enabled, $description);
    }
}
