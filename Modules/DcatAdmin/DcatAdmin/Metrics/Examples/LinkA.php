<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Box;

class LinkA extends Box
{
    private $link;

    public function __construct($title = '链接', $link = '')
    {
        $this->title($title);
        $this->link = $link;
        $content = $this->link2content($link);

        parent::__construct($title, $content);

        // 设置卡片高度样式
        $this->setHtmlAttribute('style', 'min-height: 150px;');
    }

    public function link2content($link)
    {

        return "<a href='$link' >{$this->title}</a>";
    }

    public function newopen()
    {
        $link = $this->link;
        $this->content = "<a href='$link' target=\"_blank\" >{$this->title}</a>";

        return $this;
    }

    /**
     * 设置链接
     *
     * @return $this
     */
    public function setLink($link)
    {
        $content = $this->link2content($link);
        $this->content($content);

        return $this;
    }
}
