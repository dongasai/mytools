<?php

namespace DLaravel\Exception;

/**
 * 逻辑异常
 *
 * 用于表示业务逻辑错误，如参数验证失败、数据不存在等
 */
class LogicException extends \Exception
{
    /**
     * 构造函数
     *
     * @param  string  $message  错误信息
     * @param  int  $code  错误代码
     */
    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
