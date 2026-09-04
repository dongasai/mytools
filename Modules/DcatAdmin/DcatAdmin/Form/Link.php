<?php

namespace Modules\DcatAdmin\DcatAdmin\Form;

class Link extends \Dcat\Admin\Grid\Tools\AbstractTool
{
    protected $title = '链接';

    protected $link = '';

    /**
     * 获取链接地址
     * 子类应该重写此方法以提供具体的链接地址
     */
    public function linkHref(): string
    {
        return $this->link ?: '#';
    }

    public function html()
    {
        return <<<EOT
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="{$this->linkHref()}" class="btn btn-sm btn-primary "><i class="feather icon-list"></i><span class="d-none d-sm-inline">&nbsp;{$this->title}</span></a>
</div>
EOT;
    }
}
