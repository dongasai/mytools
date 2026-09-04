<?php

namespace Modules\FeatureSms\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSms\DcatAdmin\Repositories\SmsConfigRepository;
use Modules\FeatureSms\DcatAdmin\Actions\ToggleSmsConfigStatus;
use Modules\FeatureSms\DcatAdmin\Show\SmsConfigDetail;

/**
 * 短信配置管理
 */
class SmsConfigController extends AdminController
{
    protected $title = '短信配置';

    protected $description = '管理短信网关配置，支持阿里云、腾讯云、华为云等驱动';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new SmsConfigRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('title', '配置名称')->width('200px');
            $grid->column('driver', '驱动')->width('120px')->using([
                'db' => '<span class="badge badge-gray">数据库</span>',
                'aliyun' => '<span class="badge badge-orange">阿里云</span>',
                'tencent' => '<span class="badge badge-blue">腾讯云</span>',
                'huawei' => '<span class="badge badge-red">华为云</span>',
            ]);
            $grid->column('is_open', '是否开启')->bool()->width('100px');
            $grid->column('created_at', '创建时间')->width('160px');
            $grid->column('updated_at', '更新时间')->width('160px');

            $grid->enableQuickCreateButton();
            $grid->enableBatchActions();
            $grid->disableViewButton();

            // 高级筛选
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->equal('id', 'ID');
                $filter->like('title', '配置名称');
                $filter->equal('driver', '驱动')->select([
                    'db' => '数据库',
                    'aliyun' => '阿里云',
                    'tencent' => '腾讯云',
                    'huawei' => '华为云',
                ]);
                $filter->equal('is_open', '是否开启')->select([
                    1 => '开启',
                    0 => '关闭',
                ]);
                $filter->between('created_at', '创建时间')->datetime();
            });

            // 添加自定义操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new ToggleSmsConfigStatus());
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     */
    protected function detail($id): Show
    {
        return SmsConfigDetail::make($id);
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new SmsConfigRepository, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('title', '配置名称')
                ->required()
                ->rules('required|string|max:200|unique:fsms_config,title,{{id}}')
                ->help('配置名称用于区分不同的短信服务');

            $form->select('driver', '驱动')
                ->required()
                ->options([
                    'aliyun' => '阿里云短信服务',
                    'tencent' => '腾讯云短信服务',
                    'huawei' => '华为云短信服务',
                ])
                ->help('选择短信服务提供商')
                ->rules('required|string|in:aliyun,tencent,huawei');

            $form->select('type', '类型')
                ->required()
                ->options([
                    1 => '验证码',
                    2 => '通知短信',
                    3 => '营销短信',
                ])
                ->default(1)
                ->help('短信类型');

            $form->text('group', '分组')
                ->required()
                ->default('default')
                ->rules('required|string|max:100')
                ->help('配置分组，用于区分不同业务场景');

            $form->textarea('desc', '描述')
                ->rules('max:500')
                ->help('配置描述信息');

            $form->switch('is_open', '是否开启')
                ->default(1)
                ->help('开启后该配置将用于发送短信');

            $form->textarea('value', '配置内容')
                ->help('JSON格式的配置内容，包含AccessKey、Secret等信息')
                ->rules('required|json')
                ->attribute('rows', 6);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 保存前处理
            $form->saving(function (Form $form) {
                $config = $form->value;

                // 验证JSON格式
                if (is_string($config)) {
                    json_decode($config);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $form->response()->error('配置内容必须是有效的JSON格式');
                    }
                }
            });
        });
    }

    /**
     * Get the title of the controller.
     */
    public function title(): string
    {
        return $this->title;
    }
}
