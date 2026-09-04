<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Views;

use Modules\DcatAdmin\DcatAdmin\Form\AbstractTool;
use Modules\DcatAdmin\Models\AdminGridView;

class GridHeaderButtonPrivate extends AbstractTool
{
    /**
     * @var AdminGridView
     */
    protected $adminGridView;

    public function __construct(AdminGridView $adminGridView)
    {
        $this->adminGridView = $adminGridView;
    }

    public function render(): string
    {

        $title = $this->adminGridView->title;
        $url = $this->getUrl();
        $now = $this->getNowIcon();

        return "<a href='{$url}' class='btn btn-success'>
    <span class=' d-sm-inline'>
    &nbsp;&nbsp;{$title}
    </span>
    $now
</a>";

    }

    public function getNowIcon(): string
    {
        $now_viewid = request('_viewid');
        $now = '';
        if ($now_viewid == $this->adminGridView->id) {
            $now = "<i class='fa  fa-check'></i>";
        }

        return $now;
    }

    protected function getUrl()
    {
        $p1 = $this->adminGridView->p1;
        $p1['_viewid'] = $this->adminGridView->id;

        $url = \route($this->adminGridView->router_name,
            $p1
        );

        return $url;

    }
}
