<?php

namespace Modules\Demo5\Logics;

use Carbon\Carbon;

/**
 * 统计逻辑层
 *
 * 专注于数据统计、分析和计算逻辑
 * 提供各种统计指标的计算方法
 */
class StatisticsLogic
{
    /**
     * 计算文章增长率
     *
     * @param  int  $currentCount  当前数量
     * @param  int  $previousCount  上期数量
     * @return float 增长率百分比
     */
    public static function calculateGrowthRate(int $currentCount, int $previousCount): float
    {
        if ($previousCount == 0) {
            return $currentCount > 0 ? 100.0 : 0.0;
        }

        return round((($currentCount - $previousCount) / $previousCount) * 100, 2);
    }

    /**
     * 计算时间段内的发布频率
     *
     * @param  array  $publishDates  发布日期数组
     * @param  string  $period  时间周期 ('daily', 'weekly', 'monthly')
     * @return array 统计数据
     */
    public static function calculatePublishingFrequency(array $publishDates, string $period = 'daily'): array
    {
        if (empty($publishDates)) {
            return [];
        }

        $periodMap = [
            'daily' => 'Y-m-d',
            'weekly' => 'Y-W',
            'monthly' => 'Y-m',
        ];

        $format = $periodMap[$period] ?? 'Y-m-d';
        $grouped = [];

        foreach ($publishDates as $date) {
            $key = (new Carbon($date))->format($format);
            $grouped[$key] = ($grouped[$key] ?? 0) + 1;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * 分析发布时间分布
     *
     * @param  array  $publishDates  发布日期数组
     * @return array 时间分布统计
     */
    public static function analyzeTimeDistribution(array $publishDates): array
    {
        $hourDistribution = array_fill(0, 24, 0);
        $weekdayDistribution = array_fill(1, 7, 0);
        $monthlyDistribution = array_fill(1, 12, 0);

        foreach ($publishDates as $date) {
            $carbon = new Carbon($date);

            $hourDistribution[$carbon->hour]++;
            $weekdayDistribution[$carbon->dayOfWeek]++;
            $monthlyDistribution[$carbon->month]++;
        }

        return [
            'hourly' => $hourDistribution,
            'weekday' => $weekdayDistribution,
            'monthly' => $monthlyDistribution,
            'peak_hour' => array_keys($hourDistribution, max($hourDistribution))[0] ?? 0,
            'peak_weekday' => array_keys($weekdayDistribution, max($weekdayDistribution))[0] ?? 0,
            'peak_month' => array_keys($monthlyDistribution, max($monthlyDistribution))[0] ?? 0,
        ];
    }

    /**
     * 计算参与度指标
     *
     * @param  array  $metrics  统计数据
     * @return array 参与度指标
     */
    public static function calculateEngagementMetrics(array $metrics): array
    {
        $totalPosts = $metrics['total_posts'] ?? 0;
        $totalViews = $metrics['total_views'] ?? 0;
        $totalLikes = $metrics['total_likes'] ?? 0;
        $totalComments = $metrics['total_comments'] ?? 0;
        $totalShares = $metrics['total_shares'] ?? 0;

        if ($totalPosts == 0) {
            return [
                'avg_views_per_post' => 0,
                'avg_likes_per_post' => 0,
                'avg_comments_per_post' => 0,
                'avg_shares_per_post' => 0,
                'engagement_rate' => 0,
                'virality_score' => 0,
            ];
        }

        $avgViews = $totalViews / $totalPosts;
        $avgLikes = $totalLikes / $totalPosts;
        $avgComments = $totalComments / $totalPosts;
        $avgShares = $totalShares / $totalPosts;

        // 参与度 = (点赞 + 评论 + 分享) / 浏览量
        $engagementRate = $totalViews > 0
            ? (($totalLikes + $totalComments + $totalShares) / $totalViews) * 100
            : 0;

        // 病毒式传播分数 = 分享数 * 2 + 评论数 * 1 + 点赞数 * 0.5
        $viralityScore = ($totalShares * 2 + $totalComments * 1 + $totalLikes * 0.5) / $totalPosts;

        return [
            'avg_views_per_post' => round($avgViews, 2),
            'avg_likes_per_post' => round($avgLikes, 2),
            'avg_comments_per_post' => round($avgComments, 2),
            'avg_shares_per_post' => round($avgShares, 2),
            'engagement_rate' => round($engagementRate, 2),
            'virality_score' => round($viralityScore, 2),
        ];
    }

    /**
     * 分析内容质量趋势
     *
     * @param  array  $posts  文章数据，每个包含word_count, reading_time等
     * @param  string  $period  分析周期
     * @return array 质量趋势分析
     */
    public static function analyzeQualityTrends(array $posts, string $period = 'monthly'): array
    {
        if (empty($posts)) {
            return [];
        }

        $grouped = [];

        foreach ($posts as $post) {
            $key = (new Carbon($post['created_at']))->format(self::getPeriodFormat($period));

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'count' => 0,
                    'total_word_count' => 0,
                    'total_reading_time' => 0,
                    'total_views' => 0,
                    'total_likes' => 0,
                    'total_comments' => 0,
                ];
            }

            $grouped[$key]['count']++;
            $grouped[$key]['total_word_count'] += $post['word_count'] ?? 0;
            $grouped[$key]['total_reading_time'] += $post['reading_time'] ?? 0;
            $grouped[$key]['total_views'] += $post['views'] ?? 0;
            $grouped[$key]['total_likes'] += $post['likes'] ?? 0;
            $grouped[$key]['total_comments'] += $post['comments'] ?? 0;
        }

        // 计算平均值
        foreach ($grouped as $key => &$data) {
            $count = $data['count'];
            $data['avg_word_count'] = $count > 0 ? round($data['total_word_count'] / $count) : 0;
            $data['avg_reading_time'] = $count > 0 ? round($data['total_reading_time'] / $count) : 0;
            $data['avg_views'] = $count > 0 ? round($data['total_views'] / $count) : 0;
            $data['avg_likes'] = $count > 0 ? round($data['total_likes'] / $count) : 0;
            $data['avg_comments'] = $count > 0 ? round($data['total_comments'] / $count) : 0;

            // 计算质量分数 (基于阅读时间和参与度)
            $data['quality_score'] = self::calculateQualityScore($data);
        }

        ksort($grouped);

        return array_values($grouped);
    }

    /**
     * 计算热门主题分布
     *
     * @param  array  $posts  文章数据，每个包含tags或categories
     * @param  int  $limit  返回数量限制
     * @return array 热门主题统计
     */
    public static function calculateTrendingTopics(array $posts, int $limit = 10): array
    {
        $topicCounts = [];

        foreach ($posts as $post) {
            $topics = [];

            // 从标签获取主题
            if (isset($post['tags']) && is_array($post['tags'])) {
                $topics = array_merge($topics, $post['tags']);
            }

            // 从分类获取主题
            if (isset($post['category'])) {
                $topics[] = $post['category'];
            }

            // 从标题提取关键词作为主题
            if (isset($post['title'])) {
                $keywords = self::extractTopicsFromTitle($post['title']);
                $topics = array_merge($topics, $keywords);
            }

            foreach ($topics as $topic) {
                $topic = strtolower(trim($topic));
                if (strlen($topic) > 2) {
                    $topicCounts[$topic] = ($topicCounts[$topic] ?? 0) + 1;
                }
            }
        }

        arsort($topicCounts);

        return array_slice($topicCounts, 0, $limit, true);
    }

    /**
     * 计算用户活跃度
     *
     * @param  array  $activities  用户活动数据
     * @param  int  $days  分析天数
     * @return array 活跃度分析
     */
    public static function calculateUserActivity(array $activities, int $days = 30): array
    {
        $cutoffDate = Carbon::now()->subDays($days);
        $dailyActivity = array_fill(0, $days, 0);
        $userStats = [];

        foreach ($activities as $activity) {
            $activityDate = new Carbon($activity['created_at']);

            if ($activityDate >= $cutoffDate) {
                $dayIndex = $activityDate->diffInDays($cutoffDate);
                if ($dayIndex < $days) {
                    $dailyActivity[$dayIndex]++;
                }

                $userId = $activity['user_id'];
                if (! isset($userStats[$userId])) {
                    $userStats[$userId] = ['count' => 0, 'last_activity' => null];
                }
                $userStats[$userId]['count']++;

                if (! $userStats[$userId]['last_activity'] || $activityDate > $userStats[$userId]['last_activity']) {
                    $userStats[$userId]['last_activity'] = $activityDate;
                }
            }
        }

        // 计算活跃用户数
        $activeUsers = array_filter($userStats, function ($stats) use ($cutoffDate) {
            return $stats['last_activity'] && $stats['last_activity'] >= $cutoffDate;
        });

        return [
            'daily_activity' => $dailyActivity,
            'total_activities' => array_sum($dailyActivity),
            'avg_daily_activities' => round(array_sum($dailyActivity) / $days, 2),
            'active_users_count' => count($activeUsers),
            'total_users_count' => count($userStats),
            'activity_peak_day' => array_keys($dailyActivity, max($dailyActivity))[0] ?? 0,
        ];
    }

    /**
     * 生成统计报告摘要
     *
     * @param  array  $statistics  统计数据
     * @return array 报告摘要
     */
    public static function generateSummaryReport(array $statistics): array
    {
        return [
            'overview' => [
                'total_posts' => $statistics['total_posts'] ?? 0,
                'published_posts' => $statistics['published_posts'] ?? 0,
                'draft_posts' => $statistics['draft_posts'] ?? 0,
                'total_views' => $statistics['total_views'] ?? 0,
                'total_engagement' => ($statistics['total_likes'] ?? 0) +
                                    ($statistics['total_comments'] ?? 0) +
                                    ($statistics['total_shares'] ?? 0),
            ],
            'performance' => [
                'avg_views_per_post' => $statistics['avg_views_per_post'] ?? 0,
                'engagement_rate' => $statistics['engagement_rate'] ?? 0,
                'publishing_frequency' => $statistics['publishing_frequency'] ?? 0,
            ],
            'trends' => [
                'growth_rate' => $statistics['growth_rate'] ?? 0,
                'quality_trend' => $statistics['quality_trend'] ?? 'stable',
                'peak_publishing_time' => $statistics['peak_publishing_time'] ?? 'unknown',
            ],
            'insights' => self::generateInsights($statistics),
        ];
    }

    /**
     * 生成洞察建议
     *
     * @param  array  $statistics  统计数据
     * @return array 洞察建议
     */
    protected static function generateInsights(array $statistics): array
    {
        $insights = [];

        // 参与度洞察
        $engagementRate = $statistics['engagement_rate'] ?? 0;
        if ($engagementRate < 2) {
            $insights[] = '参与度偏低，建议增加互动性内容和推广';
        } elseif ($engagementRate > 10) {
            $insights[] = '参与度表现优秀，保持当前策略';
        }

        // 发布频率洞察
        $frequency = $statistics['publishing_frequency'] ?? 0;
        if ($frequency < 1) {
            $insights[] = '发布频率较低，建议增加内容更新频率';
        } elseif ($frequency > 7) {
            $insights[] = '发布频率很高，注意保持内容质量';
        }

        // 质量趋势洞察
        $qualityTrend = $statistics['quality_trend'] ?? 'stable';
        if ($qualityTrend === 'declining') {
            $insights[] = '内容质量呈下降趋势，建议重新评估内容策略';
        } elseif ($qualityTrend === 'improving') {
            $insights[] = '内容质量持续改善，继续保持';
        }

        return $insights;
    }

    /**
     * 获取时间格式
     *
     * @param  string  $period  周期
     * @return string 格式
     */
    protected static function getPeriodFormat(string $period): string
    {
        $formats = [
            'daily' => 'Y-m-d',
            'weekly' => 'Y-W',
            'monthly' => 'Y-m',
            'yearly' => 'Y',
        ];

        return $formats[$period] ?? 'Y-m';
    }

    /**
     * 计算质量分数
     *
     * @param  array  $data  数据
     * @return float 质量分数
     */
    protected static function calculateQualityScore(array $data): float
    {
        $readingTimeWeight = 0.3;
        $engagementWeight = 0.7;

        $normalizedReadingTime = min($data['avg_reading_time'] / 10, 1); // 标准化到0-1
        $normalizedEngagement = min(($data['avg_likes'] + $data['avg_comments']) / 50, 1); // 标准化到0-1

        return round(($normalizedReadingTime * $readingTimeWeight + $normalizedEngagement * $engagementWeight) * 100, 2);
    }

    /**
     * 从标题提取主题
     *
     * @param  string  $title  标题
     * @return array 主题数组
     */
    protected static function extractTopicsFromTitle(string $title): array
    {
        // 简单的关键词提取
        $keywords = preg_split('/[\s,，.。!！?？、]+/', $title);

        return array_filter($keywords, function ($word) {
            return strlen($word) > 2 && ! in_array($word, ['的', '了', '是', '在', '和', '与', '及']);
        });
    }
}
