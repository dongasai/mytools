<?php

namespace DLaravel\Exception;

/**
 * 代码异常
 *
 * 用于表示代码层面的错误，如类型错误、方法不存在等
 */
class CodeException extends \Exception
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
