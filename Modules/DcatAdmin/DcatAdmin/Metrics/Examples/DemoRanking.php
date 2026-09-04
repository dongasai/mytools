<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Illuminate\Http\Request;

/**
 * 演示排名卡片
 * 展示Ranking组件的使用方法和效果
 * 推荐 column 4
 */
class DemoRanking extends Ranking
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->height(400);
        $this->title('演示排行榜(DemoRanking)');
        $this->dropdown([
            'sales' => '销售排行榜',
            'users' => '用户活跃榜',
            'products' => '产品热度榜',
            'regions' => '地区排行榜',
        ]);
    }

    /**
     * 处理请求
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $type = $request->get('option', 'sales');
        $data = $this->getDemoRankingData($type);

        // 卡片内容
        $this->withContent($data);
    }

    /**
     * 设置卡片内容 - 优化样式适配400px
     *
     * @param array $list 排行榜数据
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
    <div class="chart-info d-flex justify-content-between mb-1 py-0 mr-1" >
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

<div class="col-12 d-flex flex-column flex-wrap mt-2 pb-2 text-center" style="padding-left:16px;padding-right:0px;">
$listString
</div>
HTML
        );
    }

    /**
     * 获取演示排名数据
     *
     * @param  string  $type  排名类型
     */
    protected function getDemoRankingData(string $type): array
    {
        switch ($type) {
            case 'sales':
                return $this->getSalesRanking();
            case 'users':
                return $this->getUsersRanking();
            case 'products':
                return $this->getProductsRanking();
            case 'regions':
                return $this->getRegionsRanking();
            default:
                return $this->getSalesRanking();
        }
    }

    /**
     * 销售排行榜数据
     */
    private function getSalesRanking(): array
    {
        return [
            ['rank' => 1, 'title' => '张三', 'number' => '¥128,500'],
            ['rank' => 2, 'title' => '李四', 'number' => '¥95,200'],
            ['rank' => 3, 'title' => '王五', 'number' => '¥87,300'],
            ['rank' => 4, 'title' => '赵六', 'number' => '¥76,800'],
            ['rank' => 5, 'title' => '钱七', 'number' => '¥65,400'],
            ['rank' => 6, 'title' => '孙八', 'number' => '¥58,900'],
            ['rank' => 7, 'title' => '周九', 'number' => '¥52,100'],
            ['rank' => 8, 'title' => '吴十', 'number' => '¥48,600'],
        ];
    }

    /**
     * 用户活跃榜数据
     */
    private function getUsersRanking(): array
    {
        return [
            ['rank' => 1, 'title' => 'admin', 'number' => '2,580 次'],
            ['rank' => 2, 'title' => 'user001', 'number' => '1,920 次'],
            ['rank' => 3, 'title' => 'manager', 'number' => '1,650 次'],
            ['rank' => 4, 'title' => 'editor', 'number' => '1,340 次'],
            ['rank' => 5, 'title' => 'viewer', 'number' => '1,120 次'],
            ['rank' => 6, 'title' => 'guest', 'number' => '890 次'],
            ['rank' => 7, 'title' => 'tester', 'number' => '720 次'],
            ['rank' => 8, 'title' => 'demo', 'number' => '650 次'],
        ];
    }

    /**
     * 产品热度榜数据
     */
    private function getProductsRanking(): array
    {
        return [
            ['rank' => 1, 'title' => 'iPhone 15 Pro', 'number' => '8,520 次浏览'],
            ['rank' => 2, 'title' => 'MacBook Pro', 'number' => '7,230 次浏览'],
            ['rank' => 3, 'title' => 'iPad Air', 'number' => '6,890 次浏览'],
            ['rank' => 4, 'title' => 'AirPods Pro', 'number' => '5,670 次浏览'],
            ['rank' => 5, 'title' => 'Apple Watch', 'number' => '4,920 次浏览'],
            ['rank' => 6, 'title' => 'iMac', 'number' => '3,850 次浏览'],
            ['rank' => 7, 'title' => 'Mac Mini', 'number' => '2,940 次浏览'],
            ['rank' => 8, 'title' => 'Studio Display', 'number' => '2,180 次浏览'],
        ];
    }

    /**
     * 地区排行榜数据
     */
    private function getRegionsRanking(): array
    {
        return [
            ['rank' => 1, 'title' => '北京', 'number' => '45,200 订单'],
            ['rank' => 2, 'title' => '上海', 'number' => '38,900 订单'],
            ['rank' => 3, 'title' => '广州', 'number' => '32,600 订单'],
            ['rank' => 4, 'title' => '深圳', 'number' => '29,800 订单'],
            ['rank' => 5, 'title' => '杭州', 'number' => '24,500 订单'],
            ['rank' => 6, 'title' => '成都', 'number' => '21,300 订单'],
            ['rank' => 7, 'title' => '武汉', 'number' => '18,700 订单'],
            ['rank' => 8, 'title' => '南京', 'number' => '16,400 订单'],
        ];
    }
}
