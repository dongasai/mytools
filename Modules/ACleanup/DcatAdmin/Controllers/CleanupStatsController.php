<?php

namespace Modules\AClean\DcatAdmin\Controllers;

use Modules\AClean\Models\CleanupBackup;
use Modules\AClean\Models\CleanupConfig;
use Modules\AClean\Models\CleanupLog;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupTableStats;
use Modules\AClean\Models\CleanupTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 清理统计控制器
 *
 * 提供各种统计数据API
 */
class CleanupStatsController
{
    /**
     * 仪表板统计数据
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $stats = [
                // 基础统计
                'configs_count' => CleanupConfig::count(),
                'enabled_configs_count' => CleanupConfig::where('is_enabled', 1)->count(),
                'plans_count' => CleanupPlan::count(),
                'enabled_plans_count' => CleanupPlan::where('is_enabled', 1)->count(),

                // 任务统计
                'total_tasks' => CleanupTask::count(),
                'pending_tasks' => CleanupTask::where('status', 1)->count(),
                'running_tasks' => CleanupTask::whereIn('status', [2, 3])->count(),
                'completed_tasks' => CleanupTask::where('status', 4)->count(),
                'failed_tasks' => CleanupTask::where('status', 5)->count(),

                // 备份统计
                'total_backups' => CleanupBackup::count(),
                'completed_backups' => CleanupBackup::where('backup_status', 2)->count(),
                'total_backup_size' => CleanupBackup::where('backup_status', 2)->sum('backup_size'),

                // 清理统计
                'total_cleaned_records' => CleanupLog::sum('deleted_records'),
                'total_execution_time' => CleanupLog::sum('execution_time'),

                // 最近活动
                'recent_tasks' => CleanupTask::with('plan')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'name' => $task->task_name,
                            'plan_name' => $task->plan->plan_name ?? '',
                            'status' => $task->status,
                            'progress' => $task->progress,
                            'created_at' => $task->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),

                // 数据分类统计
                'category_stats' => CleanupConfig::selectRaw('data_category, COUNT(*) as count')
                    ->groupBy('data_category')
                    ->get()
                    ->map(function ($item) {
                        $categories = [
                            1 => '用户数据',
                            2 => '日志数据',
                            3 => '交易数据',
                            4 => '缓存数据',
                            5 => '配置数据',
                        ];

                        return [
                            'category' => $categories[$item->data_category] ?? '未知',
                            'count' => $item->count,
                        ];
                    }),
            ];

            return response()->json([
                'code' => 0,
                'message' => 'success',
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '获取统计数据失败：'.$e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 表格统计数据
     */
    public function tables(Request $request): JsonResponse
    {
        try {
            $stats = CleanupTableStats::selectRaw('
                    COUNT(*) as total_tables,
                    SUM(record_count) as total_records,
                    SUM(table_size_mb) as total_size_mb,
                    SUM(index_size_mb) as total_index_mb,
                    SUM(data_free_mb) as total_free_mb,
                    AVG(avg_row_length) as avg_row_length
                ')
                ->first();

            // 按大小排序的前10个表
            $largest_tables = CleanupTableStats::orderBy('table_size_mb', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($table) {
                    return [
                        'table_name' => $table->table_name,
                        'record_count' => $table->record_count,
                        'table_size_mb' => round($table->table_size_mb, 2),
                        'index_size_mb' => round($table->index_size_mb, 2),
                    ];
                });

            return response()->json([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'summary' => $stats,
                    'largest_tables' => $largest_tables,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '获取表格统计失败：'.$e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 任务统计数据
     */
    public function tasks(Request $request): JsonResponse
    {
        try {
            // 按状态统计
            $status_stats = CleanupTask::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    $statuses = [
                        1 => '待执行',
                        2 => '备份中',
                        3 => '执行中',
                        4 => '已完成',
                        5 => '已失败',
                        6 => '已取消',
                        7 => '已暂停',
                    ];

                    return [
                        'status' => $statuses[$item->status] ?? '未知',
                        'count' => $item->count,
                    ];
                });

            // 按日期统计（最近7天）
            $daily_stats = CleanupTask::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'status_stats' => $status_stats,
                    'daily_stats' => $daily_stats,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '获取任务统计失败：'.$e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * 备份统计数据
     */
    public function backups(Request $request): JsonResponse
    {
        try {
            // 按类型统计
            $type_stats = CleanupBackup::selectRaw('backup_type, COUNT(*) as count, SUM(backup_size) as total_size')
                ->where('backup_status', 2) // 只统计已完成的备份
                ->groupBy('backup_type')
                ->get()
                ->map(function ($item) {
                    $types = [
                        1 => 'SQL',
                        2 => 'JSON',
                        3 => 'CSV',
                    ];

                    return [
                        'type' => $types[$item->backup_type] ?? '未知',
                        'count' => $item->count,
                        'total_size' => $item->total_size,
                    ];
                });

            // 按日期统计（最近30天）
            $daily_stats = CleanupBackup::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(backup_size) as total_size')
                ->where('created_at', '>=', now()->subDays(30))
                ->where('backup_status', 2)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'type_stats' => $type_stats,
                    'daily_stats' => $daily_stats,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '获取备份统计失败：'.$e->getMessage(),
                'data' => null,
            ]);
        }
    }
}
