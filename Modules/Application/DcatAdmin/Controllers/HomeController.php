<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Card;
use Illuminate\Routing\Controller;
use Modules\AFile\DcatAdmin\Metrics\FileStorageConfigCheckMetric;
use Modules\Application\DcatAdmin\Metrics\CacheClearMetric;
use Modules\Application\DcatAdmin\Metrics\DatabaseHealth;
use Modules\Application\DcatAdmin\Metrics\PhpDisabledFunctionsMetric;
use Modules\Application\DcatAdmin\Metrics\SystemLogErrors;
use Modules\ABase\DcatAdmin\Metrics\RequestLogConfigCheckMetric;

/**
 * 后台首页控制器
 * 展示系统全局状态
 */
class HomeController extends Controller
{
    protected $title = '系统全局状态';

    /**
     * 系统全局状态首页
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('系统状态概览')
            ->body(function (Row $row) {
                // 第一行：系统配置检查 Metrics
                $row->column(3, new Card(new FileStorageConfigCheckMetric()));
                $row->column(3, new Card(new CacheClearMetric()));
                $row->column(3, new Card(new DatabaseHealth()));
                $row->column(3, new Card(new RequestLogConfigCheckMetric()));
            })
            ->body(function (Row $row) {
                // 第二行：PHP环境检查
                $row->column(4, new Card(new PhpDisabledFunctionsMetric()));
            })
            ->body(function (Row $row) {
                // 第三行：系统监控 Metrics
                $row->column(4, new Card(new SystemLogErrors()));
            });
    }
}