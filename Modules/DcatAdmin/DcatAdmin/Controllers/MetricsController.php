<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\AreaChart;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\DataBar;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\DemoRanking;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\InfoAlert;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Link;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\ListDataColor;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\MultiButtonCardExample;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\NewDevices;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\NewUsersDou;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Number;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Number1;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\NumberS;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\NumberS2;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\ProductOrders;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Sessions;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\SingleButtonCardExample;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Tickets;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\TotalUsers;
use Modules\DcatAdmin\DcatAdmin\Metrics\User\NewUsers;

/**
 * 图表演示
 *
 * 高度标准：150px(小型) / 200px(中型) / 300px(大型) / 400px(超大型) / 500px(超大复杂型)
 */
class MetricsController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->header('图表演示')
            ->description('Metric 卡片展示')
            ->body(function (Row $row) {
                // ===== 第一行：小型卡片（150px）=====
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(1, Number1::make());                    // 简单数字（150px）
                        $row->column(1, Number::make());                     // 数字卡片（150px）
                        $row->column(2, new Link('HomeDemo', admin_url('/demo_full'))); // 链接（150px）
                        $row->column(3, new TotalUsers);                     // 总用户数（150px）
                        $row->column(3, new InfoAlert);                       // 提示信息卡片（150px）
                    });
                });

                // ===== 第二行：中型卡片（200px）=====
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(4, new NewDevices);                     // 设备类型（200px）
                        $row->column(4, DataBar::make()                       // 横向条形图（200px）
                            ->title('资金流向统计')
                            ->withChart(['转入' => 120, '转出' => 80, '余额' => 40])
                            ->withContent(240, 120, 80));
                        $row->column(4, new NumberS2);                       // 多数字统计（150px）
                    });
                });

                // ===== 第三行：超大型卡片（400px）=====
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(4, new NewUsers);                       // 新用户折线图（400px）
                        $row->column(4, new NumberS);                       // 多行数字（400px）
                        $row->column(4, new DemoRanking);                    // 演示排行榜（400px）
                    });
                });

                // ===== 第四行：大型卡片（300px）=====
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(4, new \Modules\DcatAdmin\DcatAdmin\Metrics\Examples\NewUsersDou); // 多线折线图（300px）
                        $row->column(4, new Sessions);                       // 平均在线（300px）
                        $row->column(4, new ProductOrders);                  // 订单比例（300px）
                    });
                });

                // ===== 第五行：大型卡片（300px）=====
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(4, new \Modules\DcatAdmin\DcatAdmin\Metrics\Examples\AreaChart); // 面积图（300px）
                        $row->column(4, new SingleButtonCardExample);         // 单按钮（300px）
                        $row->column(4, new MultiButtonCardExample);          // 多按钮（300px）
                    });
                });

                // ===== 第六行：超大复杂型卡片（500px）=====
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(12, new Tickets);                        // 环形图+统计（500px）
                    });
                });
            });
    }

    /**
 * 图表演示 - 第二页
 *
 * 高度标准：150px(小型) / 200px(中型) / 300px(大型) / 400px(超大型)
 */
public function metrics2(Content $content)
{
    return $content
        ->header('图表演示 - 第二页')
        ->description('更多 Metric 卡片展示')
        ->body(function (Row $row) {
            // ===== 第一行：大型卡片（300px）=====
            $row->column(12, function (Column $column) {
                $column->row(function (Row $row) {
                    $row->column(6, AreaChart::make());                      // 面积图（300px）
                    $row->column(4, new \Modules\DcatAdmin\DcatAdmin\Metrics\Examples\NewUsersDou); // 多线折线图（300px）
                    $row->column(2, new NumberS2);                       // 多数字统计（150px）
                });
            });

            // ===== 第二行：超大型卡片（400px）=====
            $row->column(12, function (Column $column) {
                $column->row(function (Row $row) {
                    $row->column(4, ListDataColor::make()                    // 系统状态监控（150px）
                        ->title('系统状态监控')
                        ->withContent([
                            ['title' => '系统错误', 'value' => 12, 'type' => ListDataColor::TYPE_ERROR],
                            ['title' => '警告信息', 'value' => 8, 'type' => ListDataColor::TYPE_WARNING],
                            ['title' => '正常运行', 'value' => 156, 'type' => ListDataColor::TYPE_OK],
                        ]));
                    $row->column(4, new NewUsers);                           // 新用户折线图（400px）
                    $row->column(4, new Sessions);                           // 平均在线（300px）
                });
            });
        });
}

}
