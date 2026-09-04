<?php

declare(strict_types=1);

namespace Modules\Notification\DcatAdmin\Forms;

use Dcat\Admin\Form;

/**
 * 通知模板表单类.
 *
 * 用于构建通知模板的创建和编辑表单，包含所有字段配置、验证规则和帮助文本。
 * 支持模板变量配置和多渠道模板管理。
 *
 * @package Modules\Notification\DcatAdmin\Forms
 */
class NotificationTemplateForm
{
    /**
     * 构建通知模板表单.
     *
     * @param string $model 模型类名
     * @return Form
     */
    public static function make(string $model): Form
    {
        return Form::make($model, function (Form $form) {
            // 基本信息字段
            $form->display('id', 'ID');

            $form->text('name', '模板名称')
                ->required()
                ->rules('unique:notification_templates,name,' . $form->getKey())
                ->help('模板唯一标识名称');

            $form->text('notification_type', '通知类型')
                ->required()
                ->help('关联的通知类，如：WelcomeNotification');

            $form->select('channel', '适用渠道')
                ->options([
                    'sms' => '短信',
                    'mail' => '邮件',
                    'push' => '推送',
                    'database' => '数据库',
                ])
                ->required()
                ->help('选择通知发送渠道');

            // 内容字段
            $form->text('subject', '标题')
                ->help('邮件标题，使用 {变量名} 插入变量');

            $form->textarea('content', '内容模板')
                ->required()
                ->rows(10)
                ->help('使用 {变量名} 插入变量，如：欢迎 {username}！');

            $form->tags('variables', '模板变量')
                ->help('必填变量列表，如：username, order_id');

            // 状态和描述
            $form->switch('is_active', '启用状态')
                ->default(true);

            $form->textarea('description', '模板描述')
                ->rows(3)
                ->help('模板用途说明');

            // 时间字段
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}