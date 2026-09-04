<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\Application\Models\FeatureWhitelist;
use Modules\Application\Services\FeatureService;

/**
 * 功能白名单管理控制器
 *
 * 提供Dcat Admin后台白名单管理界面
 */
class FeatureWhitelistController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '功能白名单管理';

    /**
     * 列表页
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $featureId = request()->route('feature_id');
        $feature = FeatureService::getFeatureById($featureId);

        if (!$feature) {
            abort(404, '功能不存在');
        }

        $this->title = '功能「' . $feature->name . '」白名单管理';

        return Grid::make(FeatureWhitelist::where('feature_id', $featureId), function (Grid $grid) use ($featureId) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('user_id', '用户ID');
            $grid->column('user.name', '用户名称')->display(function ($value) {
                return $value ?? '-';
            });
            $grid->column('reason', '加入原因')->limit(50);
            $grid->column('created_at', '加入时间');
            $grid->column('updated_at', '更新时间');

            // 筛选器
            $grid->Filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->equal('user_id', '用户ID');
                $filter->like('reason', '加入原因');
            });

            // 快速创建
            $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use ($featureId) {
                $create->hidden('feature_id')->value($featureId);
                $create->text('user_id', '用户ID')->rules('required|integer|min:1');
                $create->text('reason', '加入原因');
            });

            // 禁止新增和删除按钮(只允许通过快速创建和删除操作)
            $grid->disableCreateButton();
        });
    }

    /**
     * 表单页
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(FeatureWhitelist::class, function (Form $form) {
            $featureId = request()->route('feature_id');
            $feature = FeatureService::getFeatureById($featureId);

            if (!$feature) {
                abort(404, '功能不存在');
            }

            $form->display('id', 'ID');
            $form->hidden('feature_id')->value($featureId);
            $form->number('user_id', '用户ID')->required();
            $form->text('reason', '加入原因');
            $form->display('created_at', '加入时间');
            $form->display('updated_at', '更新时间');

            $form->saving(function (Form $form) use ($feature) {
                $form->response()->success('已将用户添加到功能「' . $feature->name . '」的白名单');
            });
        });
    }
}