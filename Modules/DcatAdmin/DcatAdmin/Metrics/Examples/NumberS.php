<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;

/**
 * 数据统计,多个数字,竖向
 * column建议2
 *
 * 高度计算：嵌套子卡片专用公式
 * - 公式：子卡片高度 * n + 85（n = 嵌套数量）
 * - 示例：2 个 Number = 150 * 2 + 85 = 385 ≈ 400px
 * - 说明：85px = 标题栏(45px) + 内边距(40px)
 */
class NumberS extends Card
{
    protected $title = '多行数字';

    // 嵌套 2 个 Number: 150 * 2 + 85 = 385 ≈ 400
    protected $height = 400;

    public function __construct()
    {

        if ($options = $this->defaultChartOptions()) {
            $this->chartOptions = $options;
        }

        $this->init();
    }

    /**
     * 处理请求
     *
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {

        $this->withContent([
            // 一个键值对，一行
            '统计' => 162,
            '近日' => 75,
        ]);

    }

    /**
     * 卡片内容
     *
     * @param  string  $contents  键值对 title=>number
     * @return $this
     */
    public function withContent(array $contents)
    {
        $string = '';
        foreach ($contents as $title => $number) {
            $c = new Number($title);
            $c->withContent($number);
            //            $string .= <<<HTML
            // <div class="d-flex flex-column flex-wrap text-center">
            //    <h1 class="font-lg-2 mt-2 mb-0">{$content}</h1>
            // </div>
            // HTML;
            $string .= $c->render();
        }

        return $this->content($string);
    }
}
