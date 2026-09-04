<?php

namespace Modules\FeatureSms\DcatAdmin\Show;

use Dcat\Admin\Show;
use Modules\FeatureSms\Models\SmsConfig;

/**
 * 短信配置详情展示
 */
class SmsConfigDetail
{
    /**
     * 构建详情页
     *
     * @param mixed $id
     * @return Show
     */
    public static function make($id): Show
    {
        return Show::make($id, function (Show $show) {
            $show->field('id', 'ID');

            $show->field('name', '配置名称')
                ->badge('primary');

            $show->field('driver', '驱动')
                ->using([
                    'aliyun' => '<span class="badge badge-orange">阿里云</span>',
                    'tencent' => '<span class="badge badge-blue">腾讯云</span>',
                    'huawei' => '<span class="badge badge-red">华为云</span>',
                ]);

            $show->field('is_open', '状态')
                ->bool()
                ->using([
                    1 => '<span class="text-green">✓ 开启</span>',
                    0 => '<span class="text-red">✗ 关闭</span>',
                ]);

            $show->field('config', '配置内容')
                ->json()
                ->escape(false)
                ->style('max-height: 300px; overflow-y: auto;');

            $show->field('created_at', '创建时间')
                ->display(function ($value) {
                    return $value ? $value->format('Y-m-d H:i:s') : '';
                });

            $show->field('updated_at', '更新时间')
                ->display(function ($value) {
                    return $value ? $value->format('Y-m-d H:i:s') : '';
                });

            // 添加自定义工具栏
            $show->panel()->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
                $tools->disableQuickEdit();

                // 添加测试按钮
                $tools->append('<button class="btn btn-primary btn-sm" onclick="openTestSmsForm()">
                    <i class="feather icon-send"></i> 发送测试
                </button>');
            });
        });
    }
}
