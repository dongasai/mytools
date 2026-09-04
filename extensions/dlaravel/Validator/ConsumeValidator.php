<?php

namespace DLaravel\Validator;

/**
 * 验证成功后消费接口
 *
 * 用于在验证成功后执行一些后续操作
 */
interface ConsumeValidator
{
    /**
     * 验证成功后的消费操作
     *
     * @return void
     */
    public function consume();
}
