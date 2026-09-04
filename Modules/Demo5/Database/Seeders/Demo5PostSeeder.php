<?php

namespace Modules\Demo5\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\Demo5User;

/**
 * Demo5文章数据种子
 */
class Demo5PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📄 创建Demo5文章数据...');

        $postCount = Demo5Post::count();
        $targetPosts = 10;

        $this->command->info("   当前文章数量: {$postCount}");

        if ($postCount < $targetPosts) {
            $postsToCreate = $targetPosts - $postCount;
            $this->command->info("   需要创建 {$postsToCreate} 篇文章...");

            // 获取用户
            $users = Demo5User::all();
            if ($users->isEmpty()) {
                $this->command->error('❌ 没有找到用户，请先运行Demo5UserSeeder！');
                return;
            }

            // 使用具体的Factory类
            $postFactory = new \Modules\Demo5\Database\Factories\Demo5PostFactory();

            // 创建预设文章
            $postFactory->createMany([
                [
                    'title' => 'Laravel 12 新特性详解',
                    'content' => 'Laravel 12 带来了许多令人兴奋的新特性，包括改进的性能、新的语法糖、增强的类型安全性等。本文将详细介绍这些新特性，帮助开发者快速掌握最新版本的Laravel框架。',
                    'status' => 'published',
                    'user_id' => $users->random()->id,
                    'published_at' => now()->subDays(10),
                ],
                [
                    'title' => '模块化开发最佳实践',
                    'content' => '模块化开发是现代应用架构的重要组成部分。本文分享了在Laravel项目中实施模块化开发的最佳实践，包括模块设计原则、依赖管理、接口定义等关键概念。',
                    'status' => 'published',
                    'user_id' => $users->random()->id,
                    'published_at' => now()->subDays(8),
                ],
                [
                    'title' => 'Dcat Admin 使用心得',
                    'content' => 'Dcat Admin 是一个优秀的Laravel后台管理框架。本文总结了作者在使用Dcat Admin过程中的心得体会，包括配置技巧、自定义扩展、性能优化等方面的经验。',
                    'status' => 'published',
                    'user_id' => $users->random()->id,
                    'published_at' => now()->subDays(6),
                ],
                [
                    'title' => 'Filament 后台开发技巧',
                    'content' => 'Filament 是现代化的Laravel后台管理框架，采用TALL技术栈。本文介绍了Filament的核心概念、组件使用、自定义开发等实用技巧，帮助开发者快速构建优雅的后台界面。',
                    'status' => 'published',
                    'user_id' => $users->random()->id,
                    'published_at' => now()->subDays(4),
                ],
                [
                    'title' => '多后台架构设计思考',
                    'content' => '在企业级应用中，多后台架构是一种常见的设计模式。本文探讨了多后台系统的设计理念、数据隔离、权限管理、用户体验等关键问题，为复杂业务系统的架构设计提供参考。',
                    'status' => 'published',
                    'user_id' => $users->random()->id,
                    'published_at' => now()->subDays(2),
                ],
            ]);

            // 创建随机文章
            $remainingPosts = max(0, $postsToCreate - 5);
            if ($remainingPosts > 0) {
                for ($i = 0; $i < $remainingPosts; $i++) {
                    $postFactory->published()->create(['user_id' => $users->random()->id]);
                }
            }
        }

        $this->command->info("   ✅ 文章创建完成，当前文章数量: " . Demo5Post::count());
    }
}