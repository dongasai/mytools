<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Support\JavaScript;

/**
 * 环形图卡片
 * 有bug,不要用
 */
class TreeMap extends \Dcat\Admin\Widgets\Metrics\RadialBar
{
    /**
     * @var array
     */
    protected $options = [
        'icon' => null,
        'title' => null,
        'header' => null,
        'content' => null,
        'footer' => null,
        'dropdown' => [],
    ];

    /**
     * 卡片高度.
     *
     * @var int
     */
    protected $height = 300;

    /**
     * 图表高度.
     *
     * @var int
     */
    protected $chartHeight = 210;

    /**
     * 内容宽度.
     *
     * @var array
     */
    protected $contentWidth = [5, 7];

    /**
     * 图表上间距.
     *
     * @var int
     */
    protected $chartMarginTop = -10;

    /**
     * 图表下间距.
     *
     * @var int
     */
    protected $chartMarginBottom = -20;

    /**
     * 图表默认配置.
     *
     * @return array
     */
    protected function defaultChartOptions()
    {

        return [
            'chart' => [
                'height' => 200,
                'type' => 'treemap',
            ],
            'legend' => [
                'show' => false,
            ],
            'plotOptions' => [
                'treemap' => [
                    'distributed' => true,
                    'enableShades' => false,
                ],
            ],
        ];
    }

    /**
     * 设置圆圈宽度.
     *
     * @return $this
     */
    public function chartRadialBarSize(int $size)
    {
        return $this->chartOption('plotOptions.radialBar.size', $size);
    }

    /**
     * 设置圆圈间距.
     *
     * @return $this
     */
    public function chartRadialBarMargin(int $margin)
    {
        return $this->chartOption('plotOptions.radialBar.track.margin', $margin);
    }

    /**
     * 设置图表统计总数信息.
     *
     * @return $this
     */
    public function chartTotal(string $label, int $number)
    {
        return $this->chartOption('plotOptions.radialBar.dataLabels.total', [
            'show' => true,
            'label' => $label,
            'formatter' => JavaScript::make("function () { return {$number}; }"),
        ]);
    }

    /**
     * 设置图表数据.
     *
     * @param  array  $data  键值对
     * @return $this
     */
    public function withChart(array $data)
    {
        $data2 = [];
        foreach ($data as $k => $v) {
            $data2[] = [
                'y' => $v,
                'x' => $k,
            ];
        }

        return $this->chart([
            'series' => [
                'data' => $data2,
            ],
        ]);
    }

    /**
     * 卡片内容.
     *
     * @param  int  $finished
     * @param  int  $pending
     * @param  int  $rejected
     * @return $this
     */
    public function withContent($count, $in, $out)
    {
        return $this->content(
            <<<HTML
<div class="col-12 d-flex flex-column flex-wrap text-center" style="max-width: 220px">
    <div class="chart-info d-flex justify-content-between mb-1 mt-2" >
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-primary"></i>
              <span class="text-bold-600 ml-50">总</span>
          </div>
          <div class="product-result">
              <span>{$count}</span>
          </div>
    </div>

    <div class="chart-info d-flex justify-content-between mb-1">
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-warning"></i>
              <span class="text-bold-600 ml-50">总转入</span>
          </div>
          <div class="product-result">
              <span>{$in}</span>
          </div>
    </div>
      <div class="chart-info d-flex justify-content-between mb-1">
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-danger"></i>
              <span class="text-bold-600 ml-50">总转出</span>
          </div>
          <div class="product-result">
              <span>{$out}</span>
          </div>
    </div>


</div>
HTML
        );
    }
}
