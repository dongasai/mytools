<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;
use Dcat\Admin\Support\Helper;

class LabelCall extends AbstractDisplayer
{
    protected $baseClass = 'label';

    protected $call;

    public function __construct($value, Grid $grid, Column $column, $row)
    {
        parent::__construct($value, $grid, $column, $row);

        return $this;
    }

    public function call(callable $call)
    {
        $this->call = $call;

        return $this;
    }

    public function display($style = 'primary', $max = null)
    {
        if (! $value = $this->value($max)) {
            return;
        }

        $original = $this->column->getOriginal();
        $defaultStyle = is_array($style) ? ($style['default'] ?? 'default') : 'default';

        $background = $this->formatStyle(
            is_array($style) ?
                (is_scalar($original) ? ($style[$original] ?? $defaultStyle) : current($style))
                : $style
        );

        return collect($value)->map(function ($name) use ($background) {
            return "<span class='{$this->baseClass}' {$background}>$name</span>";
        })->implode(' ');
    }

    protected function formatStyle($style)
    {
        $background = 'style="background:#d2d6de;color: #555"';

        if ($style !== 'default') {
            $style = Admin::color()->get($style, $style);

            $background = "style='background:{$style}'";
        }

        return $background;
    }

    protected function value($max)
    {
        $values = Helper::array($this->value);

        if ($max && count($values) > $max) {
            $values = array_slice($values, 0, $max);
            $values[] = '...';
        }

        return $values;
    }
}
