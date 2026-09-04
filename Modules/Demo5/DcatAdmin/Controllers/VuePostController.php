<?php

namespace Modules\Demo5\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Modules\Demo5\Models\Demo5Post;

/**
 * Vue 文章管理控制器
 */
class VuePostController extends AdminController
{
    /**
     * Vue 文章管理页面
     */
    public function index(Content $content)
    {
        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view('module_demo5::vue.posts');
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('Vue 文章管理')
            ->body(view('module_demo5::vue.posts'));
    }

    /**
     * 获取文章列表
     */
    public function list(): JsonResponse
    {
        $posts = Demo5Post::with('author')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'author' => $post->author->name ?? 'Unknown',
                    'status' => $post->status,
                    'published_at' => $post->published_at
                        ? $post->published_at->format('Y-m-d H:i:s')
                        : null,
                ];
            });

        return response()->json($posts);
    }

    /**
     * 创建文章
     */
    public function store(): JsonResponse
    {
        $data = request()->only(['title', 'content', 'status', 'published_at']);
        $data['user_id'] = auth()->id() ?: 1;

        $post = Demo5Post::create($data);

        return response()->json([
            'success' => true,
            'message' => '创建成功',
            'data' => $post,
        ]);
    }

    /**
     * 更新文章
     */
    public function update($id): JsonResponse
    {
        $post = Demo5Post::findOrFail($id);
        $data = request()->only(['title', 'content', 'status', 'published_at']);
        $post->update($data);

        return response()->json([
            'success' => true,
            'message' => '更新成功',
            'data' => $post,
        ]);
    }

    /**
     * 删除文章
     */
    public function destroy($id): JsonResponse
    {
        $post = Demo5Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => '删除成功',
        ]);
    }
}