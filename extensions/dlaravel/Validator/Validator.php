<?php

namespace DLaravel\Validator;

use Inhere\Validate\Validator\AbstractValidator;
use DLaravel\Validation\ValidationCore;


/**
 * 验证类 基类
 *
 */
abstract class Validator extends AbstractValidator
{
    /**
     * 验证核心实例
     *
     * @var ValidationCore
     */
    public ValidationCore $validation;

    /**
     * 参数数组
     *
     * @var array
     */
    public array $args = [];

    /**
     * 错误消息
     *
     * @var string
     */
    public string $message = '';

    public function __construct(ValidationCore $validation, array $args = [], string $message = '')
    {
        $this->validation = $validation;
        $this->args = $args;
        $this->message = $message;

        if (method_exists($this, 'prepare')) {
            $this->prepare();
        }
    }


    /**
     * 给验证器设置数据
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function validationSet($name, $value)
    {
        if (property_exists($this->validation, $name)) {
            $this->validation->$name = $value;
        }
    }


    /**
     * 增加一个错误消息，并返回False
     * @param string $msg 错误的消息
     * @param string $field 发生错误的字段
     * @return false
     */
    protected function addError(string $msg, string $field = '')
    {
        $this->validation->addError($field, $msg);

        return false;
    }


    /**
     * 增加一个模板的错误消息，并返回False
     * @param $value
     * @param string $message 消息模版
     * @param string $msg
     * @return mixed
     * 
     */
    public function addErrorTpl($p, $msgTpl, $field = '')
    {
        //        dd(strtr($msgTpl, $p),$msgTpl,$p);
        $this->validation->addError($field, strtr($msgTpl, $p));

        return false;
    }
}
