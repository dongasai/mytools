<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

/**
 * 清理旧日志Action
 *
 * 用于清理过期的清理日志
 */
class CleanOldLogsAction extends AbstractTool
{
    /**
     * 按钮标题
     */
    protected $title = '清理旧日志';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $retentionDays = $request->input('retention_days', 90);
        $dryRun = $request->input('dry_run', false);

        // 调用服务清理旧日志
        $result = ACleanService::cleanOldLogs($retentionDays, $dryRun);

        if (! $result['success']) {
            return $this->response()
                ->error('清理失败：'.$result['message']);
        }

        $data = $result['data'];

        if ($dryRun) {
            return $this->response()
                ->success('预览完成')
                ->detail("
                    保留天数：{$retentionDays} 天<br>
                    发现过期日志：".number_format($data['expired_count'])." 条<br>
                    最早日志时间：{$data['oldest_log_date']}<br>
                    <br>
                    <small>这是预览模式，没有实际删除任何日志。</small>
                ");
        } else {
            return $this->response()
                ->success('清理完成！')
                ->detail("
                    保留天数：{$retentionDays} 天<br>
                    删除日志：".number_format($data['deleted_count'])." 条<br>
                    清理时间：{$data['execution_time']}秒<br>
                    剩余日志：".number_format($data['remaining_count']).' 条
                ')
                ->refresh();
        }

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '清理旧日志',
            '将删除超过指定天数的清理日志记录。',
            [
                'retention_days' => [
                    'type' => 'number',
                    'label' => '保留天数',
                    'value' => 90,
                    'min' => 1,
                    'max' => 365,
                    'required' => true,
                    'help' => '超过此天数的日志将被删除',
                ],
                'dry_run' => [
                    'type' => 'checkbox',
                    'label' => '预览模式',
                    'checked' => true,
                    'help' => '勾选则只预览，不实际删除日志',
                ],
            ],
        ];
    }
}
