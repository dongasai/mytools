<?php

namespace Modules\Demo5\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;
use Modules\Demo5\Models\Demo5Comment;

/**
 * 评论趋势统计图表
 *
 * 显示不同时间段的评论创建趋势
 */
class CommentTrend extends Line
{
    /**
     * 图表默认高度.
     *
     * @var int
     */
    protected $chartHeight = 150;

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('评论趋势分析');
        $this->height(300);
        $this->dropdown([
            '7' => '最近7天',
            '14' => '最近14天',
            '30' => '最近30天',
            '90' => '最近90天',
        ]);
    }

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $option = $request->get('option', '7');
        $days = (int) $option;

        $data = $this->getCommentData($days);
        $this->withContent($data['total'] . ' 条');
        $this->withChart($data['data']);
    }

    /**
     * 写入数据
     */
    public function fill()
    {
        $data = $this->getCommentData(7);
        $this->withContent($data['total'] . ' 条');
        $this->withChart($data['data']);
    }

    /**
     * 获取评论数据
     */
    protected function getCommentData(int $days): array
    {
        $comments = Demo5Comment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [];
        $total = 0;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $count = $comments->where('date', $dateStr)->first()?->count ?? 0;

            $data[] = $count;
            $total += $count;
        }

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * 设置图表数据
     */
    public function withChart(array $data)
    {
        // 生成日期标签
        $labels = [];
        $days = count($data);
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = now()->subDays($i)->format('m/d');
        }

        return $this->chart([
            'series' => [
                [
                    'name' => '新增评论',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
            ],
            'yaxis' => [
                'min' => 0,
            ],
            'fill' => [
                'opacity' => 0.8,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return val + " 条评论" }',
                ],
            ],
        ]);
    }

    /**
     * 设置卡片内容
     */
    public function withContent($content)
    {
        return $this->content(
            <<<HTML
<div class="d-flex justify-content-between align-items-center mt-1" style="margin-bottom: 2px">
    <h2 class="ml-1 font-lg-1">{$content}</h2>
    <span class="mb-0 mr-1 text-80">{$this->title}</span>
</div>
HTML
        );
    }
}