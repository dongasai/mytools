<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

/**
 * Hook订阅者抽象基类
 *
 * 提供了Hook订阅者的基础实现，简化了订阅者的开发过程
 * 参考Laravel事件订阅者的设计模式
 */
abstract class AbstractHookSubscriber implements HookSubscriberInterface
{
    /**
     * 为订阅者注册Hook处理器
     *
     * 子类需要实现此方法来定义订阅的Hook和对应的处理器方法
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
    abstract public function subscribe(): array;
}
