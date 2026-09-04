<?php

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Repositories\LlmApiLogRepository;
use Modules\FeatureAi\Models\AiProvider;
use Modules\FeatureAi\Models\AiProviderModel;

/**
 * LLM API调用日志管理
 *
 * 记录单次LLM API调用的详细信息，包括供应商、模型、token消耗、成本和响应时间。
 */
class LlmApiLogController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = 'LLM API调用日志';

    /**
     * 页面描述
     *
     * @var string
     */
    protected $description = '查看LLM API调用记录，支持按供应商、模型、服务类型、状态筛选';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $repository = new LlmApiLogRepository;

        return Grid::make($repository->getGridQuery(), function (Grid $grid) {
            // 禁用新增、编辑、删除按钮（只读模式）
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();

            $grid->column('id', 'ID')
                ->sortable()
                ->width('80px');

            // 供应商名称（关联显示）
            $grid->column('provider.provider_name', '供应商')
                ->width('120px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 模型名称（关联显示）
            $grid->column('model.model_name', '模型')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 服务类型
            $grid->column('service_type', '服务类型')
                ->width('100px');

            // 服务名称
            $grid->column('service_name', '服务名称')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 请求ID
            $grid->column('request_id', '请求ID')
                ->width('200px')
                ->copyable();

            // Tokens统计
            $grid->column('input_tokens', '输入Tokens')
                ->sortable()
                ->width('110px');

            $grid->column('output_tokens', '输出Tokens')
                ->sortable()
                ->width('110px');

            $grid->column('total_tokens', '总Tokens')
                ->sortable()
                ->width('110px');

            // 成本
            $grid->column('total_cost', '成本(美元)')
                ->sortable()
                ->width('120px')
                ->display(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            // 状态（使用badge显示）
            $grid->column('success', '状态')
                ->width('90px')
                ->display(function ($value) {
                    return $value
                        ? '<span class="badge badge-success">成功</span>'
                        : '<span class="badge badge-danger">失败</span>';
                });

            // 响应时间
            $grid->column('response_time_ms', '响应时间(ms)')
                ->sortable()
                ->width('130px')
                ->display(function ($value) {
                    return $value ? $value . 'ms' : '-';
                });

            // 创建时间
            $grid->column('created_at', '创建时间')
                ->sortable()
                ->width('160px');

            // 筛选条件
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                // ID筛选
                $filter->equal('id', 'ID');

                // 供应商筛选
                $filter->equal('provider_id', '供应商')->select(
                    AiProvider::pluck('provider_name', 'id')->toArray()
                );

                // 模型筛选
                $filter->equal('model_id', '模型')->select(
                    AiProviderModel::pluck('model_name', 'id')->toArray()
                );

                // 服务类型筛选
                $filter->equal('service_type', '服务类型');

                // 状态筛选
                $filter->equal('success', '状态')->select([
                    1 => '成功',
                    0 => '失败',
                ]);

                // 请求ID筛选
                $filter->like('request_id', '请求ID');

                // 创建时间范围
                $filter->between('created_at', '创建时间')->datetime();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id): Show
    {
        return Show::make($id, new LlmApiLogRepository, function (Show $show) {
            $show->field('id', 'ID');

            $show->field('provider_id', '供应商ID')
                ->as(function ($value) {
                    $provider = AiProvider::find($value);
                    return $provider ? $provider->provider_name . ' (ID: ' . $value . ')' : $value;
                });

            $show->field('model_id', '模型ID')
                ->as(function ($value) {
                    $model = AiProviderModel::find($value);
                    return $model ? $model->model_name . ' (ID: ' . $value . ')' : $value;
                });

            $show->field('model_name', '模型名称(冗余)');
            $show->field('request_id', '请求ID');
            $show->field('service_type', '服务类型');
            $show->field('service_name', '服务名称');

            // Tokens统计
            $show->field('input_tokens', '输入Tokens');
            $show->field('output_tokens', '输出Tokens');
            $show->field('total_tokens', '总Tokens');

            // 成本（高精度）
            $show->field('input_cost', '输入成本(美元)')
                ->as(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            $show->field('output_cost', '输出成本(美元)')
                ->as(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            $show->field('total_cost', '总成本(美元)')
                ->as(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            // 响应时间
            $show->field('response_time_ms', '响应时间(ms)')
                ->as(function ($value) {
                    return $value ? $value . 'ms' : '-';
                });

            // 状态
            $show->field('success', '状态')
                ->using([
                    1 => '成功',
                    0 => '失败',
                ]);

            // 错误类型（失败时显示）
            $show->field('error_type', '错误类型')
                ->as(function ($value) {
                    return $value ?: '-';
                });

            // 错误详细信息（失败时显示）
            $show->field('error_message', '错误详细信息')
                ->as(function ($value) {
                    return $value ?: '-';
                });

            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Get the title of the controller.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
