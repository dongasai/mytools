<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Displayers;

use Dcat\Admin\Grid\Displayers\Actions;

class LineActions extends Actions
{
    /**
     * @return string
     */
    protected function getViewLabel()
    {
        $label = trans('admin.show');

        return "<i title='{$label}' class=\"feather icon-eye grid-action-icon\">{$label}</i> &nbsp;";
    }

    /**
     * @return string
     */
    protected function getEditLabel()
    {
        $label = trans('admin.edit');

        return "<i title='{$label}' class=\"feather icon-edit-1 grid-action-icon\">{$label}</i> &nbsp;";
    }

    /**
     * @return string
     */
    protected function getQuickEditLabel()
    {
        $label = trans('admin.quick_edit');

        return "<i title='{$label}' class=\"feather icon-edit grid-action-icon\">{$label}</i> &nbsp;";
    }

    /**
     * @return string
     */
    protected function getDeleteLabel()
    {
        $label = trans('admin.delete');

        return "<i class=\"feather icon-trash grid-action-icon\" title='{$label}'>{$label}</i> &nbsp;";
    }
}
