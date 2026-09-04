<?php

namespace Modules\DcatAdmin\DcatAdmin\Support;

use Dcat\Admin\Support\LazyRenderable as BaseLazyRenderable;

/**
 * 懒加载 渲染
 */
abstract class LazyRenderable extends BaseLazyRenderable
{
    protected string $trans_path = '';
}
