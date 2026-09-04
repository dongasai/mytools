<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Views;

class GridHeaderButtonPublic extends GridHeaderButtonPrivate
{
    public function render(): string
    {

        $url = $this->getUrl();

        $title = $this->adminGridView->title;
        // fa-bookmark 实心
        // fa-bookmark-o 空心
        $now = $this->getNowIcon();

        return "<a href='{$url}' class='btn btn-success'>
    <span class='d-none d-sm-inline'>
    &nbsp;&nbsp;{$title}
    </span> $now
</a>";

    }
}
