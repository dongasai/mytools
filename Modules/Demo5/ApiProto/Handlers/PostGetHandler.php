<?php

namespace Modules\Demo5\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostGetRequest;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostGetResponse;
use Modules\ApiProto\Protobuf\Demo5\Post\PostData;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\Demo5\Services\PostService;

/**
 * 文章详情Handler
 */
class PostGetHandler extends BaseHandler
{
    /**
     * 是否需要登录
     * @var bool
     */
    protected bool $need_login = false;

    /**
     * PostService实例
     * @var PostService
     */
    protected PostService $postService;




    /**
     * 处理文章详情请求
     *
     * @param Demo5PostGetRequest $data 请求Message
     * @return Demo5PostGetResponse 响应Message
     */
    public function handle(Message $data): Message
    {
        // 创建Response对象
        $response = new Demo5PostGetResponse();

            // 从Message获取参数
            $id = $data->getId();

            // 参数验证
            if (empty($id)) {
                return $this->errorResponse(
                    $response,
                    StatusCode::STATUS_CODE_BAD_REQUEST,
                    '文章ID不能为空'
                );
            }

            // 调用Service获取文章
            $post = $this->postService->getPostById($id);

            if (!$post) {
                return $this->errorResponse(
                    $response,
                    StatusCode::STATUS_CODE_NOT_FOUND,
                    '文章不存在'
                );
            }

            // 构建成功响应
            return $this->successResponse(
                $response,
                $this->toPostData($post),
                '查询成功'
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