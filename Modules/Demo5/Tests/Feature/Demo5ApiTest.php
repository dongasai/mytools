<?php

namespace Modules\Demo5\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Demo5ApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试API接口基本功能
     */
    public function test_api_basic_functionality(): void
    {
        // 创建测试数据
        DB::table('demo5_users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 测试获取用户列表
        $response = $this->getJson('/api/demo5/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
    }

    /**
     * 测试创建用户API
     */
    public function test_create_user_api(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'status' => 1,
            'description' => 'Test user description',
        ];

        $response = $this->postJson('/api/demo5/users', $userData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'New User',
                'email' => 'newuser@example.com',
            ]);

        // 验证数据是否保存到数据库
        $this->assertDatabaseHas('demo5_users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * 测试创建用户验证失败
     */
    public function test_create_user_validation_failure(): void
    {
        $invalidData = [
            'name' => '', // 必填字段为空
            'email' => 'invalid-email', // 邮箱格式错误
            'status' => 99, // 无效状态
        ];

        $response = $this->postJson('/api/demo5/users', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'status']);
    }
}
