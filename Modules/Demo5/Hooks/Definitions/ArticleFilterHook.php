<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Demo5\Hooks\Parameters\ArticleFilterHookParameter;
use Modules\Demo5\Hooks\Results\ArticleFilterHookResult;

/**
 * ArticleFilterHook 定义
 *
 * 用于在保存文章前过滤内容，清理HTML标签
 */
class ArticleFilterHook extends HookDefinition
{
    /**
     * 参数类名（子类必须定义）
     * 必须继承自HookParameter
     */
    public readonly string $parameter_class;

    /**
     * 返回值类名（子类必须定义）
     * 必须继承自HookResult
     */
    public readonly string $return_class;

    /**
     * Hook描述
     */
    public string $description = '文章内容过滤Hook，用于在保存文章前清理HTML标签';

    public function __construct()
    {
        $this->parameter_class = ArticleFilterHookParameter::class;
        $this->return_class = ArticleFilterHookResult::class;
        parent::__construct();
    }
}