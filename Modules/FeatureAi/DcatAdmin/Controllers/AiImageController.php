<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Repositories\AiImageRepository;
use Modules\FeatureAi\Models\AiProvider;
use Modules\FeatureAi\Models\AiProviderModel;

/**
 * AI图片生成记录管理
 *
 * 只读控制器，用于查看AI图片生成记录
 *
 * @author AI Team
 */
class AiImageController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = 'AI图片生成记录';

    /**
     * 页面描述
     */
    protected $description = '查看AI图片生成记录，支持按供应商、模型、状态筛选';

    /**
     * 禁用创建按钮
     */
    protected function disableCreateButton(): void
    {
        // 只读模式，禁用创建
    }

    /**
     * 禁用编辑按钮
     */
    protected function disableEditButton(): void
    {
        // 只读模式，禁用编辑
    }

    /**
     * 禁用删除按钮
     */
    protected function disableDeleteButton(): void
    {
        // 只读模式，禁用删除
    }

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        $repository = new AiImageRepository;

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

            // 提示文本（截断50字符）
            $grid->column('prompt_text', '提示文本')
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

            // 图片URL（可点击链接）
            $grid->column('image_url', '图片URL')
                ->width('200px')
                ->display(function ($value) {
                    if (empty($value)) {
                        return '-';
                    }
                    $url = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-link">' . $url . '</a>';
                });

            // 图片尺寸
            $grid->column('image_size', '尺寸')
                ->width('100px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 成本（美元）
            $grid->column('cost', '成本(美元)')
                ->sortable()
                ->width('110px')
                ->display(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            // 状态（使用badge显示）
            $grid->column('status', '状态')
                ->width('100px')
                ->using([
                    1 => '<span class="badge badge-secondary">待处理</span>',
                    2 => '<span class="badge badge-primary">生成中</span>',
                    3 => '<span class="badge badge-success">成功</span>',
                    4 => '<span class="badge badge-danger">失败</span>',
                ]);

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
                    1 => '待处理',
                    2 => '生成中',
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
        return Show::make($id, new AiImageRepository, function (Show $show) {
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

            $show->field('user_id', '用户ID')
                ->as(function ($value) {
                    return $value ?: '系统';
                });

            // 完整提示文本（textarea）
            $show->field('prompt_text', '提示文本')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '<textarea class="form-control" rows="6" readonly>-</textarea>';
                    }
                    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<textarea class="form-control" rows="6" readonly>' . $escaped . '</textarea>';
                });

            // 图片预览
            $show->field('image_url', '图片预览')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '<span class="text-muted">暂无图片</span>';
                    }
                    $url = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<div class="mb-2"><img src="' . $url . '" alt="生成图片" style="max-width: 100%; max-height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>';
                });

            // 图片URL
            $show->field('image_url', '图片URL')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '-';
                    }
                    $url = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
                });

            // 图片路径
            $show->field('image_path', '图片路径')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            // 图片尺寸
            $show->field('image_size', '图片尺寸');

            // 成本
            $show->field('cost', '成本(美元)')
                ->as(function ($value) {
                    return $value ? '$' . number_format((float)$value, 6) : '-';
                });

            // 状态
            $show->field('status', '状态')
                ->using([
                    1 => '待处理',
                    2 => '生成中',
                    3 => '成功',
                    4 => '失败',
                ]);

            // 错误信息（textarea）
            $show->field('error_message', '错误信息')
                ->unescape()
                ->as(function ($value) {
                    if (empty($value)) {
                        return '<textarea class="form-control" rows="4" readonly>-</textarea>';
                    }
                    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<textarea class="form-control" rows="4" readonly style="color: #dc3545;">' . $escaped . '</textarea>';
                });

            // 重试次数
            $show->field('retry_count', '重试次数');

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
