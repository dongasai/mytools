<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

/**
 * 扫描表格Action
 *
 * 用于在配置管理页面扫描数据库表格
 */
class ScanTablesAction extends AbstractTool
{
    /**
     * 按钮标题
     */
    protected $title = '扫描表格';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        // 调用扫描服务
        $result = ACleanService::scanTables(true); // 强制刷新

        return $this->response()
            ->success('扫描完成！')
            ->detail("成功扫描 {$result['scanned_count']} 个表，创建/更新了 {$result['created_count']} 个配置")
            ->refresh();

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认扫描表格？',
            '此操作将扫描所有  开头的数据表并创建/更新清理配置。',
        ];
    }
}
