<?php

namespace Modules\Demo5\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Demo5模块数据库种子数据生成器
 *
 * 按照最佳实践，每个表使用独立的Seeder文件
 * 确保每个文章都有评论，包括顶级评论和回复评论
 */
class Demo5DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 开始执行Demo5模块种子数据...');

        // 1. 清理现有数据
        $this->cleanup();

        // 2. 按依赖顺序执行各个Seeder
        $this->call([
            Demo5UserSeeder::class,    // 1. 先创建用户
            Demo5PostSeeder::class,    // 2. 再创建文章
            Demo5CommentSeeder::class, // 3. 最后创建评论
        ]);

        $this->command->info('✅ Demo5模块种子数据执行完成！');
        $this->command->info('📊 数据统计:');
        $this->command->info('   - 用户: ' . \Modules\Demo5\Models\Demo5User::count());
        $this->command->info('   - 文章: ' . \Modules\Demo5\Models\Demo5Post::count());
        $this->command->info('   - 评论: ' . \Modules\Demo5\Models\Demo5Comment::count());
    }

    /**
     * 清理现有数据
     */
    private function cleanup(): void
    {
        $this->command->info('🧹 清理现有数据...');

        // 按依赖关系反向清理
        $tables = [
            'demo5_comments',
            'demo5_posts',
            'demo5_users',
        ];

        foreach ($tables as $table) {
            \DB::table($table)->truncate();
            $this->command->line("   已清理 {$table}");
        }
    }
}
