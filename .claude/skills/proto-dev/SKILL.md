---
name: proto-dev
description: Proto 开发技能 - 用于创建、验证、修复 proto 文件和生成 Handler 类。当用户提及 proto、protobuf、接口定义、Message 设计、Handler 创建、验证 proto 规范时必须使用此技能。即使用户只说"写个proto"、"设计接口"、"生成Handler"、"检查proto"等模糊表述，也必须触发此技能。
---

# Proto 开发技能

基于项目的 Handler 架构和多模块 Proto 归拢架构，提供完整的 proto 开发工作流支持。完整架构规范请参考项目文档 `docs/proto-multi-module.md`。

## 核心能力

1. **创建 proto 文件** - 从 API 路径或功能描述生成符合规范的 proto 文件
2. **验证 proto 规范** - 检查目录结构、命名规范、php_namespace、Api Path 注释等
3. **生成 Handler 类** - 根据 proto Message 自动生成 Handler 类框架
4. **修复规范问题** - 自动添加缺失的定义、修正命名、调整目录结构

---

## 工作流程

### 1. 需求识别

技能支持两种输入方式：

**方式 A：API 路径 + 模块名**
```
用户输入："为 Demo5 模块创建 proto 文件，API 路径：/api/proto/demo5/post/create"
技能输出：生成 Demo5PostCreateRequest/Response Message
```

**方式 B：功能描述**
```
用户输入："创建文章接口，需要 title、content、status 字段，模块：Demo5，handler：post，action：create"
技能输出：根据字段描述生成完整 proto 文件
```

技能会根据用户输入自动识别方式，并提取以下关键信息：
- **module**：模块名（如 Demo5、Account）
- **handler**：处理器名（如 post、user）
- **action**：方法名（如 create、list、get）

---

### 2. 规范验证

在创建或修改 proto 文件前，技能会验证：

| 验证项 | 规范要求 | 错误处理 |
|-------|---------|---------|
| **目录结构** | 业务模块禁止二级子目录 | 提示调整目录结构 |
| **php_namespace** | 必须定义 `option php_namespace` | 自动添加或提示补充 |
| **Message 命名** | `{Module}{Handler}{Action}Request/Response` | 自动修正命名 |
| **Api Path 注释** | 每个 Request 上方必须有完整 Api Path 注释 | 自动添加注释 |
| **字段命名** | 使用 snake_case | 提示修正 |
| **字段编号** | 从1开始，不重复、已使用编号不可修改 | 验证并提示 |
| **字段追加** | 新增字段必须追加新编号，严禁修改已有编号 | 强制提示，拒绝执行 |

**字段编号核心原则（重要）：**

Protobuf 字段编号是向后兼容的关键，必须遵守以下规则：

1. **编号永久性**：字段编号一旦分配使用，就不能再修改或删除
2. **追加原则**：新增字段必须使用新的编号，追加在现有编号之后
3. **禁止插值**：严禁在已有字段中间插入新编号，这会破坏向后兼容性
4. **禁止重编号**：严禁调整已有字段的编号顺序

**错误示例（禁止）：**
```protobuf
// 原始版本
message AccountAuthLoginRequest {
    string email = 1;      // 已有字段
    string password = 2;   // 已有字段
}

// 错误修改：插值、重编号（破坏兼容性）
message AccountAuthLoginRequest {
    string email = 1;      // 已有字段
    string phone = 2;      // ❌ 错误：使用了 password 的编号
    string password = 3;   // ❌ 错误：修改了已有字段的编号
}
```

**正确示例：**
```protobuf
// 原始版本
message AccountAuthLoginRequest {
    string email = 1;
    string password = 2;
    bool remember_me = 3;
}

// 正确追加：使用新编号 4
message AccountAuthLoginRequest {
    string email = 1;         // 保持不变
    string password = 2;      // 保持不变
    bool remember_me = 3;     // 保持不变
    string phone = 4;         // ✅ 正确：追加新编号 4
}
```

**向后兼容性说明：**
- 已部署的客户端使用旧的编号识别字段
- 如果修改编号，旧客户端会读取到错误的字段数据
- 只有追加新编号，才能保证新旧版本都能正确解析

---

### 3. Proto 文件创建流程

**步骤 1：加载项目规范文档**
- 读取 `docs/proto-multi-module.md` 获取完整架构规范
- 如文档不存在，使用技能内置的核心规范

**步骤 2：提取关键信息**
从用户输入提取：module、handler、action、字段列表

**步骤 3：推导命名**
```
module=demo5, handler=post, action=create

推导：
- Message 名称：Demo5PostCreateRequest / Demo5PostCreateResponse
- Api Path：/api/proto/demo5/post/create
- php_namespace：Modules\ApiProto\Protobuf\Demo5\Post
- Handler 类：PostCreateHandler

特殊情况：两部分模块名（如 NovelAi）
module=novel_ai, handler=book, action=list

推导：
- Message 名称：Novel_aiBookListRequest / Novel_aiBookListResponse
- Api Path：/api/proto/novel_ai/book/list
- php_namespace：Modules\ApiProto\Protobuf\NovelAi\Book  ← namespace 保持驼峰
- Handler 类：BookListHandler
```

**步骤 4：生成 proto 文件**
- 目录：`Modules/{Module}/ApiProto/protos/{handler}.proto`
- 内容：包含 syntax、package、options、import、Message 定义
- 添加完整 Api Path 注释

**正确的目录结构**：
```
Modules/
├── Demo5/
│   └── ApiProto/
│       └── protos/
│           └── post.proto          ← 业务模块：禁止二级子目录
├── NtEnergy/
│   └── ApiProto/
│       └── protos/
│           └── energy.proto        ← 能源模块
└── ApiProto/
    └── protos/
        └── ApiProto/
            └── common.proto        ← ApiProto模块：允许二级子目录
```

**步骤 5：验证生成的文件**
检查是否符合所有规范要求

---

### 4. Handler 类生成流程

**Handler 基类架构**（继承 BaseHandler）：
```php
abstract class BaseHandler
{
    protected bool $need_login = false;  // 是否需要登录
    public int $user_id = 0;              // 中间件注入
    protected BaseResponse $baseResponse;

    abstract public function handle(Message $data): Message;
}
```

**生成策略**（可选详细程度）：

1. **基础框架**（默认）：
   - 继承 BaseHandler
   - 设置 `need_login` 属性
   - `handle()` 方法签名

2. **完整实现**（可选）：
   - 字段提取代码
   - Service 调用代码
   - Response 构建代码

---

### 5. ApiProto 模块注册流程

创建 Proto 文件和 Handler 类后，必须在 ApiProto 模块中注册业务模块，才能让统一入口识别和处理该模块的 API 请求。

**注册方式概述**：

本项目采用 **配置文件 + Hook 动态注册** 的混合模式：

1. **基础模块**：在配置文件 `api_proto.php` 中静态声明
2. **业务模块组**：通过项目主模块的 Hook 动态注册

---

#### 方式一：配置文件静态注册（适用于基础模块）

**适用场景**：独立的基础模块，如 User1、Application、Point 等

**步骤 1：在配置文件中添加模块映射**

编辑 `Modules/ApiProto/config/api_proto.php`，在 `enabled_modules` 数组中添加：

```php
'enabled_modules' => [
    // 基础业务模块（配置文件静态声明）
    'user1' => 'User1',
    'merchant1' => 'Merchant1',
    'enterprise' => 'Enterprise',

    // 工具/功能模块
    'application' => 'Application',
    'point' => 'Point',
    'afile' => 'AFile',
    'notification' => 'Notification',
    'cms' => 'Cms',

    // 示例模块
    'demo5' => 'Demo5',
],
```

**映射规则**：
- **Key（URL路径段）**：小写+下划线形式，对应 API Path 第一段
  - 单部分模块名：直接小写（如 `demo5`, `user1`）
  - 两部分模块名：下划线分隔（如 `nt_energy`, `nt_carbon`）
- **Value（模块名）**：驼峰形式，对应业务模块目录名
  - 单部分模块名：直接驼峰（如 `Demo5`, `User1`）
  - 两部分模块名：保持驼峰（如 `NtEnergy`, `NtCarbon`）

---

#### 方式二：Hook 动态注册（适用于业务模块组）

**适用场景**：一组相关的业务模块，如碳能管理的 Nt* 系列

**实现架构**：

```
项目主模块（如 NtMain）
└── Hook Handler
    └── 注册一组业务模块
        └── ApiProto 读取合并后的模块列表
```

**步骤 1：创建 Hook Handler**

在项目主模块中创建 Handler（示例：`Modules/NtMain/Hooks/Handlers/NtModuleRegisterHandler.php`）：

```php
<?php

declare(strict_types=1);

namespace Modules\NtMain\Hooks\Handlers;

use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\ApiProto\Hooks\Parameters\ModuleRegisterParameter;
use Modules\ApiProto\Hooks\Results\ModuleRegisterResult;

/**
 * 碳能管理模块注册 Handler
 *
 * 通过 Hook 注册碳能管理相关的模块到 enabled_modules
 */
class NtModuleRegisterHandler implements HookHandlerInterface
{
    /**
     * 碳能管理模块列表
     */
    private const NT_MODULES = [
        'nt_energy' => 'NtEnergy',
        'nt_carbon' => 'NtCarbon',
        'nt_material' => 'NtMaterial',
        'nt_production' => 'NtProduction',
        'nt_emission' => 'NtEmission',
        'nt_analysis' => 'NtAnalysis',
        'nt_dashboard' => 'NtDashboard',
        'nt_report' => 'NtReport',
    ];

    /**
     * 处理模块注册
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        if (! $parameter instanceof ModuleRegisterParameter) {
            return $result;
        }

        // 合并碳能管理模块
        $mergedModules = array_merge(
            $parameter->getExistingModules(),
            self::NT_MODULES
        );

        return ModuleRegisterResult::success(
            $mergedModules,
            'NtMain 注册了 ' . count(self::NT_MODULES) . ' 个碳能管理模块'
        );
    }

    /**
     * 判断是否应该执行
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        return $parameter instanceof ModuleRegisterParameter
            && $parameter->getContext() === 'apiproto';
    }

    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return 10;
    }
}
```

**步骤 2：创建 HookServiceProvider**

创建 `Modules/NtMain/Providers/HookServiceProvider.php`：

```php
<?php

namespace Modules\NtMain\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ApiProto\Hooks\Definitions\ModuleRegisterHook;
use Modules\ABase\Hooks\Management\HookManager;
use Modules\NtMain\Hooks\Handlers\NtModuleRegisterHandler;

/**
 * NtMain Hook 服务提供者
 */
class HookServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册 Hook 定义
        if (! HookManager::isHookDefinitionRegistered(ModuleRegisterHook::class)) {
            HookManager::registerHook(ModuleRegisterHook::class);
        }
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 注册 Hook 处理器
        HookManager::add(
            ModuleRegisterHook::class,
            NtModuleRegisterHandler::class,
            10
        );
    }
}
```

**步骤 3：在模块 ServiceProvider 中注册**

编辑 `Modules/NtMain/Providers/NtMainServiceProvider.php`，添加 HookServiceProvider：

```php
protected array $providers = [
    HookServiceProvider::class,  // ← 添加 HookServiceProvider
    EventServiceProvider::class,
    RouteServiceProvider::class,
];
```

**工作原理**：

1. **ApiProtoServiceProvider** 在注册路由前调用 `ModuleRegisterHook`
2. **NtModuleRegisterHandler** 接收配置文件中的基础模块列表
3. Handler 合并 Nt* 系列模块到列表中
4. **ApiProto** 使用完整列表生成路由约束

**优势**：
- ✅ 配置文件维护基础模块（静态声明）
- ✅ 项目主模块统一管理一组业务模块
- ✅ 模块列表动态合并，易于扩展
- ✅ Hook 执行失败有降级逻辑

---

## 核心规范（精简版）

### 目录结构规范

**正确的目录结构**：

```
Modules/
├── Demo5/                          # 业务模块
│   └── ApiProto/
│       ├── protos/
│       │   └── post.proto          # ✅ protos 下直接放 proto 文件
│       └── Handlers/
│           └── PostCreateHandler.php
├── NtEnergy/                       # 能源模块
│   └── ApiProto/
│       ├── protos/
│       │   └── energy.proto        # ✅ protos 下直接放 proto 文件
│       └── Handlers/
│           └── EnergyListHandler.php
└── ApiProto/                       # ApiProto 模块
    ├── protos/
    │   └── ApiProto/
    │       └── common.proto        # ✅ ApiProto/ 下直接放 proto 文件
    ├── Protobuf/                   # 生成的 PHP 类
    └── config/
        ├── api_proto.php           # 模块配置
        └── pathlist.php            # API 映射（自动生成）
```

| 模块类型 | 目录位置 | 子目录规则 |
|---------|---------|-----------|
| **业务模块** | `Modules/{模块名}/ApiProto/protos/` | ❌ 禁止任何子目录，proto 文件直接放在 protos/ 下 |
| **ApiProto 模块** | `Modules/ApiProto/protos/ApiProto/` | ❌ 禁止再创建下级目录，proto 文件直接放在 ApiProto/ 下 |

### Message 命名规范

**基本格式**：
```
Request：{Module}{Group}{Action}Request
Response：{Module}{Group}{Action}Response
Data：{Entity}Data
```

**命名逻辑声明（重要）**：

Message 命名由三部分组成：**Module + Group + Action + 后缀**

这套命名逻辑**符合**核心转换规则（ApiPath ↔ Message 可逆转换），是转换结果的结构化解释：

1. **Module（模块前缀）**：
   - 对应 Api Path 第一段 `{module}`
   - 单部分模块名：驼峰形式（如 `Im`, `Demo5`）
   - 两部分模块名：下划线分隔（如 `Novel_ai`, `Account_auth`）

2. **Group（固定部分）**：
   - 对应 Api Path 第二段 `{group}`
   - 驼峰形式（如 `group` → `Group`, `user` → `User`）

3. **Action（方法部分）**：
   - 对应 Api Path 第三段开始的所有路径段 `{action}`
   - 多段路径合并驼峰（如 `public/groups` → `PublicGroups`, `info/detail` → `InfoDetail`）
   - 单段路径直接驼峰（如 `create` → `Create`, `list` → `List`）

4. **后缀**：
   - 固定为 `Request` 或 `Response`

**命名示例对照表**：

| Api Path | Module | Group | Action | Message 命名 | Handler 命名 |
|----------|--------|-------|--------|-------------|-------------|
| `/api/proto/im/group/public/groups` | `Im` | `Group` | `PublicGroups` | `ImGroupPublicGroupsRequest/Response` | `GroupPublicGroupsHandler` |
| `/api/proto/demo5/post/create` | `Demo5` | `Post` | `Create` | `Demo5PostCreateRequest/Response` | `PostCreateHandler` |
| `/api/proto/novel_ai/book/info/detail` | `Novel_ai` | `Book` | `InfoDetail` | `Novel_aiBookInfoDetailRequest/Response` | `BookInfoDetailHandler` |
| `/api/proto/account/user/login` | `Account` | `User` | `Login` | `AccountUserLoginRequest/Response` | `UserLoginHandler` |

**Handler 命名规则**：

Handler 命名由两部分组成：**Group + Action + Handler后缀**

- **Group**：Api Path 第二段转驼峰
- **Action**：Api Path 第三段开始的所有路径段合并驼峰
- **Handler后缀**：固定后缀 `Handler`

**Action 部分的驼峰转换说明**：

Action 是 Api Path 第三段开始的**所有路径段**，遵循核心转换规则：

- `public/groups` → `PublicGroups`（每段首字母大写后拼接）
- `info/detail` → `InfoDetail`
- `user/profile/get` → `UserProfileGet`
- `create` → `Create`（单段时直接驼峰）
- `list` → `List`

**模块前缀规则对照表**：

| 模块类型 | Message 前缀示例 | php_namespace 示例 | 说明 |
|---------|----------------|-------------------|------|
| **单部分模块名** | `Demo5PostCreateRequest` | `Modules\ApiProto\Protobuf\Demo5\Post` | 直接使用驼峰形式 |
| **两部分模块名** | `Novel_aiBookListRequest` | `Modules\ApiProto\Protobuf\NovelAi\Book` | Message 用下划线，namespace 保持驼峰 |

**命名示例**：
```protobuf
// Demo5 模块（单部分）
message Demo5PostCreateRequest { ... }
message Demo5PostCreateResponse { ... }
message PostData { ... }

// NovelAi 模块（两部分）
message Novel_aiBookListRequest { ... }
message Novel_aiBookListResponse { ... }
message BookData { ... }
```

**关键区别**：
- ✅ **Message 命名**：两部分模块名在 Message 中使用下划线分隔（如 `Novel_ai`）
- ✅ **php_namespace**：两部分模块名在 namespace 中保持驼峰形式（如 `NovelAi`）
- ❌ 错误示例：Message 中使用驼峰（`NovelAiBookListRequest`）
- ❌ 错误示例：namespace 中使用下划线（`Modules\ApiProto\Protobuf\Novel_ai\Book`)

### Proto 文件结构

每个 proto 文件必须包含：

```protobuf
syntax = "proto3";

package {module}.{handler};

option php_namespace = "Modules\\ApiProto\\Protobuf\\{Module}\\{Handler}";
option go_package = "github.com/shandong-ziyou/dailian/proto/{module}/{handler}";
option java_multiple_files = true;
option java_package = "com.shandong.ziyou.dailian.proto.{module}.{handler}";

import "ApiProto/common.proto";

// {直观的Api描述,例: 小说详情获取}
// {完整 Api Path，含前缀}
message {Name}Request {
  // 字段定义（snake_case，编号从1开始）
}

// {完整 Api Path，含前缀}
message {Name}Response {
  // 字段定义
}
```

**关键约束**：
- 每个 Request 上方必须有 `// {Api Path}` 注释
- 字段命名使用 snake_case
- 字段编号连续不跳跃
- php_namespace 格式必须正确

---

## 使用示例

### 示例 1：从 API 路径创建 proto

**用户输入**：
```
为 Account 模块创建用户登录接口的 proto 文件
API 路径：/api/proto/account/user/login
字段：username（必填）、password（必填）
```

**技能输出**：
创建文件：`Modules/Account/ApiProto/protos/login.proto`

```protobuf
syntax = "proto3";

package account.login;

option php_namespace = "Modules\\ApiProto\\Protobuf\\Account\\Login";
option go_package = "github.com/shandong-ziyou/dailian/proto/account/login";
option java_multiple_files = true;
option java_package = "com.shandong.ziyou.dailian.proto.account.login";

import "ApiProto/common.proto";

// /api/proto/account/user/login
message AccountUserLoginRequest {
  string username = 1;  // 用户名，必填
  string password = 2;  // 密码，必填
}

// /api/proto/account/user/login
message AccountUserLoginResponse {
  int32 code = 1;
  string message = 2;
  LoginData data = 3;
}

message LoginData {
  int64 user_id = 1;
  string token = 2;
}
```

---

### 示例 2：验证现有 proto 文件

**用户输入**：
```
检查 Account 模块的 proto 文件是否符合规范
```

**技能输出**：
读取 `Modules/Account/ApiProto/protos/*.proto`，执行验证：

```
验证结果：
✓ 目录结构：符合（无二级子目录）
✗ php_namespace：login.proto 缺少定义
✓ Message 命名：符合规范
✗ Api Path 注释：login.proto Request 缺少注释

修复建议：
1. login.proto 添加：
   option php_namespace = "Modules\\ApiProto\\Protobuf\\Account\\Login";
2. login.proto 在 AccountUserLoginRequest 上方添加：
   // /api/proto/account/user/login
```

---

### 示例 3：生成 Handler 类

**用户输入**：
```
根据刚才创建的 proto 文件生成 Handler 类，生成完整实现
```

**技能输出**：
创建文件：`Modules/Account/ApiProto/Handlers/UserLoginHandler.php`

```php
<?php

namespace Modules\Account\ApiProto\Handlers;

use Modules\ABase\Protobuf\BaseHandler;
use Modules\ApiProto\Protobuf\Account\Login\AccountUserLoginRequest;
use Modules\ApiProto\Protobuf\Account\Login\AccountUserLoginResponse;
use Modules\ApiProto\Protobuf\Account\Login\LoginData;
use Modules\Account\Services\UserService;
use Google\Protobuf\Internal\Message;

/**
 * {接口名字}
 * 
 * Api Path: {apppath}
 */
class UserLoginHandler extends BaseHandler
{
    protected bool $need_login = false;  // 登录接口无需登录

    /**
     * {方法备注}
     *
     * @param AccountUserLoginRequest $request 请求Message
     * @return AccountUserLoginResponse 返回Message
     */
    public function handle(Message $data): Message
    {
        $request = $data;

        // 提取字段
        $username = $request->getUsername();
        $password = $request->getPassword();

        // 调用 Service
        $user = UserService::login($username, $password);

        // 构建 Response
        $response = new AccountUserLoginResponse();
        $response->setCode(0);
        $response->setMessage('登录成功');

        $data = new LoginData();
        $data->setUserId($user->id);
        $data->setToken($user->token);
        $response->setData($data);

        return $response;
    }
}
```

---

### 示例 4：修复规范问题

**用户输入**：
```
Account 模块的 proto 文件有问题，帮我修复
```

**技能输出**：
自动修复：
1. 添加缺失的 `option php_namespace`
2. 在 Request 上方添加 Api Path 注释
3. 修正不符合规范的 Message 命名
4. 保存修改后的文件

---

### 示例 5：两部分模块名（NovelAi）

**用户输入**：
```
为 NovelAi 模块创建书籍列表接口的 proto 文件
API 路径：/api/proto/novel_ai/book/list
说明：NovelAi 是两部分模块名
```

**技能输出**：
创建文件：`Modules/NovelAi/ApiProto/protos/book.proto`

```protobuf
syntax = "proto3";

package novel_ai.book;

option php_namespace = "Modules\\ApiProto\\Protobuf\\NovelAi\\Book";  // ← namespace 保持驼峰 NovelAi
option go_package = "github.com/shandong-ziyou/dailian/proto/novelai/book";
option java_multiple_files = true;
option java_package = "com.shandong.ziyou.dailian.proto.novelai.book";

import "ApiProto/common.proto";

// /api/proto/novel_ai/book/list
message Novel_aiBookListRequest {  // ← Message 使用下划线 Novel_ai
  // 空 Request，使用 token 中的 user_id
}

// /api/proto/novel_ai/book/list
message Novel_aiBookListResponse {
  ApiProto.Common.BaseResponse base = 1;
  repeated BookBriefData books = 2;
  bool auto_created = 3;
  int64 new_book_id = 4;
}

message BookBriefData {
  int64 book_id = 1;
  string title = 2;
  string cover_image = 3;
  int32 chapter_count = 6;
  int32 word_count = 7;
}
```

**关键点说明**：
- ✅ Message 命名：`Novel_aiBookListRequest`（保留下划线）
- ✅ php_namespace：`NovelAi\Book`（NovelAi和模块名一致）
- ✅ package：`novel_ai.book`（保留下划线）

---

## 特殊情况处理

### 多个 Message 在同一 proto 文件

一个 proto 文件可以包含多个 Message 对（对应不同的 action）：

```protobuf
// /api/proto/demo5/post/create
message Demo5PostCreateRequest { ... }
message Demo5PostCreateResponse { ... }

// /api/proto/demo5/post/list
message Demo5PostListRequest { ... }
message Demo5PostListResponse { ... }
```

建议按 handler 分组文件：`post.proto` 包含所有 post 相关的 Message。

### 公共 Data Message

多个 Response 共用的 Data 结构可以单独定义：

```protobuf
message PostData {
  int32 id = 1;
  string title = 2;
  string content = 3;
}
```

---

## 常见错误与修正

| 错误类型 | 错误示例 | 修正方法 |
|---------|---------|---------|
| **Message 命名不规范** | `PostRequest` | 改为 `Demo5PostCreateRequest` |
| **缺少 php_namespace** | 无 option 定义 | 添加 `option php_namespace = "..."` |
| **缺少 Api Path 注释** | Message 上方无注释 | 添加 `// /api/proto/...` |
| **目录结构违规** | `模块/proto/login.proto` | 移至 `模块/ApiProto/protos/login.proto` |
| **字段命名不规范** | `userId` | 改为 `user_id` |
| **两部分模块名错误（Message）** | `NovelAiBookListRequest` | 改为 `Novel_aiBookListRequest` |
| **两部分模块名错误（namespace）** | `Modules\ApiProto\Protobuf\Novel_ai\Book` | 改为 `Modules\ApiProto\Protobuf\NovelAi\Book` |

---

## 技能触发关键词

此技能应在以下情况触发：
- 用户提及 "proto"、"protobuf"、"接口定义"
- 用户说 "创建 proto"、"设计 proto"、"写个 proto"
- 用户说 "验证 proto"、"检查 proto"、"proto 有问题"
- 用户说 "生成 Handler"、"创建 Handler"
- 用户提供 API 路径并需要生成接口定义
- 用户描述接口功能需要转化为 proto Message
- 用户提及 Handler 架构、统一入口、Message 设计

---

## 注意事项

1. **优先加载项目文档**：技能首先尝试读取 `docs/proto-multi-module.md`，如不存在则使用内置规范
2. **验证后生成**：创建 proto 文件前先验证目录结构是否合规
3. **自动修正命名**：根据 Api Path 自动推导并生成规范的 Message 名称
4. **完整注释**：每个 Request/Response 上方必须有完整的 Api Path 注释
5. **Handler 可选详细程度**：根据用户需求生成基础框架或完整实现
6. **禁止二级目录**：业务模块 proto 文件必须直接放在 `ApiProto/protos/` 目录下
7. **php_namespace 必须正确**：格式为 `Modules\ApiProto\Protobuf\{Module}\{Handler}`
8. **模块注册方式选择**：
   - 独立基础模块 → 配置文件静态注册（`api_proto.php`）
   - 一组业务模块 → 项目主模块 Hook 动态注册
9. **必须执行构建**：创建 Handler 后，执行 `composer run buildproto` 生成 PHP 类和 PathList
10. **验证注册结果**：执行构建后，检查 `pathlist.php` 是否包含新 API 映射

**注册方式选择指南**：

| 场景 | 推荐方式 | 示例 |
|------|---------|------|
| 单个独立模块 | 配置文件静态注册 | User1、Application、Point |
| 一组相关业务模块 | Hook 动态注册 | NtEnergy、NtCarbon 等 Nt* 系列 |
| 基础工具模块 | 配置文件静态注册 | AFile、Notification、Cms |
| 业务域模块组 | 项目主模块 Hook 注册 | 碳能管理、用户中心等 |

---

## 完整规范文档路径

详细架构和完整规范请参考：`docs/proto-multi-module.md`

---

# ApiPath ↔ Message 可逆转换规则（核心规则）

本项目采用统一前缀 `/api/proto/`

**正向转换（ApiPath → Message）：**
```
输入：/api/proto/demo5/post/create

步骤：
1. 去除项目统一前缀：/api/proto/ → demo5/post/create
2. 分割路径段：[demo5, post, create]
3. 路径段转驼峰规则：
   - / → - （先替换斜杠为连字符）
   - - 转驼峰：demo5 → Demo5, post → Post, create → Create
   - _ 保持不变：user_info → UserInfo（不插入额外分隔符）
4. 拼接驼峰段：Demo5PostCreate
5. 添加后缀：Demo5PostCreateRequest / Demo5PostCreateResponse

输出：Demo5PostCreateRequest / Demo5PostCreateResponse
```

**逆向转换（Message → ApiPath）：**
```
输入：Demo5PostCreateRequest

步骤：
1. 去除后缀：Demo5PostCreateRequest → Demo5PostCreate
2. 驼峰还原规则：
   - 大写字母前插入分隔符：Demo5 → -demo5, Post → -post, Create → -create
   - 结果：-demo5-post-create
3. 去除首字母分隔符：demo5-post-create
4. - 替换为 /：demo5/post/create
5. 添加项目统一前缀：/api/proto/demo5/post/create

输出：/api/proto/demo5/post/create
```
