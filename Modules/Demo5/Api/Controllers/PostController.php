<?php

namespace Modules\Demo5\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Enums\PostStatus;
use Modules\Demo5\Api\Resources\PostResource;
use Modules\Demo5\Api\Resources\PostCollection;

/**
 * API 文章控制器
 *
 * 提供 RESTful API 接口
 */
class PostController extends Controller
{
    /**
     * 文章列表
     *
     * @return PostCollection
     */
    public function index()
    {
        $posts = Demo5Post::with('comments')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return new PostCollection($posts);
    }

    /**
     * 文章详情
     *
     * @param int $id
     * @return PostResource
     */
    public function show($id)
    {
        $post = Demo5Post::with('comments')->findOrFail($id);

        return new PostResource($post);
    }

    /**
     * 创建文章
     *
     * @param Request $request
     * @return PostResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|integer|min:1',
            'status' => 'sometimes|string|in:draft,published,archived',
        ]);

        $post = Demo5Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => $validated['user_id'],
            'status' => $validated['status'] ?? PostStatus::Draft->value,
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        return new PostResource($post);
    }

    /**
     * 更新文章
     *
     * @param Request $request
     * @param int $id
     * @return PostResource
     */
    public function update(Request $request, $id)
    {
        $post = Demo5Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'status' => 'sometimes|string|in:draft,published,archived',
        ]);

        $post->update($validated);

        // 如果状态改为已发布且没有发布时间，设置当前时间
        if ($validated['status'] === 'published' && !$post->published_at) {
            $post->update(['published_at' => now()]);
        }

        return new PostResource($post);
    }

    /**
     * 删除文章
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Demo5Post::findOrFail($id);
        $post->delete();

        return response()->noContent();
    }

    /**
     * 按状态查询文章
     *
     * @param string $status
     * @return PostCollection
     */
    public function byStatus($status)
    {
        $posts = Demo5Post::with('comments')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return new PostCollection($posts);
    }

    /**
     * 搜索文章
     *
     * @param Request $request
     * @return PostCollection
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json(['error' => '请提供搜索关键词'], 400);
        }

        $posts = Demo5Post::with('comments')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return new PostCollection($posts);
    }
}