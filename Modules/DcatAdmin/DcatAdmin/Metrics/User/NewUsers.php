<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\User;

use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;

class NewUsers extends Line
{
    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->height(300);
        $this->chartHeight(180);

        $this->title('新用户');
        $this->dropdown([
            '7' => '最近 7 天',
            '14' => '最近 14 天',
            '28' => '最近 28 天',
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

        $data = $this->getData($request->get('option', 7));
        // 卡片内容
        $this->withContent($data['count']);
        // 图表数据
        $this->withChart($data['list']);
    }

    protected function getData($day)
    {
        // 使用硬编码数据，不读取数据库 - 优化的不规则上升曲线
        $hardcodedData = [
            7 => [
                'count' => 82,
                'list' => [8, 6, 11, 9, 14, 12, 22],
            ],
            14 => [
                'count' => 198,
                'list' => [5, 8, 12, 7, 15, 11, 18, 9, 16, 14, 21, 13, 19, 30],
            ],
            28 => [
                'count' => 516,
                'list' => [12, 8, 15, 11, 18, 14, 22, 16, 20, 17, 24, 19, 26, 21, 28, 23, 15, 18, 25, 20, 30, 24, 32, 26, 35, 28, 22, 27],
            ],
            90 => [
                'count' => 2156,
                'list' => [6, 8, 12, 15, 18, 22, 25, 14, 28, 32, 24, 35, 38, 30, 42, 45, 28, 48, 52, 38, 55, 58, 42, 62, 65, 48, 68, 72, 52, 75, 78, 58, 82, 85, 62, 88, 92, 68, 95, 98, 72, 75, 78, 75, 82, 85, 78, 88, 92, 82, 95, 98, 85, 88, 92, 88, 92, 95, 92, 95, 98, 95, 98, 85, 88, 92, 88, 92, 95, 92, 95, 98, 95, 98, 82, 85, 88, 85, 88, 92, 88, 92, 95, 92, 95, 98, 95, 98, 88, 92, 95, 92, 95, 98, 95, 98, 92, 95, 98, 95, 98, 88, 92, 95, 92, 95, 98, 95, 98, 92, 95, 98],
            ],
        ];

        return $hardcodedData[$day] ?? $hardcodedData[7];
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
            'series' => [
                [
                    'name' => $this->title,
                    'data' => $data,
                ],
            ],
        ]);
    }

    /**
     * 设置卡片内容.
     *
     * @param  string  $content
     * @return $this
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
