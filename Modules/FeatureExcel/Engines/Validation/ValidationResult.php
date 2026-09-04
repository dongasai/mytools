<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Validation;

/**
 * 验证结果
 *
 * 封装数据验证的结果，包含验证状态、错误列表和验证通过的数据
 */
class ValidationResult
{
    /**
     * 验证是否通过
     */
    protected bool $valid = false;

    /**
     * 验证错误列表
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * 验证通过的数据
     *
     * @var array
     */
    protected array $data = [];

    /**
     * 创建验证通过的结果
     *
     * @param array $data 验证通过的数据
     * @return static
     */
    public static function valid(array $data): static
    {
        $result = new static();
        $result->valid = true;
        $result->data = $data;
        return $result;
    }

    /**
     * 创建验证失败的结果
     *
     * @param array $errors 验证错误列表
     * @return static
     */
    public static function invalid(array $errors): static
    {
        $result = new static();
        $result->valid = false;
        $result->errors = $errors;
        return $result;
    }

    /**
     * 验证是否通过
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
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

    /**
     * 获取验证通过的数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
