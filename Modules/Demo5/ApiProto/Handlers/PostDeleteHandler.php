<?php

namespace Modules\Demo5\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostDeleteRequest;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostDeleteResponse;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\Demo5\Services\PostService;

/**
 * 删除文章Handler
 *
 * 处理 /api/proto/demo5/post/delete 请求
 * 对应 Message: Demo5PostDeleteRequest
 */
class PostDeleteHandler extends BaseHandler
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
     * 处理删除文章请求
     *
     * @param Demo5PostDeleteRequest $data 请求Message
     * @return Demo5PostDeleteResponse 响应Message
     */
    public function handle(Message $data): Message
    {
        // 创建Response对象
        $response = new Demo5PostDeleteResponse();

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

        // 调用Service删除文章
        $result = $this->postService->deletePost($id);

        if (!$result) {
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_NOT_FOUND,
                '文章不存在'
            );
        }

        // 更新用户活动时间
        $this->updateUserActivityTime();

        // 构建成功响应（删除操作无数据返回）
        return $this->successResponse(
            $response,
            null,
            '文章删除成功'
        );
    }
}