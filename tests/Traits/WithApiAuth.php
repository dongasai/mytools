<?php

namespace Tests\Traits;

use Modules\Account\Models\Account;
use Laravel\Sanctum\Sanctum;

/**
 * API认证测试Trait
 *
 * 提供创建认证用户和生成 Sanctum token 的辅助方法
 */
trait WithApiAuth
{
    /**
     * 创建认证用户并返回带 Authorization header 的测试客户端
     *
     * @param array $attributes 账户属性覆盖
     * @return array ['account' => Account, 'user' => User, 'token' => string, 'headers' => array]
     */
    protected function createAuthenticatedUser(array $attributes = []): array
    {
        $account = $this->createAccount($attributes);

        // 创建关联的 User 记录（用于业务逻辑）
        $user = \Modules\User\Models\User::create([
            'account_id' => $account->id,
            'nickname' => 'test_user_' . uniqid(),
            'use_status' => 1,
        ]);

        // 创建 Sanctum token
        $token = $account->createToken('test-token')->plainTextToken;

        // 构建带认证头的数组
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        return [
            'account' => $account,
            'user' => $user,
            'token' => $token,
            'headers' => $headers,
        ];
    }

    /**
     * 创建账户（无认证）
     *
     * @param array $attributes 账户属性覆盖
     * @return Account
     */
    protected function createAccount(array $attributes = []): Account
    {
        // 使用 Account 模块的工厂
        if (class_exists(\Modules\Account\Database\Factories\AccountFactory::class)) {
            return Account::factory()->create($attributes);
        }

        // 手动创建账户（如果工厂不可用）
        return Account::create([
            'email' => $attributes['email'] ?? 'test_' . uniqid() . '@example.com',
            'password' => $attributes['password'] ?? bcrypt('password'),
            'status' => $attributes['status'] ?? 'active',
        ]);
    }

    /**
     * 设置当前认证账户
     *
     * @param Account $account 账户实例
     * @return void
     */
    protected function actingAsUser(Account $account): void
    {
        Sanctum::actingAs($account);
    }

    /**
     * 创建并设置认证账户
     *
     * @param array $attributes 账户属性覆盖
     * @return Account
     */
    protected function actingAsAuthenticatedUser(array $attributes = []): Account
    {
        $account = $this->createAccount($attributes);
        $this->actingAsUser($account);
        return $account;
    }
}