<?php

namespace Modules\Demo5\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostUpdateRequest;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostUpdateResponse;
use Modules\ApiProto\Protobuf\Demo5\Post\PostData;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\Demo5\Services\PostService;

/**
 * 更新文章Handler
 *
 * 处理 /api/proto/demo5/post/update 请求
 * 对应 Message: Demo5PostUpdateRequest
 */
class PostUpdateHandler extends BaseHandler
{
    /**
     * 是否需要登录
     * true: token 中必须有 user_id（已登录用户）
     * @var bool
     */
    protected bool $need_login = true;

    /**
     * PostService实例
     * @var PostService
     */
    protected PostService $postService;

    /**
     * 处理更新文章请求
     *
     * @param Demo5PostUpdateRequest $data 请求Message
     * @return Demo5PostUpdateResponse 响应Message
     */
    public function handle(Message $data): Message
    {
        // 创建Response对象
        $response = new Demo5PostUpdateResponse();

        // 从Message获取参数
        $id = $data->getId();
        $title = $data->getTitle();
        $content = $data->getContent();
        $status = $data->getStatus();
        $publishedAt = $data->getPublishedAt();

        // 参数验证
        if (empty($id)) {
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_BAD_REQUEST,
                '文章ID不能为空'
            );
        }

        // 调用Service更新文章
        $updateData = [];
        if (!empty($title)) {
            $updateData['title'] = $title;
        }
        if (!empty($content)) {
            $updateData['content'] = $content;
        }
        if (!empty($status)) {
            $updateData['status'] = $status;
        }
        if (!empty($publishedAt)) {
            $updateData['published_at'] = \Carbon\Carbon::createFromTimestamp($publishedAt);
        }

        $post = $this->postService->updatePost($id, $updateData);

        if (!$post) {
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_NOT_FOUND,
                '文章不存在'
            );
        }

        // 更新用户活动时间
        $this->updateUserActivityTime();

        // 构建成功响应
        return $this->successResponse(
            $response,
            $this->toPostData($post),
            '文章更新成功'
        );
    }

    /**
     * Model → ProtoData转换
     *
     * @param \Modules\Demo5\Models\Demo5Post $post
     * @return PostData
     */
    private function toPostData(\Modules\Demo5\Models\Demo5Post $post): PostData
    {
        $data = new PostData();

        $data->setId($post->id);
        $data->setTitle($post->title);
        $data->setContent($post->content);
        $data->setStatus($post->status);
        $data->setUserId($post->user_id);
        $data->setCreatedAt($post->created_at->timestamp);
        $data->setUpdatedAt($post->updated_at->timestamp);

        return $data;
    }
}