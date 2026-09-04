<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Illuminate\Http\Request;

/**
 * 横向 条形卡片
 * 异步加载数据示例
 */
class DataBar extends \Dcat\Admin\Widgets\Metrics\RadialBar
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
    protected $height = 200;

    /**
     * 图表高度.
     *
     * @var int
     */
    protected $chartHeight = 140;

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
    protected $chartMarginTop = 0;

    /**
     * 图表下间距.
     *
     * @var int
     */
    protected $chartMarginBottom = -20;

    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->title('横向条形图');
        $this->dropdown([
            '7'  => 'Last 7 Days',
            '28' => 'Last 28 Days',
            '30' => 'Last Month',
            '365' => 'Last Year',
        ]);
    }

    /**
     * 处理请求，异步获取数据
     *
     * @param  Request  $request
     * @return void
     */
    public function handle(Request $request)
    {
        switch ($request->get('option')) {
            case '365':
                $this->withContent(1200, 800, 400);
                $this->withChart(['类别A' => 1200, '类别B' => 800, '类别C' => 400]);
                break;
            case '30':
                $this->withContent(300, 200, 100);
                $this->withChart(['类别A' => 300, '类别B' => 200, '类别C' => 100]);
                break;
            case '28':
                $this->withContent(280, 180, 100);
                $this->withChart(['类别A' => 280, '类别B' => 180, '类别C' => 100]);
                break;
            case '7':
            default:
                $this->withContent(100, 60, 40);
                $this->withChart(['类别A' => 100, '类别B' => 60, '类别C' => 40]);
        }
    }

    /**
     * 图表默认配置.
     *
     * @return array
     */
    protected function defaultChartOptions()
    {

        return [
            'chart' => [
                'height' => 150,
                'type' => 'bar',

            ],

            'series' => [

            ],

            'xaxis' => [
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                ],
            ],
        ];
    }

    /**
     * 设置图表数据.
     *
     * @param  array  $data  键值对 [类别 => 数值]
     * @return $this
     */
    public function withChart(array $data)
    {

        $cData = [
            'series' => [
                [
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($data),
            ],
        ];

        return $this->chart($cData);
    }

    /**
     * 卡片内容.
     *
     * @param  int  $count  总数
     * @param  int  $in  总转入
     * @param  int  $out  总转出
     * @return $this
     */
    public function withContent(int $count, int $in, int $out)
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
