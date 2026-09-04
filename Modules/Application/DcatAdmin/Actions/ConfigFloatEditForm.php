<?php

namespace Modules\Application\DcatAdmin\Actions;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Modules\Application\Models\SysConfig;
use Modules\DcatAdmin\DcatAdmin\Widgets\Form;

/**
 * 浮点数组 修改表单
 */
class ConfigFloatEditForm extends Form implements LazyRenderable
{
    use ConfigEditForm;
    use LazyWidget;

    public function form()
    {
        $id = $this->payload['id'] ?? null;

        $model = SysConfig::query()->find($id);
        if (! $model) {
            return $this->error('错误的')->refresh();
        }
        $this->display('k', 'Key')->value($model->keyname);
        $this->display('k', '标题')->value($model->title);
        $this->display('k', '描述')->value($model->desc);
        $this->decimal('value', '内容')->default($model->value)->required();
    }
}
