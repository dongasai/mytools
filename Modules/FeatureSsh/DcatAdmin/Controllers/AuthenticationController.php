<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSsh\Enums\AuthType;
use Modules\FeatureSsh\Models\Authentication;
use Modules\FeatureSsh\Models\KeyPair;
use Modules\FeatureSsh\Models\Server;
use Modules\FeatureSsh\Services\SshConnectionService;

/**
 * SSH认证管理控制器
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class AuthenticationController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = 'SSH认证管理';

    /**
     * 页面描述
     */
    protected $description = '管理SSH服务器的认证方式，支持密钥、密码、证书、Agent等多种认证方式';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(Authentication::with('server'), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('60px');
            $grid->column('name', '认证名称')->width('150px');
            $grid->column('server.name', '关联服务器')->width('150px');
            $grid->column('auth_type', '认证类型')
                ->width('120px')
                ->using(AuthType::options());
            $grid->column('is_default', '是否默认')
                ->width('100px')
                ->bool();
            $grid->column('status', '状态')
                ->width('100px')
                ->using(['active' => '启用', 'inactive' => '禁用'])
                ->dot(['active' => 'success', 'inactive' => 'danger'], 'active');
            $grid->column('description', '描述')
                ->width('200px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            $grid->enableBatchActions();
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand(false);

                $filter->like('name', '认证名称');
                $filter->equal('server_id', '关联服务器')
                    ->select(function () {
                        return Server::pluck('name', 'id')->toArray();
                    });
                $filter->equal('auth_type', '认证类型')
                    ->select(AuthType::options());
                $filter->equal('is_default', '是否默认')
                    ->select([1 => '是', 0 => '否']);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->prepend(
                    '<a href="' . admin_route('featuressh.authentications.test', ['id' => $actions->getKey()]) . '" class="btn btn-sm btn-primary" title="测试认证">
                        <i class="feather icon-wifi"></i>
                    </a>&nbsp;'
                );
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
        return Show::make($id, Authentication::with('server'), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '认证名称');
            $show->field('server.name', '关联服务器');
            $show->field('auth_type', '认证类型')
                ->as(function ($type) {
                    return $type->label();
                });
            $show->field('is_default', '是否默认')
                ->as(function ($isDefault) {
                    return $isDefault ? '是' : '否';
                });
            $show->field('status', '状态')
                ->as(function ($status) {
                    return $status === 'active' ? '启用' : '禁用';
                });
            $show->field('description', '描述');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(Authentication::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('name', '认证名称')
                ->required()
                ->rules('required|string|max:100')
                ->help('为认证配置设置一个有意义的名称');

            $form->select('server_id', '关联服务器')
                ->required()
                ->options(function () {
                    return Server::pluck('name', 'id')->toArray();
                })
                ->help('选择要关联的SSH服务器');

            $form->select('auth_type', '认证类型')
                ->required()
                ->options(AuthType::options())
                ->when('key', function (Form $form) {
                    $form->select('credentials.key_pair_id', '选择密钥对')
                        ->required()
                        ->options(function () {
                            return KeyPair::pluck('name', 'id')->toArray();
                        });
                    $form->text('credentials.username', '用户名')
                        ->required()
                        ->default('root')
                        ->help('SSH登录用户名');
                })
                ->when('password', function (Form $form) {
                    $form->text('credentials.username', '用户名')
                        ->required()
                        ->default('root')
                        ->help('SSH登录用户名');
                    $form->password('credentials.password', '密码')
                        ->required()
                        ->help('SSH登录密码（将被加密存储）');
                })
                ->when('certificate', function (Form $form) {
                    $form->textarea('credentials.certificate', '证书内容')
                        ->required()
                        ->help('SSH证书内容');
                    $form->select('credentials.key_pair_id', '选择密钥对')
                        ->options(function () {
                            return KeyPair::pluck('name', 'id')->toArray();
                        })
                        ->help('用于签名证书的私钥（可选）');
                    $form->text('credentials.username', '用户名')
                        ->required()
                        ->default('root')
                        ->help('SSH登录用户名');
                })
                ->when('agent', function (Form $form) {
                    $form->text('credentials.username', '用户名')
                        ->required()
                        ->default('root')
                        ->help('SSH登录用户名');
                });

            $form->switch('is_default', '设为默认')
                ->default(0)
                ->help('设为该服务器的默认认证方式');

            $form->switch('status', '状态')
                ->default(1)
                ->customFormat(function ($v) {
                    return $v === 'active' ? 1 : 0;
                })
                ->saving(function ($v) {
                    return $v ? 'active' : 'inactive';
                });

            $form->textarea('description', '描述')
                ->rules('max:500')
                ->help('认证配置描述（可选）');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $form->saving(function (Form $form) {
                if ($form->isEditing() && $form->status === null) {
                    $form->status = 'active';
                }

                $credentials = $form->input('credentials', []);

                if (isset($credentials['password'])) {
                    $credentials['password'] = encrypt($credentials['password']);
                }

                if (!empty($credentials)) {
                    $form->credentials = encrypt(json_encode($credentials));
                }
            });
        });
    }

    /**
     * 测试认证
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(int $id)
    {
        $auth = Authentication::find($id);

        if (!$auth) {
            return response()->json([
                'status' => false,
                'message' => '认证配置不存在',
            ]);
        }

        $result = SshConnectionService::testConnection($auth->server_id, $id);

        return response()->json([
            'status' => $result['success'],
            'message' => $result['message'],
            'data' => $result,
        ]);
    }
}
