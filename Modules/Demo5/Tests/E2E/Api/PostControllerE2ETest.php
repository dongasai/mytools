<?php

namespace Modules\Demo5\Tests\E2E\Api;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Post API E2E 测试
 *
 * 使用真实 HTTP 请求测试文章 CRUD 接口
 * 测试基础 URL: http://127.0.0.1:80
 */
class PostControllerE2ETest extends TestCase
{
    /**
     * HTTP 客户端实例
     *
     * @var Client
     */
    private Client $httpClient;

    /**
     * 测试用的文章 ID（在测试过程中创建）
     *
     * @var int|null
     */
    private ?int $testPostId = null;

    /**
     * 设置测试环境
     *
     * 初始化 HTTP 客户端
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:80',
            'timeout' => 5.0,
            'http_errors' => false, // 不自动抛出异常
        ]);
    }

    /**
     * 清理测试数据
     *
     * 删除测试过程中创建的文章
     */
    protected function tearDown(): void
    {
        if ($this->testPostId) {
            try {
                $this->httpClient->delete("/api/demo5/posts/{$this->testPostId}");
            } catch (\Exception $e) {
                // 忽略删除错误
            }
        }

        parent::tearDown();
    }

    // ==================== CRUD 测试 ====================

    /**
     * 测试文章列表接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_list_posts(): void
    {
        $response = $this->httpClient->get('/api/demo5/posts');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('current_page', $data['meta']);
        $this->assertArrayHasKey('total', $data['meta']);
    }

    /**
     * 测试创建文章接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_create_post(): void
    {
        $postData = [
            'title' => 'E2E 测试文章标题 ' . time(),
            'content' => '这是 E2E 测试文章的内容，使用真实 HTTP 请求测试。',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $response = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($postData['title'], $data['title']);
        $this->assertEquals($postData['content'], $data['content']);
        $this->assertEquals($postData['status'], $data['status']);
        $this->assertEquals($postData['user_id'], $data['user_id']);

        // 保存文章 ID 用于后续清理
        $this->testPostId = $data['id'];
    }

    /**
     * 测试查询单篇文章接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_show_post(): void
    {
        // 先创建一篇文章
        $postData = [
            'title' => 'E2E 测试文章 - 用于查询测试',
            'content' => '这是用于查询测试的文章内容。',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $createResponse = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        $postId = $createData['id'];

        // 查询文章
        $response = $this->httpClient->get("/api/demo5/posts/{$postId}");

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertEquals($postId, $data['id']);
        $this->assertEquals($postData['title'], $data['title']);
        $this->assertEquals($postData['content'], $data['content']);

        // 清理
        $this->httpClient->delete("/api/demo5/posts/{$postId}");
    }

    /**
     * 测试更新文章接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_update_post(): void
    {
        // 先创建一篇文章
        $postData = [
            'title' => 'E2E 测试文章 - 用于更新测试',
            'content' => '原始内容',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $createResponse = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        $postId = $createData['id'];

        // 更新文章
        $updateData = [
            'title' => 'E2E 测试文章 - 已更新',
            'content' => '更新后的内容',
            'status' => 'published',
        ];

        $response = $this->httpClient->put("/api/demo5/posts/{$postId}", [
            'json' => $updateData,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertEquals($postId, $data['id']);
        $this->assertEquals($updateData['title'], $data['title']);
        $this->assertEquals($updateData['content'], $data['content']);
        $this->assertEquals($updateData['status'], $data['status']);
        $this->assertNotNull($data['published_at']);

        // 清理
        $this->httpClient->delete("/api/demo5/posts/{$postId}");
    }

    /**
     * 测试删除文章接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_delete_post(): void
    {
        // 先创建一篇文章
        $postData = [
            'title' => 'E2E 测试文章 - 用于删除测试',
            'content' => '这篇文章即将被删除。',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $createResponse = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        $postId = $createData['id'];

        // 删除文章
        $response = $this->httpClient->delete("/api/demo5/posts/{$postId}");

        $this->assertEquals(204, $response->getStatusCode());

        // 验证文章已被删除
        $showResponse = $this->httpClient->get("/api/demo5/posts/{$postId}");
        $this->assertEquals(404, $showResponse->getStatusCode());
    }

    // ==================== 参数验证测试 ====================

    /**
     * 测试创建文章时缺少必填字段 - E2E 真实请求
     *
     * @test
     */
    public function test_validation_error_on_missing_required_fields(): void
    {
        $postData = [
            'title' => '只有标题，缺少内容和用户ID',
        ];

        $response = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('errors', $data);
    }

    /**
     * 测试创建文章时提供无效的状态值 - E2E 真实请求
     *
     * @test
     */
    public function test_validation_error_on_invalid_status(): void
    {
        $postData = [
            'title' => 'E2E 测试文章 - 无效状态',
            'content' => '这是测试内容。',
            'user_id' => 1,
            'status' => 'invalid_status', // 无效的状态值
        ];

        $response = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('errors', $data);
    }

    /**
     * 测试更新文章时提供空标题 - E2E 真实请求
     *
     * @test
     */
    public function test_validation_error_on_empty_title(): void
    {
        // 先创建一篇文章
        $postData = [
            'title' => 'E2E 测试文章 - 用于更新验证测试',
            'content' => '原始内容',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $createResponse = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        $postId = $createData['id'];

        // 尝试更新为空标题
        $updateData = [
            'title' => '', // 空标题
        ];

        $response = $this->httpClient->put("/api/demo5/posts/{$postId}", [
            'json' => $updateData,
        ]);

        $this->assertEquals(422, $response->getStatusCode());

        // 清理
        $this->httpClient->delete("/api/demo5/posts/{$postId}");
    }

    // ==================== 错误处理测试 ====================

    /**
     * 测试查询不存在的文章返回 404 - E2E 真实请求
     *
     * @test
     */
    public function test_404_on_nonexistent_post(): void
    {
        $response = $this->httpClient->get('/api/demo5/posts/999999');

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * 测试更新不存在的文章返回 404 - E2E 真实请求
     *
     * @test
     */
    public function test_404_on_update_nonexistent_post(): void
    {
        $updateData = [
            'title' => '不存在的文章',
        ];

        $response = $this->httpClient->put('/api/demo5/posts/999999', [
            'json' => $updateData,
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * 测试删除不存在的文章返回 404 - E2E 真实请求
     *
     * @test
     */
    public function test_404_on_delete_nonexistent_post(): void
    {
        $response = $this->httpClient->delete('/api/demo5/posts/999999');

        $this->assertEquals(404, $response->getStatusCode());
    }

    // ==================== 额外功能测试 ====================

    /**
     * 测试按状态查询文章接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_list_posts_by_status(): void
    {
        $response = $this->httpClient->get('/api/demo5/posts/status/draft');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);

        // 验证所有返回的文章状态都是 draft
        foreach ($data['data'] as $post) {
            $this->assertEquals('draft', $post['status']);
        }
    }

    /**
     * 测试搜索文章接口 - E2E 真实请求
     *
     * @test
     */
    public function test_can_search_posts(): void
    {
        // 先创建一篇包含特定关键词的文章
        $postData = [
            'title' => 'E2E 搜索测试文章 - 特定关键词 UNIQUE_KEYWORD_' . time(),
            'content' => '这篇文章包含特定的搜索关键词。',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $createResponse = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        $postId = $createData['id'];

        // 搜索文章
        $response = $this->httpClient->get('/api/demo5/posts/search?q=UNIQUE_KEYWORD');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);

        // 清理
        $this->httpClient->delete("/api/demo5/posts/{$postId}");
    }

    /**
     * 测试搜索文章时缺少关键词 - E2E 真实请求
     *
     * @test
     */
    public function test_search_without_keyword_returns_error(): void
    {
        $response = $this->httpClient->get('/api/demo5/posts/search');

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * 测试文章状态从草稿变为已发布时自动设置发布时间 - E2E 真实请求
     *
     * @test
     */
    public function test_published_at_is_set_when_status_changes_to_published(): void
    {
        // 创建草稿文章
        $postData = [
            'title' => 'E2E 测试文章 - 发布时间测试',
            'content' => '测试发布时间自动设置。',
            'user_id' => 1,
            'status' => 'draft',
        ];

        $createResponse = $this->httpClient->post('/api/demo5/posts', [
            'json' => $postData,
        ]);

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        $postId = $createData['id'];

        // 验证草稿状态时 published_at 为 null
        $this->assertNull($createData['published_at']);

        // 更新为已发布状态
        $updateData = [
            'status' => 'published',
        ];

        $updateResponse = $this->httpClient->put("/api/demo5/posts/{$postId}", [
            'json' => $updateData,
        ]);

        $updateData = json_decode($updateResponse->getBody()->getContents(), true);

        // 验证发布时间已设置
        $this->assertNotNull($updateData['published_at']);

        // 清理
        $this->httpClient->delete("/api/demo5/posts/{$postId}");
    }

    /**
     * 测试文章列表分页功能 - E2E 真实请求
     *
     * @test
     */
    public function test_posts_pagination(): void
    {
        $response = $this->httpClient->get('/api/demo5/posts');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('current_page', $data['meta']);
        $this->assertArrayHasKey('per_page', $data['meta']);
        $this->assertArrayHasKey('total', $data['meta']);
        $this->assertEquals(15, $data['meta']['per_page']);
    }
}