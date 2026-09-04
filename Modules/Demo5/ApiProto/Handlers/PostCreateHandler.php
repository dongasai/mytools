<?php

namespace Modules\Demo5\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostCreateRequest;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostCreateResponse;
use Modules\ApiProto\Protobuf\Demo5\Post\PostData;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\Demo5\Services\PostService;
use Modules\Demo5\Validations\PostsCreateValidation;

/**
 * 创建文章Handler
 *
 * 处理 /api/proto/demo5/post/create 请求
 * 对应 Message: Demo5PostCreateRequest
 */
class PostCreateHandler extends BaseHandler
{
    /**
     * 是否需要登录
     * true: token 中必须有 user_id（已登录用户）
     * @var bool
     */
    protected bool $need_login = true;

    /**
     * 处理创建文章请求
     *
     * @param Demo5PostCreateRequest $data 请求Message
     * @return Demo5PostCreateResponse 响应Message
     */
    public function handle(Message $data): Message
    {
        // 1. 提取请求参数（显性传参）
        $title = $data->getTitle();
        $content = $data->getContent();
        $status = $data->getStatus() ?: 'draft'; // 默认草稿状态
        $publishedAt = $data->getPublishedAt();
        $userId = $this->user_id; // 中间件注入的登录用户 ID

        // 2. 参数验证（使用 Validation 类）
        $validationData = [
            'title' => $title,
            'content' => $content,
        ];

        $validation = PostsCreateValidation::make($validationData)->validate();

        if ($validation->isFail()) {
            $response = new Demo5PostCreateResponse();
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_BAD_REQUEST,
                '参数验证失败: ' . $validation->firstError()
            );
        }

        // 3. 调用 Service 创建文章（显性传参）
        // Service 禁止读取 HTTP，参数由 Handler 传递
        $service = new PostService();
        $post = $service->createPost([
            'title' => $title,
            'content' => $content,
            'status' => $status,
            'user_id' => $userId,
            'published_at' => $publishedAt ? \Carbon\Carbon::createFromTimestamp($publishedAt) : null,
        ]);

        // 4. 更新用户活动时间
        $this->updateUserActivityTime();

        // 5. 构建成功响应
        $response = new Demo5PostCreateResponse();
        $postData = $this->toPostData($post);

        return $this->successResponse($response, $postData, '文章创建成功');
    }

    /**
     * Model → ProtoData 转换（纯数据映射）
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
        $data->setPublishedAt($post->published_at ? $post->published_at->timestamp : 0);
        $data->setCreatedAt($post->created_at->timestamp);
        $data->setUpdatedAt($post->updated_at->timestamp);
        $data->setWordCount($post->word_count ?: 0);

        return $data;
    }
}