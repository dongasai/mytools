<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupPlan;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 查看计划内容Action
 *
 * 跳转到计划内容管理页面，并筛选出对应计划的内容
 */
class ViewPlanContentsAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '查看内容';

    /**
     * 按钮图标
     */
    protected $icon = 'fa-list';

    /**
     * 处理请求
     */
    protected function run(Request $request)
    {
        $planId = $this->getKey();

        // 验证计划是否存在
        $plan = CleanupPlan::find($planId);
        if (! $plan) {
            return $this->response()
                ->error('计划不存在');
        }

        // 构建跳转URL，包含计划ID筛选参数
        $url = admin_url('backup-clean-admin/plan-contents') . '?plan_id=' . $planId;

        // 跳转到计划内容管理页面
        return $this->response()
            ->redirect($url);
    }
}
