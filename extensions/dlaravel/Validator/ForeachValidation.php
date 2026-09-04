<?php

namespace DLaravel\Validator;

use DLaravel\Validation\ValidationCore;

/**
 * 数组遍历验证器
 *
 * 遍历数组中的每个元素，使用指定的Validation类进行验证
 */
class ForeachValidation extends Validator
{
    /**
     * 验证数组中的每个元素
     *
     * args[0] 验证器类名
     * args[1] 索引字段名（可选）
     *
     * @param mixed $values 要验证的数组
     * @param array $data 所有验证数据
     * @return bool 验证是否通过
     */
    public function validate(mixed $values, array $data): bool
    {
        $validationClass = $this->args[0];
        $indexField = $this->args[1] ?? '';

        foreach ($values as $index => $value) {
            if ($indexField) {
                $validationData = [
                    $indexField => $value
                ];
            } else {
                $validationData = $value;
            }

            /**
             * @var ValidationCore $validation
             */
            $validation = new $validationClass($validationData);
            $validation->validate();

            if ($validation->isFail()) {
                $errorMessage = $validation->firstError();
                $this->addError("索引 {$index} 的数据验证失败: {$errorMessage}");
                return false;
            }
        }

        return true;
    }
}
