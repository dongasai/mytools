<?php

namespace Modules\AFile\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\AFile\DcatAdmin\Helpers\FileHelper;
use Modules\AFile\DcatAdmin\Repositories\ImageRepository;
use Modules\AFile\Enums\FILE_STATUS;
use Modules\AFile\Enums\FILE_VISIBILITY;
use Modules\AFile\Services\ImgService;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 图片管理控制器
 */
class ImageController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '图片管理';

    /**
     * 图片服务
     *
     * @var ImgService
     */
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = new ImgService;
    }

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ImageRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
                 $grid->column('storageConfig.id', '存储ID')->display(function ($value) {
                return $value ?? '-';
            });
            $grid->column('storageConfig.driver', '存储驱动')->display(function ($value) {
                return $value ?? '-';
            });
            $grid->column('path', '文件路径')->limit(30);
            $grid->column('o_name', '原始名称')->limit(30);
            $grid->column('fsize', '文件大小')->display(function ($value) {
                return FileHelper::formatFileSize($value);
            });
            $grid->column('type1', '文件类型');
            $grid->column('status', '文件状态')
                ->using(FILE_STATUS::getAll())
                ->label([
                    FILE_STATUS::NORMAL->value => 'success',
                    FILE_STATUS::DELETED->value => 'danger',
                ]);
            $grid->column('private', '文件可见性')
                ->display(function ($value) {
                    return $value ? FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PRIVATE->value] : FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PUBLIC->value];
                })
                ->label([
                    0 => 'info',
                    1 => 'warning',
                ]);
            $grid->column('re_type', '关联类型');
            $grid->column('re_id', '关联ID');
            $grid->column('path', '图片预览')->image('', 50, 50);
            $grid->column('created_at', '创建时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
            $grid->column('updated_at', '更新时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            $grid->column('view', '操作')->display(function () {
                $service = new \Modules\AFile\Services\ImgService();
                $url = $service->getPicUrl4Id($this->id);

                return "<a href='{$url}' target='_blank' class='btn btn-sm btn-primary'>查看</a>";
            })->unescape();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '文件ID');
                $filter->like('path', '文件路径');
                $filter->like('o_name', '原始名称');
                $filter->equal('type1', '文件类型');
                $filter->equal('status', '文件状态')->select(FILE_STATUS::getAll());
                $filter->equal('private', '文件可见性')->select([
                    FILE_VISIBILITY::PUBLIC->value => FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PUBLIC->value],
                    FILE_VISIBILITY::PRIVATE->value => FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PRIVATE->value],
                ]);
                $filter->equal('re_type', '关联类型');
                $filter->equal('re_id', '关联ID');
                $filter->equal('user_id', '用户ID');
                $filter->between('created_at', '创建时间')->datetime();
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
        return Show::make($id, new ImageRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('path', '文件路径');
            $show->field('o_name', '原始名称');
            $show->field('fsize', '文件大小')->as(function ($value) {
                return FileHelper::formatFileSize($value);
            });
            $show->field('type1', '文件类型');
            $show->field('status', '文件状态')->as(function ($value) {
                return FILE_STATUS::getAll()[$value] ?? '未知';
            });
            $show->field('private', '文件可见性')->as(function ($value) {
                return $value ? FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PRIVATE->value] : FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PUBLIC->value];
            });
            $show->field('re_type', '关联类型');
            $show->field('re_id', '关联ID');
            $show->field('path', '图片预览')->image();
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            $show->html(function () {
                $service = new \Modules\AFile\Services\ImgService();
                $url = $service->getPicUrl4Id($this->id);

                return "<a href='{$url}' target='_blank' class='btn btn-primary'>查看原图</a>";
            });
        });
    }

    /**
     * 表单页面
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new ImageRepository, function (Form $form) {
            $form->display('id');

            $form->image('image', '图片上传')
                ->required()
                ->autoUpload()
                ->uniqueName();

            $form->radio('private', '文件可见性')
                ->options([
                    FILE_VISIBILITY::PUBLIC->value => FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PUBLIC->value],
                    FILE_VISIBILITY::PRIVATE->value => FILE_VISIBILITY::getAll()[FILE_VISIBILITY::PRIVATE->value],
                ])
                ->default(FILE_VISIBILITY::PUBLIC->value);

            $form->radio('status', '文件状态')
                ->options(FILE_STATUS::getAll())
                ->default(FILE_STATUS::NORMAL->value);

            $form->text('re_type', '关联类型')
                ->help('例如：user_avatar, article_image, product_attachment');

            $form->number('re_id', '关联ID')
                ->min(0)
                ->default(0);

            $form->number('user_id', '用户ID')
                ->min(0)
                ->default(0);

            $form->display('created_at');
            $form->display('updated_at');

            $form->saving(function (Form $form) {
                if ($form->isCreating()) {
                    $image = $form->image;
                    if ($image) {
                        $userId = $form->user_id ?: 0;
                        $private = (bool) $form->private;
                        $reType = $form->re_type ?: '';
                        $reId = $form->re_id ?: 0;

                        $fileService = new \Modules\AFile\Services\FileService;
                        $imageModel = $fileService->uploadImage($image, $userId, $private, $reType, $reId);

                        $form->path = $imageModel->path;
                        $form->o_name = $imageModel->o_name;
                        $form->fsize = $imageModel->fsize;
                        $form->type1 = $imageModel->type1;
                        $form->storage_disk = $imageModel->storage_disk;
                    }
                }
            });
        });
    }
}
