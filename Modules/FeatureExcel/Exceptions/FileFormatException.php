<?php

namespace Modules\FeatureExcel\Exceptions;

/**
 * 文件格式异常
 */
class FileFormatException extends \RuntimeException
{
    /**
     * @param string $message 错误信息
     */
    public function __construct(string $message = '文件格式不支持')
    {
        parent::__construct($message);
    }
}
