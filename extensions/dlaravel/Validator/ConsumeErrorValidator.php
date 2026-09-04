<?php

namespace DLaravel\Validator;

/**
 * 验证失败后消费接口
 *
 * 用于在验证失败后执行一些清理或回滚操作
 */
interface ConsumeErrorValidator
{
    /**
     * 验证失败后的消费操作
     *
     * @return void
     */
    public function consumeError();
}
