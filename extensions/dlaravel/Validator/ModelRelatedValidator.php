<?php

namespace DLaravel\Validator;


/**
 * 模型关联验证器
 *
 * 用于验证指定值在模型中是否存在关联记录
 */
class ModelRelatedValidator extends Validator
{
    /**
     * 验证模型关联
     *
     * @param mixed $value 要验证的值
     * @param array $data 所有验证数据
     * @return bool 验证是否通过
     */
    public function validate(mixed $value, array $data): bool
    {
        $list = $this->args;

        foreach ($list as $modelClass => $field) {
            /**
             * @var \Illuminate\Database\Eloquent\Builder $query
             */
            $query = $modelClass::query();
            $record = $query->where($field, '=', $value)->first();

            if ($record) {
                $modelName = $modelClass::$NAME ?? $modelClass;
                $this->addError("值 {$value} 在 {$modelName} 中已存在关联记录");
                return false;
            }
        }

        return true;
    }
}
