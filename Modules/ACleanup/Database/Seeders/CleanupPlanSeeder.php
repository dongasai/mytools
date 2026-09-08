<?php

namespace Modules\AClean\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupPlanContent;

/**
 * 清理计划数据填充器
 */
class CleanupPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'plan_name' => '日志清理计划',
                'plan_type' => 3, // 分类清理
                'target_selection' => json_encode([
                    'data_category' => 2, // 日志数据
                ]),
                'global_conditions' => json_encode([
                    'older_than_days' => 30
                ]),
                'backup_config' => json_encode([
                    'enabled' => true,
                    'backup_type' => 1, // SQL
                    'compression_type' => 2, // gzip
                    'retention_days' => 7
                ]),
                'is_template' => true,
                'is_enabled' => true,
                'description' => '定期清理系统日志数据，保留30天内的记录并备份',
                'created_by' => 1,
            ],
            [
                'plan_name' => '用户数据清理',
                'plan_type' => 2, // 模块清理
                'target_selection' => json_encode([
                    'module_name' => 'Account'
                ]),
                'global_conditions' => json_encode([
                    'user_status' => 'deleted',
                    'deleted_days_ago' => 180
                ]),
                'backup_config' => json_encode([
                    'enabled' => true,
                    'backup_type' => 2, // JSON
                    'compression_type' => 3, // zip
                    'retention_days' => 30
                ]),
                'is_template' => false,
                'is_enabled' => true,
                'description' => '清理已删除用户的相关数据，删除180天前的记录',
                'created_by' => 1,
            ],
        ];

        foreach ($plans as $planData) {
            $plan = CleanupPlan::create($planData);

            // 为每个计划添加内容示例
            if ($plan->plan_name === '日志清理计划') {
                CleanupPlanContent::create([
                    'plan_id' => $plan->id,
                    'table_name' => 'admin_action_logs',
                    'model_class' => 'Modules\\System\\Models\\AdminActionLog',
                    'cleanup_type' => 3, // 按时间删除
                    'conditions' => json_encode([
                        'date_field' => 'created_at',
                        'days' => 30
                    ]),
                    'priority' => 1,
                    'batch_size' => 1000,
                    'backup_enabled' => true,
                    'notes' => '管理员操作日志，保留30天',
                ]);
            }
        }
    }
}
