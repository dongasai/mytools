<?php

namespace Modules\Demo5\Logics;

/**
 * 文章业务逻辑层
 *
 * 专注于文章相关的业务规则、算法和计算逻辑
 * 不涉及数据库操作和外部服务调用
 */
class PostLogic
{
    /**
     * 计算文章阅读时间（分钟）
     *
     * @param  string  $content  文章内容
     * @param  int  $wordsPerMinute  每分钟阅读字数
     * @return int 预估阅读时间（分钟）
     */
    public static function calculateReadingTime(string $content, int $wordsPerMinute = 200): int
    {
        $cleanContent = strip_tags($content);
        $wordCount = str_word_count($cleanContent);

        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * 计算文章字数
     *
     * @param  string  $content  文章内容
     * @return int 字数统计
     */
    public static function calculateWordCount(string $content): int
    {
        return str_word_count(strip_tags($content));
    }

    /**
     * 生成文章摘要
     *
     * @param  string  $content  文章内容
     * @param  int  $maxLength  最大长度
     * @return string 文章摘要
     */
    public static function generateExcerpt(string $content, int $maxLength = 200): string
    {
        $cleanContent = strip_tags($content);

        if (strlen($cleanContent) <= $maxLength) {
            return $cleanContent;
        }

        return substr($cleanContent, 0, $maxLength).'...';
    }

    /**
     * 提取文章关键词
     *
     * @param  string  $content  文章内容
     * @param  int  $limit  关键词数量限制
     * @return array 关键词数组
     */
    public static function extractKeywords(string $content, int $limit = 10): array
    {
        $cleanContent = strtolower(strip_tags($content));

        // 移除常见停用词
        $stopWords = [
            'the', 'is', 'at', 'which', 'on', 'a', 'an', 'as', 'are', 'was', 'were',
            'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
            'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these',
            'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which',
            'who', 'when', 'where', 'why', 'how', 'all', 'each', 'every', 'both', 'few',
            'more', 'most', 'other', 'some', 'such', 'only', 'own', 'same', 'so', 'than',
            'too', 'very', 'just', '的', '了', '在', '是', '我', '有', '和', '就', '不',
            '人', '都', '一', '一个', '上', '也', '很', '到', '说', '要', '去', '你',
            '会', '着', '没有', '看', '好', '自己', '这', '那', '里', '就是', '还', '把',
        ];

        // 分词并过滤停用词
        $words = str_word_count($cleanContent, 1);
        $filteredWords = array_diff($words, $stopWords);

        // 统计词频
        $wordCounts = array_count_values($filteredWords);
        arsort($wordCounts);

        return array_slice(array_keys($wordCounts), 0, $limit);
    }

    /**
     * 检查文章标题质量评分
     *
     * @param  string  $title  文章标题
     * @return array 评分信息
     */
    public static function evaluateTitleQuality(string $title): array
    {
        $score = 0;
        $issues = [];

        // 长度检查
        $length = strlen($title);
        if ($length < 10) {
            $issues[] = '标题过短，建议至少10个字符';
        } elseif ($length > 100) {
            $issues[] = '标题过长，建议不超过100个字符';
        } else {
            $score += 20;
        }

        // 包含数字
        if (preg_match('/\d/', $title)) {
            $score += 10;
        }

        // 包含疑问词
        $questionWords = ['什么', '如何', '为什么', '怎么', 'what', 'how', 'why'];
        foreach ($questionWords as $word) {
            if (stripos($title, $word) !== false) {
                $score += 15;
                break;
            }
        }

        // 情感词汇检查
        $emotionalWords = ['震惊', '惊呆', '必看', '重磅', '独家', '揭秘'];
        foreach ($emotionalWords as $word) {
            if (stripos($title, $word) !== false) {
                $score -= 10;
                $issues[] = '避免使用过于夸张的词汇';
                break;
            }
        }

        // 标点符号检查
        if (! preg_match('/[。！？.!?]$/', $title)) {
            $issues[] = '建议以句号或问号结尾';
        } else {
            $score += 10;
        }

        return [
            'score' => max(0, min(100, $score)),
            'issues' => $issues,
            'quality' => self::getQualityLevel($score),
        ];
    }

    /**
     * 获取质量等级
     *
     * @param  int  $score  评分
     * @return string 质量等级
     */
    protected static function getQualityLevel(int $score): string
    {
        if ($score >= 80) {
            return 'excellent'; // 优秀
        } elseif ($score >= 60) {
            return 'good'; // 良好
        } elseif ($score >= 40) {
            return 'average'; // 一般
        } else {
            return 'poor'; // 较差
        }
    }

    /**
     * 检查文章内容重复度（简单实现）
     *
     * @param  string  $content  文章内容
     * @param  array  $existingContents  已存在的内容数组
     * @return float 重复度百分比 (0-100)
     */
    public static function calculateSimilarity(string $content, array $existingContents = []): float
    {
        if (empty($existingContents)) {
            return 0.0;
        }

        $contentWords = array_unique(str_word_count(strtolower(strip_tags($content)), 1));
        $maxSimilarity = 0.0;

        foreach ($existingContents as $existingContent) {
            $existingWords = array_unique(str_word_count(strtolower(strip_tags($existingContent)), 1));

            if (empty($existingWords)) {
                continue;
            }

            $commonWords = array_intersect($contentWords, $existingWords);
            $totalWords = array_unique(array_merge($contentWords, $existingWords));

            $similarity = count($commonWords) / count($totalWords) * 100;
            $maxSimilarity = max($maxSimilarity, $similarity);
        }

        return round($maxSimilarity, 2);
    }

    /**
     * 判断是否为热门时间段
     *
     * @param  \DateTime  $dateTime  时间
     * @return bool 是否为热门时间段
     */
    public static function isPeakPublishingTime(\DateTime $dateTime): bool
    {
        $hour = (int) $dateTime->format('H');
        $dayOfWeek = (int) $dateTime->format('N'); // 1-7 (Monday-Sunday)

        // 工作日的8-10点和18-22点为热门时间
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            return ($hour >= 8 && $hour <= 10) || ($hour >= 18 && $hour <= 22);
        }

        // 周末的10-12点和14-18点为热门时间
        return ($dayOfWeek >= 6 && $dayOfWeek <= 7) &&
               (($hour >= 10 && $hour <= 12) || ($hour >= 14 && $hour <= 18));
    }

    /**
     * 推荐最佳发布时间
     *
     * @param  \DateTime  $currentTime  当前时间
     * @return \DateTime 推荐的发布时间
     */
    public static function recommendPublishTime(\DateTime $currentTime): \DateTime
    {
        $tomorrow = clone $currentTime;
        $tomorrow->modify('+1 day');
        $tomorrow->setTime(9, 0, 0);

        // 找到下一个热门时间段
        for ($i = 0; $i < 7; $i++) {
            $checkTime = clone $tomorrow;
            $checkTime->modify("+{$i} days");

            if ($this->isPeakPublishingTime($checkTime)) {
                return $checkTime;
            }
        }

        // 如果没找到，返回明天上午9点
        return $tomorrow;
    }

    /**
     * 计算文章参与度评分
     *
     * @param  array  $metrics  统计数据 ['views' => int, 'likes' => int, 'comments' => int, 'shares' => int]
     * @return float 参与度评分 (0-100)
     */
    public static function calculateEngagementScore(array $metrics): float
    {
        $views = $metrics['views'] ?? 0;
        $likes = $metrics['likes'] ?? 0;
        $comments = $metrics['comments'] ?? 0;
        $shares = $metrics['shares'] ?? 0;

        if ($views == 0) {
            return 0.0;
        }

        // 权重：点赞(30%) + 评论(40%) + 分享(30%)
        $likeScore = ($likes / $views) * 30;
        $commentScore = ($comments / $views) * 40;
        $shareScore = ($shares / $views) * 30;

        return round(min(100, ($likeScore + $commentScore + $shareScore) * 100), 2);
    }
}
