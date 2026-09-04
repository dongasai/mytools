<?php

namespace Modules\FeatureSms\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\FeatureSms\Models\SmsCode;

/**
 * 短信发送趋势统计
 */
class SmsSendTrend extends Card
{
    /**
     * 卡片内容
     *
     * @return string
     */
    protected function init()
    {
        parent::init();

        $this->title('短信发送趋势');
        $this->height(300);
        $this->dropdown([
            '7' => '最近7天',
            '30' => '最近30天',
        ]);
    }

    /**
     * 卡片内容
     *
     * @return string
     */
    public function handle(Request $request)
    {
        $days = $request->get('option', 7);

        // 获取日期范围内的短信发送数据
        $startDate = now()->subDays($days);
        $endDate = now();

        // 按日期分组统计发送数量
        $data = SmsCode::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('sent_at')
            ->groupBy('date')
            ->selectRaw('DATE(sent_at) as date, COUNT(*) as count')
            ->orderBy('date', 'asc')
            ->get();

        // 构建图表数据
        $dates = [];
        $counts = [];

        // 填充所有日期的数据，包括没有发送的日期
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = $data->where('date', $date)->first()?->count ?? 0;

            $dates[] = substr($date, 5); // 只显示月-日
            $counts[] = $count;
        }

        $this->withContent([
            'labels' => $dates,
            'values' => $counts,
        ]);
    }

    /**
     * 渲染内容
     *
     * @return string
     */
    protected function renderContent()
    {
        $content = $this->content;

        return <<<HTML
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{$this->title}</h4>
    </div>
    <div class="card-body">
        <canvas id="sms-send-trend" width="400" height="200"></canvas>
    </div>
</div>
<script>
if (typeof Chart !== 'undefined') {
    const ctx = document.getElementById('sms-send-trend').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {$content['labels']},
            datasets: [{
                label: '发送数量',
                data: {$content['values']},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
</script>
HTML;
    }
}
