<?php

namespace Modules\FeatureSms\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSms\DcatAdmin\Metrics\SmsSendTrend;

/**
 * FeatureSms 仪表盘控制器
 */
class DashboardController extends AdminController
{
    /**
     * 仪表盘首页
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content): Content
    {
        return $content
            ->title('短信管理仪表盘')
            ->description('短信服务使用情况概览')
            ->body($this->dashboard());
    }

    /**
     * 构建仪表盘内容
     *
     * @return string
     */
    protected function dashboard()
    {
        // 获取统计数据
        $totalConfigs = \Modules\FeatureSms\Models\SmsConfig::count();
        $activeConfigs = \Modules\FeatureSms\Models\SmsConfig::where('is_open', 1)->count();
        $todaySends = \Modules\FeatureSms\Models\SmsCode::whereDate('sent_at', today())->count();
        $monthSends = \Modules\FeatureSms\Models\SmsCode::whereMonth('sent_at', now()->month)->count();

        return <<<HTML
<div class="row">
    <!-- 统计卡片 -->
    <div class="col-lg-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="feather icon-settings"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">总配置数</span>
                <span class="info-box-number">{$totalConfigs}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="feather icon-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">启用配置</span>
                <span class="info-box-number">{$activeConfigs}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="feather icon-send"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">今日发送</span>
                <span class="info-box-number">{$todaySends}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="feather icon-trending-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">本月发送</span>
                <span class="info-box-number">{$monthSends}</span>
            </div>
        </div>
    </div>
</div>

<!-- 图表区域 -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">短信发送趋势</h4>
            </div>
            <div class="card-body">
                <div id="sms-send-trend-container"></div>
            </div>
        </div>
    </div>
</div>

<script>
// 加载短信发送趋势图表
setTimeout(function() {
    Dcat.Fetch({
        url: '".route('featuresms.metrics.sms-send-trend')."',
        method: 'get',
        data: { option: 7 },
        success: function(data) {
            if (data.status) {
                document.getElementById('sms-send-trend-container').innerHTML = data.data;
            }
        }
    });
}, 100);
</script>
HTML;
    }
}
