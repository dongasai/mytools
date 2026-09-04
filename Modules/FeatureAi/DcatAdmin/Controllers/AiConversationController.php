<?php

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Repositories\AiConversationRepository;
use Modules\FeatureAi\Models\AiProvider;
use Modules\FeatureAi\Models\AiProviderModel;

/**
 * AI对话日志管理
 */
class AiConversationController extends AdminController
{
    protected $title = 'AI对话日志';

    protected $description = '查看AI对话记录和调用日志，支持按供应商、模型、状态筛选';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        $repository = new AiConversationRepository;

        return Grid::make($repository->getGridQuery(), function (Grid $grid) {
            // 禁用新增和编辑按钮（只读模式）
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();

            $grid->column('id', 'ID')->sortable()->width('80px');

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

            $grid->column('conversation_id', '会话ID')
                ->width('180px')
                ->copyable();

            // 用户提问（显示前50字符）
            $grid->column('prompt_text', '用户提问')
                ->width('250px')
                ->display(function ($value) {
                    if (empty($value)) {
                        return '-';
                    }
                    $text = strip_tags($value);
                    if (mb_strlen($text) > 50) {
                        return mb_substr($text, 0, 50) . '...';
                    }
                    return $text;
                });

            // 状态（使用badge显示）
            $grid->column('status', '状态')
                ->width('100px')
                ->using([
                    1 => '<span class="badge badge-primary">进行中</span>',
                    2 => '<span class="badge badge-success">已完成</span>',
                    3 => '<span class="badge badge-danger">失败</span>',
                ]);

            $grid->column('input_tokens', '输入Tokens')
                ->sortable()
                ->width('110px');

            $grid->column('output_tokens', '输出Tokens')
                ->sortable()
                ->width('110px');

            $grid->column('total_cost', '成本(美元)')
                ->sortable()
                ->width('110px')
                ->display(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            $grid->column('response_time_ms', '响应时间(ms)')
                ->sortable()
                ->width('120px')
                ->display(function ($value) {
                    return $value ? $value . 'ms' : '-';
                });

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

                // 状态筛选
                $filter->equal('status', '状态')->select([
                    1 => '进行中',
                    2 => '已完成',
                    3 => '失败',
                ]);

                // 会话ID筛选
                $filter->like('conversation_id', '会话ID');

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
        return Show::make($id, new AiConversationRepository, function (Show $show) {
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

            $show->field('conversation_id', '会话ID');

            $show->field('user_id', '用户ID')
                ->as(function ($value) {
                    return $value ?: '系统测试';
                });

            // prompt_text 使用 textarea
            $show->field('prompt_text', '用户提问')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '<textarea class="form-control" rows="6" readonly>-</textarea>';
                    }
                    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<textarea class="form-control" rows="6" readonly>' . $escaped . '</textarea>';
                });

            // response_text 使用 textarea
            $show->field('response_text', 'AI响应')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '<textarea class="form-control" rows="6" readonly>-</textarea>';
                    }
                    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<textarea class="form-control" rows="6" readonly>' . $escaped . '</textarea>';
                });

            // 状态
            $show->field('status', '状态')
                ->using([
                    1 => '进行中',
                    2 => '已完成',
                    3 => '失败',
                ]);

            $show->field('input_tokens', '输入Tokens');
            $show->field('output_tokens', '输出Tokens');

            $show->field('total_cost', '成本(美元)')
                ->as(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            $show->field('response_time_ms', '响应时间(ms)')
                ->as(function ($value) {
                    return $value ? $value . 'ms' : '-';
                });

            // context_json 使用 json 格式显示
            $show->field('context_json', '对话上下文')
                ->json();

            // error_message 使用 textarea
            $show->field('error_message', '错误信息')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '<textarea class="form-control" rows="4" readonly>-</textarea>';
                    }
                    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<textarea class="form-control" rows="4" readonly>' . $escaped . '</textarea>';
                });

            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
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
