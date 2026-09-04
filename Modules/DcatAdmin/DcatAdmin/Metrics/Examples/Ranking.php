<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\RadialBar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 排行榜
 */
class Ranking extends RadialBar
{
    protected $chartPullRight = false;

    protected $contentWidth = [11, 0];

    /**
     * 排行榜 排名颜色
     *
     * @var string[]
     */
    protected $rankClass = [
        'text-primary',
        'text-warning',
        'text-danger',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
        'text-secondary',
    ];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {

        $this->height(200);
        $this->id('metric-card-'.Str::random(8));
        $this->class('card');

        // $this->title('订单排行榜');
        $this->dropdown([
            '1' => '最近 1 天',
            '7' => '最近 7 天',
            '14' => '最近 14 天',
            '30' => '最近 30 天',
            '90' => '最近 90 天',

        ]);
    }

    /**
     * 处理请求
     *
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $data = $this->getData($request->get('option', 1));

        // 卡片内容
        $this->withContent($data);

    }

    /**
     * 获取数据 - 子类需要重写此方法
     *
     * @param  string  $option
     * @return array
     */
    protected function getData($option)
    {
        // 默认示例数据
        return [
            ['rank' => '1', 'title' => '示例项目1', 'number' => '100'],
            ['rank' => '2', 'title' => '示例项目2', 'number' => '80'],
            ['rank' => '3', 'title' => '示例项目3', 'number' => '60'],
        ];
    }

    /**
     * 卡片内容.
     *
     * @param  int  $finished
     * @param  int  $pending
     * @param  int  $rejected
     * @return $this
     */
    public function withContent($list)
    {

        $listString = '';
        foreach ($list as $i => $value) {
            $rank = $value['rank'];
            $color = $this->rankClass[$i];
            $name = $value['title'];
            $number = $value['number'];
            $listString .= <<<HTML
    <div class="chart-info d-flex justify-content-between mb-1 mr-1" >
          <div class="series-info d-flex align-items-center">
              <i class=" $color" style="width: 20px"> {$rank} </i>
              &nbsp;&nbsp;
              <span class="text-bold-600 ml-50"> {$name} </span>
          </div>
          <div class="product-result">
              <span>{$number}</span>
          </div>
    </div>
HTML;
        }

        return $this->content(
            <<<HTML

<div class="col-12 d-flex flex-column flex-wrap mt-1 text-center" style="padding-left:16px;padding-right:0px;">
$listString
</div>
HTML
        );

    }
}
