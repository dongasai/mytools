<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Handlers;

use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;

/**
 * 文章内容过滤器
 *
 * 自动清理HTML标签，只保留基本的格式化标签
 */
class PostContentFilterHandler implements HookHandlerInterface
{
    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return 10;
    }

    /**
     * 检查处理器是否应该执行
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        return true;
    }

    /**
     * 处理Hook的核心方法
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 检查参数类型
        if (! $parameter instanceof \Modules\Demo5\Hooks\Parameters\PostContentParameter) {
            return $result;
        }

        $content = $parameter->getContent();
        $filteredContent = strip_tags($content, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6>');

        return \Modules\Demo5\Hooks\Results\PostContentResult::success($filteredContent, [
            'original_length' => strlen($content),
            'filtered_length' => strlen($filteredContent),
            'handler' => static::class,
        ]);
    }

    /**
     * 获取处理器描述
     */
    public function getDescription(): string
    {
        return '文章内容过滤器，自动清理HTML标签';
    }

    /**
     * 获取处理器版本
     */
    public function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * 获取处理器作者
     */
    public function getAuthor(): string
    {
        return 'Demo5';
    }

    /**
     * 获取处理器标签
     */
    public function getTags(): array
    {
        return ['filter', 'content', 'html'];
    }

    /**
     * 检查处理器是否支持指定的参数类型
     */
    public function supportsParameter(string $parameterClass): bool
    {
        return $parameterClass === \Modules\Demo5\Hooks\Parameters\PostContentParameter::class ||
            is_subclass_of($parameterClass, \Modules\Demo5\Hooks\Parameters\PostContentParameter::class);
    }

    /**
     * 检查处理器是否支持指定的返回值类型
     */
    public function supportsResult(string $resultClass): bool
    {
        return $resultClass === \Modules\Demo5\Hooks\Results\PostContentResult::class ||
            is_subclass_of($resultClass, \Modules\Demo5\Hooks\Results\PostContentResult::class);
    }

    /**
     * 获取处理器唯一标识
     */
    public function getIdentifier(): string
    {
        return 'demo5.post_content_filter';
    }
}
