<?php

namespace Modules\Demo5\Commands;

use Illuminate\Console\Command;
use Modules\Demo5\Services\PostService;

/**
 * 模块演示数据生成命令
 *
 * 用于为Module Demo5生成演示数据，包括文章
 * 支持自定义数量和清空现有数据
 * 演示队列任务的使用
 *
 * 使用方法：
 * php artisan module_demo5:generate-demo-data
 * php artisan module_demo5:generate-demo-data --posts=50 --truncate
 */
class GenerateDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module_demo5:generate-demo-data
                            {--posts=20 : Number of posts to create}
                            {--truncate : Truncate existing data before generating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate demo posts for Module Demo5';

    protected PostService $postService;

    public function __construct(
        PostService $postService
    ) {
        parent::__construct();
        $this->postService = $postService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to generate demo data for Module Demo5...');

        $postCount = $this->option('posts');
        $truncate = $this->option('truncate');

        if ($truncate) {
            $this->truncateData();
        }

        // 生成文章
        $this->generatePosts($postCount);

        $this->info('Module Demo5 demo data generation completed!');

        return self::SUCCESS;
    }

    /**
     * Truncate existing data
     */
    protected function truncateData(): void
    {
        $this->warn('Truncating existing posts...');

        // 删除文章
        $this->postService->getAllPosts()->each(function ($post) {
            $this->postService->deletePost($post->id);
        });

        $this->info('Existing posts truncated.');
    }

    /**
     * Generate posts
     */
    protected function generatePosts(int $count): void
    {
        $this->info("Generating {$count} posts...");
        $this->info('注意：已发布的文章将自动触发队列任务进行后续处理');
        $progressBar = $this->output->createProgressBar($count);

        $statuses = ['published', 'draft', 'archived'];
        $publishedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $status = fake()->randomElement($statuses);
            $postData = [
                'title' => fake()->sentence(6),
                'content' => fake()->text(1000), // 生成纯文本内容
                'status' => $status,
                'user_id' => 1, // 固定用户ID
                'published_at' => ($status === 'published')
                    ? fake()->dateTimeBetween('-1 year', 'now')
                    : null,
            ];

            $this->postService->createPost($postData);

            if ($status === 'published') {
                $publishedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("✅ 生成了 {$count} 篇文章");
        $this->info("📊 其中 {$publishedCount} 篇已发布，队列任务将自动处理后续操作");
        $this->info("💡 使用 'php artisan queue:work --queue=demo5-posts' 来处理队列任务");
        $this->newLine();
    }
}
