<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Handlers;

use Modules\ABase\Hooks\Core\CoreHookHandler;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\Demo5\Hooks\Parameters\ArticleFilterHookParameter;
use Modules\Demo5\Hooks\Results\ArticleFilterHookResult;

/**
 * ArticleFilterHook 处理器
 *
 * 清理HTML标签，只保留 <p><br><strong><em>
 */
class ArticleFilterHookHandler extends CoreHookHandler
{
    /**
     * 处理器优先级（越小越先执行）
     */
    protected static int $priority = 10;

    /**
     * 允许保留的HTML标签
     */
    private static array $allowedTags = ['<p>', '<br>', '<strong>', '<em>'];

    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return self::$priority;
    }

    /**
     * 判断是否应该执行此处理器
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        // 只有当内容不为空时才执行
        $data = $parameter->toArray();
        return !empty($data['content']) || !empty($data['title']);
    }

    /**
     * 处理文章内容过滤
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        try {
            // 类型转换
            if (!($parameter instanceof ArticleFilterHookParameter)) {
                return ArticleFilterHookResult::failure('参数类型错误');
            }

            // 获取参数
            $title = $parameter->getTitle();
            $content = $parameter->getContent();
            $authorId = $parameter->getAuthorId();

            // 过滤HTML标签
            $filteredTitle = static::filterHtmlTags($title);
            $filteredContent = static::filterHtmlTags($content);

            // 尝试记录日志（忽略日志失败）
            try {
                static::log('ArticleFilterHook处理完成', [
                    'original_title_length' => strlen($title),
                    'filtered_title_length' => strlen($filteredTitle),
                    'original_content_length' => strlen($content),
                    'filtered_content_length' => strlen($filteredContent),
                    'author_id' => $authorId,
                ]);
            } catch (\Exception $logException) {
                // 日志失败不影响业务逻辑
            }

            return ArticleFilterHookResult::success(
                $filteredTitle,
                $filteredContent,
                $authorId,
                '文章内容过滤成功'
            );
        } catch (\Exception $e) {
            // 尝试记录错误日志（忽略日志失败）
            try {
                static::logError('ArticleFilterHook处理失败', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Exception $logException) {
                // 日志失败不影响业务逻辑
            }

            return ArticleFilterHookResult::failure(
                $e->getMessage(),
                '文章内容过滤失败'
            );
        }
    }

    /**
     * 过滤HTML标签，只保留允许的标签
     *
     * @param string $content 原始内容
     * @return string 过滤后的内容
     */
    private static function filterHtmlTags(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // 使用 strip_tags 保留允许的标签
        $allowedTagsString = implode('', self::$allowedTags);
        $filtered = strip_tags($content, $allowedTagsString);

        // 清理可能的XSS攻击向量（处理标签属性）
        $filtered = static::sanitizeTagAttributes($filtered);

        return $filtered;
    }

    /**
     * 清理HTML标签的属性，防止XSS攻击
     *
     * @param string $content 内容
     * @return string 清理后的内容
     */
    private static function sanitizeTagAttributes(string $content): string
    {
        // 移除所有标签的属性，只保留标签本身
        $patterns = [
            '/<p\s+[^>]*>/i' => '<p>',
            '/<strong\s+[^>]*>/i' => '<strong>',
            '/<em\s+[^>]*>/i' => '<em>',
            '/<br\s+[^>]*>/i' => '<br>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }
}