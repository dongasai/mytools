<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Round;
use Illuminate\Http\Request;

/**
 * 图表卡片：左侧三个数值，右侧三环比例（最内侧总数）
 * 通常展示各状态比例
 * column建议4
 *
 * 高度说明：左侧数值列表 + 右侧三环比例图，复杂组合
 * 高度 300px：图表高度 220px + 内容区域优化
 */
class ProductOrders extends Round
{
    /**
     * 图表高度
     */
    protected $chartHeight = 260;

    /**
     * 内容宽度比例 [左侧, 右侧]
     */
    protected $contentWidth = [5, 7];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->height(300);
        $this->title('订单比例');
        $this->chartLabels(['状态1', '状态2', '状态3']);
        $this->dropdown([
            '7' => 'Last 7 Days',
            '28' => 'Last 28 Days',
            '30' => 'Last Month',
            '365' => 'Last Year',
        ]);
    }

    /**
     * 处理请求，获取数值
     *
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        switch ($request->get('option')) {
            case '365':
            case '30':
            case '28':
            case '7':
            default:
                // 卡片内容 - 键值对形式（包含颜色映射）
                $this->withContent(
                    [
                        '已完成' => 23043,
                        '进行中' => 14658,
                        '退款中' => 4758,
                    ],
                    [
                        'text-primary',
                        'text-warning',
                        'text-danger',
                    ]
                );

                // 图表数据 ，三环数值
                $this->withChart([70, 52, 26]);

                // 总数 （内环数值）
                $this->chartTotal('Total', 344);
        }
    }

    /**
     * 设置图表数据.
     *
     *
     * @return $this
     */
    public function withChart(array $data)
    {
        return $this->chart([
            'series' => $data,
        ]);
    }

    /**
     * 卡片内容 - 键值对形式（包含颜色映射）
     *
     * @param array $items 键值对数组 ['已完成' => 23043, ...]
     * @param array $colors 颜色数组 ['text-primary', ...]（可选，默认6种）
     * @return $this
     */
    public function withContent(array $items, array $colors = [])
    {
        // 默认颜色映射 - 6种颜色
        $defaultColors = ['text-primary', 'text-warning', 'text-danger', 'text-success', 'text-info', 'text-secondary'];
        $colors = !empty($colors) ? $colors : $defaultColors;

        $listString = '';
        $index = 0;
        foreach ($items as $label => $value) {
            $color = $colors[$index % count($colors)] ?? 'text-secondary';
            $listString .= <<<HTML
    <div class="chart-info d-flex justify-content-between py-1" style="font-size: 1.1rem;" >
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 {$color}"></i>
              <span class="text-bold-600 ml-50">{$label}</span>
          </div>
          <div class="product-result">
              <span>{$value}</span>
          </div>
    </div>
HTML;
            $index++;
        }

        return $this->content(
            <<<HTML
<div class="col-12 d-flex flex-column justify-content-center text-center" style="max-width: 220px; min-height: 200px;">
$listString
</div>
HTML
        );
    }
}
