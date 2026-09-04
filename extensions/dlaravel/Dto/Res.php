<?php

namespace DLaravel\Dto;

/**
 * 动作型 返回结果
 * 不适用于查询型处理
 */
class Res
{
    public bool $success = true;

    public bool $error = false;

    public string $message = '';

    public array $data;

    public function __construct($success, $message, $data = [])
    {
        $this->error = ! $success;

        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
    }

    /**
     * 成功
     *
     * @param  string  $message  成功消息
     * @param  array  $data  返回数据
     * @return static
     */
    public static function success(string $message = 'success', array $data = [])
    {
        return new static(true, $message, $data);
    }

    /**
     * 失败
     *
     * @return static
     */
    public static function error(string $message, $data = [])
    {
        return new static(false, $message, $data);
    }
}
