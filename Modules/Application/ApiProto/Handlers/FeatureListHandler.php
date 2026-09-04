<?php

namespace Modules\Application\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Application\Feature\ApplicationFeatureListRequest;
use Modules\ApiProto\Protobuf\Application\Feature\ApplicationFeatureListResponse;
use Modules\ApiProto\Protobuf\ApiProto\Common\BaseResponse;
use Modules\Application\Logics\FeatureLogic;

/**
 * 获取用户可用功能列表
 *
 * Api Path: /api/proto/application/feature/list
 */
class FeatureListHandler extends BaseHandler
{
    /**
     * 是否需要登录
     *
     * @var bool
     */
    protected bool $need_login = true;

    /**
     * 处理请求
     *
     * @param Message $data 请求Message
     * @return ApplicationFeatureListResponse 返回Message
     */
    public function handle(Message $data): Message
    {
        // 使用中间件注入的 user_id
        $userId = $this->user_id;

        // 调用 Logic 层获取功能列表
        $featureKeys = FeatureLogic::getEnabledFeatureKeys($userId);

        // 构建 Response
        $response = new ApplicationFeatureListResponse();

        // 设置 BaseResponse
        $base = new BaseResponse();
        $base->setCode(0);
        $base->setMsg('成功');
        $response->setBase($base);

        // 设置功能列表（repeated 字段使用 setFeatures 方法接受数组）
        $response->setFeatures($featureKeys);

        return $response;
    }
}