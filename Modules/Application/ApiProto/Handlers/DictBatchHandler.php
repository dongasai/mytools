<?php

namespace Modules\Application\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictBatchRequest;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictBatchResponse;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictData;
use Modules\ApiProto\Protobuf\Application\Dict\ApplicationDictTypeData;
use Modules\ApiProto\Protobuf\ApiProto\Common\BaseResponse;
use Modules\Application\Logics\DictLogic;

/**
 * 批量获取字典
 *
 * Api Path: /api/proto/application/dict/batch
 */
class DictBatchHandler extends BaseHandler
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
     * @return ApplicationDictBatchResponse 返回Message
     */
    public function handle(Message $data): Message
    {
        $dictTypes = iterator_to_array($data->getDictTypes()->getIterator());

        // 需要返回extra字段的字典类型
        $extraTypes = ['gas_type', 'n2o_factor', 'global_factor', 'gas_gwp'];

        // 检查是否有需要extra的字典类型
        $hasExtraType = false;
        foreach ($dictTypes as $type) {
            if (in_array($type, $extraTypes)) {
                $hasExtraType = true;
                break;
            }
        }

        // 根据需要选择查询方法
        if ($hasExtraType) {
            $batchData = DictLogic::getBatchWithExtra($dictTypes);
        } else {
            $batchData = DictLogic::getBatch($dictTypes);
        }

        $response = new ApplicationDictBatchResponse();

        $base = new BaseResponse();
        $base->setCode(0);
        $base->setMsg('成功');
        $response->setBase($base);

        // 构建类型数据列表
        $typeDataList = [];
        foreach ($batchData as $type => $items) {
            $typeData = new ApplicationDictTypeData();
            $typeData->setDictType($type);  // 回传过滤条件

            $dictDataList = [];
            foreach ($items as $item) {
                $dictData = new ApplicationDictData();
                $dictData->setLabel($item['label']);
                $dictData->setValue($item['value']);
                $dictData->setSort($item['sort']);
                // 对于需要extra的字典类型，从remark获取
                if ($hasExtraType && isset($item['extra']) && !empty($item['extra'])) {
                    $dictData->setExtra($item['extra']);
                }
                $dictDataList[] = $dictData;
            }
            $typeData->setItems($dictDataList);
            $typeDataList[] = $typeData;
        }
        $response->setData($typeDataList);

        return $response;
    }
}
