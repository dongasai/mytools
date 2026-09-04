<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

/**
 * Hook处理器接口
 *
 * 所有Hook处理器都必须实现此接口，提供统一的处理器签名和元数据
 */
interface HookHandlerInterface
{
    /**
     * 处理Hook的核心方法
     *
     * @param  HookParameterInterface  $parameter  Hook参数，包含输入数据
     * @param  HookResultInterface  $result  前一个处理器的输出结果，HookManager会为第一个处理器创建初始结果
     * @return HookResultInterface 当前处理器处理后的新结果
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface;

    /**
     * 获取处理器优先级
     *
     * 数值越小优先级越高，执行顺序越靠前
     * 建议范围：1-50
     * - 1-10: 核心功能、必需处理
     * - 11-20: 常规功能、标准处理
     * - 21-30: 可选功能、扩展处理
     * - 31+: 调试、日志、监控
     *
     * @return int 优先级数值
     */
    public static function getPriority(): int;

    /**
     * 检查处理器是否应该执行
     *
     * 可以基于参数或其他条件判断是否应该执行此处理器
     * 返回false时，HookManager会跳过此处理器
     *
     * @param  HookParameterInterface  $parameter  Hook参数
     * @param  HookResultInterface  $result  当前结果
     * @return bool 是否应该执行
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool;
}
