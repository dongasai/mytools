<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

/**
 * Hook订阅者接口
 *
 * 定义了Hook订阅者的基本契约，允许类订阅多个Hook并统一管理处理器
 * 参考Laravel事件订阅者的设计模式
 */
interface HookSubscriberInterface
{
    /**
     * 为订阅者注册Hook处理器
     *
     * 返回格式：[
     *     'hook_class_name' => 'handler_method_name',           // 方法名字符串
     *     'hook_class_name' => [$this, 'handler_method'],      // 可调用数组
     *     'hook_class_name' => function($param, $result) { ... }, // 闭包
     *     ...
     * ]
     *
     * @return array 订阅的Hook配置数组
     */
    public function subscribe(): array;
}
