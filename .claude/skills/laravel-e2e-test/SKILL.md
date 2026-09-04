---
name: laravel-e2e-test
description: 为 Laravel 模块化项目生成真实的 E2E API 测试代码（使用 PHPUnit + Guzzle HTTP Client，发起真实 HTTP 请求，不是 Laravel 内部路由调用）。当用户提到"E2E测试"、"端到端测试"、"黑盒测试"、"API测试"、"接口测试"、"真实请求测试"、"真实HTTP请求"或需要验证完整 HTTP 请求链路时，必须使用此技能。此技能会生成完整的测试类文件，包括基础 CRUD 测试、认证授权测试、参数验证、错误处理等场景，测试文件放置在 Modules/*/Tests/E2E/ 目录。
---

# Laravel E2E API 测试生成器

## 技能概述

这个技能帮助您为 Laravel 模块化项目编写**真实的 E2E API 测试**代码，使用 PHPUnit + Guzzle HTTP Client 发起真实 HTTP 请求，而不是 Laravel 内部的路由调用（如 `getJson()`）。

## 为什么需要真实 E2E 测试？

Laravel 的 `$this->getJson('/api/users')` 只是内部路由调用，**不是真实 HTTP 请求**，无法测试：
- 完整的 HTTP 请求/响应链路
- Web 服务器配置（Apache/Nginx）
- 中间件的真实执行顺序
- 实际的网络延迟和性能

**真实 E2E 测试**能够验证从客户端发起 HTTP 请求到服务器响应的完整链路。

## 核心特性

- ✅ 使用 Guzzle HTTP Client 发起真实 HTTP 请求
- ✅ 测试基础 URL：`http://127.0.0.1:80`（默认）
- ✅ 生成完整的测试类文件（可直接使用）
- ✅ 测试文件放置在 `Modules/*/Tests/E2E/` 目录
- ✅ 包含 CRUD、认证、参数验证、错误处理等场景
- ✅ 遵循 PHPUnit 最佳实践

## 使用方式

### 1. 提供必要信息

在使用此技能时，请提供以下信息：

**必需信息：**
- 模块名称（如 `Demo5`）
- API 路径（如 `/api/users`）
- 测试场景（如 CRUD、认证测试等）

**可选信息：**
- 测试基础 URL（默认 `http://127.0.0.1:80`）
- 认证方式（如 Bearer Token）
- 特定测试数据

### 2. 生成的测试代码结构

技能会生成以下结构：

**基础测试类结构：**
```php
<?php

namespace Modules\{Module}\Tests\E2E\Api;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class {Resource}ControllerE2ETest extends TestCase
{
    private Client $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:80',
            'timeout' => 5.0,
        ]);
    }

    // 测试方法...
}
```

**带认证的测试类结构（重要！）：**
```php
<?php

namespace Modules\{Module}\Tests\E2E\Api;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class {Resource}ControllerE2ETest extends TestCase
{
    private Client $httpClient;
    private string $testToken; // 认证 token

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:80',
            'timeout' => 5.0,
        ]);

        // 初始化测试 token（Bearer Token）
        // 方式1: 使用已知的测试用户 token
        $this->testToken = 'test-user-bearer-token-here';

        // 方式2: 如果使用 Laravel Sanctum，可能需要通过 API 获取
        // $authResponse = $this->httpClient->post('/api/login', [...]);
        // $this->testToken = json_decode($authResponse->getBody(), true)['token'];
    }

    // 测试方法...
}
```

### 3. 测试场景覆盖

生成的测试代码会覆盖以下场景：

#### 基础 CRUD 测试
- `test_can_list_{resource}` - 列表查询
- `test_can_create_{resource}` - 创建资源
- `test_can_show_{resource}` - 单条查询
- `test_can_update_{resource}` - 更新资源
- `test_can_delete_{resource}` - 删除资源

#### 认证授权测试
- `test_authenticated_user_can_access` - 已认证用户访问（必须包含此方法名）
- `test_unauthenticated_user_cannot_access` - 未认证用户拒绝访问（必须包含此方法名）
- `test_user_without_permission_cannot_access` - 无权限用户拒绝

**认证测试的关键要求：**
1. 方法命名必须包含 "authenticated" 或 "unauthenticated"
2. Authorization header 必须使用 Guzzle 的 `headers` 选项设置
3. 未认证测试必须验证返回 401 状态码

**Bearer Token 认证的代码示例：**
```php
/**
 * 测试已认证用户可以访问商户列表 - 使用 Bearer Token
 */
public function test_authenticated_user_can_access_merchants(): void
{
    // 使用 Guzzle 的 headers 选项设置 Authorization
    $response = $this->httpClient->get('/api/merchants', [
        'headers' => [
            'Authorization' => 'Bearer ' . $this->testToken,
            'Accept' => 'application/json',
        ]
    ]);

    $this->assertEquals(200, $response->getStatusCode());
}

/**
 * 测试未认证用户无法访问 - 返回 401 Unauthorized
 */
public function test_unauthenticated_user_cannot_access_merchants(): void
{
    // 不设置 Authorization header
    $response = $this->httpClient->get('/api/merchants', [
        'headers' => [
            'Accept' => 'application/json',
        ]
    ]);

    // 必须验证返回 401 状态码
    $this->assertEquals(401, $response->getStatusCode());
}
```

#### 参数验证测试
- `test_validation_error_on_invalid_data` - 参数验证错误
- `test_validation_error_on_missing_required_fields` - 必填字段缺失

#### 错误处理测试
- `test_404_on_nonexistent_resource` - 资源不存在返回 404
- `test_500_on_server_error` - 服务器错误处理

## 使用示例

### 示例 1：生成用户模块的 CRUD 测试

**用户输入：**
```
为 Demo5 模块的 /api/users 接口生成 E2E 测试
```

**技能生成：**
- 测试类：`Modules/Demo5/Tests/E2E/Api/UserControllerE2ETest.php`
- 包含完整的 CRUD 测试方法

### 示例 2：生成带认证的订单模块测试

**用户输入：**
```
为 Order 模块的 /api/orders 接口生成带认证的 E2E 测试，需要 Bearer Token 认证
```

**技能生成：**
- 测试类包含认证测试方法
- 测试方法中包含 Authorization header 设置

### 示例 3：生成特定场景测试

**用户输入：**
```
为 Merchant 模块的 /api/merchants 接口生成参数验证和错误处理的 E2E 测试
```

**技能生成：**
- 重点覆盖参数验证测试
- 重点覆盖错误处理测试

## 测试前提条件

在使用生成的测试代码前，请确保：

1. **服务器已启动** - `http://127.0.0.1:80` 服务正在运行
2. **环境已准备** - 数据库、配置已初始化
3. **测试数据准备** - 如需认证，确保有测试用户和 token

## 技能执行流程

当用户请求生成 E2E 测试时，技能会：

1. **分析需求** - 确定模块名称、API 路径、测试场景
2. **生成测试类** - 创建完整的 PHPUnit 测试类文件
3. **包含必要导入** - PHPUnit TestCase、Guzzle Client
4. **生成 setUp 方法** - 初始化 HTTP Client，如需认证则初始化 token
5. **生成测试方法** - 根据场景生成相应测试方法
6. **添加 PHPDoc 注释** - 所有方法必须有注释
7. **保存到正确位置** - `Modules/*/Tests/E2E/Api/` 目录

**认证测试生成时的强制检查点：**

当生成认证测试时，必须确保：
- ✅ setUp 方法中初始化 `$this->testToken` 属性
- ✅ 测试方法名包含 "authenticated" 或 "unauthenticated"
- ✅ Authorization header 使用 Guzzle 的 `'headers'` 选项设置
- ✅ 格式为：`'Authorization' => 'Bearer ' . $this->testToken`
- ✅ 未认证测试必须验证 401 状态码
- ✅ 已认证测试必须验证成功状态码（200、201等）

## 重要提示

### 真实 E2E 测试 vs Laravel 集成测试

| 测试方式 | 是否真实请求 | 测试范围 | 适用场景 |
|---------|------------|---------|---------|
| `$this->getJson()` | ❌ 内部路由调用 | 集成测试 | 快速测试代码逻辑 |
| PHPUnit + Guzzle | ✅ 真实 HTTP 请求 | E2E 测试 | 验证完整链路 |

### 不使用 RefreshDatabase

由于是真实 HTTP 请求测试，不使用 Laravel 的 `RefreshDatabase` trait。测试数据管理需要：
- 测试前手动准备数据
- 测试后手动清理数据
- 或使用独立的测试数据库

### PHPDoc 规范

生成的代码必须包含完整的 PHPDoc 注释：

```php
/**
 * 测试用户列表接口 - E2E 真实请求
 */
public function test_can_list_users(): void
{
    // ...
}
```

## 技能触发条件

此技能会在以下场景自动触发：

- 用户提到 **"E2E测试"**、**"端到端测试"**、**"黑盒测试"**
- 用户提到 **"API测试"**、**"接口测试"**、**"真实请求测试"**
- 用户提到 **"真实HTTP请求测试"**、**"不要getJson"**
- 用户提到 **"完整链路测试"**、**"验证HTTP响应"**
- 用户需要验证完整的请求链路（路由、中间件、控制器、响应）

即使用户没有明确说"E2E测试"，但提到了需要真实 HTTP 请求或完整链路测试，此技能也应该被触发。

## 输出格式

技能会生成：

- **完整测试类文件** - 可直接放入 `Modules/*/Tests/E2E/Api/` 目录使用
- **文件路径建议** - 明确告知用户文件应保存的位置
- **使用说明** - 如何运行测试、前提条件等

## 项目特定配置

此技能针对您的项目进行了配置：

- **测试基础 URL**: `http://127.0.0.1:80`
- **模块化结构**: 测试文件放置在 `Modules/*/Tests/E2E/Api/`
- **PHP 版本**: PHP 8.3
- **框架版本**: Laravel 12
- **遵循项目规范**: PHPDoc 注释、模块化分层

## 最佳实践建议

1. **先启动服务器** - 确保 `http://127.0.0.1:80` 服务正在运行
2. **准备测试数据** - 确保有测试用户、认证 token 等
3. **运行单个测试** - 使用 `php artisan test --filter UserControllerE2ETest`
4. **验证响应结构** - 检查响应 JSON 结构是否符合预期
5. **处理异步操作** - 如有队列任务，可能需要等待或轮询

## 常见问题

**Q: 测试失败返回 Connection refused？**
A: 检查服务器是否启动，确认 `http://127.0.0.1:80` 可访问

**Q: 测试失败返回 401 Unauthorized？**
A: 检查认证 token 是否有效，是否正确设置 Authorization header

**Q: 测试数据污染真实数据库？**
A: 建议使用独立的测试数据库，或测试后手动清理数据

**Q: Guzzle 和 Laravel Http Client 有什么区别？**
A: Guzzle 是通用 HTTP Client，Laravel Http Client 是 Guzzle 的封装，两者都可以发起真实 HTTP 请求，此技能默认使用 Guzzle

---

**记住：真实 E2E 测试验证完整的 HTTP 请求链路，这是确保 API 正常工作的最终防线！**