<?php

namespace Modules\FeatureExcel\Exceptions;

/**
 * 验证异常
 */
class ValidationException extends \RuntimeException
{
    /**
     * 验证错误列表
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * @param array $errors 验证错误
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct('数据验证失败');
    }

    /**
     * 获取验证错误
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
