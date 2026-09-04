<?php

namespace DLaravel\Validator;

/**
 * 验证准备接口
 *
 * 用于在验证开始前执行一些准备工作
 */
interface PrepareValidator
{
    /**
     * 验证前的准备工作
     *
     * @return void
     */
    public function prepare();
}
