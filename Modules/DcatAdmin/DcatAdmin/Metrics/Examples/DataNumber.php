<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\RadialBar;
use Illuminate\Support\Str;

/**
 * 显示多个数值
 * 两行
 */
class DataNumber extends RadialBar
{
    protected $contentWidth = [12, 0];

    protected $chartPullRight = true;

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        $this->id('metric-card-'.Str::random(8));
        $this->class('card');
        $this->height(200);
        $this->dropdown([
            '1' => '最近 1 天',
            '7' => '最近 7 天',
            '30' => '最近 30 天',
            '90' => '最近 90 天',
        ]);
    }

    /**
     * 卡片内容
     *
     * @param  string  $count
     * @return $this
     */
    public function withContent($dataList)
    {
        $string = '';
        foreach ($dataList as $key => $value) {

            $string .= <<<HTML
<div class="text-center" style="font-weight: 600">
    <h4>{$key}</h4>
    <span class="font-md-5" style="min-width: 90px;">{$value}</span>
</div>

HTML;
        }

        return $this->content(
            <<<HTML
<div class="d-flex justify-content-between p-1 ">
$string
</div>


HTML
        );
    }

    /**
     * 卡片底部内容.
     *
     * @param  string  $new
     * @param  string  $open
     * @param  string  $response
     * @return $this
     */
    public function withFooter($dataList)
    {
        $string = '';
        foreach ($dataList as $key => $value) {

            $string .= <<<HTML
 <div class="text-center">
        <p>$key</p>
        <span class="font-md-5">{$value}</span>
    </div>

HTML;
        }

        return $this->footer(
            <<<HTML
<div class="d-flex justify-content-between p-1" style="padding-top: 0!important;">
 $string
</div>
HTML
        );
    }
}
