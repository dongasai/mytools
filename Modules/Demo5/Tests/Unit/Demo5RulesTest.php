<?php

namespace Modules\Demo5\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Modules\Demo5\Rules\ChinesePhoneNumber;
use Modules\Demo5\Rules\StrongPassword;
use Modules\Demo5\Rules\UniqueCaseInsensitive;
use Tests\TestCase;

class Demo5RulesTest extends TestCase
{
    /**
     * 测试中国手机号验证规则
     */
    public function test_chinese_phone_number_rule(): void
    {
        $rule = new ChinesePhoneNumber;

        // 有效的手机号
        $validPhones = [
            '13812345678',
            '15987654321',
            '18611112222',
            '19912345678',
        ];

        foreach ($validPhones as $phone) {
            $failCalled = false;
            $rule->validate('phone', $phone, function () use (&$failCalled) {
                $failCalled = true;
            });
            $this->assertFalse($failCalled, "Phone {$phone} should be valid");
        }

        // 无效的手机号
        $invalidPhones = [
            '12345678901', // 不是有效的开头
            '1381234567',  // 位数不够
            '138123456789', // 位数过多
            '10812345678', // 无效开头
            'abc12345678', // 包含字母
            '17412345678',  // 卫星通信号段
        ];

        foreach ($invalidPhones as $phone) {
            $failCalled = false;
            $rule->validate('phone', $phone, function () use (&$failCalled) {
                $failCalled = true;
            });
            $this->assertTrue($failCalled, "Phone {$phone} should be invalid");
        }
    }

    /**
     * 测试强密码验证规则
     */
    public function test_strong_password_rule(): void
    {
        $rule = new StrongPassword;

        // 有效的强密码
        $validPasswords = [
            'MyPassword123!',
            'Strong@Pass456',
            'Complex$789Pwd',
            'Test#Password1',
        ];

        foreach ($validPasswords as $password) {
            $failCalled = false;
            $rule->validate('password', $password, function () use (&$failCalled) {
                $failCalled = true;
            });
            $this->assertFalse($failCalled, "Password should be valid: {$password}");
        }

        // 无效的弱密码
        $invalidPasswords = [
            'password',     // 常见弱密码
            '123456',       // 纯数字
            'mypassword',   // 没有大写字母和数字
            'MYPASSWORD',   // 没有小写字母和数字
            'MyPassword',   // 没有数字和特殊字符
            '12345678',     // 没有字母
            'MyPass1',      // 长度不够
            'MyPassword123', // 没有特殊字符
        ];

        foreach ($invalidPasswords as $password) {
            $failCalled = false;
            $rule->validate('password', $password, function () use (&$failCalled) {
                $failCalled = true;
            });
            $this->assertTrue($failCalled, "Password should be invalid: {$password}");
        }
    }

    /**
     * 测试大小写不敏感唯一验证规则
     */
    public function test_unique_case_insensitive_rule(): void
    {
        // 创建测试表和数据
        DB::table('demo5_users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rule = new UniqueCaseInsensitive('demo5_users', 'email');

        // 测试相同邮箱但不同大小写（应该失败）
        $failCalled = false;
        $rule->validate('email', 'TEST@EXAMPLE.COM', function () use (&$failCalled) {
            $failCalled = true;
        });
        $this->assertTrue($failCalled, 'Same email with different case should fail');

        // 测试不同的邮箱（应该通过）
        $failCalled = false;
        $rule->validate('email', 'different@example.com', function () use (&$failCalled) {
            $failCalled = true;
        });
        $this->assertFalse($failCalled, 'Different email should pass');

        // 测试忽略指定ID的情况
        $testUser = DB::table('demo5_users')->where('email', 'test@example.com')->first();
        $ruleWithIgnore = new UniqueCaseInsensitive('demo5_users', 'email', $testUser->id);

        $failCalled = false;
        $ruleWithIgnore->validate('email', 'test@example.com', function () use (&$failCalled) {
            $failCalled = true;
        });
        $this->assertFalse($failCalled, 'Same email should pass when ignoring the record');
    }
}
