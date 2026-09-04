<?php

namespace Modules\FeatureSms\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSms\DcatAdmin\Repositories\SmsCodeRepository;

/**
 * 短信验证码管理
 */
class SmsCodeController extends AdminController
{
    protected $title = '短信验证码';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new SmsCodeRepository, function (Grid $grid) {
            $grid->column('id', 'ID');
            $grid->column('mobile', '手机号');
            $grid->column('code_value', '验证码');
            $grid->column('type', '类型')->using(\Modules\FeatureSms\Enums\CODE_TYPE::getLabels());
            $grid->column('token', '令牌');
            $grid->column('created_at', '创建时间');

            $grid->disableCreateButton();
            $grid->disableBatchActions();
            $grid->disableQuickCreateButton();
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->like('mobile', '手机号');
                $filter->equal('type', '类型')->select(\Modules\FeatureSms\Enums\CODE_TYPE::getLabels());
                $filter->between('created_at', '创建时间')->datetime();
            });

            $grid->model()->latest('id');
        });
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, new SmsCodeRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('mobile', '手机号');
            $show->field('code_value', '验证码');
            $show->field('type', '类型')->using(\Modules\FeatureSms\Enums\CODE_TYPE::getLabels());
            $show->field('token', '令牌');
            $show->field('created_at', '创建时间');
        });
    }
}
