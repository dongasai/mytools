<?php

namespace Modules\Application\DcatAdmin\Actions;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Modules\DcatAdmin\DcatAdmin\Widgets\Form;
use Modules\Application\Models\ApplicationConfig;

/**
 * Json配置 修改表单
 */
class ConfigJsonEditForm extends Form implements LazyRenderable
{
    use ConfigEditForm,LazyWidget;

    public function form()
    {
        $id = $this->payload['id'] ?? null;

        $model = ApplicationConfig::query()->find($id);
        if (! $model) {
            return $this->error('错误的')->refresh();
        }
        $this->display('k', 'Key')->value($model->keyname);
        $this->display('k', '标题')->value($model->title);
        $this->display('k', '描述')->value($model->desc);
        //        dump($model);
        $this->keyValue('value', '内容')
            ->default(json_decode($model->value, true))
            ->required();
    }
}
