<?php

namespace Modules\AFile\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\AFile\Services\FileStorageConfigCheckService;

/**
 * 文件存储配置检查卡片
 *
 * 检查 file_storage_configs 表中的配置项是否正确
 */
class FileStorageConfigCheckMetric extends Card
{
    /**
     * 初始化卡片
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->title('文件存储配置检查');
        $this->height(300);  // 卡片总高度 300px
    }

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request)
    {
        $summary = FileStorageConfigCheckService::getSummary();
        $this->withContent($summary);
    }

    /**
     * 卡片内容
     *
     * @param array $data 检查结果数据
     * @return $this
     */
    public function withContent(array $data)
    {
        $total = $data['total'];
        $issues = $data['issues'];
        $status = $data['status'];

        if ($total === 0) {
            $html = <<<HTML
<div class="d-flex flex-column text-center py-3">
    <div class="text-success mb-2">
        <i class="fa fa-check-circle fa-2x"></i>
    </div>
    <h5 class="text-success">所有配置项正常</h5>
</div>
HTML;
        } else {
            $issueList = '';
            foreach ($issues as $issue) {
                $issueList .= "<li class='text-muted'>{$issue}</li>";
            }

            // 添加快速跳转链接
            $configListUrl = admin_url('module_afile/storage-configs');

            $html = <<<HTML
<div class="d-flex flex-column py-2">
    <div class="text-danger mb-2">
        <i class="fa fa-exclamation-triangle"></i>
        <strong>发现 {$total} 个问题</strong>
    </div>
    <ul class="mt-2 mb-2" style="padding-left: 20px; max-height: 200px; overflow-y: auto;">
        {$issueList}
    </ul>
    <div class="text-center mt-2">
        <a href="{$configListUrl}" class="btn btn-sm btn-warning">
            <i class="fa fa-wrench"></i> 前往修复
        </a>
    </div>
</div>
HTML;
        }

        return $this->content($html);
    }
}