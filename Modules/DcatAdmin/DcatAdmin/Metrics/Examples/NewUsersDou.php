<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;

/**
 * 折线图卡片，多条线
 */
class NewUsersDou extends Line
{
    /**
     * 图表默认高度.
     *
     * @var int
     */
    protected $chartHeight = 280;

    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->height(400);
        $this->title('新用户折线图');
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
        $generator = function ($len, $min = 10, $max = 300) {
            for ($i = 0; $i <= $len; $i++) {
                yield mt_rand($min, $max);
            }
        };

        switch ($request->get('option')) {
            case '365':
                // 卡片内容
                $this->withContent(mt_rand(1000, 5000).'k');
                // 图表数据 - 多条线
                $this->withChart([
                    '新用户注册' => collect($generator(30, 50, 200))->toArray(),
                    '活跃用户' => collect($generator(30, 80, 300))->toArray(),
                    '付费用户' => collect($generator(30, 10, 100))->toArray(),
                ]);
                break;
            case '30':
                // 卡片内容
                $this->withContent(mt_rand(400, 1000).'k');
                // 图表数据 - 多条线
                $this->withChart([
                    '新用户注册' => collect($generator(30, 40, 150))->toArray(),
                    '活跃用户' => collect($generator(30, 60, 250))->toArray(),
                    '付费用户' => collect($generator(30, 8, 80))->toArray(),
                ]);
                break;
            case '28':
                // 卡片内容
                $this->withContent(mt_rand(400, 1000).'k');
                // 图表数据 - 多条线
                $this->withChart([
                    '新用户注册' => collect($generator(28, 35, 120))->toArray(),
                    '活跃用户' => collect($generator(28, 55, 200))->toArray(),
                    '付费用户' => collect($generator(28, 5, 60))->toArray(),
                ]);
                break;
            case '7':
            default:
                // 卡片内容
                $this->withContent('89.2k');
                // 图表数据 - 多条线
                $this->withChart([
                    '新用户注册' => [28, 40, 36, 52, 38, 60, 55],
                    '活跃用户' => [45, 55, 48, 65, 52, 75, 68],
                    '付费用户' => [8, 12, 10, 15, 11, 18, 16],
                ]);
        }
    }

    /**
     * 设置图表数据.
     *
     * @param  array  $data  多条线数据，格式：['线名称' => [数据数组], ...]
     * @return $this
     */
    public function withChart(array $data)
    {
        $series = [];

        // 定义不同线条的颜色
        $colors = [
            '#1890ff', // 蓝色 - 新用户注册
            '#52c41a', // 绿色 - 活跃用户
            '#fa8c16', // 橙色 - 付费用户
            '#722ed1', // 紫色
            '#eb2f96', // 粉色
            '#13c2c2', // 青色
        ];

        // 如果是单维数组，保持原有兼容性
        if (is_numeric(array_keys($data)[0])) {
            $series[] = [
                'name' => $this->title,
                'data' => $data,
            ];
        } else {
            // 多条线数据处理
            $colorIndex = 0;
            foreach ($data as $name => $lineData) {
                $series[] = [
                    'name' => $name,
                    'data' => $lineData,
                ];
                $colorIndex++;
            }
        }

        return $this->chart([
            'series' => $series,
            'colors' => array_slice($colors, 0, count($series)), // 根据线条数量设置颜色
            'legend' => [
                'show' => true,
                'position' => 'bottom',
                'horizontalAlign' => 'center',
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
