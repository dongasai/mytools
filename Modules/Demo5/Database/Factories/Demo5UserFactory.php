<?php

namespace Modules\Demo5\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Demo5\Models\Demo5User;

/**
 * Demo5 用户工厂
 */
class Demo5UserFactory extends Factory
{
    protected $model = Demo5User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'avatar' => 'https://via.placeholder.com/150',
            'status' => fake()->randomElement(['active', 'inactive', 'banned']),
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}