<?php

namespace Modules\Demo5\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\Demo5Comment;
use Modules\Demo5\Models\Demo5User;

/**
 * Vue 仪表盘控制器
 */
class VueDashboardController extends AdminController
{
    /**
     * Vue 仪表盘页面
     */
    public function index(Content $content)
    {
        return $this->vueview($content, 'module_demo5::vue.dashboard', []);
    }

    /**
     * 获取统计数据
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'posts' => Demo5Post::count(),
            'comments' => Demo5Comment::count(),
            'users' => Demo5User::count(),
            'active_users' => Demo5User::whereHas('posts')
                ->orWhereHas('comments')
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * 获取图表数据
     */
    public function getChartData(): JsonResponse
    {
        // 获取近7天的评论趋势
        $dates = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dates[] = $date;
            $values[] = Demo5Comment::whereDate('created_at', $date)->count();
        }

        return response()->json([
            'dates' => $dates,
            'values' => $values,
        ]);
    }

    /**
     * 获取最新文章列表
     */
    public function getRecentPosts(): JsonResponse
    {
        $posts = Demo5Post::with('author')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'author' => $post->author->name ?? 'Unknown',
                    'views' => $post->views ?? 0,
                    'comments' => $post->comments()->count(),
                    'status' => $post->status,
                    'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($posts);
    }
}