<?php

namespace Modules\Application\DcatAdmin\Actions;

use Dcat\Admin\Widgets\Modal;

trait ConfigEditAction
{
    protected $ConfigEditForm;

    public function render2()
    {
        $this->ConfigEditForm = $this->configEditForm();
        // 实例化表单类并传递自定义参数
        $form = $this->ConfigEditForm::make();
        $form->payload([
            'id' => $this->getKey(),
            'value' => $this->getRow()->value1,
        ]);

        return Modal::make()
            ->lg()
            ->title($this->title())
            ->body($form)
            ->button($this->title());
    }

    public function configEditForm(): string
    {
        return $this->ConfigEditForm;

    }
}
