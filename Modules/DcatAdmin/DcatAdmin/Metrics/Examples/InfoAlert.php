<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Modules\DcatAdmin\DcatAdmin\Metrics\Base\AlertCard;

/**
 * 提示信息卡片示例
 *
 * 展示提示信息卡片
 *
 * 使用场景：
 * - 操作说明
 * - 重要提示
 * - 公告通知
 *
 * 使用方式：
 * 在控制器中：$row->column(4, new InfoAlert);
 * 或自定义：(new InfoAlert)->alertTitle('自定义标题')->alertContent('自定义内容')->alertType('warning');
 */
class InfoAlert extends AlertCard
{
    /**
     * 初始化提示卡片
     */
    protected function initAlert()
    {
        $this->alertTitle = '提示信息';
        $this->alertContent = '这是一个使用 Box 组件包裹的示例内容区域';
        $this->alertType = 'info';
        $this->height = 150;
    }
}