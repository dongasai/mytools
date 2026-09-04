<?php

namespace Modules\DcatAdmin\DcatAdmin\Show;

use Dcat\Admin\Form\Field;
use Dcat\Admin\Show\AbstractField;

class Map extends AbstractField
{
    public function render($latitude = null, $longitude = null)
    {
        Field\Map::requireAssets();
        $map = new Field\Map(null, ['longitude']);
        $map->value([
            'lat' => $latitude,
            'lng' => $longitude,
        ])->disable();

        return $map->render();
    }
}
