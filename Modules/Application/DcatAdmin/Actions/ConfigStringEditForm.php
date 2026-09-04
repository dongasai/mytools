<?php

namespace Modules\Application\DcatAdmin\Actions;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Illuminate\Support\Facades\DB;
use Modules\Application\Models\ApplicationConfig;
use Modules\DcatAdmin\DcatAdmin\Widgets\Form;

/**
 * 字符串配置 修改表单
 */
class ConfigStringEditForm extends Form implements LazyRenderable
{
    use ConfigEditForm,LazyWidget;

    public function __construct($data = [], $key = null)
    {
        parent::__construct($data, $key);
        //        dump(func_get_args());
    }

    public function run($input)
    {
        $id = $this->payload['id'] ?? null;
        if (! $id) {
            return $this->error('input-error');
        }
        /**
         * @var ApplicationConfig $model
         */
        $model = ApplicationConfig::query()->find($id);
        //        dump($model);
        if (! $model) {
            return $this->error('noinfo');
        }
        DB::beginTransaction();
        $model->value = $input['value'];
        if (! $model->save()) {
            return $this->error('error');
        }
        DB::commit();

        return $this->success('ok')->refresh();
    }

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

        $this->text('value', '内容')->default($model->value)->required();
    }
}
