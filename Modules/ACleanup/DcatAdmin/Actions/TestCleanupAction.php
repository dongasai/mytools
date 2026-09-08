<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 测试清理Action
 *
 * 用于测试单个配置的清理效果，不实际删除数据
 */
class TestCleanupAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '测试清理';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $configId = $this->getKey();

        // 调用预览服务
        $result = ACleanService::previewCleanup($configId);

        if (! $result['success']) {
            return $this->response()
                ->error('测试失败：' . $result['message']);
        }

        $data = $result['data'];

        return $this->response()
            ->success('测试完成！')
            ->detail("
                表名：{$data['table_name']}<br>
                清理类型：{$data['cleanup_type_name']}<br>
                当前记录数：" . number_format($data['current_count']) . '<br>
                预计删除：' . number_format($data['estimated_delete']) . '<br>
                预计保留：' . number_format($data['estimated_remain']) . "<br>
                删除比例：{$data['delete_percentage']}%
            ");
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认测试清理？',
            '此操作只会预览清理效果，不会实际删除数据。',
        ];
    }
}
