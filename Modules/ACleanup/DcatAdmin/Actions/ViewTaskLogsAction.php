<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupTask;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 查看任务日志Action
 *
 * 用于查看清理任务的执行日志
 */
class ViewTaskLogsAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '查看日志';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $taskId = $this->getKey();

        $task = CleanupTask::with('logs')->find($taskId);

        if (! $task) {
            return $this->response()
                ->error('任务不存在');
        }

        $logs = $task->logs()->orderBy('created_at', 'desc')->get();

        if ($logs->isEmpty()) {
            return $this->response()
                ->info('该任务暂无执行日志');
        }

        $html = '<div class="table-responsive"><table class="table table-striped table-sm">';
        $html .= '<thead><tr><th>表名</th><th>清理类型</th><th>删除前</th><th>删除后</th><th>删除数量</th><th>执行时间</th><th>状态</th><th>执行时间</th></tr></thead>';
        $html .= '<tbody>';

        $cleanupTypes = [
            1 => '清空表',
            2 => '删除所有',
            3 => '按时间删除',
            4 => '按用户删除',
            5 => '按条件删除',
        ];

        foreach ($logs as $log) {
            $cleanupType = $cleanupTypes[$log->cleanup_type] ?? '未知';
            $status = empty($log->error_message) ?
                '<span class="badge badge-success">成功</span>' :
                '<span class="badge badge-danger">失败</span>';

            $html .= '<tr>';
            $html .= "<td>{$log->table_name}</td>";
            $html .= "<td>{$cleanupType}</td>";
            $html .= '<td>' . number_format($log->before_count) . '</td>';
            $html .= '<td>' . number_format($log->after_count) . '</td>';
            $html .= '<td>' . number_format($log->deleted_records) . '</td>';
            $html .= '<td>' . number_format($log->execution_time, 3) . 's</td>';
            $html .= "<td>{$status}</td>";
            $html .= "<td>{$log->created_at}</td>";
            $html .= '</tr>';

            // 如果有错误信息，显示在下一行
            if (! empty($log->error_message)) {
                $html .= "<tr class='table-danger'>";
                $html .= "<td colspan='8'><small><strong>错误：</strong>{$log->error_message}</small></td>";
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></div>';

        // 添加统计信息
        $totalDeleted = $logs->sum('deleted_records');
        $totalTime = $logs->sum('execution_time');
        $successCount = $logs->where('error_message', '')->count();
        $failCount = $logs->where('error_message', '!=', '')->count();

        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-md-3"><div class="card card-body text-center">';
        $html .= '<h5 class="text-primary">' . number_format($totalDeleted) . '</h5>';
        $html .= '<small>总删除记录数</small>';
        $html .= '</div></div>';

        $html .= '<div class="col-md-3"><div class="card card-body text-center">';
        $html .= '<h5 class="text-info">' . number_format($totalTime, 3) . 's</h5>';
        $html .= '<small>总执行时间</small>';
        $html .= '</div></div>';

        $html .= '<div class="col-md-3"><div class="card card-body text-center">';
        $html .= '<h5 class="text-success">' . $successCount . '</h5>';
        $html .= '<small>成功操作数</small>';
        $html .= '</div></div>';

        $html .= '<div class="col-md-3"><div class="card card-body text-center">';
        $html .= '<h5 class="text-danger">' . $failCount . '</h5>';
        $html .= '<small>失败操作数</small>';
        $html .= '</div></div>';
        $html .= '</div>';

        return $this->response()
            ->success('任务执行日志')
            ->detail($html);
    }
}
