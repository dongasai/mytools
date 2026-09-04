<?php

namespace Modules\Demo5\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Events\PostCreatedEvent;
use Modules\Demo5\Logics\PostLogic;
use Modules\Demo5\Logics\ValidationLogic;
use Modules\Demo5\QueueJobs\ProcessPostPublishingJob;

class PostService
{
    // Logic层现在使用静态方法，无需依赖注入

    /**
     * 获取所有文章列表
     */
    public function getAllPosts(): Collection
    {
        return Demo5Post::latest()->get();
    }

    /**
     * 根据ID获取文章
     */
    public function getPostById(int $id): ?Demo5Post
    {
        return Demo5Post::find($id);
    }

    /**
     * 创建新文章
     */
    public function createPost(array $data): Demo5Post
    {
        // 使用验证逻辑验证数据
        $validation = ValidationLogic::validatePostData($data);
        if (! $validation['valid']) {
            throw new \InvalidArgumentException('数据验证失败: '.implode(', ', $validation['errors']));
        }

        // 预处理数据
        $processedData = $this->preprocessPostData($data);

        $post = Demo5Post::create($processedData);

        // 触发文章创建事件
        Event::dispatch(new PostCreatedEvent($post, $processedData, $processedData['user_id'] ?? null));

        // 如果文章状态是已发布，触发发布处理队列任务
        if ($post->status === 'published') {
            ProcessPostPublishingJob::dispatch($post->id);
        }

        return $post;
    }

    /**
     * 预处理文章数据
     */
    protected function preprocessPostData(array $data): array
    {
        // 自动设置发布时间
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // 使用业务逻辑计算相关字段
        if (isset($data['content'])) {
            $data['word_count'] = PostLogic::calculateWordCount($data['content']);
            $data['reading_time'] = PostLogic::calculateReadingTime($data['content']);
        }

        return $data;
    }

    /**
     * 验证文章数据
     */
    public function validatePostData(array $data, bool $isUpdate = false): array
    {
        return ValidationLogic::validatePostData($data, $isUpdate);
    }

    /**
     * 获取文章统计信息
     */
    public function getPostStatistics(Demo5Post $post): array
    {
        return [
            'word_count' => PostLogic::calculateWordCount($post->content),
            'reading_time' => PostLogic::calculateReadingTime($post->content),
            'excerpt' => PostLogic::generateExcerpt($post->content),
            'keywords' => PostLogic::extractKeywords($post->content),
            'title_quality' => PostLogic::evaluateTitleQuality($post->title),
        ];
    }

    /**
     * 批量验证文章数据
     */
    public function validateBatchPosts(array $postsData): array
    {
        $results = [];
        $allValid = true;

        foreach ($postsData as $index => $data) {
            $validation = ValidationLogic::validatePostData($data);
            $results[$index] = $validation;
            if (! $validation['valid']) {
                $allValid = false;
            }
        }

        return [
            'all_valid' => $allValid,
            'results' => $results,
        ];
    }

    /**
     * 发布文章
     */
    public function publishPost(int $id): bool
    {
        $post = $this->getPostById($id);

        if (! $post) {
            return false;
        }

        $result = $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        if ($result) {
            // 触发发布处理队列任务
            ProcessPostPublishingJob::dispatch($post->id);
        }

        return $result;
    }

    /**
     * 更新文章
     */
    public function updatePost(int $id, array $data): bool
    {
        $post = $this->getPostById($id);

        if (! $post) {
            return false;
        }

        return $post->update($data);
    }

    /**
     * 删除文章
     */
    public function deletePost(int $id): bool
    {
        $post = $this->getPostById($id);

        if (! $post) {
            return false;
        }

        return $post->delete();
    }

    /**
     * 根据状态获取文章
     */
    public function getPostsByStatus(string $status): Collection
    {
        return Demo5Post::where('status', $status)->get();
    }

    /**
     * 搜索文章
     */
    public function searchPosts(string $keyword): Collection
    {
        return Demo5Post::where('title', 'like', "%{$keyword}%")
            ->orWhere('content', 'like', "%{$keyword}%")
            ->get();
    }
}
