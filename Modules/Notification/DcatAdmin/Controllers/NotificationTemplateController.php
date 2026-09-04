<?php

declare(strict_types=1);

namespace Modules\Notification\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Modules\Notification\DcatAdmin\Grids\NotificationTemplateGrid;
use Modules\Notification\DcatAdmin\Forms\NotificationTemplateForm;
use Modules\Notification\Models\NotificationTemplate;

/**
 * 通知模板后台控制器.
 *
 * 用于管理通知模板的后台控制器，提供模板的增删改查功能。
 * 支持多种通知渠道的模板配置，包括短信、邮件、推送和数据库通知。
 *
 * @package Modules\Notification\DcatAdmin\Controllers
 */
class NotificationTemplateController extends AdminController
{
    /**
     * 模型类名.
     *
     * @var string
     */
    protected $model = NotificationTemplate::class;

    /**
     * 构建通知模板列表表格.
     *
     * @return Grid
     */
    public function grid(): Grid
    {
        return NotificationTemplateGrid::make($this->model);
    }

    /**
     * 构建通知模板表单.
     *
     * @return Form
     */
    public function form(): Form
    {
        return NotificationTemplateForm::make($this->model);
    }
}