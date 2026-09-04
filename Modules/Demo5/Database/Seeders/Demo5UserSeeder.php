<?php

namespace Modules\Demo5\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Demo5\Models\Demo5User;

/**
 * Demo5用户数据种子
 */
class Demo5UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👤 创建Demo5用户数据...');

        $userCount = Demo5User::count();
        $targetUsers = 5;

        $this->command->info("   当前用户数量: {$userCount}");

        if ($userCount < $targetUsers) {
            $usersToCreate = $targetUsers - $userCount;
            $this->command->info("   需要创建 {$usersToCreate} 个用户...");

            // 创建预设用户
            $users = [
                [
                    'name' => '管理员',
                    'email' => 'admin@demo5.com',
                    'status' => 'active',
                    'last_login_at' => now()->subDays(1),
                ],
                [
                    'name' => '开发者张三',
                    'email' => 'zhangsan@demo5.com',
                    'phone' => '13800138000',
                    'status' => 'active',
                    'last_login_at' => now()->subDays(2),
                ],
                [
                    'name' => '开发者李四',
                    'email' => 'lisi@demo5.com',
                    'phone' => '13900139000',
                    'status' => 'active',
                    'last_login_at' => now()->subDays(3),
                ],
                [
                    'name' => '测试用户王五',
                    'email' => 'wangwu@demo5.com',
                    'status' => 'active',
                    'last_login_at' => now()->subDays(5),
                ],
                [
                    'name' => '临时用户赵六',
                    'email' => 'zhaoliu@demo5.com',
                    'status' => 'inactive',
                    'last_login_at' => now()->subDays(30),
                ],
            ];

            foreach ($users as $userData) {
                Demo5User::create($userData);
            }

            $this->command->info("   ✅ 用户创建完成，当前用户数量: " . Demo5User::count());
        } else {
            $this->command->info("   ✅ 用户已存在，无需创建");
        }
    }
}