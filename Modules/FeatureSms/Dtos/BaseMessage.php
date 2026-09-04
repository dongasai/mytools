<?php

namespace Modules\FeatureSms\Dtos;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Message;

/**
 * 基础消息类
 */
class BaseMessage extends Message
{
    /**
     * 构造函数
     *
     * @param  array  $attributes  消息属性
     * @param  string  $type  消息类型
     */
    public function __construct(array $attributes = [], string $type = MessageInterface::TEXT_MESSAGE)
    {
        $this->type = $type;
        $this->setData($attributes);
    }
}
