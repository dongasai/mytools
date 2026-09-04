<?php

namespace DLaravel\Validator;


/**
 * 小数验证器,格式验证,最长长度
 */
class FloatValidator extends Validator
{

    public function validate(mixed $value, array $data): bool
    {
        $intLen = $this->args[0];
        $floatLen = $this->args[1];
        $exp = explode('.', $value);
        if (count($exp) > 2) {
            // 存在两个点
            $this->validation->addError('',"验证内容存在不止一个\".\"。",'');
            return false;
        }
        if (strlen($exp[0]) > $intLen) {
            $this->validation->addError('',"验证内容整数部分超长。",'');
            return false;
        }
        if (count($exp) > 1) {
            if (strlen($exp[1]) > $floatLen) {
                $this->validation->addError('',"验证内容小数部分超长。",'');
                return false;
            }
        }

        return true;

    }

}
