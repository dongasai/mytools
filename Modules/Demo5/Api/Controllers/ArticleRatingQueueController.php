<?php

namespace Modules\Demo5\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Demo5\Services\ArticleRatingQueueService;

/**
 * 文章评价队列控制器
 *
 * 用于派发文章评价任务到RabbitMQ，由Rust消费者完整处理业务流程
 *
 * @package Modules\Demo5\Api\Controllers
 */
class ArticleRatingQueueController extends Controller
{
    /**
     * 文章评价队列服务
     *
     * @var ArticleRatingQueueService
     */
    protected ArticleRatingQueueService $queueService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->queueService = new ArticleRatingQueueService();
    }

    /**
     * 派发文章评价任务
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dispatch(Request $request): JsonResponse
    {
        // 验证请求参数
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'required|string|max:100',
            'article_id' => 'nullable|integer',
        ]);

        try {
            // 派发任务到RabbitMQ
            $taskId = $this->queueService->dispatchRatingTask(
                $validated['title'],
                $validated['content'],
                $validated['author'],
                $validated['article_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => '文章评价任务已派发到队列',
                'task_id' => $taskId,
                'queue' => 'article_rating',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '任务派发失败',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}