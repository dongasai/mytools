<?php

namespace DLaravel\Validator;

use DLaravel\Validation\ValidationCore;

/**
 * 数据验证器
 *
 * 使用指定的Validation类验证整个数据
 */
class DataValidation extends Validator
{
    /**
     * 验证数据
     *
     * args[0] 验证器类名
     *
     * @param mixed $values 要验证的值
     * @param array $data 所有验证数据
     * @return bool 验证是否通过
     */
    public function validate(mixed $values, array $data): bool
    {
        $validationClass = $this->args[0];

        /**
         * @var ValidationCore $validation
         */
        $validation = new $validationClass($data);
        $validation->validate();

        if ($validation->isFail()) {
            $errorMessage = $validation->firstError();
            $this->addError("数据验证失败: {$errorMessage}");
            return false;
        }

        return true;
    }
}
