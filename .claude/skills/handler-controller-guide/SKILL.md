---
name: handler-controller-guide
description: Handler/Controller 开发指南。用于创建 ApiProto Handler 或 Restful Api Controller 代码，或审查现有代码是否符合分层架构规范。当用户提到"创建 Handler"、"新建 Controller"、"生成 Handler 代码"、"审查 Handler"、"Handler 开发"、"Controller 开发"、"入口层代码"、"ApiProto Handler"等关键词时触发此 skill。也适用于用户请求帮助理解 Handler/Controller 的职责边界、验证流程、响应构建等问题。
---

# Handler/Controller 开发指南

本 skill 用于指导 Laravel 模块化项目中的入口层（Handler/Controller）开发，涵盖 ApiProto Handler 和 Restful Api Controller 两种类型。

## 核心原则

**完整层级关系**：
```
Proto/Api规范 → Handler/Controller → Validation → Validator → Service → Logic → Model → Event
```

**入口层职责**：协调流程、参数提取、HTTP/session 读取、验证调用、Service 调用、响应构建
**禁止操作**：业务逻辑实现、直接 Model 操作

---

## 层级关系详解

### 调用链路

**ApiProto 流程**：
```
Proto定义 → Message生成 → Handler → Validation → Validator → Service → Logic → Model → Event
```

**Restful Api 流程**：
```
Api规范 → Controller → Validation → Validator → Service → Logic → Model → Event
```

### 各层职责

| 层级 | 职责 | 可调用对象 | 禁止操作 |
|------|------|-----------|---------|
| **Proto/Api规范** | 定义接口契约、数据结构 | - | - |
| **Handler/Controller** | 协调流程、参数提取、HTTP/session 读取、响应构建 | Validation, Service, Logic, getClientInfo() | Model操作, 业务逻辑 |
| **Validation** | 验证规则配置、调用Validator | Validator, Model（查询） | 业务逻辑, HTTP/session读取 |
| **Validator** | 自定义验证逻辑实现 | Logic, Model（查询） | 业务逻辑, HTTP/session读取 |
| **Service** | 业务协调、数据操作、事件触发 | Logic, Model, Event, 其他Service | HTTP/session读取, request() |
| **Logic** | 纯函数计算、复杂数据组装 | -（静态方法） | HTTP/session, Model, Service调用 |
| **Model** | 数据结构、关系映射、数据库查询 | - | HTTP/session, 业务逻辑 |
| **Event** | 跨模块通信、异步处理 | Listener, Queue | - |

### 关键规范

1. **显性传参原则**：
   - Handler → Service：参数由 Handler 从 Message 提取并传递
   - Controller → Service：参数由 Controller 从 Request 提取并传递
   - Service 禁止读取 HTTP/session/request()

2. **单模块全栈架构**：
   - 一个模块包含完整业务逻辑（Api/DcatAdmin/Web 共享 Models/Services/Logics）
   - Handler/Controller 是入口层，协调流程不写业务

3. **事件驱动架构**：
   - Service 触发 Event（如 AccountRegistered）
   - Event → Listener → Queue（跨模块通信）

---

## ApiProto Handler 开发

### 1. Handler 代码模板

```php
<?php

namespace Modules\{模块}\ApiProto\Handlers;

use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\{模块}\{子包}\{HandlerName}Request;
use Modules\ApiProto\Protobuf\{模块}\{子包}\{HandlerName}Response;
use Modules\ApiProto\Protobuf\{模块}\{子包}\{DataClass};
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\{模块}\Services\{ServiceClass};
use Modules\{模块}\Validations\{ValidationClass};

/**
 * {功能描述}Handler
 *
 * 处理 /api/proto/{模块}/{子包}/{路径} 请求
 * 对应 Message: {HandlerName}Request
 */
class {HandlerName}Handler extends BaseHandler
{
    /**
     * 是否需要登录
     * true: token 中必须有 user_id（已登录用户）
     * false: token 可以是匿名或登录状态
     * @var bool
     */
    protected bool $need_login = {true/false};

    /**
     * 处理请求
     *
     * @param {真实request类名} $data 
     * @return {真实Response类名}
     */
    public function handle(Message $data): Message
    {
        // 1. 提取请求参数
        $param1 = $data->getParam1();
        $param2 = $data->getParam2();
        // ...

        // 2. 参数验证（使用 Validation 类）
        $validationData = [
            'param1' => $param1,
            'param2' => $param2,
        ];

        $validation = {ValidationClass}::make($validationData)->validate();
        if ($validation->isFail()) {
            $response = new {HandlerName}Response();
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_BAD_REQUEST,
                '参数验证失败: ' . $validation->firstError()
            );
        }

        // 3. 调用 Service 处理业务（显性传参）
        $service = new {ServiceClass}();
        $result = $service->methodName($param1, $param2, $this->user_id);

        // 4. 构建成功响应
        $response = new {HandlerName}Response();
        $dataObj = $this->toDataObject($result);

        return $this->successResponse($response, $dataObj, '操作成功');
    }

    /**
     * Model → ProtoData 转换
     *
     * @param \Modules\{模块}\Models\{ModelClass} $model
     * @return {DataClass}
     */
    private function toDataObject($model): {DataClass}
    {
        $data = new {DataClass}();
        $data->setId($model->id);
        $data->setName($model->name);
        // ... 其他字段映射
        $data->setCreatedAt($model->created_at->timestamp);

        return $data;
    }
}
```

### 2. Handler 核心规范

#### 必须遵守
- ✅ 继承 `BaseHandler`，实现 `handle(Message $data): Message`
- ✅ 设置 `need_login` 属性（true 需要 user_id，false 可匿名）
- ✅ **显性传参**：从 Message 提取参数，传递给 Service/Validation
- ✅ 使用 Validation 类验证参数（禁止 Handler 内写验证逻辑）
- ✅ 使用 `successResponse()` / `errorResponse()` 构建响应
- ✅ Model → ProtoData 转换方法（私有方法，纯数据映射）

#### 禁止操作
- ❌ 业务逻辑计算（由 Service/Logic 负责）
- ❌ 直接操作 Model（由 Service 调用 Model）
- ❌ 内联验证逻辑（必须用 Validation 类）
- ❌ 数据库查询（由 Service/Model 负责）

### 3. Handler 流程详解

**标准流程**：
```
提取参数 → Validation 验证 → Service 调用 → Model→ProtoData 转换 → 响应构建
```

**参数提取**：
```php
// 从 Message 对象提取参数
$title = $data->getTitle();
$content = $data->getContent();
$userId = $this->user_id; // 中间件注入的登录用户 ID
```

**参数验证**：
```php
// 使用 Validation 类（继承 inhere/php-validate）
$validation = PostCreateValidation::make([
    'title' => $title,
    'content' => $content,
])->validate();

if ($validation->isFail()) {
    return $this->errorResponse(
        $response,
        StatusCode::STATUS_CODE_BAD_REQUEST,
        '参数验证失败: ' . $validation->firstError()
    );
}
```

**Service 调用**：
```php
// 显性传参，禁止 Service 内读取 HTTP
$service = new PostService();
$post = $service->createPost($title, $content, $userId);
```

**响应构建**：
```php
// 成功响应
$response = new Demo5PostsCreateResponse();
$postData = $this->toPostData($post);
return $this->successResponse($response, $postData, '文章创建成功');

// 错误响应
$response = new Demo5PostsCreateResponse();
return $this->errorResponse(
    $response,
    StatusCode::STATUS_CODE_NOT_FOUND,
    '文章不存在'
);

// 列表响应（带分页）
$postsData = array_map(fn($post) => $this->toPostData($post), $posts);
return $this->listResponse(
    $response,
    $postsData,
    $page,
    $pageSize,
    $total,
    '文章列表获取成功'
);
```

### 4. Proto Message 命名推导

**路径 → Message 转换**：
```
/api/proto/demo5/post/create → Demo5PostsCreateRequest / Demo5PostsCreateResponse
/api/proto/account/auth/login → AccountAuthLoginRequest / AccountAuthLoginResponse
```

**推导规则**：
1. 路径连字符转为驼峰：`create` → `Create`
2. 路径层级拼接：`demo5/post/create` → `Demo5PostCreate`
3. 添加模块前缀：`Demo5`
4. Request/Response 后缀

**Handler 命名**：去掉 Request/Response 后缀
```
Demo5PostsCreateRequest → Demo5PostsCreateHandler
AccountAuthLoginRequest → AccountAuthLoginHandler
```

### 5. 客户端信息获取

Handler 是入口层，**负责 HTTP/session 读取**，读取后显性传给 Service（Service 禁止读 HTTP）。BaseHandler 提供 `getClientInfo()` 便捷方法：

```php
// 获取客户端 IP 和 UserAgent
$clientInfo = $this->getClientInfo();
$ip = $clientInfo['ip'];
$userAgent = $clientInfo['user_agent'];

// 显性传递给 Service
$service->login($email, $password, $ip, $userAgent);
```

---

## Restful Api Controller 开发

### 1. Controller 代码模板

```php
<?php

namespace Modules\{模块}\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\{模块}\Services\{ServiceClass};
use Modules\{模块}\Api\Resources\{ResourceClass};

/**
 * {功能描述}Controller
 *
 * 提供 RESTful API 接口
 */
class {ControllerName}Controller extends Controller
{
    /**
     * 列表查询
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // 1. 参数提取
        $filters = $request->only(['status', 'keyword']);
        $perPage = $request->get('per_page', 15);

        // 2. 调用 Service（显性传参）
        $service = new {ServiceClass}();
        $list = $service->getList($filters, $perPage);

        // 3. 返回 Resource
        return {ResourceClass}::collection($list);
    }

    /**
     * 详情查询
     *
     * @param int $id
     * @return {ResourceClass}
     */
    public function show($id)
    {
        $service = new {ServiceClass}();
        $model = $service->getDetail($id);

        return new {ResourceClass}($model);
    }

    /**
     * 创建资源
     *
     * @param Request $request
     * @return {ResourceClass}
     */
    public function store(Request $request)
    {
        // 1. 参数验证
        $validated = $request->validate([
            'field1' => 'required|string',
            'field2' => 'required|integer',
        ]);

        // 2. 调用 Service（显性传参）
        $service = new {ServiceClass}();
        $model = $service->create($validated);

        // 3. 返回 Resource
        return new {ResourceClass}($model);
    }

    /**
     * 更新资源
     *
     * @param Request $request
     * @param int $id
     * @return {ResourceClass}
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'field1' => 'sometimes|string',
            'field2' => 'sometimes|integer',
        ]);

        $service = new {ServiceClass}();
        $model = $service->update($id, $validated);

        return new {ResourceClass}($model);
    }

    /**
     * 删除资源
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = new {ServiceClass}();
        $service->delete($id);

        return response()->noContent();
    }
}
```

### 2. Controller 核心规范

#### 必须遵守
- ✅ 继承 `Controller`（标准 RESTful）
- ✅ 标准方法名：`index/show/store/update/destroy`
- ✅ **显性传参**：从 Request 提取参数，传递给 Service
- ✅ 调用 Service 处理业务（禁止直接调用 Model）
- ✅ 返回 Resource（PostResource/PostCollection）
- ✅ 使用 `$request->validate()` 验证参数

#### 禁止操作
- ❌ 业务逻辑计算（由 Service/Logic 负责）
- ❌ 直接操作 Model（必须调用 Service）
- ❌ 复杂验证逻辑（内联简单验证即可）
- ❌ 数据库查询（由 Service/Model 负责）

### 3. Resource 定义

```php
<?php

namespace Modules\{模块}\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * {资源名称}Resource
 */
class {ResourceClass} extends JsonResource
{
    /**
     * 转换资源为数组
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

---

## 最佳实践参考

查看项目中的最佳实践 Handler 实现：

**✅ 最佳实践示例（Account 模块）**：
- `Modules/Account/ApiProto/Handlers/AuthRegisterByPhoneHandler.php`（短信注册）
- `Modules/Account/ApiProto/Handlers/AuthSendRegisterCodeHandler.php`（发送验证码）

**❌ 反面教材（Demo5 模块）**：
- `Modules/Demo5/Api/Controllers/PostController.php`（**直接调用 Model**，不符合规范！）
- 正确做法：Controller 应调用 Service，Service 再调用 Model

**BaseHandler 基类**：
- `Modules/ApiProto/Handlers/BaseHandler.php`（响应构建方法）

---

## 开发流程建议

### 新建 Handler 流程

1. **定义 Proto Message**：
   - 根据业务需求编写 proto 定义
   - 示例路径：`Modules/{模块}/protos/{子包}.proto`

   ```protobuf
   syntax = "proto3";
   package account.auth;

   message AccountAuthRegisterByPhoneRequest {
       string phone = 1;
       string country_code = 2;
       string code = 3;
       string password = 4;
       string referral_code = 5;  // 可选邀请码
   }

   message AccountAuthRegisterByPhoneResponse {
       ApiProto.Common.BaseResponse base = 1;
       .account.account.AccountAccountData data = 2;
   }
   ```

2. **生成 Proto PHP 代码**：
   ```bash
   composer run buildproto
   ```

3. **创建 Handler 文件**：
   - 目录：`Modules/{模块}/ApiProto/Handlers/`
   - 命名：从路径推导 Handler 名（连字符转驼峰）

   ```
   /api/proto/account/auth/register-by-phone → AccountAuthRegisterByPhoneHandler
   ```

4. **创建 Validation 类**：
   - 目录：`Modules/{模块}/Validations/`
   - 命名：`AccountRegisterByPhoneValidation.php`
   - 继承 `Inhere\Validate\Validation`

5. **调用 Service 处理业务**：
   - Service 已存在：直接调用静态方法或实例方法
   - Service 不存在：先创建 Service 方法

6. **编写 Model→ProtoData 转换**：
   - 私有方法：`buildAccountData()` 或 `toPostData()`
   - 纯数据映射，无逻辑计算

7. **测试验证**：
   - 使用 `playwright-laravel` agent 测试 API
   - 使用 `review-code` agent 审查代码规范

### 新建 Controller 流程

1. **创建 Controller 文件**：
   - 目录：`Modules/{模块}/Api/Controllers/`
   - 命名：`{ControllerName}Controller.php`

2. **创建 Resource 类**：
   - 目录：`Modules/{模块}/Api/Resources/`
   - 命名：`{ResourceClass}.php`

3. **调用 Service**：
   - Service 已存在：直接调用
   - Service 不存在：先创建 Service 方法

4. **定义路由**：
   - 文件：`Modules/{模块}/Api/routes.php`
   - 标准 RESTful 路径

5. **测试验证**：
   - 使用 playwright-laravel agent 测试 API
   - 使用 review-code agent 审查代码规范

---

## 常见问题

### 1. Handler 如何获取登录用户 ID？

```php
// 中间件自动注入到 BaseHandler
$userId = $this->user_id;

// 传递给 Service（显性传参）
$service->createPost($title, $content, $userId);
```

### 2. Handler 如何获取客户端 IP？

```php
// Handler 是入口层，可以直接读取 HTTP/session
// 也可用 BaseHandler 的便捷方法
$clientInfo = $this->getClientInfo();
$ip = $clientInfo['ip'];

// 关键：读取后显性传给 Service（Service 禁止读 HTTP）
$service->login($email, $password, $ip);
```

### 3. Handler 是否需要 try-catch？

**两种风格**：

**风格 1：Handler 不捕获异常（推荐）**
```php
// Handler 不捕获异常，让中间件统一处理
public function handle(Message $data): Message
{
    $service->createPost($title, $content);
    return $this->successResponse($response, $data);
}
// 异常由中间件捕获并返回统一错误响应
```

**风格 2：Handler 捕获异常（特殊场景）**
```php
// 需要自定义错误消息时使用 try-catch
public function handle(Message $data): Message
{
    try {
        $service->createPost($title, $content);
        return $this->successResponse($response, $data);
    } catch (\Throwable $e) {
        return $this->errorResponse(
            $response,
            StatusCode::STATUS_CODE_INTERNAL_ERROR,
            '创建失败: ' . $e->getMessage()
        );
    }
}
```

**建议**：大多数场景使用风格 1，特殊错误处理使用风格 2。

### 4. Controller 是否需要 Validation 类？

**简单验证**：使用 `$request->validate()`
```php
// Controller 内联验证（简单规则）
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required|string',
]);
```

**复杂验证**：使用 Validation 类
```php
// 使用 Validation 类（复杂规则、自定义验证）
$validation = PostCreateValidation::make($request->all())->validate();
if ($validation->isFail()) {
    return response()->json(['error' => $validation->firstError()], 400);
}
```

### 5. Handler 如何处理列表分页？

```php
// Service 返回分页对象
$paginator = $service->getPostList($filters, $page, $pageSize);

// Handler 使用 listResponse 构建响应
$postsData = array_map(fn($post) => $this->toPostData($post), $paginator->items());

return $this->listResponse(
    $response,
    $postsData,
    $paginator->currentPage(),
    $paginator->perPage(),
    $paginator->total(),
    '文章列表获取成功'
);
```

---

## 审查代码规范

审查现有 Handler/Controller 时检查：

### Handler 审查清单
1. ✅ 继承 `BaseHandler`，实现 `handle()` 方法
2. ✅ 设置 `need_login` 属性（true/false）
3. ✅ 参数从 Message 提取（显性传参）
4. ✅ 使用 Validation 类验证（禁止内联验证）
5. ✅ 调用 Service 处理业务（禁止直接 Model 操作）
6. ✅ 使用 `successResponse()` / `errorResponse()` 构建响应
7. ✅ Model→ProtoData 转换方法（私有、纯映射）
8. ❌ 无业务逻辑计算（由 Service/Logic 负责）
9. ❌ 无数据库查询（由 Service/Model 负责）

### Controller 审查清单
1. ✅ 继承 `Controller`（标准 RESTful）
2. ✅ 标准方法名（index/show/store/update/destroy）
3. ✅ 参数从 Request 提取（显性传参）
4. ✅ 调用 Service 处理业务（禁止直接 Model 操作）
5. ✅ 返回 Resource（PostResource/PostCollection）
6. ✅ 使用 `$request->validate()` 或 Validation 类验证
7. ❌ 无业务逻辑计算（由 Service/Logic 负责）
8. ❌ 无数据库查询（由 Service/Model 负责）

---

## 层职责对比（完整架构）

| 层级 | 职责 | 可调用对象 | 禁止操作 | 示例文件 |
|------|------|-----------|---------|---------|
| **Proto/Api规范** | 定义接口契约、数据结构 | - | - | `Modules/Account/protos/auth.proto` |
| **Handler** | 协调流程、参数提取、HTTP/session 读取、响应构建 | Validation, Service, Logic, getClientInfo() | Model, 业务逻辑 | `Modules/Account/ApiProto/Handlers/AuthRegisterByPhoneHandler.php` |
| **Controller** | HTTP处理、参数提取、Resource返回 | Validation, Service, Logic, Request | Model, 业务逻辑 | `Modules/{模块}/Api/Controllers/*.php` |
| **Validation** | 验证规则配置、调用Validator | Validator, Model（查询） | 业务逻辑, HTTP/session | `Modules/Account/Validations/AccountRegisterByPhoneValidation.php` |
| **Validator** | 自定义验证逻辑实现 | Logic, Model（查询） | 业务逻辑, HTTP/session | `Modules/Account/Validators/*.php` |
| **Service** | 业务协调、数据操作、事件触发 | Logic, Model, Event, 其他Service | HTTP/session, request() | `Modules/Account/Services/AccountService.php` |
| **Logic** | 纯函数计算、复杂数据组装 | -（静态方法） | HTTP/session, Model, Service调用 | `Modules/{模块}/Logics/*.php` |
| **Model** | 数据结构、关系映射、数据库查询 | - | HTTP/session, 业务逻辑 | `Modules/Account/Models/Account.php` |
| **Event** | 跨模块通信、异步触发 | Listener, Queue | - | `Modules/Account/Events/ModuleAccountRegisteredEvent.php` |
| **Listener** | 事件处理、队列任务 | Service, Logic, Queue | HTTP/session | `Modules/Referral/Listeners/HandleAccountRegisteredListener.php` |

---

## 调用链路示例（短信注册）

**完整流程**：
```
Proto定义 → Handler → Validation → Validator → Service → Logic → Model → Event → Listener
```

**详细步骤**：

1. **Proto 定义**（接口契约）：
   ```protobuf
   message AccountAuthRegisterByPhoneRequest {
       string phone = 1;
       string code = 3;
       string password = 4;
       string referral_code = 5;
   }
   ```

2. **Handler**（协调流程）：
   ```php
   // 提取参数 → 验证 → Service调用 → 响应构建
   $phone = $data->getPhone();
   $validation = AccountRegisterByPhoneValidation::make(...)->validate();
   $account = AccountService::createAccountByPhone($phone, $password, $referralCode);
   return $this->successResponse($response, $accountData);
   ```

3. **Validation**（验证规则配置）：
   ```php
   class AccountRegisterByPhoneValidation extends Validation
   {
       public function rules(): array {
           return [
               ['phone', 'required'],
               ['phone', 'phoneFormat'],  // 调用 Validator
               ['phone', 'phoneUnique'],
           ];
       }
   }
   ```

4. **Validator**（自定义验证）：
   ```php
   protected function phoneFormatValidator(mixed $value): bool {
       $countryCode = $this->get('country_code', '+86');
       return PhoneBinding::validatePhoneFormat($value, $countryCode);  // 调用 Model
   }
   ```

5. **Service**（业务协调）：
   ```php
   public static function createAccountByPhone($phone, $password, $referralCode) {
       $account = Account::create([...]);  // 调用 Model
       $phoneBinding = new PhoneBinding();  // 调用 Model
       event(new ModuleAccountRegisteredEvent($account, $referralCode));  // 触发 Event
       return $account;
   }
   ```

6. **Model**（数据库操作）：
   ```php
   Account::create(['email' => $phone . '@phone.placeholder', ...]);
   PhoneBinding::create(['account_id' => $account->id, 'phone' => $phone, ...]);
   ```

7. **Event**（跨模块通信）：
   ```php
   event(new ModuleAccountRegisteredEvent($account, $referralCode));
   ```

8. **Listener**（事件处理）：
   ```php
   class HandleAccountRegisteredListener {
       public function handle(ModuleAccountRegisteredEvent $event) {
           ReferralService::createReferralRelation($referralCode, $account->id);  // 调用 Service
       }
   }
   ```

**关键规范**：
- Handler 提取参数，显性传递给 Service（Service 禁止读取 HTTP）
- Validation 配置规则，Validator 实现验证逻辑
- Service 协调业务，调用 Model 和 Logic，触发 Event
- Logic 纯计算，无状态依赖（静态方法）
- Model 数据操作，禁止业务逻辑

## 开发示例

### 示例 1：短信注册 Handler（最佳实践）

**业务需求**：短信注册 API，不需要登录，支持邀请码

**Proto 定义**（`Modules/Account/protos/auth.proto`）：
```protobuf
syntax = "proto3";
package account.auth;

// /api/proto/account/auth/register-by-phone
message AccountAuthRegisterByPhoneRequest {
    string phone = 1;           // 手机号
    string country_code = 2;    // 国家代码
    string code = 3;            // 短信验证码
    string password = 4;        // 密码
    string referral_code = 5;   // 邀请码（可选）
}

message AccountAuthRegisterByPhoneResponse {
    ApiProto.Common.BaseResponse base = 1;
    .account.account.AccountAccountData data = 2;
}
```

**Handler 实现**（最佳实践，符合所有规范）：
```php
<?php

namespace Modules\Account\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\ApiProto\Protobuf\Account\Auth\AccountAuthRegisterByPhoneRequest;
use Modules\ApiProto\Protobuf\Account\Auth\AccountAuthRegisterByPhoneResponse;
use Modules\ApiProto\Protobuf\Account\Account\AccountAccountData;
use Modules\Account\Services\AccountService;
use Modules\Account\Validations\AccountRegisterByPhoneValidation;
use Modules\FeatureSms\Services\SmsService;
use Modules\FeatureSms\Enums\CODE_TYPE;

/**
 * 短信注册 Handler
 *
 * 处理 /api/proto/account/auth/register-by-phone
 * 对应 Message: AccountAuthRegisterByPhoneRequest
 */
class AuthRegisterByPhoneHandler extends BaseHandler
{
    /**
     * 注册接口不需要登录
     * @var bool
     */
    protected bool $need_login = false;

    /**
     * 处理短信注册请求
     *
     * @param Message $data AccountAuthRegisterByPhoneRequest
     * @return Message AccountAuthRegisterByPhoneResponse
     */
    public function handle(Message $data): Message
    {
        // 1. 提取请求参数（显性传参）
        $phone = $data->getPhone();
        $countryCode = $data->getCountryCode();
        $code = $data->getCode();
        $password = $data->getPassword();
        $referralCode = $data->getReferralCode(); // 提取邀请码

        // 2. 参数验证（使用 Validation 类）
        $validationData = [
            'phone' => $phone,
            'country_code' => $countryCode,
            'password' => $password,
            'code' => $code,
        ];

        $validation = AccountRegisterByPhoneValidation::make($validationData)->validate();

        if ($validation->isFail()) {
            $response = new AccountAuthRegisterByPhoneResponse();
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_BAD_REQUEST,
                '参数验证失败: ' . $validation->firstError()
            );
        }

        // 3. 验证短信验证码（调用 SmsService）
        $isVerified = SmsService::verifyCode(CODE_TYPE::REGISTER, $phone, $code);

        if (!$isVerified) {
            $response = new AccountAuthRegisterByPhoneResponse();
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_BAD_REQUEST,
                '验证码错误或已过期'
            );
        }

        // 4. 调用 Service 创建账户（显性传参）
        // Service 禁止读取 HTTP，参数由 Handler 传递
        $account = AccountService::createAccountByPhone(
            $phone,
            $countryCode,
            $password,
            \Modules\Account\Enums\AccountStatus::ACTIVE,
            true,  // isRegistration = true，触发事件
            $referralCode // 传递邀请码
        );

        if (!$account) {
            $response = new AccountAuthRegisterByPhoneResponse();
            return $this->errorResponse(
                $response,
                StatusCode::STATUS_CODE_INTERNAL_ERROR,
                '注册失败'
            );
        }

        // 5. 构建响应数据
        $response = new AccountAuthRegisterByPhoneResponse();
        $accountData = $this->buildAccountData($account);

        return $this->successResponse($response, $accountData, '注册成功');
    }

    /**
     * Model → ProtoData 转换（纯数据映射）
     *
     * @param \Modules\Account\Models\Account $account
     * @return AccountAccountData
     */
    private function buildAccountData($account): AccountAccountData
    {
        $data = new AccountAccountData();
        $data->setId($account->id);
        $data->setEmail($account->email);
        $data->setStatus($account->account_status);
        $data->setCreatedAt($account->created_at->timestamp);

        return $data;
    }
}
```

**关键规范验证**：
- ✅ 继承 BaseHandler，实现 handle() 方法
- ✅ need_login = false（注册不需要登录）
- ✅ 参数从 Message 提取（显性传参）
- ✅ 使用 Validation 类验证（AccountRegisterByPhoneValidation）
- ✅ 调用 Service 处理业务（AccountService::createAccountByPhone）
- ✅ Service 静态方法调用，参数显性传递
- ✅ Model→ProtoData 转换方法（buildAccountData）
- ✅ 使用 successResponse/errorResponse 构建响应
- ❌ 无业务逻辑计算（由 Service/Logic 负责）
- ❌ 无数据库查询（由 Service/Model 负责）
- ✅ 参数从 Message 提取，显性传给 Service

### 示例 2：文章 Controller（反面教材）

**反面教材**：Demo5 的 PostController 直接调用 Model，不符合规范！

```php
// ❌ 错误示例：Controller 直接操作 Model
class PostController extends Controller
{
    public function index()
    {
        // ❌ 错误：直接调用 Model
        $posts = Demo5Post::with('comments')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return new PostCollection($posts);
    }
}
```

**正确做法**：Controller 应调用 Service，Service 再调用 Model

```php
// ✅ 正确示例：Controller 调用 Service
class PostController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'keyword']);
        $perPage = $request->get('per_page', 15);

        // ✅ 正确：调用 Service（显性传参）
        $service = new PostService();
        $list = $service->getPostList($filters, $perPage);

        return PostResource::collection($list);
    }
}
```

**Service 实现**（正确的 Service 示例）：
```php
// ✅ Service 调用 Model，禁止读取 HTTP
class PostService
{
    public function getPostList(array $filters, int $perPage)
    {
        // Service 操作 Model
        $query = Demo5Post::with('comments');

        // Service 处理筛选逻辑
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }
}
```

**对比总结**：
- ❌ 反面教材：Controller → Model（违反分层原则）
- ✅ 正确做法：Controller → Service → Model（符合分层原则）

---

## 注意事项

1. **生成代码后测试**：
   - 使用 `playwright-laravel` agent 测试 API 功能
   - 使用 `review-code` agent 审查代码规范

2. **参考最佳实践**：
   - 遇到问题先查看 Demo5 模块的 Handler/Controller
   - 查看 BaseHandler 基类的响应构建方法

3. **遵守分层原则**：
   - Handler/Controller 只协调流程，不写业务逻辑
   - Service 处理业务协调，禁止读取 HTTP
   - Logic 纯计算，无状态依赖

4. **显性传参原则**：
   - Handler → Service：参数由 Handler 显性传递
   - Controller → Service：参数由 Controller 从 Request 提取并传递

5. **错误处理**：
   - 大多数场景：Handler 不捕获异常，由中间件统一处理
   - 特殊场景：Handler 捕获异常并返回自定义错误消息

6. **Proto 生成**：
   - 修改 proto 后运行：`composer run buildproto`
   - Handler 使用生成的 PHP Message 类

---

**最后提醒**：Handler/Controller 是入口层，职责单一且清晰。协调流程、验证参数、调用 Service、构建响应——其他逻辑交给 Service/Logic 层处理。保持简单、专注、可测试。
