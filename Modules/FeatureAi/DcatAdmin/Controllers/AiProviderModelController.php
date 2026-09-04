<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Actions\CopyModelAction;
use Modules\FeatureAi\DcatAdmin\Repositories\AiProviderModelRepository;
use Modules\FeatureAi\Enums\AiModelType;
use Modules\FeatureAi\Models\AiProvider;

/**
 * AI供应商模型管理
 */
class AiProviderModelController extends AdminController
{
    protected $title = 'AI模型管理';

    protected $description = '管理AI供应商下的具体模型配置，包括模型名称、类型、token限制、成本定价等';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new AiProviderModelRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('80px');

            // 供应商名称（关联显示）
            $grid->column('provider_id', '供应商')
                ->width('150px')
                ->display(function ($value) {
                    $provider = $this->provider;

                    return $provider ? $value.': '.$provider->provider_name : '-';
                });

            $grid->column('model_name', '模型名称')
                ->width('200px')
                ->copyable();

            // 模型类型（使用badge显示）
            $grid->column('model_type', '模型类型')
                ->width('120px')
                ->using([
                    'chat' => '<span class="badge badge-primary">聊天模型</span>',
                    'image' => '<span class="badge badge-success">图片生成</span>',
                    'embedding' => '<span class="badge badge-info">嵌入模型</span>',
                    'other' => '<span class="badge badge-secondary">其他</span>',
                ]);

            $grid->column('max_tokens', '最大Tokens')
                ->sortable()
                ->width('120px')
                ->display(function ($value) {
                    return $value ? number_format($value) : '-';
                });

            $grid->column('is_active', '是否启用')
                ->switch()
                ->width('100px');

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

                // 模型类型筛选
                $filter->equal('model_type', '模型类型')->select([
                    'chat' => '聊天模型',
                    'image' => '图片生成',
                    'embedding' => '嵌入模型',
                    'other' => '其他',
                ]);

                // 是否启用筛选
                $filter->equal('is_active', '是否启用')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);

                // 创建时间范围
                $filter->between('created_at', '创建时间')->datetime();
            });

            // 行操作按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new CopyModelAction); // 添加复制按钮
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
        return Show::make($id, new AiProviderModelRepository, function (Show $show) {
            $show->field('id', 'ID');

            $show->field('provider_id', '供应商')
                ->as(function ($value) {
                    $provider = AiProvider::find($value);

                    return $provider ? $provider->provider_name.' (ID: '.$value.')' : $value;
                });

            $show->field('model_name', '模型名称');

            $show->field('model_type', '模型类型')
                ->using([
                    'chat' => '聊天模型',
                    'image' => '图片生成',
                    'embedding' => '嵌入模型',
                    'other' => '其他',
                ]);

            $show->field('max_tokens', '最大Tokens');

            $show->field('cost_per_input_token', '输入Token单价(美元)')
                ->as(function ($value) {
                    return $value ? '$'.number_format((float) $value, 10) : '-';
                });

            $show->field('cost_per_output_token', '输出Token单价(美元)')
                ->as(function ($value) {
                    return $value ? '$'.number_format((float) $value, 10) : '-';
                });

            $show->field('is_active', '是否启用')
                ->using([
                    1 => '启用',
                    0 => '禁用',
                ]);

            $show->field('config_json', '模型配置')
                ->json();

            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new AiProviderModelRepository, function (Form $form) {
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

            $form->text('model_name', '模型名称')
                ->required()
                ->rules('required|string|max:128')
                ->help('如：gpt-4、claude-3-opus-20240229等');

            // 模型类型下拉
            $modelTypeOptions = [];
            foreach (AiModelType::cases() as $case) {
                $modelTypeOptions[$case->value] = $case->getName();
            }

            $form->select('model_type', '模型类型')
                ->required()
                ->options($modelTypeOptions)
                ->help('选择模型类型');

            $form->number('max_tokens', '最大Tokens')
                ->min(1)
                ->help('模型支持的最大token数量限制');

            $form->decimal('cost_per_input_token', '输入Token单价(美元)')
                ->rules('min:0')
                ->default(0)
                ->help('每1000个输入token的美元价格，如：0.03表示$0.03/1K tokens');

            $form->decimal('cost_per_output_token', '输出Token单价(美元)')
                ->rules('min:0')
                ->default(0)
                ->help('每1000个输出token的美元价格');

            $form->switch('is_active', '是否启用')
                ->default(1)
                ->help('启用后该模型可用于AI服务');

            $form->textarea('config_json', '模型配置')
                ->help('JSON格式的模型特定配置，如：{"temperature": 0.7, "top_p": 1}')
                ->attribute('rows', 4);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 保存前验证JSON格式
            $form->saving(function (Form $form) {
                $configJson = $form->input('config_json');
                if (! empty($configJson) && is_string($configJson)) {
                    json_decode($configJson);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $form->response()->error('模型配置必须是有效的JSON格式');
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
