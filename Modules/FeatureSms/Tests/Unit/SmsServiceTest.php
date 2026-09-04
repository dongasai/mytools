<?php

namespace Modules\FeatureSms\Tests\Unit;

use Carbon\Carbon;
use Modules\FeatureSms\Enums\CODE_TYPE;
use Modules\FeatureSms\Models\SmsCode;
use Modules\FeatureSms\Services\SmsService;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    protected SmsService $smsService;

    protected string $testPhone = '13800138000';

    protected string $testToken = 'test_token';

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = new SmsService;
    }

    public function test_send_code_successfully()
    {
        $result = $this->smsService->sendCode(CODE_TYPE::LOGIN, $this->testPhone, $this->testToken);
        $this->assertTrue($result);

        // 验证数据库中是否保存了验证码记录
        $smsCode = SmsCode::query()
            ->where('mobile', $this->testPhone)
            ->where('type', CODE_TYPE::LOGIN->value)
            ->orderByDesc('id')
            ->first();
        dump($smsCode->id);
        $this->assertNotNull($smsCode);
        $this->assertEquals($this->testPhone, $smsCode->mobile);
        $this->assertEquals($this->testToken, $smsCode->token);
        $this->assertEquals(CODE_TYPE::LOGIN->value, $smsCode->type);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $smsCode->code_value);
    }

    public function test_send_code_with_invalid_type()
    {
        $this->expectException(\TypeError::class);
        $this->smsService->sendCode(999, $this->testPhone, $this->testToken);
    }

    public function test_verify_code_with_invalid_params()
    {
        $result = $this->smsService->verifyCode(CODE_TYPE::REGISTER->value, '', '');
        $this->assertFalse($result);
    }

    public function test_verify_code_with_expired_code()
    {
        // 创建一个过期的验证码记录
        $expiredCode = new SmsCode;
        $expiredCode->mobile = $this->testPhone;
        $expiredCode->token = $this->testToken;
        $expiredCode->code_value = '123456';
        $expiredCode->type = CODE_TYPE::LOGIN->value;
        $expiredCode->created_at = Carbon::now()->subSeconds(SmsService::CODE_EXPIRE_TIME + 1);
        $expiredCode->save();

        $result = $this->smsService->verifyCode(CODE_TYPE::LOGIN->value, $this->testPhone, '123456');
        $this->assertFalse($result);

        // 验证过期的验证码记录是否被删除
        $this->assertDatabaseMissing('fsms_code', ['id' => $expiredCode->id]);
    }

    public function test_verify_code_with_invalid_code()
    {
        // 创建一个有效的验证码记录
        $validCode = new SmsCode;
        $validCode->mobile = $this->testPhone;
        $validCode->token = $this->testToken;
        $validCode->code_value = '123456';
        $validCode->type = CODE_TYPE::LOGIN->value;
        $validCode->created_at = Carbon::now();
        $validCode->save();

        $result = $this->smsService->verifyCode(CODE_TYPE::LOGIN->value, $this->testPhone, '654321');
        $this->assertFalse($result);

        // 验证错误的验证码不会删除记录
        $this->assertDatabaseHas('fsms_code', ['id' => $validCode->id]);
    }

    public function test_verify_code_with_valid_code()
    {
        // 创建一个有效的验证码记录
        $validCode = new SmsCode;
        $validCode->mobile = $this->testPhone;
        $validCode->token = $this->testToken;
        $validCode->code_value = '123456';
        $validCode->type = CODE_TYPE::LOGIN->value;
        $validCode->created_at = Carbon::now();
        $validCode->save();

        $result = $this->smsService->verifyCode(CODE_TYPE::LOGIN->value, $this->testPhone, '123456');
        $this->assertTrue($result);

        // 验证成功后记录应该被删除
        $this->assertDatabaseMissing('fsms_code', ['id' => $validCode->id]);
    }
}
