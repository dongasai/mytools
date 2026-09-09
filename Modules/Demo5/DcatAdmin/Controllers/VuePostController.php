<?php

namespace Modules\Demo5\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Modules\DcatAdmin\DcatAdmin\AdminController;
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
        return $this->vueview($content, 'module_demo5::vue.posts', []);
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