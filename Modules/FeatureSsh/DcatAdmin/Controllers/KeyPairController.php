<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSsh\Enums\KeyType;
use Modules\FeatureSsh\Models\KeyPair;
use Modules\FeatureSsh\Services\KeyPairService;

/**
 * SSH密钥对管理控制器
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class KeyPairController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = 'SSH密钥对管理';

    /**
     * 页面描述
     */
    protected $description = '管理SSH密钥对，支持生成、导入、查看公钥等功能';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(KeyPair::class, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('60px');
            $grid->column('name', '密钥名称')->width('150px');
            $grid->column('type', '类型')
                ->width('100px')
                ->using(KeyType::options());
            $grid->column('fingerprint', '指纹')
                ->width('300px')
                ->display(function ($value) {
                    return $value ?: '-';
                });
            $grid->column('comment', '注释')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '-';
                });
            $grid->column('created_at', '创建时间')
                ->width('150px');

            $grid->enableBatchActions();
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand(false);

                $filter->like('name', '密钥名称');
                $filter->equal('type', '类型')
                    ->select(KeyType::options());
                $filter->like('fingerprint', '指纹');
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->prepend(
                    '<a href="' . admin_route('featuressh.key-pairs.public-key', ['id' => $actions->getKey()]) . '" class="btn btn-sm btn-info" title="查看公钥">
                        <i class="feather icon-key"></i>
                    </a>&nbsp;'
                );
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append('<a href="' . admin_route('featuressh.key-pairs.import') . '" class="btn btn-primary">
                    <i class="feather icon-download"></i> 导入密钥
                </a>');
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
        return Show::make($id, KeyPair::class, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '密钥名称');
            $show->field('type', '类型')
                ->as(function ($type) {
                    return $type->label();
                });
            $show->field('fingerprint', '指纹');
            $show->field('comment', '注释');
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
        return Form::make(KeyPair::class, function (Form $form) {
            if ($form->isCreating()) {
                $this->createForm($form);
            } else {
                $this->editForm($form);
            }
        });
    }

    /**
     * 创建表单
     *
     * @param Form $form
     */
    private function createForm(Form $form): void
    {
        $form->text('name', '密钥名称')
            ->required()
            ->rules('required|string|max:100')
            ->help('为密钥设置一个有意义的名称');

        $form->select('type', '密钥类型')
            ->required()
            ->options(KeyType::options())
            ->default('ed25519')
            ->help('ED25519为推荐类型，更安全高效');

        $form->password('passphrase', '私钥密码（可选）')
            ->help('为私钥设置密码保护（可选）');

        $form->text('comment', '注释（可选）')
            ->help('密钥注释信息，会包含在公钥中');

        $form->textarea('description', '描述')
            ->rules('max:500')
            ->help('密钥描述信息（可选）');

        $form->saving(function (Form $form) {
            $name = $form->name;
            $type = $form->type ?? 'ed25519';
            $passphrase = $form->passphrase ?? null;
            $comment = $form->comment ?? null;

            $keyPair = KeyPairService::generate($name, $type, $passphrase);

            if ($comment) {
                $keyPair->comment = $comment;
                $keyPair->save();
            }

            if ($form->description) {
                $keyPair->description = $form->description;
                $keyPair->save();
            }

            return $form->response()->success('密钥对生成成功')->redirect(admin_route('featuressh.key-pairs.index'));
        });
    }

    /**
     * 编辑表单
     *
     * @param Form $form
     */
    private function editForm(Form $form): void
    {
        $form->display('id', 'ID');

        $form->text('name', '密钥名称')
            ->required()
            ->rules('required|string|max:100');

        $form->display('type', '密钥类型')
            ->customFormat(function ($type) {
                return $type->label();
            });

        $form->display('fingerprint', '指纹');

        $form->text('comment', '注释')
            ->help('密钥注释信息');

        $form->textarea('description', '描述')
            ->rules('max:500');

        $form->display('created_at', '创建时间');
        $form->display('updated_at', '更新时间');
    }

    /**
     * 导入密钥表单
     *
     * @param Content $content
     * @return Content
     */
    public function import(Content $content): Content
    {
        $form = new Form();

        $form->action(admin_route('featuressh.key-pairs.import-save'));
        $form->method('POST');

        $form->text('name', '密钥名称')
            ->required()
            ->rules('required|string|max:100')
            ->help('为导入的密钥设置一个名称');

        $form->select('type', '密钥类型')
            ->required()
            ->options(KeyType::options())
            ->default('ed25519')
            ->help('选择密钥类型');

        $form->textarea('private_key', '私钥内容')
            ->required()
            ->rules('required|string')
            ->attribute('rows', 8)
            ->help('粘贴私钥内容（支持PEM格式）');

        $form->textarea('public_key', '公钥内容（可选）')
            ->attribute('rows', 3)
            ->help('如果不提供，将从私钥自动推导公钥');

        $form->password('passphrase', '私钥密码（可选）')
            ->help('如果私钥有密码保护，请输入密码');

        $form->textarea('description', '描述')
            ->rules('max:500')
            ->help('密钥描述信息（可选）');

        $body = '<div class="card">
            <div class="card-header"><h4>导入SSH密钥</h4></div>
            <div class="card-body">' . $form->render() . '</div>
        </div>';

        return $content
            ->title('导入SSH密钥')
            ->description('导入现有的SSH私钥')
            ->body($body);
    }

    /**
     * 保存导入的密钥
     *
     * @return mixed
     */
    public function importSave()
    {
        $request = request();

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:rsa,ed25519,ecdsa',
            'private_key' => 'required|string',
            'public_key' => 'nullable|string',
            'passphrase' => 'nullable|string',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $keyPair = KeyPairService::import(
                $data['name'],
                $data['private_key'],
                $data['public_key'] ?? null,
                $data['passphrase'] ?? null
            );

            if (!empty($data['description'])) {
                $keyPair->description = $data['description'];
                $keyPair->save();
            }

            return redirect(admin_route('featuressh.key-pairs.index'))
                ->with('success', '密钥导入成功');
        } catch (\Exception $e) {
            return back()->withErrors(['import_error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * 查看公钥
     *
     * @param Content $content
     * @param int $id
     * @return Content
     */
    public function publicKey(Content $content, int $id): Content
    {
        $keyPair = KeyPair::find($id);

        if (!$keyPair) {
            return $content
                ->title('查看公钥')
                ->body('<div class="alert alert-danger">密钥不存在</div>');
        }

        $publicKeyContent = $keyPair->getPublicKeyForAuthorizedKeys();

        $body = '<div class="card">
            <div class="card-header">
                <h4>' . $keyPair->name . ' - 公钥</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>密钥类型：</strong>' . $keyPair->type->label() . '<br>
                    <strong>指纹：</strong>' . $keyPair->fingerprint . '<br>
                    <strong>创建时间：</strong>' . $keyPair->created_at . '
                </div>
                <div class="form-group">
                    <label>公钥内容（用于添加到 authorized_keys）</label>
                    <textarea class="form-control" rows="8" readonly>' . $publicKeyContent . '</textarea>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success" onclick="copyToClipboard(this)">
                        <i class="feather icon-copy"></i> 复制公钥
                    </button>
                    <a href="' . admin_route('featuressh.key-pairs.index') . '" class="btn btn-primary">返回列表</a>
                </div>
            </div>
        </div>
        <script>
        function copyToClipboard(btn) {
            const textarea = btn.closest(".card-body").querySelector("textarea");
            textarea.select();
            document.execCommand("copy");
            btn.innerHTML = "<i class=\'feather icon-check\'></i> 已复制";
            setTimeout(function() {
                btn.innerHTML = "<i class=\'feather icon-copy\'></i> 复制公钥";
            }, 2000);
        }
        </script>';

        return $content
            ->title('查看公钥 - ' . $keyPair->name)
            ->body($body);
    }
}
