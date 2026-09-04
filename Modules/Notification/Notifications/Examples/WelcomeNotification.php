<?php

declare(strict_types=1);

namespace Modules\Notification\Notifications\Examples;

use Modules\Notification\Notifications\BaseNotification;

/**
 * 欢迎通知示例类.
 *
 * 用于新用户注册成功后的欢迎通知。
 * 支持数据库渠道，演示模板覆盖和默认内容。
 */
class WelcomeNotification extends BaseNotification
{
    /**
     * 用户名.
     *
     * @var string
     */
    protected string $username;

    /**
     * 构造函数.
     *
     * @param string $username 用户名
     */
    public function __construct(string $username)
    {
        $this->username = $username;
        $this->data = [
            'username' => $username,
        ];
    }

    /**
     * 获取通知类型标识.
     *
     * @return string 通知类型标识
     */
    public function getNotificationType(): string
    {
        return 'WelcomeNotification';
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
        $username = $variables['username'] ?? '用户';

        if ($channel === 'database') {
            return [
                'message' => "欢迎 {$username} 加入平台",
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
        return ['database'];
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
        ];
    }
}