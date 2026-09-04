<?php

namespace DLaravel\Validator;

use DLaravel\Exception\CodeException;
use DLaravel\Exception\LogicException;
use Illuminate\Database\Eloquent\Model;

/**
 * 模型主键查找验证器
 *
 * 用于验证模型主键是否存在，并可选择性地将找到的模型实例赋值给指定字段
 *
 * 使用示例：
 * ['id' => ['modelPk', User::class, 'user']] // 验证 id 是否存在，并将用户实例赋值给 user 字段
 */
class ModelPkValidator extends Validator
{
    /**
     * 验证模型主键是否存在
     *
     * @param mixed $value 要验证的主键值
     * @param array $data 所有验证数据
     * @return bool 验证是否通过
     * @throws LogicException 当模型类不存在时抛出
     */
    public function validate(mixed $value, array $data): bool
    {
        $model = $this->args[0];
        if (!is_subclass_of($model, Model::class)) {
            throw new LogicException("模型类 {$model} 不存在或不是有效的 Eloquent 模型");
        }
        $one = $model::find($value);
        $bol = $one !== null;
        if ($bol) {
            $field = $this->args[1];
            if ($field) {
                $this->validation->$field = $one;
            }
            return true;
        }
        return false;
    }
}
