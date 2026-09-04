<?php

declare(strict_types=1);

namespace Modules\Demo5\QueueJobs;

use DLaravel\Queue\QueueJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Demo5\Models\Demo5Post;

/**
 * 文章发布处理队列任务
 *
 * 用于异步处理文章发布后的相关操作
 * 例如：发送通知邮件、更新缓存、生成搜索索引等
 *
 * 使用方法：
 * ProcessPostPublishingJob::dispatch($postId);
 * ProcessPostPublishingJob::dispatch($postId)->delay(now()->addMinutes(5));
 */
class ProcessPostPublishingJob extends QueueJob
{
    public int $postId;

    public int $timeout = 120; // 任务超时时间（秒）

    public int $tries = 3; // 失败重试次数

    public int $backoff = 30; // 重试间隔（秒）

    /**
     * 创建新的任务实例
     *
     * @param  int  $postId  文章ID
     */
    public function __construct(int $postId)
    {
        parent::__construct(['post_id' => $postId]);
        $this->postId = $postId;
        $this->onQueue('demo5-posts'); // 指定队列名称
    }

    /**
     * 执行任务
     */
    public function run(): bool
    {
        $post = Demo5Post::find($this->postId);

        if (! $post) {
            Log::warning('文章发布任务：文章不存在', ['post_id' => $this->postId]);
            return false;
        }

        if ($post->status !== 'published') {
            Log::info('文章发布任务：文章状态不是已发布，跳过处理', [
                'post_id' => $this->postId,
                'status' => $post->status,
            ]);
            return true;
        }

        try {
            // 记录任务开始
            Log::info('开始处理文章发布任务', [
                'post_id' => $this->postId,
                'post_title' => $post->title,
                'job_id' => $this->job?->getJobId(),
            ]);

            // 1. 更新文章统计信息
            $this->updatePostStatistics($post);

            // 2. 生成文章摘要（如果需要）
            $this->generatePostSummary($post);

            // 3. 更新搜索索引
            $this->updateSearchIndex($post);

            // 4. 发送发布通知（模拟）
            $this->sendPublishNotification($post);

            // 5. 清理相关缓存
            $this->clearRelatedCache($post);

            Log::info('文章发布任务处理完成', ['post_id' => $this->postId]);
        } catch (\Exception $e) {
            Log::error('文章发布任务处理失败', [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return true;
    }

    /**
     * 获取任务负载信息
     */
    public function payload(): array
    {
        return [
            'task' => 'process_post_publishing',
            'post_id' => $this->postId,
        ];
    }

    /**
     * 任务失败时的处理
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('文章发布任务执行失败', [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // 可以在这里发送失败通知给管理员
        // Mail::to('admin@example.com')->send(new PostPublishingFailedNotification($this->postId, $exception));
    }

    /**
     * 更新文章统计信息
     */
    protected function updatePostStatistics(Demo5Post $post): void
    {
        // 模拟更新统计信息的操作
        $post->update([
            'word_count' => str_word_count(strip_tags($post->content)),
            'reading_time' => ceil(str_word_count(strip_tags($post->content)) / 200), // 假设每分钟阅读200字
        ]);

        Log::info('文章统计信息已更新', ['post_id' => $this->postId]);
    }

    /**
     * 生成文章摘要
     */
    protected function generatePostSummary(Demo5Post $post): void
    {
        // 模拟生成文章摘要的操作
        $summary = str_limit(strip_tags($post->content), 200);

        // 可以将摘要存储到单独的字段或缓存中
        // Cache::put("post_summary_{$this->postId}", $summary, now()->addDays(30));

        Log::info('文章摘要已生成', ['post_id' => $this->postId, 'summary_length' => strlen($summary)]);
    }

    /**
     * 更新搜索索引
     */
    protected function updateSearchIndex(Demo5Post $post): void
    {
        // 模拟更新搜索索引的操作
        // 实际项目中可能使用 Elasticsearch、Algolia 等
        $searchData = [
            'id' => $post->id,
            'title' => $post->title,
            'content' => strip_tags($post->content),
            'status' => $post->status,
            'published_at' => $post->published_at,
            'tags' => [], // 可以从文章内容中提取标签
        ];

        // SearchIndex::update($searchData);

        Log::info('搜索索引已更新', ['post_id' => $this->postId]);
    }

    /**
     * 发送发布通知
     */
    protected function sendPublishNotification(Demo5Post $post): void
    {
        // 模拟发送发布通知
        // 实际项目中可能发送邮件、短信、推送通知等

        $notificationData = [
            'post_title' => $post->title,
            'post_url' => route('posts.show', $post->id),
            'author_id' => $post->user_id,
            'published_at' => $post->published_at,
        ];

        // Mail::to('subscribers@example.com')->send(new NewPostPublishedNotification($notificationData));

        Log::info('发布通知已发送', [
            'post_id' => $this->postId,
            'post_title' => $post->title,
        ]);
    }

    /**
     * 清理相关缓存
     */
    protected function clearRelatedCache(Demo5Post $post): void
    {
        // 清理文章列表缓存
        // Cache::tags(['posts', 'list'])->flush();

        // 清理相关统计缓存
        // Cache::tags(['statistics', 'posts'])->flush();

        // 清理用户文章缓存
        // Cache::tags(['user', 'posts', $post->user_id])->flush();

        Log::info('相关缓存已清理', ['post_id' => $this->postId]);
    }

    /**
     * 获取任务标识
     */
    public function uniqueId(): string
    {
        return "process_post_publishing_{$this->postId}";
    }
}