<?php

declare(strict_types=1);

namespace Modules\Notification\Commands;

use Illuminate\Console\Command;
use Modules\Notification\Models\NotificationTemplate;
use Modules\Notification\Logics\TemplateLogic;

/**
 * 模板测试命令.
 *
 * 测试通知模板渲染功能。
 */
class TemplateNotificationCommand extends Command
{
    /**
     * 命令名称.
     *
     * @var string
     */
    protected $signature = 'notification:template
        {--channel=mail : 通知渠道 (mail|sms|push|database)}
        {--type=WelcomeNotification : 通知类型}
        {--create : 创建测试模板（如果不存在）}';

    /**
     * 命令描述.
     *
     * @var string
     */
    protected $description = '测试通知模板渲染功能';

    /**
     * 执行命令.
     */
    public function handle(): int
    {
        $channel = $this->option('channel');
        $type = $this->option('type');

        $this->info('=== 测试模板渲染 ===');
        $this->info('渠道: ' . $channel);
        $this->info('类型: ' . $type);

        // 查询模板
        $template = NotificationTemplate::where('notification_type', $type)
            ->where('channel', $channel)
            ->first();

        if (!$template) {
            if ($this->option('create')) {
                $template = $this->createTestTemplate($type, $channel);
                $this->info('✓ 测试模板创建成功');
            } else {
                $this->error('模板不存在');
                $this->info('提示: 使用 --create 选项自动创建测试模板');
                $this->info('示例: php artisan notification:template --create');
                return self::FAILURE;
            }
        }

        $this->displayTemplateInfo($template);

        // 准备测试变量
        $variables = $this->prepareTestVariables($template);

        $this->info('测试变量: ' . json_encode($variables, JSON_UNESCAPED_UNICODE));

        // 渲染模板
        $rendered = TemplateLogic::renderTemplate($type, $channel, $variables);

        if (!$rendered) {
            $this->error('模板渲染失败');
            return self::FAILURE;
        }

        $this->info('✓ 模板渲染成功');
        $this->info('渲染结果:');
        $this->info('- 标题: ' . ($rendered['subject'] ?: '(无)'));
        $this->info('- 内容: ' . $rendered['content']);

        return self::SUCCESS;
    }

    /**
     * 显示模板信息.
     */
    protected function displayTemplateInfo(NotificationTemplate $template): void
    {
        $this->info('模板信息:');
        $this->info('- ID: ' . $template->id);
        $this->info('- 名称: ' . $template->name);
        $this->info('- 标题: ' . ($template->subject ?: '(无)'));
        $this->info('- 内容: ' . mb_substr($template->content, 0, 50) . '...');
        $this->info('- 变量: ' . json_encode($template->variables, JSON_UNESCAPED_UNICODE));
        $this->info('- 启用: ' . ($template->is_active ? '是' : '否'));
    }

    /**
     * 准备测试变量.
     */
    protected function prepareTestVariables(NotificationTemplate $template): array
    {
        $variables = [];

        if ($template->variables) {
            foreach ($template->variables as $var) {
                $variables[$var] = '测试值_' . $var;
            }
        }

        return $variables;
    }

    /**
     * 创建测试模板.
     */
    protected function createTestTemplate(string $type, string $channel): NotificationTemplate
    {
        $templateData = [
            'name' => '测试模板-' . $type,
            'notification_type' => $type,
            'channel' => $channel,
            'subject' => '测试通知 - {timestamp}',
            'content' => '亲爱的 {username}，这是一条测试通知消息：{message}',
            'variables' => ['username', 'message', 'timestamp'],
            'is_active' => true,
        ];

        return NotificationTemplate::create($templateData);
    }
}