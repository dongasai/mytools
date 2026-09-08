<?php

namespace Modules\AClean\DcatAdmin\Tools;

use Dcat\Admin\Show\AbstractTool;

/**
 * 返回计划列表工具按钮
 */
class BackToPlanListTool extends AbstractTool
{
    /**
     * 按钮标题
     */
    public function title()
    {
        return '<i class="fa fa-calendar"></i> 计划列表';
    }

    /**
     * 默认样式
     */
    protected $style = 'btn btn-outline-primary';

    /**
     * 渲染按钮（直接跳转，不触发 AJAX）
     */
    public function render()
    {
        $url = admin_url('backup-clean-admin/backup-plans');

        return <<<HTML
<a href="{$url}" class="{$this->style}">
    {$this->title()}
</a>
HTML;
    }
}