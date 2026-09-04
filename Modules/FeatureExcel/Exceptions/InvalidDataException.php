<?php

namespace Modules\FeatureExcel\Exceptions;

/**
 * 数据无效异常
 */
class InvalidDataException extends \RuntimeException
{
    /**
     * @param string $message 错误信息
     */
    public function __construct(string $message = '数据无效')
    {
        parent::__construct($message);
    }
}
