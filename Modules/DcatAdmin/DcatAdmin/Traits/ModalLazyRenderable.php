<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

use Dcat\Admin\Widgets\Modal;
use Modules\DcatAdmin\DcatAdmin\Widgets\Button;

trait ModalLazyRenderable
{
    public static function makeModal()
    {

        $obj = static::make();
        $button = new Button($obj->title);
        $modal = Modal::make()
            ->lg()
            ->title($obj->title)
            ->body($obj)
            ->button($button);

        return $modal;
    }
}
