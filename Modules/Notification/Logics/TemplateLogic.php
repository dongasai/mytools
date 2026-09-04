<?php

declare(strict_types=1);

namespace Modules\Notification\Logics;

use Modules\Notification\Models\NotificationTemplate;

/**
 * 模板逻辑类.
 *
 * 提供模板渲染和验证相关的静态方法。
 */
class TemplateLogic
{
    /**
     * 查找激活的通知模板.
     *
     * @param string $notificationType 通知类型
     * @param string $channel 通知渠道
     * @return NotificationTemplate|null 模板实例，不存在则返回null
     */
    public static function findActiveTemplate(string $notificationType, string $channel): ?NotificationTemplate
    {
        return NotificationTemplate::where('notification_type', $notificationType)
            ->where('channel', $channel)
            ->where('is_active', true)
            ->first();
    }

    /**
     * 渲染模板内容.
     *
     * 使用提供的变量替换模板中的占位符（{变量名}格式）。
     *
     * @param NotificationTemplate $template 模板实例
     * @param array<string, mixed> $variables 变量数组
     * @return string 渲染后的内容
     */
    public static function renderTemplateContent(NotificationTemplate $template, array $variables): string
    {
        $content = $template->content;

        foreach ($variables as $key => $value) {
            $placeholder = '{' . $key . '}';
            $content = str_replace($placeholder, (string) $value, $content);
        }

        return $content;
    }

    /**
     * 渲染模板标题.
     *
     * 使用提供的变量替换标题中的占位符（{变量名}格式）。
     *
     * @param NotificationTemplate $template 模板实例
     * @param array<string, mixed> $variables 变量数组
     * @return string|null 渲染后的标题，如果模板无标题则返回null
     */
    public static function renderTemplateSubject(NotificationTemplate $template, array $variables): ?string
    {
        if (!$template->subject) {
            return null;
        }

        $subject = $template->subject;

        foreach ($variables as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, (string) $value, $subject);
        }

        return $subject;
    }

    /**
     * 渲染模板内容.
     *
     * 根据通知类型和渠道获取激活的模板，并使用提供的变量渲染内容。
     *
     * @param string $notificationType 通知类型
     * @param string $channel 通知渠道
     * @param array<string, mixed> $variables 变量数组
     * @return array{subject: string|null, content: string}|null 返回渲染后的标题和内容，模板不存在则返回null
     */
    public static function renderTemplate(string $notificationType, string $channel, array $variables): ?array
    {
        $template = self::findActiveTemplate($notificationType, $channel);

        if (!$template) {
            return null;
        }

        return [
            'subject' => self::renderTemplateSubject($template, $variables),
            'content' => self::renderTemplateContent($template, $variables),
        ];
    }

    /**
     * 验证模板变量完整性.
     *
     * 检查提供的变量是否包含模板所需的所有变量，返回缺失的变量列表。
     *
     * @param NotificationTemplate $template 模板实例
     * @param array<string, mixed> $providedVariables 提供的变量数组
     * @return array<int, string> 缺失的变量名称数组
     */
    public static function validateVariables(NotificationTemplate $template, array $providedVariables): array
    {
        $requiredVariables = $template->variables ?? [];

        $missingVariables = [];

        foreach ($requiredVariables as $variableName) {
            if (! array_key_exists($variableName, $providedVariables)) {
                $missingVariables[] = $variableName;
            }
        }

        return $missingVariables;
    }
}