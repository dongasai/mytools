<?php

namespace Modules\AClean\DcatAdmin\Actions\Batch;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

/**
 * 批量禁用Action
 *
 * 用于批量禁用清理配置
 */
class BatchDisableAction extends BatchAction
{
    /**
     * 按钮标题
     */
    protected $title = '批量禁用';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        // 获取选中的ID
        $ids = $this->getKey();

        if (empty($ids)) {
            return $this->response()->error('请选择要禁用的配置');
        }

        // 批量更新
        $count = $this->getModel()::whereIn('id', $ids)->update(['is_enabled' => 0]);

        return $this->response()
            ->success("成功禁用 {$count} 个配置")
            ->refresh();

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认批量禁用？',
            '此操作将禁用选中的所有清理配置。',
        ];
    }
}
