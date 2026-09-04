<?php

namespace Modules\Application\Tests\E2E\Admin;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

/**
 * 功能开关后台管理 E2E 测试
 *
 * 测试 Dcat Admin 后台功能开关管理界面
 * 使用真实 HTTP 请求验证完整链路
 */
class FeatureAdminControllerE2ETest extends TestCase
{
    /**
     * Guzzle HTTP Client
     *
     * @var Client
     */
    private Client $httpClient;

    /**
     * 管理员登录 Cookie
     *
     * @var string
     */
    private string $adminCookie;

    /**
     * 测试基础 URL
     *
     * @var string
     */
    private string $baseUrl = 'http://127.0.0.1:80';

    /**
     * 初始化测试环境
     *
     * 设置 HTTP Client 和管理员登录状态
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 初始化 Guzzle HTTP Client
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 5.0,
            'cookies' => true, // 启用 Cookie 支持（用于 Dcat Admin Session）
        ]);

        // 初始化管理员登录 Cookie（需要先登录后台获取）
        // 方式1: 使用已知的测试管理员 Session Cookie
        $this->adminCookie = 'admin-session-cookie-here';

        // 方式2: 通过登录接口获取 Session Cookie
        // $loginResponse = $this->httpClient->post('/admin/login', [
        //     'form_params' => [
        //         'username' => 'admin',
        //         'password' => 'admin',
        //     ]
        // ]);
        // $this->adminCookie = $loginResponse->getHeaderLine('Set-Cookie');
    }

    /**
     * 测试管理员可以访问功能列表页面
     *
     * 验证管理员登录后可以访问功能管理列表页面
     */
    public function test_admin_can_access_feature_list_page(): void
    {
        // 发送真实 HTTP 请求，携带管理员 Cookie
        $response = $this->httpClient->get('/admin/module_application/features', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Accept' => 'text/html,application/xhtml+xml',
            ]
        ]);

        // 验证响应状态码（成功）
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应内容包含功能管理界面
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString('功能管理', $body);
        $this->assertStringContainsString('功能名称', $body);
        $this->assertStringContainsString('功能标识', $body);
    }

    /**
     * 测试未登录用户无法访问后台页面
     *
     * 验证未登录用户会重定向到登录页面
     */
    public function test_unauthenticated_user_cannot_access_admin_page(): void
    {
        // 发送真实 HTTP 请求，不携带 Cookie
        $response = $this->httpClient->get('/admin/module_application/features', [
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml',
            ],
            'allow_redirects' => false, // 禁止自动重定向
        ]);

        // 验证响应状态码（302 重定向到登录页）
        $this->assertEquals(302, $response->getStatusCode());

        // 验证重定向到登录页
        $location = $response->getHeaderLine('Location');
        $this->assertStringContainsString('/admin/login', $location);
    }

    /**
     * 测试管理员可以创建新功能
     *
     * 验证管理员可以通过后台界面创建新功能
     */
    public function test_admin_can_create_new_feature(): void
    {
        // 发送真实 HTTP POST 请求，创建新功能
        $response = $this->httpClient->post('/admin/module_application/features', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'name' => '测试功能',
                'key' => 'test_feature_e2e',
                'description' => 'E2E 测试创建的功能',
                'is_enabled' => 1,
                'percentage' => 0,
                'group' => '实验性',
            ]
        ]);

        // 验证响应状态码（成功或重定向）
        $statusCode = $response->getStatusCode();
        $this->assertTrue($statusCode === 200 || $statusCode === 302);

        // 如果是重定向，验证重定向到列表页
        if ($statusCode === 302) {
            $location = $response->getHeaderLine('Location');
            $this->assertStringContainsString('/admin/module_application/features', $location);
        }
    }

    /**
     * 测试管理员可以查看功能详情
     *
     * 验证管理员可以查看单个功能的详细信息
     */
    public function test_admin_can_view_feature_detail(): void
    {
        // 发送真实 HTTP 请求，查看功能详情（假设功能 ID 为 1）
        $response = $this->httpClient->get('/admin/module_application/features/1', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Accept' => 'text/html,application/xhtml+xml',
            ]
        ]);

        // 验证响应状态码（成功）
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应内容包含功能详情
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString('ID', $body);
        $this->assertStringContainsString('功能名称', $body);
        $this->assertStringContainsString('功能标识', $body);
    }

    /**
     * 测试管理员可以更新功能设置
     *
     * 验证管理员可以修改功能的灰度百分比和分组
     */
    public function test_admin_can_update_feature_settings(): void
    {
        // 发送真实 HTTP PUT 请求，更新功能设置（假设功能 ID 为 1）
        $response = $this->httpClient->put('/admin/module_application/features/1', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'name' => '更新后的功能名称',
                'percentage' => 50, // 设置灰度百分比
                'group' => '创作类',
                'is_enabled' => 1,
            ]
        ]);

        // 验证响应状态码（成功或重定向）
        $statusCode = $response->getStatusCode();
        $this->assertTrue($statusCode === 200 || $statusCode === 302);
    }

    /**
     * 测试管理员可以删除功能
     *
     * 验证管理员可以删除不需要的功能
     */
    public function test_admin_can_delete_feature(): void
    {
        // 发送真实 HTTP DELETE 请求，删除功能（假设功能 ID 为 999）
        $response = $this->httpClient->delete('/admin/module_application/features/999', [
            'headers' => [
                'Cookie' => $this->adminCookie,
            ]
        ]);

        // 验证响应状态码（成功或重定向）
        $statusCode = $response->getStatusCode();
        $this->assertTrue($statusCode === 200 || $statusCode === 302);
    }

    /**
     * 测试管理员可以访问白名单管理页面
     *
     * 验证管理员可以管理功能的白名单用户
     */
    public function test_admin_can_access_whitelist_page(): void
    {
        // 发送真实 HTTP 请求，访问白名单页面（假设功能 ID 为 1）
        $response = $this->httpClient->get('/admin/module_application/features/1/whitelist', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Accept' => 'text/html,application/xhtml+xml',
            ]
        ]);

        // 验证响应状态码（成功）
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应内容包含白名单管理界面
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString('白名单', $body);
        $this->assertStringContainsString('用户ID', $body);
    }

    /**
     * 测试管理员可以添加用户到白名单
     *
     * 验证管理员可以通过后台界面添加白名单用户
     */
    public function test_admin_can_add_user_to_whitelist(): void
    {
        // 发送真实 HTTP POST 请求，添加用户到白名单（假设功能 ID 为 1）
        $response = $this->httpClient->post('/admin/module_application/features/1/whitelist', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'user_id' => 1, // 测试用户 ID
                'reason' => 'E2E 测试添加',
            ]
        ]);

        // 验证响应状态码（成功或重定向）
        $statusCode = $response->getStatusCode();
        $this->assertTrue($statusCode === 200 || $statusCode === 302);
    }

    /**
     * 测试管理员可以访问黑名单管理页面
     *
     * 验证管理员可以管理功能的黑名单用户
     */
    public function test_admin_can_access_blacklist_page(): void
    {
        // 发送真实 HTTP 请求，访问黑名单页面（假设功能 ID 为 1）
        $response = $this->httpClient->get('/admin/module_application/features/1/blacklist', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Accept' => 'text/html,application/xhtml+xml',
            ]
        ]);

        // 验证响应状态码（成功）
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应内容包含黑名单管理界面
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString('黑名单', $body);
        $this->assertStringContainsString('用户ID', $body);
    }

    /**
     * 测试管理员可以添加用户到黑名单
     *
     * 验证管理员可以通过后台界面添加黑名单用户
     */
    public function test_admin_can_add_user_to_blacklist(): void
    {
        // 发送真实 HTTP POST 请求，添加用户到黑名单（假设功能 ID 为 1）
        $response = $this->httpClient->post('/admin/module_application/features/1/blacklist', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'user_id' => 2, // 测试用户 ID
                'reason' => 'E2E 测试添加',
            ]
        ]);

        // 验证响应状态码（成功或重定向）
        $statusCode = $response->getStatusCode();
        $this->assertTrue($statusCode === 200 || $statusCode === 302);
    }

    /**
     * 测试后台界面性能
     *
     * 验证后台页面加载时间在合理范围内（< 3秒）
     */
    public function test_admin_page_performance(): void
    {
        $startTime = microtime(true);

        // 发送真实 HTTP 请求
        $response = $this->httpClient->get('/admin/module_application/features', [
            'headers' => [
                'Cookie' => $this->adminCookie,
                'Accept' => 'text/html,application/xhtml+xml',
            ]
        ]);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 验证响应状态码
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应时间（< 3秒）
        $this->assertLessThan(3.0, $duration, 'Admin page load time should be less than 3 seconds');
    }
}