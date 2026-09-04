<?php

namespace Modules\Demo5\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Demo5\Enums\CommentStatus;
use Modules\Demo5\Models\Demo5Comment;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\Demo5User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Demo5\Models\Demo5Comment>
 */
class Demo5CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Demo5\Models\Demo5Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 获取已存在的用户和文章ID，避免创建新的数据
        $existingUserId = \Modules\Demo5\Models\Demo5User::inRandomOrder()->value('id') ?? 1;
        $existingPostId = \Modules\Demo5\Models\Demo5Post::inRandomOrder()->value('id') ?? 1;

        return [
            'content' => $this->faker->paragraph(rand(1, 5)),
            'status' => $this->faker->randomElement([
                CommentStatus::Pending->value,
                CommentStatus::Approved->value,
                CommentStatus::Rejected->value,
            ]),
            'post_id' => $existingPostId,
            'user_id' => $existingUserId,
            'parent_id' => $this->faker->optional(0.3)->randomDigitNotNull(), // 30% 概率为回复评论
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Pending->value,
        ]);
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Approved->value,
        ]);
    }

    /**
     * Indicate that the comment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Rejected->value,
        ]);
    }

    /**
     * Create a top-level comment (not a reply).
     */
    public function topLevel(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a reply comment.
     */
    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Demo5Comment::factory(),
        ]);
    }

    /**
     * Create a short comment.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create a long comment.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(5, true),
        ]);
    }

    /**
     * Create a comment for a specific post.
     */
    public function forPost(Demo5Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'post_id' => $post->id,
        ]);
    }

    /**
     * Create a comment by a specific user.
     */
    public function byUser(Demo5User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a reply to a specific comment.
     */
    public function replyTo(Demo5Comment $comment): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $comment->id,
            'post_id' => $comment->post_id,
        ]);
    }

    /**
     * Create a recent comment.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create an old comment.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }

    /**
     * Create a positive comment.
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '很好的文章！学到了很多。',
                '赞同作者的观点，写得很好。',
                '非常感谢分享，内容很有用。',
                '这篇文章解决了我长期困惑的问题。',
                '写得很详细，值得收藏学习。',
            ]),
        ]);
    }

    /**
     * Create a negative comment.
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '不太同意作者的看法。',
                '内容有些主观，缺乏客观分析。',
                '文章质量一般，需要改进。',
                '感觉文章写得不够深入。',
                '有些观点我持保留意见。',
            ]),
        ]);
    }
}
