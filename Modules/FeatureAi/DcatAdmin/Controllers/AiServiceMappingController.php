<?php

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Repositories\AiServiceMappingRepository;
use Modules\FeatureAi\Models\AiProvider;

/**
 * AI服务映射管理
 */
class AiServiceMappingController extends AdminController
{
    protected $title = 'AI服务映射';

    protected $description = '管理服务类型到AI供应商的映射关系';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new AiServiceMappingRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('service_type', '服务类型')->width('150px');
            $grid->column('service_name', '服务名字')->width('150px')->display(function ($value) {
                return empty($value) ? '默认' : $value;
            });
            $grid->column('provider_id', '供应商')->width('250px')->display(function ($value) {
                $provider = AiProvider::find($value);
                if (!$provider) {
                    return '-';
                }

                return "{$provider->provider_name} ({$provider->provider_type})";
            });
            $grid->column('is_active', '是否启用')->bool()->width('100px');
            $grid->column('description', '映射说明')->width('200px');
            $grid->column('created_at', '创建时间')->width('160px');

            $grid->disableViewButton();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->like('service_type', '服务类型');
                $filter->equal('is_active', '是否启用')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);
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
        return Show::make($id, new AiServiceMappingRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('service_type', '服务类型');
            $show->field('service_name', '服务名字');
            $show->field('provider_id', '供应商');
            $show->field('is_active', '是否启用')->using([
                1 => '启用',
                0 => '禁用',
            ]);
            $show->field('description', '映射说明');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new AiServiceMappingRepository, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('service_type', '服务类型')
                ->required()
                ->rules('required|string|max:50')
                ->help('服务类型标识，如a/b等');

            $form->text('service_name', '服务名字')
                ->default('')
                ->rules('nullable|string|max:100')
                ->help('服务名字，留空表示该类型的默认供应商');

            // 准备供应商选项
            $providerOptions = AiProvider::where('is_active', 1)
                ->get()
                ->mapWithKeys(fn ($p) => [$p->id => "{$p->provider_name} ({$p->provider_type})"])
                ->toArray();

            $form->select('provider_id', '供应商')
                ->required()
                ->options($providerOptions)
                ->help('选择启用的AI服务供应商');

            $form->switch('is_active', '是否启用')
                ->default(1)
                ->help('启用后该映射关系生效');

            $form->textarea('description', '映射说明')
                ->help('映射用途说明');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
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
