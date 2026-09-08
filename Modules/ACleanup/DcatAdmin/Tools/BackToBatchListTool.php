<?php

namespace Modules\AClean\DcatAdmin\Tools;

use Dcat\Admin\Show\AbstractTool;

/**
 * 返回批次列表工具按钮
 */
class BackToBatchListTool extends AbstractTool
{
    /**
     * 按钮标题
     */
    public function title()
    {
        return '<i class="fa fa-list"></i> 批次列表';
    }

    /**
     * 默认样式
     */
    protected $style = 'btn btn-primary';

    /**
     * 渲染按钮（直接跳转，不触发 AJAX）
     */
    public function render()
    {
        $url = admin_url('backup-clean-admin/batches');

        return <<<HTML
<a href="{$url}" class="{$this->style}">
    {$this->title()}
</a>
HTML;
    }
}