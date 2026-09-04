<?php

namespace Modules\AFile\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\AFile\DcatAdmin\Actions\CopyStorageConfigAction;
use Modules\AFile\DcatAdmin\Actions\FixStorageConfigAction;
use Modules\AFile\DcatAdmin\Actions\TestConnectionAction;
use Modules\AFile\DcatAdmin\Repositories\StorageConfigRepository;
use Modules\AFile\DcatAdmin\Tools\SyncFilesystemsTool;
use Modules\AFile\DcatAdmin\Tools\TestConnectionTool;
use Modules\AFile\Enums\STORAGE_DRIVER;
use Modules\AFile\Enums\STORAGE_STATUS;
use Modules\AFile\Services\StorageConfigService;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 存储配置管理控制器
 */
class StorageConfigController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '存储配置管理';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new StorageConfigRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '存储名称');
            $grid->column('driver', '存储驱动')
                ->display(function ($value) {
                    $driver = STORAGE_DRIVER::tryFrom($value);
                    return $driver ? $driver->description() : $value;
                })
                ->label([
                    STORAGE_DRIVER::LOCAL->value => 'default',
                    STORAGE_DRIVER::PUBLIC->value => 'success',
                    STORAGE_DRIVER::S3->value => 'info',
                    STORAGE_DRIVER::OSS->value => 'primary',
                    STORAGE_DRIVER::FTP->value => 'warning',
                    STORAGE_DRIVER::SFTP->value => 'warning',
                ]);
            $grid->column('description', '描述')->limit(30);
            $grid->column('is_default', '默认存储')->display(function ($v) {
                return $v ? '是' : '否';
            })->label([1 => 'success', 0 => 'default'])->switch();
            $grid->column('is_temp', '临时存储')->display(function ($v) {
                return $v ? '是' : '否';
            })->switch();
            $grid->column('status', '状态')
                ->using(STORAGE_STATUS::getAll())
                ->label([
                    STORAGE_STATUS::DISABLED->value => 'danger',
                    STORAGE_STATUS::ENABLED->value => 'success',
                ])->switch();
            $grid->column('env', '环境');
            $grid->column('created_at', '创建时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
            $grid->column('updated_at', '更新时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            // 添加测试连接 Action 和修复配置 Action
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new CopyStorageConfigAction());
                $actions->append(new FixStorageConfigAction());
                $actions->append(new TestConnectionAction());
            });

            // 添加顶部工具栏按钮 - 同步文件系统配置
            $grid->tools(new SyncFilesystemsTool());

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->like('name', '存储名称');
                $filter->equal('driver', '存储驱动')->select(STORAGE_DRIVER::getAll());
                $filter->equal('status', '存储状态')->select(STORAGE_STATUS::getAll());
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
        return Show::make($id, new StorageConfigRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '存储名称');
            $show->field('driver', '存储驱动')->as(function ($value) {
                $driver = STORAGE_DRIVER::tryFrom($value);
                return $driver ? $driver->description() : $value;
            });
            $show->field('description', '描述');
            $show->field('config', '配置')->as(function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }

                return $value;
            });
            $show->field('is_default', '默认存储')->as(function ($v) {
                return $v ? '是' : '否';
            });
            $show->field('is_temp', '临时存储')->as(function ($v) {
                return $v ? '是' : '否';
            });
            $show->field('status', '状态')->as(function ($value) {
                return STORAGE_STATUS::getAll()[$value] ?? '未知';
            });
            $show->field('env', '环境');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            // 添加测试连接 Tool（详情页专用）
            $show->tools(function (Show\Tools $tools) {
                $tools->append(new TestConnectionTool());
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
        return Form::make(new StorageConfigRepository, function (Form $form) {
            $form->display('id');

            $form->text('name', '存储名称')
                ->required()
                ->rules('unique:file_storage_configs,name,' . $form->getKey());

            $form->select('driver', '存储驱动')
                ->options(STORAGE_DRIVER::getAll())
                ->default(STORAGE_DRIVER::LOCAL->value)
                ->required()
                ->help('选择存储驱动类型，不同驱动需要不同的配置参数');

            // 修复config字段双重转义问题
            $form->textarea('config', '配置(JSON)')
                ->required()
                ->customFormat(function ($value) {
                    // 编辑时：解码JSON字符串，格式化为易读的JSON
                    if (is_string($value) && !empty($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                        }
                    }
                    return $value;
                })
                ->saving(function ($value) {
                    // 保存时：确保JSON格式正确，避免双重编码
                    if (is_string($value) && !empty($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // 返回紧凑的JSON字符串（不含换行和缩进）
                            return json_encode($decoded, JSON_UNESCAPED_SLASHES);
                        }
                    }
                    return $value;
                })
                ->help('JSON格式配置，如 {"key": "...", "secret": "..."}');

            $form->text('description', '描述');

            // 使用switch字段处理布尔值
            $form->switch('is_default', '默认存储')
                ->default(0);

            $form->switch('is_temp', '临时存储')
                ->default(0);

            $form->switch('status', '状态')
                ->default(1);

            $form->select('env', '环境')
                ->options([
                    'development' => 'development',
                    'testing' => 'testing',
                    'production' => 'production',
                ])
                ->default(app()->environment())
                ->required();

            $form->display('created_at');
            $form->display('updated_at');

            $form->saved(function (Form $form) {
                // 清除缓存
                (new StorageConfigService)->clearCache();
            });
        });
    }
}
