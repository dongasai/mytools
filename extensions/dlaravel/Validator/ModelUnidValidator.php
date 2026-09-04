<?php

namespace DLaravel\Validator;


/**
 * 模型唯一性验证器
 *
 * 验证指定字段的值在模型中是否唯一（不存在则验证通过）
 */
class ModelUnidValidator extends Validator
{
    /**
     * 验证模型字段唯一性
     *
     * @param mixed $value 要验证的值
     * @param array $data 所有验证数据
     * @return bool 验证是否通过
     */
    public function validate(mixed $value, array $data): bool
    {
        $modelClass = $this->args[0];
        $field = $this->args[1];

        $model = $modelClass::query()->where([
            $field => $value
        ])->first();

        if ($model) {
            $this->addError("值 '{$value}' 在字段 '{$field}' 中已存在，不满足唯一性要求");
            return false;
        }

        return true;
    }
}
