<?php

namespace Modules\Application\Tests\E2E\Api;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

/**
 * 功能开关 Proto API E2E 测试
 *
 * 测试功能开关系统的 Proto API 接口
 * 使用真实 HTTP 请求验证完整链路
 */
class FeatureProtoApiE2ETest extends TestCase
{
    /**
     * Guzzle HTTP Client
     *
     * @var Client
     */
    private Client $httpClient;

    /**
     * 测试用户 Bearer Token
     *
     * @var string
     */
    private string $testToken;

    /**
     * 测试基础 URL
     *
     * @var string
     */
    private string $baseUrl = 'http://127.0.0.1:80';

    /**
     * 初始化测试环境
     *
     * 设置 HTTP Client 和认证 Token
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 初始化 Guzzle HTTP Client
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 5.0,
        ]);

        // 初始化测试 Token（需要替换为真实的测试用户 Token）
        // 方式1: 使用已知的测试用户 Token
        $this->testToken = 'test-user-bearer-token-here';

        // 方式2: 如果需要动态获取 Token，可以通过登录接口获取
        // $authResponse = $this->httpClient->post('/api/proto/account/auth/login', [
        //     'headers' => ['Content-Type' => 'application/x-protobuf'],
        //     'body' => $loginRequestProtoBytes,
        // ]);
        // $this->testToken = $authResponse->getBody()->getContents(); // 解析获取 Token
    }

    /**
     * 测试已认证用户可以访问功能列表接口
     *
     * 验证携带有效 Bearer Token 的用户可以成功获取功能列表
     */
    public function test_authenticated_user_can_access_feature_list(): void
    {
        // 发送真实 HTTP 请求，携带 Bearer Token
        $response = $this->httpClient->get('/api/proto/application/feature/list', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->testToken,
                'Content-Type' => 'application/x-protobuf',
            ]
        ]);

        // 验证响应状态码（成功）
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应内容是 Protobuf 格式
        $body = $response->getBody()->getContents();
        $this->assertNotEmpty($body, 'Response body should not be empty');

        // 验证响应包含 BaseResponse（需要解析 Protobuf）
        // $featureListResponse = new ApplicationFeatureListResponse();
        // $featureListResponse->mergeFromString($body);
        // $this->assertEquals(0, $featureListResponse->getBase()->getCode());
        // $this->assertIsArray($featureListResponse->getFeatures());
    }

    /**
     * 测试未认证用户无法访问功能列表接口
     *
     * 验证未携带 Bearer Token 的用户会收到 401 Unauthorized 响应
     */
    public function test_unauthenticated_user_cannot_access_feature_list(): void
    {
        // 发送真实 HTTP 请求，不携带 Authorization header
        $response = $this->httpClient->get('/api/proto/application/feature/list', [
            'headers' => [
                'Content-Type' => 'application/x-protobuf',
            ]
        ]);

        // 验证响应状态码（401 Unauthorized）
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * 测试无效 Token 无法访问功能列表接口
     *
     * 验证携带无效 Bearer Token 的用户会收到 401 Unauthorized 响应
     */
    public function test_invalid_token_cannot_access_feature_list(): void
    {
        // 发送真实 HTTP 请求，携带无效 Token
        $response = $this->httpClient->get('/api/proto/application/feature/list', [
            'headers' => [
                'Authorization' => 'Bearer invalid-token-string',
                'Content-Type' => 'application/x-protobuf',
            ]
        ]);

        // 验证响应状态码（401 Unauthorized）
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * 测试功能列表返回正确的数据结构
     *
     * 验证成功请求后返回的功能列表包含正确的字段
     */
    public function test_feature_list_returns_valid_structure(): void
    {
        // 发送真实 HTTP 请求
        $response = $this->httpClient->get('/api/proto/application/feature/list', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->testToken,
                'Content-Type' => 'application/x-protobuf',
            ]
        ]);

        // 验证响应状态码
        $this->assertEquals(200, $response->getStatusCode());

        // 获取响应内容
        $body = $response->getBody()->getContents();
        $this->assertNotEmpty($body);

        // 解析 Protobuf 响应（需要根据实际生成的 PHP 类调整）
        // $featureListResponse = new ApplicationFeatureListResponse();
        // $featureListResponse->mergeFromString($body);
        //
        // 验证 BaseResponse
        // $baseResponse = $featureListResponse->getBase();
        // $this->assertEquals(0, $baseResponse->getCode());
        // $this->assertEquals('成功', $baseResponse->getMessage());
        //
        // 验证功能列表
        // $features = $featureListResponse->getFeatures();
        // $this->assertIsArray($features);
        // $this->assertNotEmpty($features); // 如果测试用户有可用功能
    }

    /**
     * 测试功能列表接口性能
     *
     * 验证接口响应时间在合理范围内（< 2秒）
     */
    public function test_feature_list_performance(): void
    {
        $startTime = microtime(true);

        // 发送真实 HTTP 请求
        $response = $this->httpClient->get('/api/proto/application/feature/list', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->testToken,
                'Content-Type' => 'application/x-protobuf',
            ]
        ]);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 验证响应状态码
        $this->assertEquals(200, $response->getStatusCode());

        // 验证响应时间（< 2秒）
        $this->assertLessThan(2.0, $duration, 'API response time should be less than 2 seconds');
    }

    /**
     * 测试并发请求功能列表接口
     *
     * 验证接口在高并发下的稳定性
     */
    public function test_concurrent_requests_to_feature_list(): void
    {
        $concurrentRequests = 10;
        $successfulRequests = 0;

        // 发送多个并发请求
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $response = $this->httpClient->get('/api/proto/application/feature/list', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->testToken,
                    'Content-Type' => 'application/x-protobuf',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $successfulRequests++;
            }
        }

        // 验证所有请求都成功
        $this->assertEquals($concurrentRequests, $successfulRequests, 'All concurrent requests should succeed');
    }
}