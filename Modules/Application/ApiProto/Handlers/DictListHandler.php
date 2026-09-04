<?php

namespace Modules\Application\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictData;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictListRequest;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictListResponse;
use Modules\ApiProto\Protobuf\ApiProto\Common\BaseResponse;
use Modules\Application\Logics\DictLogic;

/**
 * 获取字典列表
 *
 * Api Path: /api/proto/application/dict/list
 */
class DictListHandler extends BaseHandler
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
     * @return ApplicationDictListResponse 返回Message
     */
    public function handle(Message $data): Message
    {
        $dictType = $data->getDictType();

        $list = DictLogic::getList($dictType);

        $response = new ApplicationDictListResponse();

        $base = new BaseResponse();
        $base->setCode(0);
        $base->setMsg('成功');
        $response->setBase($base);

        // 回传过滤条件
        $response->setDictType($dictType);

        // 构建数据列表
        $dictDataList = [];
        foreach ($list as $item) {
            $dictData = new ApplicationDictData();
            $dictData->setLabel($item['label']);
            $dictData->setValue($item['value']);
            $dictData->setSort($item['sort']);
            $dictDataList[] = $dictData;
        }
        $response->setData($dictDataList);

        return $response;
    }
}
