---
name: module-config-guide
description: Laravel 模块配置管理指南。用于创建配置文件、修复错误的 config() 调用、指导 ServiceProvider 配置注册。当用户提及配置、config、模块配置、ServiceProvider registerConfig、配置读取、配置文件创建时触发。即使只说"配置"、"读取配置"、"创建配置"、"config调用"等简单表述,只要涉及模块配置管理,都应使用此技能。
---

# Laravel 模块配置管理指南

## 核心机制：配置命名空间

Laravel 模块使用**命名空间配置机制**避免冲突：

```
module_{模块名}::config.{配置键名}
```

**原理**:
- 配置文件：`Modules/{模块}/config/config.php`
- ServiceProvider 注册：`merge_config_from($file, 'module_account::config')`
- 读取方式：`config('module_account::config.enable_email_registration')`

**为什么使用 `::` 格式？**
- 避免多模块配置冲突（每个模块都有 config.php）
- Laravel 标准包配置机制
- 清晰区分来源（module_account vs module_user）

---

## 一、创建配置文件

### 1. 配置文件位置和命名

配置文件可以有**任意名称**，放在模块的 `config/` 目录：

```
Modules/{模块名}/config/
├── config.php         → module_{模块名}::config
├── admin_menu.php     → module_{模块名}::admin_menu
├── api.php            → module_{模块名}::api
└── custom.php         → module_{模块名}::custom
```

**命名空间注册规则**:
```php
// ServiceProvider.php 第121-123行
foreach (glob($configPath . '/*.php') as $file) {
    $name = basename($file, '.php');  // 提取文件名（去掉.php）
    $this->merge_config_from($file, strtolower($this->nameLower) . '::' . $name);
}
```

**读取方式**:
```php
// config.php
config('module_account::config.enable_email_registration')

// admin_menu.php
config('module_account::admin_menu')

// api.php
config('module_account::api.endpoint')
```

### 2. 主配置文件（推荐使用 config.php）

```
Modules/{模块名}/config/config.php
```

**推荐作为主配置文件**，包含模块核心配置：

```php
<?php

return [
    // 配置项说明
    'enable_email_registration' => false,
    'enable_phone_registration' => true,

    // 嵌套配置
    'minimax' => [
        'api_key' => env('MINIMAX_API_KEY', ''),
        'default_chat_model' => 'MiniMax-M2.7',
    ],

    // 环境变量支持
    'admin_user_id' => env('MODULE_ADMIN_USER_ID', '1'),
];
```

### 3. 其他配置文件（可选）

可以创建多个配置文件，按功能分类：

**后台菜单配置**:
```
Modules/{模块名}/config/admin_menu.php
```

**API配置**:
```
Modules/{模块名}/config/api.php
```

**数据库配置**:
```
Modules/{模块名}/config/database.php
```

**示例：多配置文件结构**:
```php
// Modules/Account/config/config.php - 主配置
return [
    'enable_email_registration' => false,
    'enable_phone_registration' => true,
];

// Modules/Account/config/admin_menu.php - 后台菜单
return [
    ['title' => '账户管理', 'icon' => 'fa-user', 'children' => [...]],
];

// Modules/Account/config/api.php - API配置
return [
    'rate_limit' => 100,
    'timeout' => 30,
];
```

**读取示例**:
```php
config('module_account::config.enable_email_registration')
config('module_account::admin_menu')
config('module_account::api.rate_limit')
```

### 4. 多配置文件最佳实践

**何时拆分配置文件？**

✅ **推荐拆分场景**:
- 配置项超过30个 → 按功能域拆分
- 不同环境使用不同配置集 → 拆分为独立文件
- 后台菜单、API、数据库配置独立 → 使用专用文件

❌ **不推荐拆分场景**:
- 配置项少于10个 → 保持单文件即可
- 配置项高度耦合 → 拆分会增加维护成本

**示例：合理拆分**
```
Modules/NovelAi/config/
├── config.php       → 核心功能配置（minimax api）
├── admin_menu.php   → 后台菜单配置
└── models.php       → 模型配置（可选）
```

**示例：不合理拆分**
```
Modules/SimpleModule/config/
├── config.php       → 2个配置项
├── database.php     → 1个配置项
├── api.php          → 1个配置项
```
**原因**: 配置太少，过度拆分反而增加复杂度。

---

## 二、配置注册（ServiceProvider）

### 1. 继承基类 ServiceProvider

**基类**: `Modules\ABase\Support\ServiceProvider`

**自动注册**:
```php
class AccountServiceProvider extends ServiceProvider
{
    protected string $name = 'Account';
    protected string $nameLower = 'module_account';  // 重要：必须以 module_ 开头

    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        $this->registerConfig();  // 基类方法，自动注册所有 config/*.php
        // ...
    }
}
```

**基类 registerConfig 实现**（无需重复实现）:
```php
// Modules/ABase/Support/ServiceProvider.php
protected function registerConfig(): void
{
    $configPath = $this->modulePath . '/config';
    if (is_dir($configPath)) {
        foreach (glob($configPath . '/*.php') as $file) {
            $name = basename($file, '.php');
            $this->merge_config_from($file, strtolower($this->nameLower) . '::' . $name);
        }
    }
}
```

### 2. ❌ 错误：重复实现 registerConfig

**不要在子类重复实现**:
```php
// ❌ 错误示例（AccountServiceProvider）
protected function registerConfig(): void
{
    $configPath = __DIR__ . '/../config';
    foreach (glob($configPath . '/*.php') as $file) {
        $name = basename($file, '.php');
        $this->merge_config_from($file, strtolower($this->nameLower) . '::' . $name);
    }
}
```

**原因**: 基类已提供完整实现，重复实现会导致维护负担。

### 3. nameLower 必须以 module_ 开头

```php
protected string $nameLower = 'module_account';  // ✅ 正确
protected string $nameLower = 'account';         // ❌ 错误
```

**原因**: 配置键格式为 `module_account::config`，如果 nameLower 不带 `module_`，会导致配置键不一致。

---

## 三、配置读取

### 1. ✅ 正确的读取方式

```php
// 基础配置项
config('module_account::config.enable_email_registration', false)

// 嵌套配置项
config('module_novelai::config.minimax.api_key', '')
config('module_novelpromotion::config.purchase.admin.user_id', '1')

// 后台菜单配置
config('module_account::admin_menu')
```

### 2. ❌ 常见错误读取方式

```php
// ❌ 错误：缺少命名空间
config('account.enable_email_registration')      // 无法读取
config('FeatureSms.super_code')                  // 无法读取
config('novel_ai.minimax.api_key')               // 无法读取

// ❌ 错误：缺少 config 层级
config('module_account::enable_email_registration')  // 错误键名

// ✅ 正确格式
config('module_account::config.enable_email_registration')
```

### 3. 模块间配置读取

```php
// Account 模块读取 Referral 模块配置
$referralConfig = config('module_referral::config.auto_assign_code', false);

// 跨模块配置共享示例
if (config('module_featuresms::config.super_code.enabled', false)) {
    // 使用超级验证码
}
```

---

## 四、常见问题和修复

### 问题 1：配置读取不到

**症状**: `config('account.xxx')` 返回 null

**原因**: 缺少命名空间前缀

**修复**:
```php
// 搜索错误使用
grep -r "config('模块名\." Modules/{模块} --include="*.php"

// 批量修复
config('模块名.xxx') → config('module_模块名::config.xxx')
```

### 问题 2：ServiceProvider 重复实现

**症状**: 多个 ServiceProvider 都实现了 registerConfig

**修复**: 移除重复实现，只保留基类方法调用
```php
public function boot(): void
{
    $this->modulePath = dirname(__DIR__);
    $this->registerConfig();  // 调用基类方法
    // ...
}
```

### 问题 3：nameLower 设置错误

**症状**: 配置键不一致

**修复**:
```php
protected string $nameLower = 'module_account';  // 必须以 module_ 开头
```

---

## 五、完整示例

### 创建新模块配置流程

**步骤 1**: 创建配置文件
```bash
# 创建配置目录
mkdir -p Modules/NewModule/config

# 创建配置文件
touch Modules/NewModule/config/config.php
touch Modules/NewModule/config/admin_menu.php  # 可选
```

**步骤 2**: 编写配置内容
```php
// Modules/NewModule/config/config.php
<?php

return [
    'feature_enabled' => env('NEW_MODULE_FEATURE_ENABLED', true),
    'api_config' => [
        'endpoint' => env('NEW_MODULE_API_ENDPOINT', 'https://api.example.com'),
        'timeout' => 30,
    ],
];
```

**步骤 3**: 配置 ServiceProvider
```php
// Modules/NewModule/Providers/NewModuleServiceProvider.php
class NewModuleServiceProvider extends ServiceProvider
{
    protected string $name = 'NewModule';
    protected string $nameLower = 'module_newmodule';  // 注意：module_ 前缀

    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        $this->registerConfig();  // 基类自动注册
        // ...
    }
}
```

**步骤 4**: 读取配置
```php
// 在 Service/Handler/Controller 中读取
$enabled = config('module_newmodule::config.feature_enabled', false);
$timeout = config('module_newmodule::config.api_config.timeout', 30);
```

---

## 六、最佳实践检查清单

**创建配置前检查**:
- ✅ 配置文件位置正确：`Modules/{模块}/config/config.php`
- ✅ ServiceProvider 继承基类：`Modules\ABase\Support\ServiceProvider`
- ✅ nameLower 设置正确：`module_{模块名}`
- ✅ boot() 方法调用基类 registerConfig()

**读取配置时检查**:
- ✅ 使用完整命名空间：`module_{模块名}::config.xxx`
- ✅ 提供默认值：`config('xxx', default_value)`
- ✅ 嵌套配置正确：`module_xxx::config.nested.key`

**避免常见错误**:
- ❌ 不要重复实现 registerConfig
- ❌ 不要使用 `config('模块名.xxx')` 格式
- ❌ 不要忘记 `::config` 层级
- ❌ nameLower 不要缺少 `module_` 前缀

---

## 七、调试和验证

### 验证配置是否正确注册

```bash
# 使用 tinker 测试
php artisan tinker --execute="echo config('module_account::config.enable_email_registration');"
```

### 搜索错误配置使用

```bash
# 查找模块内所有 config() 使用
cd Modules/{模块}
grep -r "config(" --include="*.php" | grep -v "::config\."

# 查找特定错误模式
grep -r "config('模块名\." --include="*.php"
```

### 检查 ServiceProvider 重复实现

```bash
# 查找所有重复的 registerConfig
grep -r "protected function registerConfig" Modules --include="*ServiceProvider.php" -l
```

---

## 八、参考资源

**相关模块**:
- `Modules/ABase/Support/ServiceProvider.php` - 基类实现
- `Modules/Account/config/config.php` - 配置示例
- `Modules/Account/Providers/AccountServiceProvider.php` - ServiceProvider 示例

**最佳实践模块**:
- `Modules/Demo5` - 完整单模块架构示例
- `Modules/Account` - 事件驱动架构示例

---

**最后更新**: 2026-05-24
**版本**: v1.0