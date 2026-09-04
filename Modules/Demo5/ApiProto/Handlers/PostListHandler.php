<?php

namespace Modules\Demo5\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostListRequest;
use Modules\ApiProto\Protobuf\Demo5\Post\Demo5PostListResponse;
use Modules\ApiProto\Protobuf\Demo5\Post\PostData;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\Demo5\Services\PostService;

/**
 * 文章列表Handler
 */
class PostListHandler extends BaseHandler
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
     * 处理文章列表请求
     *
     * @param Demo5PostListRequest $data 请求Message
     * @return Demo5PostListResponse 响应Message
     */
    public function handle(Message $data): Message
    {
        // 创建Response对象
        $response = new Demo5PostListResponse();

            // 从Message获取参数
            $page = $data->getPage();
            $pageSize = $data->getPageSize();

            // 调用Service获取文章列表
            $posts = $this->postService->getAllPosts();

            // 转换为PostData数组
            $postDataList = [];
            foreach ($posts as $post) {
                $postDataList[] = $this->toPostData($post);
            }

            // 构建列表响应（带分页）
            return $this->listResponse(
                $response,
                $postDataList,
                $page,
                $pageSize,
                count($posts), // 总数
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
