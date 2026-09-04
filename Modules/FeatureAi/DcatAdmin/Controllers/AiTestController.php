<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Repositories\AiTestRepository;
use Modules\FeatureAi\DcatAdmin\Validations\AiTestValidation;
use Modules\FeatureAi\Models\AiProvider;
use Modules\FeatureAi\Models\AiProviderModel;
use Modules\FeatureAi\Models\AiTestResult;

/**
 * AI集成测试管理
 *
 * 管理AI集成测试任务，包括连接测试、响应测试、成本测试、图片测试等
 * Show详情页内嵌AiTestResult Grid显示测试结果详情
 */
class AiTestController extends AdminController
{
    protected $title = 'AI集成测试管理';

    protected $description = '管理AI集成测试任务，支持连接测试、响应测试、成本测试、图片测试等类型，详情页可查看具体测试结果';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new AiTestRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('80px');

            // 供应商名称（关联显示）
            $grid->column('provider.provider_name', '供应商')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 模型名称（关联显示）
            $grid->column('model.model_name', '模型')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 测试类型
            $grid->column('test_type', '测试类型')
                ->width('120px')
                ->using([
                    'connect' => '<span class="badge badge-info">连接测试</span>',
                    'response' => '<span class="badge badge-primary">响应测试</span>',
                    'cost' => '<span class="badge badge-success">成本测试</span>',
                    'image' => '<span class="badge badge-warning">图片测试</span>',
                ]);

            $grid->column('test_name', '测试名称')
                ->width('200px')
                ->copyable();

            // 状态（badge显示）
            $grid->column('status', '状态')
                ->width('100px')
                ->using([
                    1 => '<span class="badge" style="background:#6b7280;color:#fff">待执行</span>',
                    2 => '<span class="badge" style="background:#3b82f6;color:#fff">执行中</span>',
                    3 => '<span class="badge" style="background:#10b981;color:#fff">成功</span>',
                    4 => '<span class="badge" style="background:#ef4444;color:#fff">失败</span>',
                ]);

            // 统计信息
            $grid->column('success_count', '成功数')
                ->width('80px')
                ->display(function ($value) {
                    return $value ?? 0;
                });

            $grid->column('fail_count', '失败数')
                ->width('80px')
                ->display(function ($value) {
                    return $value ?? 0;
                });

            $grid->column('avg_response_time_ms', '平均响应(ms)')
                ->width('120px')
                ->sortable()
                ->display(function ($value) {
                    return $value ? number_format($value) : '-';
                });

            $grid->column('created_at', '创建时间')
                ->sortable()
                ->width('160px');

            // 高级筛选
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->equal('id', 'ID');

                // 供应商筛选
                $filter->equal('provider_id', '供应商')->select(
                    AiProvider::where('is_active', 1)
                        ->pluck('provider_name', 'id')
                        ->toArray()
                );

                // 模型筛选
                $filter->equal('model_id', '模型')->select(
                    AiProviderModel::where('is_active', 1)
                        ->pluck('model_name', 'id')
                        ->toArray()
                );

                // 测试类型筛选
                $filter->equal('test_type', '测试类型')->select([
                    'connect' => '连接测试',
                    'response' => '响应测试',
                    'cost' => '成本测试',
                    'image' => '图片测试',
                ]);

                // 状态筛选
                $filter->equal('status', '状态')->select([
                    1 => '待执行',
                    2 => '执行中',
                    3 => '成功',
                    4 => '失败',
                ]);

                // 创建时间范围
                $filter->between('created_at', '创建时间')->datetime();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, new AiTestRepository, function (Show $show) {
            $show->panel()->body(function ($show) {
                // 基本信息区域
                $show->html('<div class="card mb-4"><div class="card-header"><h5>基本信息</h5></div><div class="card-body">');

                $show->field('id', 'ID');

                $show->field('provider_id', '供应商')
                    ->as(function ($value) {
                        $provider = AiProvider::find($value);
                        return $provider ? $provider->provider_name . ' (ID: ' . $value . ')' : $value;
                    });

                $show->field('model_id', '模型')
                    ->as(function ($value) {
                        $model = AiProviderModel::find($value);
                        return $model ? $model->model_name . ' (ID: ' . $value . ')' : $value;
                    });

                $show->field('test_type', '测试类型')
                    ->using([
                        'connect' => '连接测试',
                        'response' => '响应测试',
                        'cost' => '成本测试',
                        'image' => '图片测试',
                    ]);

                $show->field('test_name', '测试名称');

                $show->field('test_config_json', '测试配置')
                    ->json();

                $show->field('status', '状态')
                    ->using([
                        1 => '待执行',
                        2 => '执行中',
                        3 => '成功',
                        4 => '失败',
                    ]);

                $show->field('total_tests', '总测试次数');
                $show->field('success_count', '成功次数');
                $show->field('fail_count', '失败次数');
                $show->field('avg_response_time_ms', '平均响应时间(毫秒)');

                $show->field('created_at', '创建时间');
                $show->field('updated_at', '更新时间');

                $show->html('</div></div>');

                // 内嵌测试结果 Grid
                $testId = $show->getKey();
                $resultsCount = AiTestResult::where('test_id', $testId)->count();

                $show->html('<div class="card"><div class="card-header"><h5>测试结果详情 (' . $resultsCount . '条记录)</h5></div><div class="card-body">');

                // 创建内嵌Grid
                $resultGrid = new Grid(new AiTestResult);
                $resultGrid->model()->where('test_id', $testId);
                $resultGrid->setResource(admin_url('ai-test-results'));
                $resultGrid->disableCreateButton();
                $resultGrid->disableExport();
                $resultGrid->disableBatchDelete();

                // Grid列定义
                $resultGrid->column('test_sequence', '测试序号')->sortable()->width('100px');

                $resultGrid->column('is_success', '是否成功')
                    ->width('100px')
                    ->using([
                        1 => '<span class="badge badge-success">成功</span>',
                        0 => '<span class="badge badge-danger">失败</span>',
                    ]);

                $resultGrid->column('response_time_ms', '响应时间(ms)')
                    ->width('120px')
                    ->display(function ($value) {
                        return $value ? number_format($value) . 'ms' : '-';
                    });

                $resultGrid->column('input_tokens', '输入Tokens')
                    ->width('100px')
                    ->display(function ($value) {
                        return $value ? number_format($value) : '-';
                    });

                $resultGrid->column('output_tokens', '输出Tokens')
                    ->width('100px')
                    ->display(function ($value) {
                        return $value ? number_format($value) : '-';
                    });

                $resultGrid->column('cost', '成本')
                    ->width('100px')
                    ->display(function ($value) {
                        return $value ? '$' . $value : '-';
                    });

                $resultGrid->column('response_text', '响应文本')
                    ->width('250px')
                    ->display(function ($value) {
                        if (empty($value)) {
                            return '-';
                        }
                        $short = mb_substr($value, 0, 50);
                        return $short . (mb_strlen($value) > 50 ? '...' : '');
                    });

                $resultGrid->column('error_message', '错误信息')
                    ->width('200px')
                    ->display(function ($value) {
                        if (empty($value)) {
                            return '-';
                        }
                        return '<span class="text-danger">' . mb_substr($value, 0, 50) . '</span>';
                    });

                $resultGrid->column('created_at', '测试时间')
                    ->width('160px');

                $show->html($resultGrid->render());
                $show->html('</div></div>');
            });
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new AiTestRepository, function (Form $form) {
            $form->display('id', 'ID');

            // 供应商选择（仅显示启用的供应商）
            $form->select('provider_id', '供应商')
                ->required()
                ->options(
                    AiProvider::where('is_active', 1)
                        ->pluck('provider_name', 'id')
                        ->toArray()
                )
                ->help('选择AI服务提供商（仅显示启用的供应商）');

            // 模型选择（动态加载，根据provider_id）
            $form->select('model_id', '模型')
                ->options(function ($value) {
                    if (!$value) {
                        return [];
                    }
                    return AiProviderModel::where('id', $value)
                        ->pluck('model_name', 'id')
                        ->toArray();
                })
                ->ajax(admin_url('ai-provider-models'))
                ->help('选择AI模型，会根据供应商动态加载');

            // 测试类型下拉
            $form->select('test_type', '测试类型')
                ->required()
                ->options([
                    'connect' => '连接测试 - 验证API连接可用性',
                    'response' => '响应测试 - 验证响应质量和准确性',
                    'cost' => '成本测试 - 验证成本计算准确性',
                    'image' => '图片测试 - 验证图片生成能力',
                ])
                ->help('选择要执行的测试类型');

            $form->text('test_name', '测试名称')
                ->required()
                ->rules('required|string|max:128')
                ->help('测试任务的名称，用于标识和查找');

            $form->textarea('test_config_json', '测试配置')
                ->help('JSON格式的测试配置参数，如：{"timeout": 30, "retry": 3}')
                ->attribute('rows', 4);

            // 状态选择
            $form->select('status', '状态')
                ->required()
                ->options([
                    1 => '待执行',
                    2 => '执行中',
                    3 => '成功',
                    4 => '失败',
                ])
                ->default(1)
                ->help('测试任务当前状态');

            // 统计字段（只读）
            $form->display('total_tests', '总测试次数');
            $form->display('success_count', '成功次数');
            $form->display('fail_count', '失败次数');
            $form->display('avg_response_time_ms', '平均响应时间(毫秒)');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 使用AiTestValidation进行验证
            $form->setValidator(AiTestValidation::class);

            // 保存前验证JSON格式
            $form->saving(function (Form $form) {
                $configJson = $form->input('test_config_json');
                if (!empty($configJson) && is_string($configJson)) {
                    json_decode($configJson);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $form->response()->error('测试配置必须是有效的JSON格式');
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
