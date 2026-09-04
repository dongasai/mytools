<?php

namespace Modules\Application\Exceptions;

/**
 * 功能不存在异常
 *
 * 当查询的功能开关不存在时抛出此异常
 */
class FeatureNotFoundException extends \RuntimeException
{
    /**
     * 功能标识
     *
     * @var string
     */
    protected string $featureKey;

    /**
     * 构造函数
     *
     * @param string $featureKey 功能标识
     * @param string $message 异常消息
     * @param int $code 异常代码
     */
    public function __construct(string $featureKey, string $message = '', int $code = 0)
    {
        $this->featureKey = $featureKey;

        if ($message === '') {
            $message = "功能不存在: {$featureKey}";
        }

        parent::__construct($message, $code);
    }

    /**
     * 获取功能标识
     *
     * @return string
     */
    public function getFeatureKey(): string
    {
        return $this->featureKey;
    }
}