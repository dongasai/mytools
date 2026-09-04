<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\RadialBar;
use Illuminate\Support\Str;

/**
 * 显示多个数值
 * 一行
 */
class NumberS2 extends RadialBar
{
    protected $height=150;
    protected $contentWidth = [12, 0];

    protected $chartPullRight = true;

    protected $decimal_places = 2;

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        $this->id('metric-card-'.Str::random(8));
        $this->class('card');

        $this->dropdown([
            '1' => '最近 1 天',
            '7' => '最近 7 天',
            '30' => '最近 30 天',
            '90' => '最近 90 天',
        ]);
    }

    /**
     * 处理请求,获取数值
     *
     *
     * @return mixed|void
     */
    public function handle(\Illuminate\Http\Request $request)
    {
        switch ($request->get('option')) {
            case '1':
            default:
                // 演示数值
                $data = [
                    'sum' => 10,
                    'sum_price' => 1.1,
                    'sum_huansuan' => 100,
                    'sum2' => 102,
                ];
        }

        // 卡片底部
        $this->withContent([
            '数额' => $data['sum'],
            '交易额' => $data['sum_price'],
            '交易额2' => $data['sum2'],
            '换算额' => $data['sum_huansuan'],
        ]);

    }

    /**
     * 卡片内容.
     *
     * @param  string  $new
     * @param  string  $open
     * @param  string  $response
     * @return $this
     */
    public function withContent($dataList)
    {
        $string = '';
        foreach ($dataList as $key => $value) {
            $value = number_format(floatval($value), $this->decimal_places);

            $string .= <<<HTML
 <div class="text-center">
        <p>$key</p>
        <span class="font-md-5">{$value}</span>
    </div>

HTML;
        }

        return $this->content(
            <<<HTML
<div class="d-flex justify-content-between p-1" style="padding-top: 15px !important;">
 $string
</div>
HTML
        );
    }
}
