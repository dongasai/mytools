<?php

namespace Modules\Demo5\Logics;

use Modules\Demo5\Enums\PostStatus;

/**
 * 验证逻辑层
 *
 * 专注于数据验证、业务规则验证和格式检查
 * 提供纯函数式的验证方法
 */
class ValidationLogic
{
    /**
     * 验证文章数据
     *
     * @param  array  $data  文章数据
     * @param  bool  $isUpdate  是否为更新操作
     * @return array 验证结果 ['valid' => bool, 'errors' => array]
     */
    public static function validatePostData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // 标题验证
        if (! $isUpdate || isset($data['title'])) {
            $titleErrors = self::validateTitle($data['title'] ?? '');
            $errors = array_merge($errors, $titleErrors);
        }

        // 内容验证
        if (! $isUpdate || isset($data['content'])) {
            $contentErrors = self::validateContent($data['content'] ?? '');
            $errors = array_merge($errors, $contentErrors);
        }

        // 状态验证
        if (! $isUpdate || isset($data['status'])) {
            $statusErrors = self::validateStatus($data['status'] ?? '');
            $errors = array_merge($errors, $statusErrors);
        }

        // 用户ID验证
        if (! $isUpdate || isset($data['user_id'])) {
            $userIdErrors = self::validateUserId($data['user_id'] ?? null);
            $errors = array_merge($errors, $userIdErrors);
        }

        // 发布时间验证
        if (! $isUpdate || isset($data['published_at'])) {
            $publishTimeErrors = self::validatePublishTime($data['published_at'] ?? null, $data['status'] ?? '');
            $errors = array_merge($errors, $publishTimeErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * 验证标题
     *
     * @param  string  $title  标题
     * @return array 错误信息
     */
    public static function validateTitle(string $title): array
    {
        $errors = [];

        if (empty($title)) {
            $errors[] = '标题不能为空';
        } else {
            $length = mb_strlen($title, 'UTF-8');

            if ($length < 5) {
                $errors[] = '标题至少需要5个字符';
            } elseif ($length > 200) {
                $errors[] = '标题不能超过200个字符';
            }

            // 检查是否包含特殊字符
            if (preg_match('/[\x00-\x1F\x7F]/', $title)) {
                $errors[] = '标题包含非法字符';
            }

            // 检查是否全为空格或标点符号
            if (! preg_match('/[\p{L}\p{N}]/u', $title)) {
                $errors[] = '标题必须包含至少一个字母或数字';
            }
        }

        return $errors;
    }

    /**
     * 验证内容
     *
     * @param  string  $content  内容
     * @return array 错误信息
     */
    public static function validateContent(string $content): array
    {
        $errors = [];

        if (empty($content)) {
            $errors[] = '内容不能为空';
        } else {
            $cleanContent = strip_tags($content);
            $length = mb_strlen($cleanContent, 'UTF-8');

            if ($length < 20) {
                $errors[] = '内容至少需要20个字符';
            } elseif ($length > 50000) {
                $errors[] = '内容不能超过50000个字符';
            }

            // 检查是否包含可疑链接
            $linkCount = substr_count($content, 'http://') + substr_count($content, 'https://');
            if ($linkCount > 10) {
                $errors[] = '链接数量不能超过10个';
            }

            // 检查内容质量
            $wordCount = str_word_count($cleanContent);
            if ($wordCount < 10) {
                $errors[] = '内容过于简单，请提供更有价值的信息';
            }
        }

        return $errors;
    }

    /**
     * 验证状态
     *
     * @param  string  $status  状态
     * @return array 错误信息
     */
    public static function validateStatus(string $status): array
    {
        $errors = [];

        $validStatuses = [
            PostStatus::Published->value,
            PostStatus::Draft->value,
            PostStatus::Archived->value,
        ];

        if (empty($status)) {
            $errors[] = '状态不能为空';
        } elseif (! in_array($status, $validStatuses)) {
            $errors[] = '无效的状态值';
        }

        return $errors;
    }

    /**
     * 验证用户ID
     *
     * @param  mixed  $userId  用户ID
     * @return array 错误信息
     */
    public static function validateUserId($userId): array
    {
        $errors = [];

        if ($userId === null || $userId === '') {
            $errors[] = '用户ID不能为空';
        } elseif (! is_numeric($userId) || $userId <= 0) {
            $errors[] = '用户ID必须是正整数';
        }

        return $errors;
    }

    /**
     * 验证发布时间
     *
     * @param  mixed  $publishTime  发布时间
     * @param  string  $status  状态
     * @return array 错误信息
     */
    public static function validatePublishTime($publishTime, string $status): array
    {
        $errors = [];

        if ($status === PostStatus::Published->value) {
            if (empty($publishTime)) {
                $errors[] = '已发布文章必须设置发布时间';
            } else {
                try {
                    // 如果已经是DateTime对象，直接使用
                    $dateTime = $publishTime instanceof \DateTime ? $publishTime : new \DateTime($publishTime);
                    $now = new \DateTime;

                    if ($dateTime > $now) {
                        $errors[] = '发布时间不能是未来时间';
                    }

                    // 检查发布时间是否过于久远
                    $oneYearAgo = clone $now;
                    $oneYearAgo->modify('-1 year');

                    if ($dateTime < $oneYearAgo) {
                        $errors[] = '发布时间不能早于一年前';
                    }
                } catch (\Exception $e) {
                    $errors[] = '发布时间格式无效';
                }
            }
        }

        return $errors;
    }

    /**
     * 验证搜索关键词
     *
     * @param  string  $keyword  关键词
     * @return array 验证结果
     */
    public static function validateSearchKeyword(string $keyword): array
    {
        $errors = [];

        $keyword = trim($keyword);
        $length = mb_strlen($keyword, 'UTF-8');

        if ($length == 0) {
            $errors[] = '搜索关键词不能为空';
        } elseif ($length < 2) {
            $errors[] = '搜索关键词至少需要2个字符';
        } elseif ($length > 100) {
            $errors[] = '搜索关键词不能超过100个字符';
        }

        // 检查是否包含特殊字符
        if (preg_match('/[<>"\']/', $keyword)) {
            $errors[] = '搜索关键词包含非法字符';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'cleaned' => trim($keyword),
        ];
    }

    /**
     * 验证分页参数
     *
     * @param  int  $page  页码
     * @param  int  $perPage  每页数量
     * @return array 验证结果
     */
    public static function validatePaginationParams(int $page, int $perPage): array
    {
        $errors = [];

        if ($page < 1) {
            $errors[] = '页码必须大于0';
        } elseif ($page > 1000) {
            $errors[] = '页码不能超过1000';
        }

        if ($perPage < 1) {
            $errors[] = '每页数量必须大于0';
        } elseif ($perPage > 100) {
            $errors[] = '每页数量不能超过100';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'page' => max(1, min(1000, $page)),
            'per_page' => max(1, min(100, $perPage)),
        ];
    }

    /**
     * 验证排序参数
     *
     * @param  string  $sortBy  排序字段
     * @param  string  $sortOrder  排序方向
     * @return array 验证结果
     */
    public static function validateSortParams(string $sortBy, string $sortOrder): array
    {
        $errors = [];

        $validSortFields = ['id', 'title', 'status', 'created_at', 'updated_at', 'published_at'];
        $validSortOrders = ['asc', 'desc'];

        if (! in_array($sortBy, $validSortFields)) {
            $errors[] = '无效的排序字段';
        }

        if (! in_array(strtolower($sortOrder), $validSortOrders)) {
            $errors[] = '无效的排序方向';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sort_by' => in_array($sortBy, $validSortFields) ? $sortBy : 'created_at',
            'sort_order' => in_array(strtolower($sortOrder), $validSortOrders) ? strtolower($sortOrder) : 'desc',
        ];
    }

    /**
     * 验证批量操作参数
     *
     * @param  array  $ids  ID数组
     * @param  string  $action  操作类型
     * @return array 验证结果
     */
    public static function validateBatchOperation(array $ids, string $action): array
    {
        $errors = [];

        if (empty($ids)) {
            $errors[] = '请选择要操作的项目';
        } elseif (count($ids) > 100) {
            $errors[] = '批量操作最多支持100个项目';
        }

        foreach ($ids as $id) {
            if (! is_numeric($id) || $id <= 0) {
                $errors[] = '包含无效的ID';
                break;
            }
        }

        $validActions = ['publish', 'draft', 'archive', 'delete'];
        if (! in_array($action, $validActions)) {
            $errors[] = '无效的操作类型';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'ids' => array_filter($ids, 'is_numeric'),
            'action' => in_array($action, $validActions) ? $action : null,
        ];
    }

    /**
     * 清理和验证HTML内容
     *
     * @param  string  $html  HTML内容
     * @param  array  $allowedTags  允许的HTML标签
     * @return array 验证结果
     */
    public static function validateHtmlContent(string $html, array $allowedTags = ['p', 'br', 'strong', 'em', 'ul', 'ol', 'li']): array
    {
        $errors = [];

        // 移除不允许的HTML标签
        $cleanedHtml = strip_tags($html, $allowedTags);

        // 检查是否包含脚本标签
        if (stripos($html, '<script') !== false) {
            $errors[] = '不允许包含脚本标签';
        }

        // 检查是否包含危险属性
        $dangerousPatterns = [
            '/on\w+\s*=/i',  // 事件处理器
            '/javascript:/i',  // JavaScript协议
            '/data:text\/html/i',  // Data URL
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $html)) {
                $errors[] = '包含不安全的HTML属性';
                break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'cleaned_html' => $cleanedHtml,
        ];
    }
}
