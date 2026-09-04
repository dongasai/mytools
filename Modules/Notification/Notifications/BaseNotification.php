<?php

declare(strict_types=1);

namespace Modules\Notification\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Notification\Logics\TemplateLogic;

/**
 * 通知抽象基类.
 *
 * 所有通知类的基础，提供模板渲染和默认内容支持。
 * 支持模板系统覆盖默认内容，并定义统一的通知数据结构。
 */
abstract class BaseNotification extends Notification
{
    /**
     * 通知数据.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * 通知渠道列表.
     *
     * @var array<int, string>
     */
    protected array $channels = [];

    /**
     * 获取通知类型标识.
     *
     * 用于模板匹配和通知分类。
     *
     * @return string 通知类型标识
     */
    abstract public function getNotificationType(): string;

    /**
     * 获取默认内容.
     *
     * 当模板不存在时使用此方法提供默认通知内容。
     *
     * @param string $channel 通知渠道
     * @param array<string, mixed> $variables 变量数组
     * @return array<string, mixed> 渠道特定内容数组
     */
    abstract public function getDefaultContent(string $channel, array $variables): array;

    /**
     * 获取通知数据.
     *
     * 返回用于存储的通知数据数组。
     *
     * @param mixed $notifiable 接收通知的对象
     * @return array<string, mixed> 通知数据数组
     */
    public function toArray($notifiable): array
    {
        return $this->data;
    }

    /**
     * 渲染通知内容.
     *
     * 优先使用模板系统渲染，如果模板不存在则使用默认内容。
     *
     * @param string $channel 通知渠道
     * @param array<string, mixed> $variables 变量数组
     * @return array<string, mixed> 渲染后的内容数组
     */
    protected function renderContent(string $channel, array $variables): array
    {
        $notificationType = $this->getNotificationType();

        $rendered = TemplateLogic::renderTemplate($notificationType, $channel, $variables);

        if ($rendered['content'] === '') {
            return $this->getDefaultContent($channel, $variables);
        }

        return $rendered;
    }
}