<?php

namespace DLaravel\Validator;


/**
 * 反向验证器
 *
 * 使用指定的验证器进行验证，但返回相反的结果
 */
class ReverseValidator extends Validator
{
    /**
     * 反向验证
     *
     * @param mixed $value 要验证的值
     * @param array $data 所有验证数据
     * @return bool 验证是否通过（与原验证器结果相反）
     */
    public function validate(mixed $value, array $data): bool
    {
        $validatorClass = $this->args[0];
        $validationData = [$value];

        /**
         * @var Validator $validator
         */
        $validator = new $validatorClass($this->validation);
        $result = $validator->validate($value, $validationData);

        // 返回相反的结果
        if ($result) {
            $this->addError("反向验证失败：原验证器验证通过");
            return false;
        }

        return true;
    }
}
