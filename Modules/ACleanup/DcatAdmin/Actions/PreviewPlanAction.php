<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 预览计划Action
 *
 * 用于预览清理计划的执行效果
 */
class PreviewPlanAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '预览效果';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $planId = $this->getKey();

        // 调用预览服务
        $result = ACleanService::previewPlan($planId);

        if (! $result['success']) {
            return $this->response()
                ->error('预览失败：' . $result['message']);
        }

        $data = $result['data'];

        $html = '<div class="row">';
        $html .= '<div class="col-md-6">';
        $html .= '<h5>总体统计</h5>';
        $html .= '<ul class="list-unstyled">';
        $html .= '<li><strong>包含表数：</strong>' . number_format($data['total_tables']) . '</li>';
        $html .= '<li><strong>总记录数：</strong>' . number_format($data['total_records']) . '</li>';
        $html .= '<li><strong>预计删除：</strong>' . number_format($data['estimated_delete']) . '</li>';
        $html .= '<li><strong>预计保留：</strong>' . number_format($data['estimated_remain']) . '</li>';
        $html .= '<li><strong>删除比例：</strong>' . $data['delete_percentage'] . '%</li>';
        $html .= '<li><strong>预计执行时间：</strong>' . $data['estimated_time'] . '</li>';
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '<div class="col-md-6">';
        $html .= '<h5>风险评估</h5>';
        $html .= '<ul class="list-unstyled">';

        $riskLevel = $data['risk_level'];
        $riskColor = $riskLevel === 'high' ? 'danger' : ($riskLevel === 'medium' ? 'warning' : 'success');
        $riskText = $riskLevel === 'high' ? '高风险' : ($riskLevel === 'medium' ? '中风险' : '低风险');

        $html .= '<li><strong>风险等级：</strong><span class="badge badge-' . $riskColor . '">' . $riskText . '</span></li>';
        $html .= '<li><strong>高风险表：</strong>' . count($data['high_risk_tables']) . ' 个</li>';
        $html .= '<li><strong>备份建议：</strong>' . ($data['backup_recommended'] ? '强烈建议' : '可选') . '</li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        if (! empty($data['warnings'])) {
            $html .= '<div class="alert alert-warning mt-3">';
            $html .= '<h6>⚠️ 注意事项：</h6>';
            $html .= '<ul class="mb-0">';
            foreach ($data['warnings'] as $warning) {
                $html .= '<li>' . $warning . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        if (! empty($data['high_risk_tables'])) {
            $html .= '<div class="mt-3">';
            $html .= '<h6>🔴 高风险表：</h6>';
            $html .= '<div class="table-responsive">';
            $html .= '<table class="table table-sm table-striped">';
            $html .= '<thead><tr><th>表名</th><th>当前记录数</th><th>预计删除</th><th>删除比例</th></tr></thead>';
            $html .= '<tbody>';

            foreach ($data['high_risk_tables'] as $table) {
                $html .= '<tr>';
                $html .= '<td>' . $table['table_name'] . '</td>';
                $html .= '<td>' . number_format($table['current_count']) . '</td>';
                $html .= '<td>' . number_format($table['estimated_delete']) . '</td>';
                $html .= '<td><span class="badge badge-danger">' . $table['delete_percentage'] . '%</span></td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            $html .= '</div>';
            $html .= '</div>';
        }

        return $this->response()
            ->success('预览完成')
            ->detail($html);
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认预览计划？',
            '此操作将分析计划的执行效果，不会实际删除数据。',
        ];
    }
}
