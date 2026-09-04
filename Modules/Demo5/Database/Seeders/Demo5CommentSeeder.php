<?php

namespace Modules\Demo5\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Demo5\Models\Demo5Comment;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\Demo5User;
use Modules\Demo5\Enums\CommentStatus;

/**
 * Demo5评论数据种子 - 确保每篇文章都有评论
 */
class Demo5CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💬 创建Demo5评论数据...');

        $posts = Demo5Post::all();
        $users = Demo5User::all();

        if ($posts->isEmpty()) {
            $this->command->error('❌ 没有找到文章，请先运行Demo5PostSeeder！');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('❌ 没有找到用户，请先运行Demo5UserSeeder！');
            return;
        }

        $totalComments = 0;

        // 为每篇文章创建评论
        $posts->each(function (Demo5Post $post) use ($users, &$totalComments) {
            $existingComments = Demo5Comment::where('post_id', $post->id)->count();
            $targetComments = rand(3, 12); // 每篇文章3-12条评论

            $this->command->info("   文章「{$post->title}」: 已有 {$existingComments} 条评论，目标 {$targetComments} 条");

            if ($existingComments < $targetComments) {
                $commentsToCreate = $targetComments - $existingComments;

                // 创建顶级评论 (60-80%)
                $topLevelCount = (int) ($commentsToCreate * rand(60, 80) / 100);
                $this->createTopLevelComments($post, $users, $topLevelCount);

                // 创建回复评论 (20-40%)
                $replyCount = $commentsToCreate - $topLevelCount;
                if ($replyCount > 0) {
                    $this->createReplyComments($post, $users, $replyCount);
                }

                $totalComments += $commentsToCreate;
                $this->command->info("     → 新增 {$commentsToCreate} 条评论");
            }
        });

        $this->command->info("   ✅ 评论创建完成，当前评论数量: " . Demo5Comment::count());
        $this->command->info("   ✅ 确保每篇文章都有评论！");
    }

    /**
     * 创建顶级评论
     */
    private function createTopLevelComments(Demo5Post $post, $users, int $count): void
    {
        // 预设评论内容
        $positiveComments = [
            '很好的文章！学到了很多新知识。',
            '赞同作者的观点，分析得很透彻。',
            '非常感谢分享，内容对我很有帮助。',
            '这篇文章解决了我长期困惑的问题。',
            '写得很详细，值得收藏学习。',
            '理论与实践结合得很好，受益匪浅。',
            '思路清晰，逻辑严谨，是一篇优质文章。',
            '期待作者更多的精彩分享！',
        ];

        $neutralComments = [
            '文章内容不错，但有些地方可以进一步完善。',
            '观点值得参考，但需要更多的实例支撑。',
            '整体来说写得还可以，有参考价值。',
            '文章结构合理，但深度有待加强。',
            '内容比较全面，但创新性不足。',
        ];

        $questionComments = [
            '文章中提到的某个概念能否进一步解释一下？',
            '关于文中的观点，我有一些不同的看法。',
            '作者能分享一下相关的实践案例吗？',
            '这种方法在实际项目中应用效果如何？',
        ];

        for ($i = 0; $i < $count; $i++) {
            $commentType = rand(1, 10);

            if ($commentType <= 6) { // 60% 正面评论
                $content = $positiveComments[array_rand($positiveComments)];
                $status = CommentStatus::Approved->value;
            } elseif ($commentType <= 8) { // 20% 中性评论
                $content = $neutralComments[array_rand($neutralComments)];
                $status = CommentStatus::Approved->value;
            } else { // 20% 提问评论或待审核
                $content = $questionComments[array_rand($questionComments)];
                $status = rand(1, 3) === 1 ? CommentStatus::Pending->value : CommentStatus::Approved->value;
            }

            Demo5Comment::factory()
                ->forPost($post)
                ->byUser($users->random())
                ->create([
                    'content' => $content,
                    'status' => $status,
                    'parent_id' => null,
                    'created_at' => now()->subDays(rand(1, 30))->subHours(rand(1, 23)),
                ]);
        }
    }

    /**
     * 创建回复评论
     */
    private function createReplyComments(Demo5Post $post, $users, int $count): void
    {
        // 获取该文章的顶级评论
        $parentComments = Demo5Comment::where('post_id', $post->id)
            ->whereNull('parent_id')
            ->get();

        if ($parentComments->isEmpty()) {
            return;
        }

        // 预设回复内容
        $replies = [
            '说得很对，我也这么认为。',
            '谢谢分享，确实如此。',
            '有道理，学习了。',
            '补充一点我的看法...',
            '这个观点很新颖，值得思考。',
            '同意楼主的看法。',
            '确实如此，深有同感。',
            '感谢解答，明白了。',
        ];

        for ($i = 0; $i < $count; $i++) {
            $parentComment = $parentComments->random();

            Demo5Comment::factory()
                ->forPost($post)
                ->byUser($users->random())
                ->replyTo($parentComment)
                ->create([
                    'content' => $replies[array_rand($replies)],
                    'status' => CommentStatus::Approved->value,
                    'created_at' => $parentComment->created_at->addMinutes(rand(5, 120)),
                ]);
        }
    }
}