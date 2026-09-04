<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Subscribers;

use Modules\ABase\Hooks\Core\AbstractHookSubscriber;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;
use Modules\ABase\Hooks\Results\DemoHookResult;
use DLaravel\Contracts\LogInterface;

/**
 * DemoHook订阅者
 *
 * 演示如何使用订阅者模式来处理DemoHook
 * 与Handler不同，Subscriber可以同时处理多个Hook
 */
class DemoHookSubscriber extends AbstractHookSubscriber
{
    /**
     * 日志服务实例
     */
    private LogInterface $logger;

    /**
     * 构造函数
     *
     * @param  LogInterface  $logger  日志服务
     */
    public function __construct(LogInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 为订阅者注册Hook处理器
     *
     * @return array 订阅的Hook配置数组
     */
    public function subscribe(): array
    {
        return [
            \Modules\ABase\Hooks\Definitions\DemoHook::class => 'handleDemoHook',
        ];
    }

    /**
     * 处理DemoHook
     *
     * 作为Subscriber，这个方法会在DemoHookHandler执行之后执行
     * 可以对结果进行二次处理或添加额外的逻辑
     *
     * @param  HookParameterInterface  $parameter  Hook参数
     * @param  HookResultInterface  $result  前一个处理器的结果
     * @return HookResultInterface 处理后的结果
     */
    public function handleDemoHook(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 确保参数和结果类型正确
        if (! $parameter instanceof DemoHookParameter || ! $result instanceof DemoHookResult) {
            return $result;
        }

        // 获取前一个处理器的结果
        $processedName = $result->getProcessedName();
        $processedValue = $result->getProcessedValue();
        $appliedOptions = $result->getAppliedOptions();

        // 添加订阅者特有的处理逻辑
        $finalName = $processedName . '_by_subscriber';
        $finalValue = $processedValue + 10; // 增加10
        $finalOptions = array_merge($appliedOptions, [
            'subscriber' => static::class,
            'subscriber_processed_at' => time(),
            'subscriber_note' => 'Subscriber added extra processing',
        ]);

        // 记录订阅者处理日志
        $this->logger->info('DemoHookSubscriber处理完成', [
            'original_name' => $parameter->getName(),
            'final_name' => $finalName,
            'original_value' => $parameter->getValue(),
            'final_value' => $finalValue,
        ]);

        // 创建新的结果对象，保持原有的成功/失败状态
        if ($result->isSuccess()) {
            return DemoHookResult::success(
                $finalName,
                $finalValue,
                $finalOptions,
                (string) time(),
                'DemoHook processed by Handler and Subscriber'
            );
        } else {
            // 如果前一个处理器失败了，添加错误信息但不改变失败状态
            $result->addError('Processed by DemoHookSubscriber but previous handler failed');

            return $result;
        }
    }

    /**
     * 获取订阅者优先级
     *
     * Subscriber的优先级通常比Handler低，确保在Handler之后执行
     *
     * @return int 优先级
     */
    public static function getPriority(): int
    {
        return 30; // 比DemoHookHandler的优先级20更低，后执行
    }
}
