<?php

namespace Modules\AClean\DcatAdmin\Controllers;

use Modules\AClean\DcatAdmin\Repositories\CleanupPlanContentRepository;
use Modules\AClean\Models\CleanupConfig;
use Modules\AClean\Models\CleanupPlan;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 计划内容管理控制器
 *
 * 路由：/admin/cleanup/plan-contents
 */
class CleanupPlanContentController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '计划内容管理';

    /**
     * 数据仓库
     */
    protected function repository()
    {
        return CleanupPlanContentRepository::class;
    }

    /**
     * 列表页面
     */
    protected function grid(): Grid
    {
        return Grid::make(new CleanupPlanContentRepository, function (Grid $grid) {
            // 基础设置
            $grid->column('id', 'ID')->sortable();

            // 计划信息
            $grid->column('plan.plan_name', '所属计划')->sortable();

            // Model/表信息
            $grid->column('model_class', 'Model类')->display(function ($value) {
                if (! empty($value)) {
                    return "<span >{$value}</span>";
                }

                return '<span class="label label-warning">未设置</span>';
            })->sortable();

            $grid->column('table_name', '表名')->display(function ($value) {
                if (! empty($this->model_class)) {
                    try {
                        $model = new $this->model_class;
                        $actualTable = $model->getTable();

                        return "<span class='text-muted'>{$actualTable}</span>";
                    } catch (\Exception $e) {
                        return "<span class='text-danger'>{$value} (Model错误)</span>";
                    }
                } else {
                    return "<span class='text-warning'>{$value} (旧数据)</span>";
                }
            })->sortable();

            // 清理类型
            $grid->column('cleanup_type', '清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ])->label([
                1 => 'danger',
                2 => 'warning',
                3 => 'info',
                4 => 'primary',
                5 => 'secondary',
            ])->sortable();

            // 配置信息
            $grid->column('priority', '优先级')->sortable();
            $grid->column('batch_size', '批处理大小')->display(function ($value) {
                return number_format($value);
            })->sortable();

            // 状态
            $grid->column('is_enabled', '启用状态')->using([1 => '启用', 0 => '禁用'])->label([
                1 => 'success',
                0 => 'danger',
            ])->sortable();
            $grid->column('backup_enabled', '备份启用')->using([1 => '启用', 0 => '禁用'])->label([
                1 => 'success',
                0 => 'danger',
            ])->sortable();

            // 条件描述
            $grid->column('conditions_description', '清理条件')->display(function () {
                return $this->conditions_description;
            });

            // 时间
            $grid->column('created_at', '创建时间')->sortable();
            $grid->column('updated_at', '更新时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                // 按计划筛选
                $plans = CleanupPlan::pluck('plan_name', 'id')->toArray();
                $filter->equal('plan_id', '所属计划')->select($plans);

                // Model类筛选
                $filter->like('model_class', 'Model类');

                // 表名筛选
                $filter->like('table_name', '表名');

                // 按清理类型筛选
                $filter->equal('cleanup_type', '清理类型')->select([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ]);

                // 按状态筛选
                $filter->equal('is_enabled', '启用状态')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);

                $filter->equal('backup_enabled', '备份启用')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);

                // 按表名搜索
                $filter->like('table_name', '表名');

                // 按优先级范围
                $filter->between('priority', '优先级');
            });

            // 批量操作
            $grid->batchActions([
                new \Modules\AClean\DcatAdmin\Actions\BatchEnableAction,
                new \Modules\AClean\DcatAdmin\Actions\BatchDisableAction,
                new \Modules\AClean\DcatAdmin\Actions\BatchMigrateToModelAction,
            ]);

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\EditPlanContentAction);
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\DeletePlanContentAction);
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\TestCleanupAction);
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\MigrateToModelAction);
            });

            // 设置每页显示数量
            $grid->paginate(20);
        });
    }

    /**
     * 详情页面
     */
    protected function detail($id): Show
    {
        return Show::make($id, new CleanupPlanContentRepository, function (Show $show) {
            $show->field('id', 'ID');

            // 关联信息
            $show->field('plan.plan_name', '所属计划');

            // Model/表信息
            $show->field('model_class', 'Model类')->as(function ($value) {
                if (! empty($value)) {
                    return $value;
                } else {
                    return '未设置（旧数据）';
                }
            });

            $show->field('table_name', '表名')->as(function ($value) {
                if (! empty($this->model_class)) {
                    try {
                        $model = new $this->model_class;

                        return $model->getTable().' (从Model获取)';
                    } catch (\Exception $e) {
                        return $value.' (Model错误: '.$e->getMessage().')';
                    }
                } else {
                    return $value.' (旧数据)';
                }
            });

            // 清理配置
            $show->field('cleanup_type', '清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ]);

            $show->field('conditions', '清理条件')->json();
            $show->field('conditions_description', '条件描述');

            // 执行配置
            $show->field('priority', '优先级');
            $show->field('batch_size', '批处理大小');

            // 状态配置
            $show->field('is_enabled', '启用状态')->using([1 => '启用', 0 => '禁用']);
            $show->field('backup_enabled', '备份启用')->using([1 => '启用', 0 => '禁用']);

            // 备注和时间
            $show->field('notes', '备注说明');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * 创建/编辑表单
     */
    protected function form(): Form
    {
        return Form::make(new CleanupPlanContentRepository, function (Form $form) {
            $form->display('id', 'ID');

            // 基础信息
            $form->select('plan_id', '所属计划')
                ->options(CleanupPlan::pluck('plan_name', 'id')->toArray())
                ->required();

            // Model类选择（推荐使用）
            $availableModels = $this->getAvailableModels();
            $form->select('model_class', 'Model类')
                ->options($availableModels)
                ->help('选择要清理的Model类（推荐使用）');

            // 表名选择（兼容旧数据）
            $availableTables = CleanupConfig::whereNull('model_class')
                ->orWhere('model_class', '')
                ->pluck('table_name', 'table_name')
                ->toArray();

            if (! empty($availableTables)) {
                $form->select('table_name', '表名（兼容旧数据）')
                    ->options($availableTables)
                    ->help('仅用于兼容旧数据，建议使用Model类选择');
            }

            // 清理类型
            $form->select('cleanup_type', '清理类型')
                ->options([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ])
                ->required()
                ->help('选择清理方式');

            // 清理条件（JSON配置）
            $form->keyValue('conditions', '清理条件')
                ->help('JSON格式的清理条件配置，根据清理类型设置相应参数');

            // 执行配置
            $form->number('priority', '优先级')
                ->default(100)
                ->min(1)
                ->max(999)
                ->help('数字越小优先级越高');

            $form->number('batch_size', '批处理大小')
                ->default(1000)
                ->min(100)
                ->max(10000)
                ->help('每批处理的记录数量');

            // 状态配置
            $form->switch('is_enabled', '启用状态')->default(1);
            $form->switch('backup_enabled', '备份启用')->default(1);

            // 备注
            $form->textarea('notes', '备注说明')
                ->help('对此清理配置的说明');

            // 表单验证
            $form->saving(function (Form $form) {
                $modelClass = $form->input('model_class');
                $tableName = $form->input('table_name');

                // 验证至少选择了Model类或表名
                if (empty($modelClass) && empty($tableName)) {
                    return $form->response()->error('请至少选择Model类或表名');
                }

                // 如果选择了Model类，验证Model是否存在
                if (! empty($modelClass)) {
                    if (! class_exists($modelClass)) {
                        return $form->response()->error('选择的Model类不存在：'.$modelClass);
                    }

                    // 自动设置表名
                    try {
                        $modelInstance = new $modelClass;
                        $form->input('table_name', $modelInstance->getTable());
                    } catch (\Exception $e) {
                        return $form->response()->error('Model类实例化失败：'.$e->getMessage());
                    }
                }
            });

            // 时间字段
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 添加JavaScript增强用户体验
            $form->html('
                <script>
                $(document).ready(function() {
                    // 监听Model类选择变化
                    $(document).on("change", "select[name=model_class]", function() {
                        var modelClass = $(this).val();
                        if (modelClass) {
                            // 显示提示信息
                            var className = modelClass.split("\\").pop();
                            var moduleName = modelClass.match(/App\\Module\\([^\\]+)\\/);
                            moduleName = moduleName ? moduleName[1] : "Unknown";

                            // 创建提示信息
                            var infoHtml = "<div class=\"alert alert-info\">" +
                                "<strong>已选择Model：</strong>" + className + "<br>" +
                                "<strong>所属模块：</strong>" + moduleName + "<br>" +
                                "<strong>建议：</strong>使用Model类可以利用Laravel的所有特性，如软删除、事件等" +
                                "</div>";

                            // 移除旧的提示信息
                            $(".model-info-alert").remove();

                            // 添加新的提示信息
                            $(this).closest(".form-group").after("<div class=\"model-info-alert\">" + infoHtml + "</div>");
                        } else {
                            $(".model-info-alert").remove();
                        }
                    });

                    // 页面加载时如果已有选择，显示提示
                    var initialModel = $("select[name=model_class]").val();
                    if (initialModel) {
                        $("select[name=model_class]").trigger("change");
                    }
                });
                </script>
            ', '用户体验增强');
        });
    }

    /**
     * 获取可用的Model类列表
     */
    private function getAvailableModels(): array
    {
        // 从CleanupConfig中获取已配置的Model类
        $configuredModels = CleanupConfig::whereNotNull('model_class')
            ->where('model_class', '!=', '')
            ->pluck('model_class', 'model_class')
            ->toArray();

        // 格式化显示名称
        $formattedModels = [];
        foreach ($configuredModels as $modelClass) {
            $className = class_basename($modelClass);
            $moduleName = $this->extractModuleName($modelClass);
            $formattedModels[$modelClass] = "{$moduleName} - {$className}";
        }

        // 按模块和类名排序
        asort($formattedModels);

        return $formattedModels;
    }

    /**
     * 从Model类名中提取模块名
     */
    private function extractModuleName(string $modelClass): string
    {
        if (preg_match('/App\\Module\\([^\\]+)\\Models\\/', $modelClass, $matches)) {
            return $matches[1];
        }

        return 'Unknown';
    }
}
