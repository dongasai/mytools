<?php

namespace Modules\Demo5\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Demo5\Models\Demo5Post;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Demo5\Models\Demo5Post>
 */
class Demo5PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Demo5\Models\Demo5Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 使用固定的默认用户ID，测试时通过 create(['user_id' => xxx]) 传入实际值
        $defaultUserId = 1;

        return [
            'title' => $this->faker->sentence(rand(3, 8)),
            'content' => $this->faker->paragraphs(rand(3, 8), true),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'user_id' => $defaultUserId,
            'published_at' => fn(array $attributes) => $attributes['status'] === 'published'
                ? $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now')
                : null,
        ];
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the post is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'published_at' => $this->faker->optional(0.6)->dateTimeBetween('-2 years', '-1 month'),
        ]);
    }

    /**
     * Indicate that the post is scheduled for future publication.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Create a post with short content.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraph(2),
        ]);
    }

    /**
     * Create a post with long content.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(15, true),
        ]);
    }

    /**
     * Create a post with a specific author.
     *
     * @param mixed $user 用户对象或用户ID
     */
    public function byUser($user): static
    {
        $userId = is_object($user) ? $user->id : $user;

        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Create a post published recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a post published long ago.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }
}
