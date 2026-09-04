<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Demo5\Hooks\Parameters\PostContentParameter;
use Modules\Demo5\Hooks\Results\PostContentResult;

/**
 * 文章内容过滤钩子定义
 */
class PostContentFilterHook extends HookDefinition
{
    public readonly string $parameter_class;

    public readonly string $return_class;

    public string $description = '文章内容过滤器';

    public function __construct()
    {
        $this->parameter_class = PostContentParameter::class;
        $this->return_class = PostContentResult::class;
        parent::__construct();
    }
}
