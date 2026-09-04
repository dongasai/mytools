<?php

namespace Modules\Demo5\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\User;
use Tests\TestCase;

/**
 * 文章 Proto API e2e 测试
 *
 * 测试所有 Proto API 端点：
 * - POST /api/demo5-proto/posts/list
 * - POST /api/demo5-proto/posts/get
 * - POST /api/demo5-proto/posts/create
 * - POST /api/demo5-proto/posts/update
 * - POST /api/demo5-proto/posts/delete
 */
class PostProtoApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ========== 文章列表API测试 ==========

    /**
     * 测试空数据列表
     */
    public function test_list_posts_success_empty_data(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/list', [
            'page' => 1,
            'page_size' => 20,
        ], ['Content-Type' => 'application/json']);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 0,
                'message' => '文章列表获取成功',
            ])
            ->assertJsonStructure([
                'code',
                'message',
                'data',
                'pagination' => ['page', 'page_size', 'total'],
            ])
            ->assertJsonPath('pagination.total', 0);
    }

    /**
     * 测试有数据的列表
     */
    public function test_list_posts_with_data(): void
    {
        Demo5Post::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/demo5-proto/posts/list', [
            'page' => 1,
            'page_size' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('pagination.total', 3);
    }

    /**
     * 测试分页参数
     */
    public function test_list_posts_pagination(): void
    {
        Demo5Post::factory()->count(25)->create(['user_id' => $this->user->id]);

        // 第一页
        $response = $this->postJson('/api/demo5-proto/posts/list', [
            'page' => 1,
            'page_size' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.total', 25);

        // 第二页
        $response = $this->postJson('/api/demo5-proto/posts/list', [
            'page' => 2,
            'page_size' => 10,
        ]);

        $response->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.page', 2);
    }

    /**
     * 测试状态筛选
     */
    public function test_list_posts_status_filter(): void
    {
        Demo5Post::factory()->count(2)->draft()->create(['user_id' => $this->user->id]);
        Demo5Post::factory()->count(3)->published()->create(['user_id' => $this->user->id]);
        Demo5Post::factory()->count(1)->archived()->create(['user_id' => $this->user->id]);

        // 筛选已发布
        $response = $this->postJson('/api/demo5-proto/posts/list', [
            'status' => 'published',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('pagination.total', 3);
    }

    // ========== 文章详情API测试 ==========

    /**
     * 测试成功获取文章详情
     */
    public function test_get_post_success(): void
    {
        $post = Demo5Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/demo5-proto/posts/get', [
            'id' => $post->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 0,
                'message' => '文章详情获取成功',
            ])
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonPath('data.content', $post->content)
            ->assertJsonPath('data.status', $post->status)
            ->assertJsonPath('data.user_id', $post->user_id);
    }

    /**
     * 测试时间戳字段为整数
     */
    public function test_get_post_timestamp_fields(): void
    {
        $post = Demo5Post::factory()->published()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/demo5-proto/posts/get', [
            'id' => $post->id,
        ]);

        $data = $response->json('data');

        // 验证时间戳字段为整数
        $this->assertIsInt($data['created_at']);
        $this->assertIsInt($data['updated_at']);
        $this->assertIsInt($data['published_at']);

        // 验证数值合理
        $this->assertGreaterThan(0, $data['created_at']);
        $this->assertGreaterThan(0, $data['updated_at']);
        $this->assertGreaterThan(0, $data['published_at']);
    }

    /**
     * 测试文章不存在
     */
    public function test_get_post_not_found(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/get', [
            'id' => 99999,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 404,
                'message' => '文章不存在',
            ]);
    }

    /**
     * 测试无效ID（负数）
     */
    public function test_get_post_invalid_id_negative(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/get', [
            'id' => -1,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 测试无效ID（字符串）
     */
    public function test_get_post_invalid_id_string(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/get', [
            'id' => 'invalid',
        ]);

        // Proto解析失败或转换失败
        $response->assertStatus(500);
    }

    // ========== 创建文章API测试 ==========

    /**
     * 测试成功创建文章（完整数据）
     */
    public function test_create_post_success(): void
    {
        $postData = [
            'title' => '新文章标题',
            'content' => '这是新文章的内容',
            'status' => 'draft',
            'user_id' => $this->user->id,
            'published_at' => 0,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(201)
            ->assertJson([
                'code' => 0,
                'message' => '文章创建成功',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'content',
                    'status',
                    'user_id',
                    'word_count',
                ],
            ]);

        $this->assertDatabaseHas('demo5_posts', [
            'title' => '新文章标题',
            'content' => '这是新文章的内容',
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * 测试创建已发布文章（自动设置published_at）
     */
    public function test_create_post_with_published_status(): void
    {
        $postData = [
            'title' => '已发布文章',
            'content' => '内容',
            'status' => 'published',
            'user_id' => $this->user->id,
            'published_at' => 0,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('demo5_posts', [
            'title' => '已发布文章',
            'status' => 'published',
        ]);

        // 验证published_at已自动设置
        $post = Demo5Post::where('title', '已发布文章')->first();
        $this->assertNotNull($post->published_at);
    }

    /**
     * 测试最小必填字段
     */
    public function test_create_post_minimal_fields(): void
    {
        $postData = [
            'title' => '最小文章',
            'content' => '内容',
            'user_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(201)
            ->assertJson(['code' => 0]);

        $this->assertDatabaseHas('demo5_posts', [
            'title' => '最小文章',
        ]);
    }

    /**
     * 测试缺少必填字段title
     */
    public function test_create_post_missing_title(): void
    {
        $postData = [
            'content' => '内容',
            'user_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(422);
    }

    /**
     * 测试缺少必填字段content
     */
    public function test_create_post_missing_content(): void
    {
        $postData = [
            'title' => '标题',
            'user_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(422);
    }

    /**
     * 测试缺少必填字段user_id
     */
    public function test_create_post_missing_user_id(): void
    {
        $postData = [
            'title' => '标题',
            'content' => '内容',
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(422);
    }

    /**
     * 测试空标题
     */
    public function test_create_post_empty_title(): void
    {
        $postData = [
            'title' => '',
            'content' => '内容',
            'user_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(422);
    }

    /**
     * 测试无效状态值
     */
    public function test_create_post_invalid_status(): void
    {
        $postData = [
            'title' => '标题',
            'content' => '内容',
            'status' => 'invalid_status',
            'user_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/demo5-proto/posts/create', $postData);

        $response->assertStatus(422);
    }

    // ========== 更新文章API测试 ==========

    /**
     * 测试成功更新所有字段
     */
    public function test_update_post_success(): void
    {
        $post = Demo5Post::factory()->draft()->create(['user_id' => $this->user->id]);

        $updateData = [
            'id' => $post->id,
            'title' => '更新后的标题',
            'content' => '更新后的内容',
            'status' => 'published',
            'published_at' => time(),
        ];

        $response = $this->postJson('/api/demo5-proto/posts/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 0,
                'message' => '文章更新成功',
            ])
            ->assertJsonPath('data.title', '更新后的标题')
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('demo5_posts', [
            'id' => $post->id,
            'title' => '更新后的标题',
            'status' => 'published',
        ]);
    }

    /**
     * 测试部分字段更新（仅title）
     */
    public function test_update_post_partial_fields(): void
    {
        $post = Demo5Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => '原标题',
            'content' => '原内容',
        ]);

        $response = $this->postJson('/api/demo5-proto/posts/update', [
            'id' => $post->id,
            'title' => '仅更新标题',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', '仅更新标题')
            ->assertJsonPath('data.content', '原内容'); // 内容未变

        $this->assertDatabaseHas('demo5_posts', [
            'id' => $post->id,
            'title' => '仅更新标题',
            'content' => '原内容',
        ]);
    }

    /**
     * 测试状态变更（draft→published）
     */
    public function test_update_post_status_change(): void
    {
        $post = Demo5Post::factory()->draft()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/demo5-proto/posts/update', [
            'id' => $post->id,
            'status' => 'published',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('demo5_posts', [
            'id' => $post->id,
            'status' => 'published',
        ]);
    }

    /**
     * 测试文章不存在
     */
    public function test_update_post_not_found(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/update', [
            'id' => 99999,
            'title' => '更新标题',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 404,
                'message' => '文章不存在',
            ]);
    }

    /**
     * 测试缺少id字段
     */
    public function test_update_post_missing_id(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/update', [
            'title' => '更新标题',
        ]);

        $response->assertStatus(422);
    }

    /**
     * 测试更新为空标题
     */
    public function test_update_post_empty_title(): void
    {
        $post = Demo5Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/demo5-proto/posts/update', [
            'id' => $post->id,
            'title' => '',
        ]);

        $response->assertStatus(422);
    }

    // ========== 删除文章API测试 ==========

    /**
     * 测试成功删除文章
     */
    public function test_delete_post_success(): void
    {
        $post = Demo5Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/demo5-proto/posts/delete', [
            'id' => $post->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 0,
                'message' => '文章删除成功',
            ]);

        $this->assertDatabaseMissing('demo5_posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * 测试删除后数据库验证
     */
    public function test_delete_post_verify_database(): void
    {
        $post = Demo5Post::factory()->create(['user_id' => $this->user->id]);
        $postId = $post->id;

        $this->postJson('/api/demo5-proto/posts/delete', ['id' => $postId]);

        // 多次验证确保真的删除了
        $this->assertDatabaseMissing('demo5_posts', ['id' => $postId]);
        $this->assertNull(Demo5Post::find($postId));
    }

    /**
     * 测试删除不存在的文章
     */
    public function test_delete_post_not_found(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/delete', [
            'id' => 99999,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 404,
                'message' => '文章不存在',
            ]);
    }

    /**
     * 测试无效ID（负数）
     */
    public function test_delete_post_invalid_id(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/delete', [
            'id' => -1,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 测试缺少id字段
     */
    public function test_delete_post_missing_id(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/delete', []);

        $response->assertStatus(422);
    }

    // ========== Proto中间件验证 ==========

    /**
     * 测试Content-Type必须为application/json
     */
    public function test_content_type_validation(): void
    {
        $response = $this->post('/api/demo5-proto/posts/list', [
            'page' => 1,
        ], ['Content-Type' => 'text/plain']);

        // 应返回415 Unsupported Media Type
        $response->assertStatus(415);
    }

    /**
     * 测试请求体必须为有效JSON
     */
    public function test_invalid_json_body(): void
    {
        $response = $this->postJson('/api/demo5-proto/posts/create', 'invalid json string');

        $response->assertStatus(400);
    }
}