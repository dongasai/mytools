<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Application\DcatAdmin\Helper\FilterHelper;
use Modules\Application\DcatAdmin\Helper\FormHelper;
use Modules\Application\DcatAdmin\Helper\GridHelper;
use Modules\Application\DcatAdmin\Helper\ShowHelper;
use Modules\Application\DcatAdmin\Tools\RefreshCacheTool;
use Modules\Application\Enums\CONFIG_TYPE;
use Modules\Application\Services\ConfigService;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\Application\DcatAdmin\Repositories\AppConfig;

/**
 * 系统配置管理控制器
 */
class ConfigAdminController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '系统配置管理';

    /**
     * 配置服务
     *
     * @var ConfigService
     */
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = new ConfigService;
    }

 

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new AppConfig, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $helper->columnId();
            $helper->columnKeyname();
            $helper->columnTitle();
            $helper->columnConfigType();
            $helper->columnValue();
            $helper->columnGroup();
            $helper->columnGroup2();
            $helper->columnIsClient();
            $helper->columnCreatedAt();
            $helper->columnUpdatedAt();

            // 添加刷新缓存按钮
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new RefreshCacheTool());
            });

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $helper = new FilterHelper($filter, $this);
                $helper->equalId(); // 使用基类方法
                $helper->likeKeyname();
                $helper->likeTitle();
                $helper->equalConfigType();
                $helper->likeValue();
                $helper->equalGroup();
                $helper->equalGroup2();
                $helper->equalIsClient();
                $helper->betweenCreatedAt();
            });
        });
    }

    /**
     * 详情页面
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new AppConfig, function (Show $show) {
            $helper = new ShowHelper($show, $this);

            $helper->fieldId();
            $helper->fieldKeyname();
            $helper->fieldTitle();
            $helper->fieldConfigType();
            $helper->fieldValue();
            $helper->fieldGroup();
            $helper->fieldGroup2();
            $helper->fieldDesc();
            $helper->fieldOptions();
            $helper->fieldIsClient();
            $helper->fieldCreatedAt();
            $helper->fieldUpdatedAt();
            $helper->fieldDeletedAt();
        });
    }

    /**
     * 表单页面
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new AppConfig, function (Form $form) {
            $helper = new FormHelper($form, $this);

            $form->display('id', 'ID');
            $helper->textKeyname();
            $helper->textTitle();
            $helper->selectConfigType();
            $helper->textareaValue();
            $helper->textGroup();
            $helper->textGroup2();
            $helper->textareaDesc();
            $helper->textareaOptions();
            $helper->switchIsClient();

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 保存前回调
            $form->saving(function (Form $form) {
                // 处理选项
                if ($form->options) {
                    $options = json_decode($form->options, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $form->response()->error('选项不是有效的JSON格式');
                    }
                    $form->options = $options;
                }

                // 根据类型处理值
                if ($form->type && $form->value) {
                    switch ($form->type) {
                        case CONFIG_TYPE::TYPE_INT->value:
                            $form->value = (int) $form->value;
                            break;
                        case CONFIG_TYPE::TYPE_FLOAT->value:
                            $form->value = (float) $form->value;
                            break;
                        case CONFIG_TYPE::TYPE_BOOL->value:
                        case CONFIG_TYPE::TYPE_IS->value:
                            $form->value = (bool) $form->value;
                            break;
                        case CONFIG_TYPE::TYPE_JSON->value:
                        case CONFIG_TYPE::TYPE_EMBEDS->value:
                            $value = json_decode($form->value, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                return $form->response()->error('值不是有效的JSON格式');
                            }
                            $form->value = $value;
                            break;
                    }
                }
            });

            // 保存后回调
            $form->saved(function (Form $form) {
                // 刷新缓存
                $this->service->refreshCache();

                return $form->response()->success('保存成功，缓存已刷新');
            });
        });
    }
}
