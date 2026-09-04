<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;

/**
 * 统计卡片：标题+数字
 * column建议1
 */
class Number extends Card
{
    protected $height = 150;

    protected $title = '数字卡片';

    public function __construct($title = null, $icon = null)
    {

        if ($title) {
            $this->title($title);
        }
        if ($icon) {
            $this->icon($icon);
        }

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

        $this->withContent(162);

    }

    /**
     * 卡片内容
     *
     * @param  string  $content
     * @return $this
     */
    public function withContent($content)
    {
        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    <h1 class="font-lg-2 mt-2 mb-0">{$content}</h1>
</div>
HTML
        );
    }
}
