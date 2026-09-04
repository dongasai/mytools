<?php

namespace Modules\FeatureSms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FeatureSms\Enums\CODE_TYPE;
use Tests\TestCase;

/**
 * 短信 API 功能测试
 */
class SmsApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试发送验证码
     */
    public function test_send_sms_code(): void
    {
        $response = $this->postJson('/api/feature-sms/send-code', [
            'phone' => '13800138000',
            'type' => CODE_TYPE::LOGIN->value,
            'token' => 'test-token-123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    /**
     * 测试验证码验证
     */
    public function test_verify_sms_code(): void
    {
        $response = $this->postJson('/api/feature-sms/verify-code', [
            'phone' => '13800138000',
            'code' => '123456',
            'type' => CODE_TYPE::LOGIN->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    /**
     * 测试无效手机号
     */
    public function test_invalid_phone_number(): void
    {
        $response = $this->postJson('/api/feature-sms/send-code', [
            'phone' => '123456',
            'type' => CODE_TYPE::LOGIN->value,
            'token' => 'test-token-123',
        ]);

        $response->assertStatus(422);
    }
}
