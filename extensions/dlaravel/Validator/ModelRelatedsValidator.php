<?php

namespace DLaravel\Validator;

use App\Models\Hp\User;

/**
 * 关联查询,批量
 *
 */
class ModelRelatedsValidator extends Validator
{
    public function validate(mixed $value, array $data): bool
    {

        $list = $this->args;

        foreach ($list as $modelClass => $field){
            /**
             * @var \Illuminate\Database\Eloquent\Builder $query
             */
            $query = $modelClass::query();
            $re = $query->whereIn($field,$value)->first();
            if($re){
                return false;
            }
        }
        return  false;
    }

}
