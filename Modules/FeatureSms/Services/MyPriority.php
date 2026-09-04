<?php

namespace Modules\FeatureSms\Services;

use Overtrue\EasySms\Contracts\StrategyInterface;

/**
 * 自定义短信发送策略
 * 优先使用数据库网关
 */
class MyPriority implements StrategyInterface
{
    /**
     * 应用策略
     *
     * @param  array  $gateways  可用的网关列表
     * @return array 排序后的网关列表
     */
    public function apply(array $gateways): array
    {
        // 查找 mygateway 在数组中的位置
        $index = array_search('mygateway', $gateways);

        // 如果找到 mygateway，则将其作为唯一的网关返回
        if ($index !== false) {
            return [$gateways[$index]];
        }

        // 如果没有找到 mygateway，则返回原始网关列表
        return $gateways;
    }
}
