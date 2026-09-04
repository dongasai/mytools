<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid;

interface SelectTable
{
    /**
     * 显示模型
     */
    public function getModel(): string;

    /**
     * 选择模型的ID（关联）
     */
    public function getModelSelectId(): string;

    /**
     * 展示的字段名字
     */
    public function getModelViewName(): string;
}
