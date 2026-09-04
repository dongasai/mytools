<?php

namespace DLaravel\Validator;

use DLaravel\Validation\ValidationCore;


/**
 *
 * 遍历数组,使用Validator
 *
 */
class ForeachValidator extends Validator
{


    public function validate(mixed $values, array $data): bool
    {
        $vaClass = $this->args[0];
        // "数值{value}没有通过验证"
        $message =$this->message;
        foreach ($values as $k => $value) {

            $data = [
               $value
            ];
            /**
             * @var Validator $va
             */
            $va  = new $vaClass($this->validation);
            $res = $va->validate($value, $data);
            if (!$res) {
                $p = [
                    '{index}'=>$k,
                    '{value}'=>$value,
                    '{message}'=>''
                ];
                $this->addErrorTpl($p, $message);

                return false;
            }
        }

        return true;
    }

}
