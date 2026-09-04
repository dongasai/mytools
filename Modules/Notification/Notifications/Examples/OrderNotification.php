<?php

declare(strict_types=1);

namespace Modules\Notification\Notifications\Examples;

use Modules\Notification\Notifications\BaseNotification;

/**
 * 订单通知示例类.
 *
 * 用于订单创建、支付、完成等场景的通知。
 * 支持邮件、短信和数据库渠道，演示多渠道通知实现。
 */
class OrderNotification extends BaseNotification
{
    /**
     * 订单ID.
     *
     * @var string
     */
    protected string $orderId;

    /**
     * 订单金额.
     *
     * @var float
     */
    protected float $amount;

    /**
     * 构造函数.
     *
     * @param string $orderId 订单ID
     * @param float $amount 订单金额
     */
    public function __construct(string $orderId, float $amount)
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->data = [
            'orderId' => $orderId,
            'amount' => $amount,
        ];
    }

    /**
     * 获取通知类型标识.
     *
     * @return string 通知类型标识
     */
    public function getNotificationType(): string
    {
        return 'OrderNotification';
    }

    /**
     * 获取默认内容.
     *
     * 当模板不存在时使用此默认内容。
     *
     * @param string $channel 通知渠道
     * @param array<string, mixed> $variables 变量数组
     * @return array<string, mixed> 渠道特定内容数组
     */
    public function getDefaultContent(string $channel, array $variables): array
    {
        $orderId = $variables['orderId'] ?? '';
        $amount = $variables['amount'] ?? 0;

        if ($channel === 'mail') {
            return [
                'subject' => '订单创建成功',
                'body' => "您的订单 {$orderId} 已创建成功，金额为 {$amount} 元。",
            ];
        }

        if ($channel === 'sms') {
            return [
                'message' => "订单{$orderId}创建成功，金额{$amount}元",
            ];
        }

        if ($channel === 'database') {
            return [
                'message' => "订单 {$orderId} 创建成功",
                'amount' => $amount,
            ];
        }

        return [];
    }

    /**
     * 获取通知渠道.
     *
     * @param mixed $notifiable 接收通知的对象
     * @return array<int, string> 渠道数组
     */
    public function via($notifiable): array
    {
        return ['mail', 'sms', 'database'];
    }

    /**
     * 获取邮件通知内容.
     *
     * @param mixed $notifiable 接收通知的对象
     * @return array<string, mixed> 邮件内容数组
     */
    public function toMail($notifiable): array
    {
        $content = $this->renderContent('mail', $this->data);

        return [
            'subject' => $content['subject'] ?? '订单通知',
            'body' => $content['body'] ?? $content['content'] ?? '',
        ];
    }

    /**
     * 获取短信通知内容.
     *
     * @param mixed $notifiable 接收通知的对象
     * @return array<string, mixed> 短信内容数组
     */
    public function toSms($notifiable): array
    {
        $content = $this->renderContent('sms', $this->data);

        return [
            'message' => $content['message'] ?? $content['content'] ?? '',
        ];
    }

    /**
     * 获取数据库通知内容.
     *
     * @param mixed $notifiable 接收通知的对象
     * @return array<string, mixed> 数据库内容数组
     */
    public function toDatabase($notifiable): array
    {
        $content = $this->renderContent('database', $this->data);

        return [
            'message' => $content['message'] ?? $content['content'] ?? '',
            'amount' => $content['amount'] ?? $this->amount,
        ];
    }
}