<?php

namespace DLaravel\Exception;

/**
 * Handler not found
 */
class HandleNotException extends \Exception
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
