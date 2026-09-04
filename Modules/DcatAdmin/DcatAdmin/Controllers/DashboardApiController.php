<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * 仪表盘数据 API 控制器
 *
 * 提供仪表盘统计数据的接口
 */
class DashboardApiController
{
    /**
     * 获取统计数据
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        // 模拟数据，实际应从数据库获取
        return response()->json([
            'users' => ['value' => 1234, 'trend' => 12.5],
            'orders' => ['value' => 567, 'trend' => -5.2],
            'revenue' => ['value' => 89012, 'trend' => 8.7],
            'visits' => ['value' => 34567, 'trend' => 15.3],
        ]);
    }

    /**
     * 获取图表数据
     *
     * @return JsonResponse
     */
    public function getChartData(): JsonResponse
    {
        $dates = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('m-d');
            $values[] = rand(100, 500);
        }

        return response()->json([
            'dates' => $dates,
            'values' => $values,
        ]);
    }
}
