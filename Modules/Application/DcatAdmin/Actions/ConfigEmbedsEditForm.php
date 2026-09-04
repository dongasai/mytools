<?php

namespace Modules\Application\DcatAdmin\Actions;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Modules\Application\Models\SysConfig;
use Modules\DcatAdmin\DcatAdmin\Widgets\Form;

/**
 * 固定键值对 修改表单
 */
class ConfigEmbedsEditForm extends Form implements LazyRenderable
{
    use ConfigEditForm,LazyWidget;

    public function form()
    {
        $id = $this->payload['id'] ?? null;

        $model = SysConfig::query()->find($id);
        if (! $model) {
            return $this->error('错误的')->refresh();
        }
        //        dump($model);
        $kv = json_decode($model->value, true);
        $options = [];
        if ($model->options) {
            $options = json_decode($model->options, true);
        }
        $this->display('k', 'Key')->value($model->keyname);
        $this->display('k', '标题')->value($model->title);
        $this->display('k', '描述')->value($model->desc);
        $this->embeds('value', '内容', function (\Dcat\Admin\Form\EmbeddedForm $form) use ($kv, $options) {

            $nochange = $options['nochange'] ?? [];
            foreach ($kv as $k => $v) {
                if (substr($k, 0, 2) == 'is') {
                    if (in_array($k, $nochange)) {
                        $form->switch($k, $k)->default($v)->readOnly();
                    } else {
                        $form->switch($k, $k)->default($v);
                    }
                } elseif (is_bool($v)) {
                    if (in_array($k, $nochange)) {
                        $form->switch($k, $k)->default($v)->readOnly();
                    } else {
                        $form->switch($k, $k)->default($v);
                    }
                } else {
                    if (in_array($k, $nochange)) {
                        $form->text($k, $k)->default($v)->readOnly();
                    } else {
                        $form->text($k, $k)->default($v);
                    }
                }
            }

        })->default($kv)
            ->required();
    }
}
