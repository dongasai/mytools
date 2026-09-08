<?php

namespace Modules\AClean\DcatAdmin\Actions\Batch;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

/**
 * 批量启用Action
 *
 * 用于批量启用清理配置
 */
class BatchEnableAction extends BatchAction
{
    /**
     * 按钮标题
     */
    protected $title = '批量启用';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        // 获取选中的ID
        $ids = $this->getKey();

        if (empty($ids)) {
            return $this->response()->error('请选择要启用的配置');
        }

        // 批量更新
        $count = $this->getModel()::whereIn('id', $ids)->update(['is_enabled' => 1]);

        return $this->response()
            ->success("成功启用 {$count} 个配置")
            ->refresh();

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认批量启用？',
            '此操作将启用选中的所有清理配置。',
        ];
    }
}
