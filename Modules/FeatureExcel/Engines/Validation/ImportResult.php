<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Validation;

/**
 * 导入结果
 *
 * 封装整个导入流程的结果，包含成功状态、数据和错误信息
 */
class ImportResult
{
    /**
     * 导入是否成功
     */
    protected bool $success = false;

    /**
     * 导入数据
     *
     * @var array
     */
    protected array $data = [];

    /**
     * 错误信息
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * 创建成功的导入结果
     *
     * @param array $data 导入数据
     * @return static
     */
    public static function success(array $data): static
    {
        $result = new static();
        $result->success = true;
        $result->data = $data;
        return $result;
    }

    /**
     * 创建失败的导入结果
     *
     * @param array $errors 错误信息
     * @return static
     */
    public static function failed(array $errors): static
    {
        $result = new static();
        $result->success = false;
        $result->errors = $errors;
        return $result;
    }

    /**
     * 导入是否成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * 获取导入数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取错误信息
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
