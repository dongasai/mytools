<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;

/**
 * 文章内容处理结果
 *
 * 用于Hook系统的文章内容处理结果返回
 */
class PostContentResult extends HookResult
{
    public function __construct(
        public readonly string $processedContent = '',
        public readonly array $metadata = []
    ) {
        parent::__construct(true);
    }

    public static function success(string|array $data, array $metadata = []): static
    {
        $content = is_array($data) ? ($data['content'] ?? '') : $data;
        $meta = is_array($data) ? array_merge($data['metadata'] ?? [], $metadata) : $metadata;

        return new static($content, $meta);
    }

    public static function failure(array $errors): static
    {
        $result = new static('', []);
        foreach ($errors as $error) {
            $result->addError($error);
        }

        return $result;
    }

    /**
     * 获取处理后的内容
     */
    public function getProcessedContent(): string
    {
        return $this->processedContent;
    }

    /**
     * 获取元数据
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * 获取指定元数据
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * 实现JsonSerializable接口
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::toArray(), [
            'processed_content' => $this->getProcessedContent(),
            'metadata' => $this->getMetadata(),
        ]);
    }
}
