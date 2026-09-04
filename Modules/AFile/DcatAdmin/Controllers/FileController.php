<?php

namespace Modules\AFile\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\AFile\DcatAdmin\Helpers\FileHelper;
use Modules\AFile\DcatAdmin\Repositories\FileRepository;
use Modules\AFile\Enums\FILE_STATUS;
use Modules\AFile\Enums\FILE_VISIBILITY;
use Modules\AFile\Services\FileService;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 文件管理控制器
 */
class FileController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '文件管理';

    /**
     * 文件服务
     *
     * @var FileService
     */
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = new FileService;
    }

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        $service = $this->service;

        return Grid::make(new FileRepository, function (Grid $grid) use ($service) {
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
            $grid->column('path', '文件预览')->display(function ($path) use ($service) {
                $url = $service->getFileUrl($this->id);
                $extension = pathinfo($path, PATHINFO_EXTENSION);

                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                    return "<img src='{$url}' style='max-width:50px;max-height:50px;' />";
                } elseif (in_array(strtolower($extension), ['mp4', 'webm', 'ogg'])) {
                    return "<i class='fa fa-file-video-o'></i>";
                } elseif (in_array(strtolower($extension), ['mp3', 'wav'])) {
                    return "<i class='fa fa-file-audio-o'></i>";
                } elseif (in_array(strtolower($extension), ['pdf'])) {
                    return "<i class='fa fa-file-pdf-o'></i>";
                } elseif (in_array(strtolower($extension), ['doc', 'docx'])) {
                    return "<i class='fa fa-file-word-o'></i>";
                } elseif (in_array(strtolower($extension), ['xls', 'xlsx'])) {
                    return "<i class='fa fa-file-excel-o'></i>";
                } elseif (in_array(strtolower($extension), ['ppt', 'pptx'])) {
                    return "<i class='fa fa-file-powerpoint-o'></i>";
                } elseif (in_array(strtolower($extension), ['zip', 'rar', '7z'])) {
                    return "<i class='fa fa-file-archive-o'></i>";
                } elseif (in_array(strtolower($extension), ['txt'])) {
                    return "<i class='fa fa-file-text-o'></i>";
                } else {
                    return "<i class='fa fa-file-o'></i>";
                }
            });
            $grid->column('created_at', '创建时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
            $grid->column('updated_at', '更新时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            $grid->column('download', '操作')->display(function () use ($service) {
                $url = $service->getFileUrl($this->id);

                return "<a href='{$url}' target='_blank' class='btn btn-sm btn-primary'>下载</a>";
            });

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
        $service = $this->service;

        return Show::make($id, new FileRepository, function (Show $show) use ($service) {
            $show->field('id', 'ID');
            $show->field('storageConfig.id', '存储ID')->as(function ($value) {
                return $value ?? '-';
            });
            $show->field('storageConfig.driver', '存储驱动')->as(function ($value) {
                return $value ?? '-';
            });
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
            $show->field('path', '文件预览')->as(function ($path) use ($service) {
                $url = $service->getFileUrl($this->id);
                $extension = pathinfo($path, PATHINFO_EXTENSION);

                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                    return "<img src='{$url}' style='max-width:200px;max-height:200px;' />";
                } elseif (in_array(strtolower($extension), ['mp4', 'webm', 'ogg'])) {
                    return "<video src='{$url}' controls style='max-width:200px;max-height:200px;'></video>";
                } elseif (in_array(strtolower($extension), ['mp3', 'wav'])) {
                    return "<audio src='{$url}' controls></audio>";
                } else {
                    return "<a href='{$url}' target='_blank'>下载文件</a>";
                }
            });
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            $show->html(function () use ($service) {
                $url = $service->getFileUrl($this->id);

                return "<a href='{$url}' target='_blank' class='btn btn-primary'>下载文件</a>";
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
        return Form::make(new FileRepository, function (Form $form) {
            $form->display('id');

            $form->file('file', '文件上传')
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
                    $file = $form->file;
                    if ($file) {
                        $userId = $form->user_id ?: 0;
                        $reType = $form->re_type ?: '';
                        $reId = $form->re_id ?: 0;

                        $fileModel = $this->service->uploadFile($file, $userId, $reType, $reId);

                        $form->path = $fileModel->path;
                        $form->o_name = $fileModel->o_name;
                        $form->fsize = $fileModel->fsize;
                        $form->type1 = $fileModel->type1;
                    }
                }
            });
        });
    }
}
