<?php

namespace Modules\AClean\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AClean\Models\CleanupConfig;

/**
 * 清理配置数据填充器
 */
class CleanupConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'table_name' => 'admin_action_logs',
                'model_class' => 'Modules\\System\\Models\\AdminActionLog',
                'module_name' => 'System',
                'data_category' => 2, // 日志数据
                'default_cleanup_type' => 3, // 按时间删除
                'default_conditions' => json_encode([
                    'days' => 90,
                    'date_field' => 'created_at'
                ]),
                'is_enabled' => true,
                'priority' => 1,
                'batch_size' => 1000,
                'description' => '管理员操作日志清理，保留90天内的记录',
            ],
            [
                'table_name' => 'user_profiles',
                'model_class' => 'Modules\\Account\\Models\\UserProfile',
                'module_name' => 'Account',
                'data_category' => 1, // 用户数据
                'default_cleanup_type' => 4, // 按用户删除
                'default_conditions' => json_encode([
                    'user_status' => 'deleted'
                ]),
                'is_enabled' => false,
                'priority' => 10,
                'batch_size' => 100,
                'description' => '已删除用户档案数据清理',
            ],
            [
                'table_name' => 'failed_jobs',
                'model_class' => null,
                'module_name' => 'System',
                'data_category' => 2, // 日志数据
                'default_cleanup_type' => 2, // 删除所有
                'default_conditions' => json_encode([
                    'older_than_hours' => 24
                ]),
                'is_enabled' => true,
                'priority' => 1,
                'batch_size' => 500,
                'description' => '失败任务队列清理，删除24小时前的记录',
            ],
        ];

        foreach ($configs as $config) {
            CleanupConfig::updateOrCreate(
                ['table_name' => $config['table_name']],
                $config
            );
        }
    }
}
