<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupPlanContent;
use Modules\DcatAdmin\DcatAdmin\RowAction;

/**
 * 删除计划内容Action
 */
class DeletePlanContentAction extends RowAction
{
    /**
     * 标题
     */
    protected $title = '删除内容';

    /**
     * 处理请求
     */
    public function handle()
    {
        $id = $this->getKey();

        $content = CleanupPlanContent::findOrFail($id);

        // 检查是否可以删除
        $planContentsCount = CleanupPlanContent::where('plan_id', $content->plan_id)->count();
        if ($planContentsCount <= 1) {
            return $this->response()
                ->error('计划至少需要保留一个清理内容');
        }

        // 删除计划内容
        $content->delete();

        return $this->response()
            ->success('删除成功')
            ->refresh();
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认删除此计划内容？',
            '⚠️ 删除后无法恢复，请谨慎操作！',
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        return true; // 根据实际需求设置权限
    }
}
