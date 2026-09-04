# FeatureSms 模块

短信功能模块，提供短信验证码发送、验证等功能。采用**单模块全栈架构**，包含完整的业务逻辑和 Admin 后台管理。

- 数据表前缀 `fsms_`
- **架构**: 单模块全栈（Models/Services/Logics + DcatAdmin）
- **入口**: Admin 后台 + API 接口

## 功能特性

- ✅ 短信验证码发送
- ✅ 短信验证码验证
- ✅ 多种验证码类型支持（登录、注册、密码重置）
- ✅ 短信网关配置管理
- ✅ 验证码有效期控制
- ✅ 防重复发送机制
- ✅ Admin 后台管理界面（Dcat Admin）

## 目录结构（单模块全栈架构）

```
Modules/FeatureSms/
├── DcatAdmin/                # Admin后台入口
│   ├── Controllers/          # 后台控制器
│   ├── Actions/              # 操作类
│   ├── Forms/                # 表单类
│   ├── Metrics/              # 图表类
│   ├── Repositories/         # 数据仓库
│   ├── Show/                 # 详情页
│   └── Requests/             # 请求验证
├── Models/                   # 数据模型（业务层）
├── Services/                 # 服务层（业务层）
├── Dtos/                     # 数据传输对象
├── Enums/                    # 枚举类型
├── Rules/                    # 验证规则
├── Commands/                 # Artisan命令
├── config/                   # 配置文件
│   ├── config.php            # 模块配置
│   └── admin_menu.php        # Admin菜单配置
├── routes/                   # 路由定义
│   ├── admin.php             # Admin路由
│   ├── api.php               # API路由
│   └── web.php               # Web路由
├── Providers/                # 服务提供者
├── Tests/                    # 测试文件
└── resources/                # 资源文件
```

## 安装与配置

### 1. 发布配置文件

```bash
php artisan vendor:publish --provider="Modules\\FeatureSms\\Providers\\FeatureSmsServiceProvider"
```

### 2. 配置短信网关

在后台管理中配置短信网关参数，或者在 `config/modules/feature-sms.php` 中配置。

### 3. 添加定时任务

将以下命令添加到定时任务中，定期清理过期验证码：

```bash
# 每小时清理一次过期验证码
0 * * * * cd /path-to-your-project && php artisan feature-sms:clean-expired-codes --hours=24
```

## API 接口

### 发送验证码

**POST** `/api/feature-sms/send-code`

```json
{
    "phone": "13800138000",
    "type": 1,
    "token": "unique-token"
}
```

### 验证验证码

**POST** `/api/feature-sms/verify-code`

```json
{
    "phone": "13800138000",
    "code": "123456",
    "type": 1
}
```

## 使用示例

### 发送验证码

```php
use Modules\FeatureSms\Services\SmsService;
use Modules\FeatureSms\Enums\CODE_TYPE;

$smsService = app(SmsService::class);

try {
    $result = $smsService->sendCode(
        CODE_TYPE::LOGIN,
        '13800138000',
        'unique-token'
    );

    if ($result) {
        // 发送成功
    }
} catch (\Exception $e) {
    // 处理异常
}
```

### 验证验证码

```php
use Modules\FeatureSms\Services\SmsService;
use Modules\FeatureSms\Enums\CODE_TYPE;

$smsService = app(SmsService::class);

$isValid = $smsService->verifyCode(
    CODE_TYPE::LOGIN,
    '13800138000',
    '123456'
);

if ($isValid) {
    // 验证成功
}
```

## 验证码类型

- `CODE_TYPE::LOGIN` (1): 登录验证码
- `CODE_TYPE::REGISTER` (2): 注册验证码
- `CODE_TYPE::RESET_PASSWORD` (3): 密码重置验证码

## Admin 后台管理

访问路径: `/admin/featuresms`

功能菜单：
- **仪表盘** (`/admin/featuresms/dashboard`)
- **短信配置** (`/admin/featuresms/sms-configs`)
- **短信网关** (`/admin/featuresms/sms-gateways`)
- **短信记录** (`/admin/featuresms/sms-logs`)
- **验证码管理** (`/admin/featuresms/sms-codes`)

## 配置说明

### config/config.php

```php
return [
    'code_expire_time' => 600,      // 验证码有效期（秒）
    'daily_limit' => 10,           // 每日发送限制
    'send_interval' => 60,         // 发送间隔（秒）
    'default_driver' => 'log',     // 默认驱动
    'drivers' => [ ... ],          // 可用驱动配置
    'templates' => [ ... ],        // 验证码模板
];
```

## 测试

运行测试：

```bash
php artisan test --filter=FeatureSms
```

## 许可证

MIT License 
