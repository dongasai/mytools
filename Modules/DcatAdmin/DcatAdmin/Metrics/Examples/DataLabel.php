<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;

/**
 * 显示数值图表,折线图
 * apex-charts
 */
class DataLabel extends Line
{
    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->title('任务运行');
        $this->height(300);
        $this->chartHeight(220);

    }

    /**
     * 处理请求
     *
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {

        // 图表数据
        $this->withChart([28, 40, 36, 52, 38, 60, 55], [1, 2, 3, 4, 5, 6, 7]);

    }

    /**
     * 设置图表数据.
     *
     *
     * @return $this
     */
    public function withChart(array $data, array $categories)
    {
        $this->chart([
            'chart' => [
                'type' => 'area',
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => $this->title,
                    'data' => $data,
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'xaxis' => [
                'type' => 'number',
                'categories' => $categories,
                'labels' => [
                    'show' => true,
                ],
                'axisBorder' => [
                    'show' => true,
                ],
            ],
            'tooltip' => [
                'x' => [
                    'show' => true,
                ],
            ],

        ]);
    }
}
