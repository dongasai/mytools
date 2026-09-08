<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

/**
 * 导出日志Action
 *
 * 用于导出清理日志
 */
class ExportLogsAction extends AbstractTool
{
    /**
     * 按钮标题
     */
    protected $title = '导出日志';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $format = $request->input('format', 'csv');
        $dateRange = $request->input('date_range', '7');
        $includeErrors = $request->input('include_errors', false);

        // 调用服务导出日志
        $result = ACleanService::exportLogs([
            'format' => $format,
            'date_range' => $dateRange,
            'include_errors' => $includeErrors,
        ]);

        if (! $result['success']) {
            return $this->response()
                ->error('导出失败：'.$result['message']);
        }

        $data = $result['data'];

        return $this->response()
            ->success('导出完成！')
            ->detail("
                导出格式：{$data['format']}<br>
                导出记录数：".number_format($data['records_count'])."<br>
                文件大小：{$data['file_size']}<br>
                <br>
                <a href='{$data['download_url']}' class='btn btn-primary' target='_blank'>
                    <i class='fa fa-download'></i> 下载文件
                </a>
            ");

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '导出清理日志',
            '请选择导出格式和时间范围。',
            [
                'format' => [
                    'type' => 'select',
                    'label' => '导出格式',
                    'options' => [
                        'csv' => 'CSV格式',
                        'excel' => 'Excel格式',
                        'json' => 'JSON格式',
                    ],
                    'default' => 'csv',
                    'required' => true,
                ],
                'date_range' => [
                    'type' => 'select',
                    'label' => '时间范围',
                    'options' => [
                        '1' => '最近1天',
                        '7' => '最近7天',
                        '30' => '最近30天',
                        '90' => '最近90天',
                        'all' => '全部',
                    ],
                    'default' => '7',
                    'required' => true,
                ],
                'include_errors' => [
                    'type' => 'checkbox',
                    'label' => '仅包含错误日志',
                    'checked' => false,
                    'help' => '勾选则只导出有错误的日志记录',
                ],
            ],
        ];
    }
}
