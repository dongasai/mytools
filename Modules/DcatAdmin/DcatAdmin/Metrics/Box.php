<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Widget;

/**
 * 盒子组件
 */
class Box extends Widget
{
    protected $view = 'module_dcatadmin::widgets.box';

    /**
     * 盒子内容
     */
    public $content = '';

    /**
     * 内边距样式
     */
    public $padding = 'padding: 10px;';

    /**
     * 构造函数
     *
     * @param  string  $content  盒子内容
     * @param  string  $padding  内边距样式
     */
    public function __construct(string $content, string $padding = 'padding: 10px;')
    {
        $this->content = $content;
        $this->padding = $padding;
    }

    /**
     * 默认变量
     */
    public function defaultVariables()
    {
        return [
            'content' => $this->content,
            'padding' => $this->padding,
            'attributes' => $this->formatHtmlAttributes(),
            'options' => $this->options,
            'class' => $this->getElementClass(),
            'selector' => $this->getElementSelector(),
        ];
    }
}
