---
name: module-structure
description: 进行模块初始化/模块文件夹创建时/模块规划时激活
---

# 模块结构规则

## 核心理念：单模块全栈架构

**核心思想**: 每个业务模块是独立的功能单元，内部包含完整的分层架构和所有对外入口（Web、API、Admin、Merchant等）。模块即边界，入口在模块内，业务逻辑也在模块内。

基于单模块自包含理念，采用**单业务单模块全栈架构**模式，实现清晰的业务边界和零跨模块依赖。

---

## 一、单模块架构总览

### 1.1 架构层次

```
┌─────────────────────────────────────────┐
│         模块（业务边界）                  │
├─────────────────────────────────────────┤
│  入口层：Web │ API │ Admin │ Merchant   │
│  (模块内所有入口形式)                     │
├─────────────────────────────────────────┤
│  业务层：Models │ Services │ Logics     │
│  (核心业务逻辑，不依赖外部)               │
├─────────────────────────────────────────┤
│  基础层：Events │ Jobs │ Enums │ DTOs   │
│  (支撑组件)                              │
└─────────────────────────────────────────┘
```

### 1.2 模块特点

**单模块 = 单业务 + 多入口**

| 特性 | 说明 |
|------|------|
| **业务独立** | 模块封装完整业务逻辑，不依赖其他业务模块 |
| **入口全栈** | Web/API/Admin/Merchant都在同一模块内 |
| **分层清晰** | 入口层 → 业务层 → 基础层，职责明确 |
| **自包含** | 数据库迁移、配置、路由、视图全部在模块内 |
| **易于测试** | 模块可独立运行和测试 |

---

## 二、单模块完整架构

### 2.1 分层设计

```
请求到达 → Controller → Service → Logic → Model
```

| 层级 | 职责 | 特点 |
|------|------|------|
| **Models** | 数据结构定义、关系映射、数据访问 | Eloquent ORM，数据验证 |
| **Services** | 协调组件、复杂业务编排 | 实例化，可注入依赖，**禁止读取HTTP/session/cookie** |
| **Logics** | 纯函数计算 | 静态方法，无状态，高性能 |
| **Controller/Web/Admin/Api** | HTTP请求处理、session读取、调用下层 | 显性传参给Services，不同入口形式 |

### 2.2 入口组织形式

单模块内可包含多种入口形式：

```
模块根目录/
├── Api/                    # API入口（RESTful API）
│   ├── Controllers/        # API控制器
│   ├── Requests/           # API请求验证
│   ├── Resources/          # API资源响应
│   └── Transformers/       # 数据转换器
├── DactAdmin/                  # DactAdmin后台入口
│   ├── Controllers/        # 后台控制器
│   ├── Forms/              # 表单类
│   ├── Grids/              # 表格类
│   ├── Actions/            # 操作类
│   └── Repositories/       # 数据仓库
├── Web/                    # Web前端入口（可选）
│   ├── Controllers/        # Web控制器
│   └── Requests/           # Web请求验证
├── Merchant/               # 商户后台入口（可选）
│   ├── Controllers/        # 商户控制器
│   ├── Pages/              # Livewire页面
│   └── Components/         # Livewire组件
├── Console/                # CLI命令入口
│   └── Commands/           # Artisan命令
```

### 2.3 关键规则

- **Services层**: 与HTTP状态无关，参数必须由Controller显性传入，禁止隐性读取HTTP数据
- **Logics层**: 纯静态方法，无副作用，易于测试和缓存
- **入口层**: 负责读取session/cookie，准备参数传递给Services
- **共享业务**: 所有入口共享同一 Models/Services/Logics 层

### 2.4 完整目录结构(严格遵守)

```
业务模块根目录/
├── Api/                            # API入口（RESTful）
│   ├── Controllers/                # API控制器
│   ├── Requests/                   # API请求验证
│   ├── Resources/                  # API资源响应
│   └── Transformers/               # 数据转换器
├── DactAdmin/                          # DactAdmin后台入口
│   ├── Actions/                    # 操作类
│   ├── Controllers/                # 后台控制器
│   ├── Forms/                      # 表单类
│   ├── Grids/                      # 表格类
│   ├── Metrics/                    # 图表类
│   ├── Repositories/               # 数据仓库
│   └── Requests/                   # 后台请求验证
│   └── .../                              # 后期其他特有目录
├── Web/                            # Web前端入口（可选）
│   ├── Controllers/                # Web控制器
│   └── Requests/                   # Web请求验证
├── Merchant/                       # 商户后台入口（可选）
│   ├── Controllers/                # 商户控制器
│   ├── Pages/                      # Livewire页面
│   └── Components/                 # Livewire组件
├── Commands/                       # Artisan命令
├── config/                         # 模块配置文件
│   ├── admin_menu.php              # Admin菜单配置
│   └── module_config.php           # 模块自定义配置
├── Database/                       # 数据库相关
│   ├── Factories/                  # 模型工厂
│   ├── Migrations/                 # 数据库迁移
│   └── Seeders/                    # 数据种子
├── Docs/                           # 模块文档
├── Dtos/                           # 数据传输对象
├── Enums/                          # 枚举类型
├── Events/                         # 事件定义
├── Listeners/                      # 事件监听器
├── Logics/                         # 业务逻辑层（静态方法）
├── Models/                         # Eloquent模型层
├── Providers/                      # 服务提供者
├── QueueJobs/                      # 队列任务
├── Services/                       # 服务层（业务协调）
├── Validators/                     # Validator 验证器
├── Validations/                    # Validation 验证
├── resources/                      # 资源文件（可选）
│   ├── assets/                     # 静态资源
│   └── views/                      # 视图模板（Web/Admin共用）
├── routes/                         # 路由定义
│   ├── api.php                     # API路由
│   ├── admin.php                   # Admin后台路由
│   ├── web.php                     # Web路由（可选）
│   ├── merchant.php                # 商户路由（可选）
│   └── console.php                 # 控制台路由
├── Tests/                          # 测试文件
│   ├── Feature/                    # 功能测试
│   └── Unit/                       # 单元测试
├── DEV.md                          # 开发记录
├── README.md                       # 模块说明
└── module.json                     # 模块配置
```

---

## 三、入口类型选择指南

单模块内可按需启用不同入口形式：

| 业务场景 | 推荐入口形式 | 说明 |
|---------|-------------|------|
| 内部管理后台、数据管理 | **Admin入口** (DactAdmin) | 面向管理员，快速搭建后台 |
| RESTful API接口 | **Api入口** | 面向客户端/第三方，前后端分离 |
| 用户前台网站、H5页面 | **Web入口** (可选) | 面向最终用户，传统Web页面 |
| 商户管理、现代化界面 | **Merchant入口** (可选) | 面向商户用户，Livewire组件化 |

**入口组合建议**：
- **标准组合**：Admin + Api（后台管理 + API接口）
- **全栈组合**：Admin + Api + Web + Merchant（多入口全覆盖）
- **最小组合**：仅 Admin 或 仅 Api（单一入口）

---

## 四、单模块开发规范

### 4.1 核心开发原则

1. **逻辑优先**: 优先使用Logics层的静态方法
2. **服务协调**: 复杂业务流程使用Services层
3. **事件解耦**: 使用事件系统降低模块内组件耦合
4. **队列异步**: 耗时操作使用队列任务
5. **DTO传参**: 复杂数据使用DTO封装
6. **枚举状态**: 使用枚举管理状态类型
7. **类型转换**: 使用Casts处理复杂数据类型
8. **入口共享**: 所有入口共享同一业务层（Models/Services/Logics）

### 4.2 模块边界

- **单模块内完整**: 业务逻辑和所有入口形式在同一模块内
- **入口层职责**: 只负责接收请求、解析参数、调用业务层、返回响应
- **业务层职责**: 纯业务处理，不依赖HTTP状态（session/cookie）
- **基础层职责**: 支撑组件（Events/Jobs/Enums/DTOs）

### 4.3 数据流向

```
请求 → DactAdmin/Api/Web Controller → Service → Logic → Model
事件 → Event → 模块内Listener → QueueJob → 结果
配置 → Provider → Service → Application
```

### 4.4 事件驱动架构

**双Event设计**：模块内Event(完整参数,同步) → 统一转发器(异步) → 跨模块Event(精简参数,异步)

#### Event命名规范

| 类型 | 格式 | 示例 |
|------|------|------|
| 模块内Event | `{业务对象}{动作}Event` | `AccountCreatedEvent` |
| 跨模块Event | `Module{模块名}{事件名}Event` | `ModuleAccountCreatedEvent` |
| 统一转发器 | `DispatchModule{模块名}` | `DispatchModuleAccount` |

#### Event参数设计

```php
// 模块内Event（完整参数）
class AccountCreatedEvent {
    public Account $account;
    public array $data;
}

// 跨模块Event（精简参数）
class ModuleAccountCreatedEvent {
    public Account $account;
}
```

#### Listener实现规范

```php
// 模块内Listener（同步，不实现ShouldQueue）
class LogAccountCreatedListener {
    public function handle(AccountCreatedEvent $event): void {
        Log::info('账户创建', $event->getLogData());
    }
}

// 跨模块Listener（异步，queue='event')
class CreateDefaultUser implements ShouldQueue {
    use Queueable;
    public string $queue = 'event';
    public int $tries = 3;

    public function handle(ModuleAccountCreatedEvent $event): void {
        // 创建默认用户
    }
}
```

#### 统一转发器架构

```php
// Modules/Base/Listeners/ModuleDispatcher.php
abstract class ModuleDispatcher implements ShouldQueue {
    use Queueable;
    public string $queue = 'event';
    protected array $eventMap = [];

    public function handle(object $event): void {
        $config = $this->eventMap[get_class($event)];
        $payload = collect($config['fields'])
            ->map(fn($f) => $event->{$f})
            ->all();
        $config['target']::dispatch(...$payload);
    }
}

// Modules/Account/Listeners/DispatchModuleAccount.php
class DispatchModuleAccount extends ModuleDispatcher {
    protected array $eventMap = [
        AccountCreatedEvent::class => [
            'target' => ModuleAccountCreatedEvent::class,
            'fields' => ['account'],
        ],
        AccountUpdatedEvent::class => [
            'target' => ModuleAccountUpdatedEvent::class,
            'fields' => ['account'],
        ],
    ];
}
```

#### EventServiceProvider注册

```php
protected $listen = [
    AccountCreatedEvent::class => [
        LogAccountCreatedListener::class,  // 同步
        DispatchModuleAccount::class,      // 异步转发
    ],
    ModuleAccountCreatedEvent::class => [
        \Modules\User\Listeners\CreateDefaultUser::class,  // 其他模块监听
    ],
];
```

#### 新增Event流程

1. 定义模块内Event + 跨模块Event
2. 配置统一转发器eventMap（无需新建转发器）
3. 注册EventServiceProvider

### 4.5 跨模块通信

**优先使用跨模块事件**（详见4.4节）：

- **跨模块Event系统**（✅ 首选）：发布/订阅模式，模块间解耦通信
  - 异步队列处理，隔离故障
  - 精简参数传递，API契约稳定
  - 统一转发器架构，代码简洁

**其他方式**（仅特殊场景使用）：

- **服务注入**：通过ServiceProvider注册服务，其他模块可注入使用（同步调用）
- **接口契约**：定义接口规范，模块间通过接口交互（强耦合，谨慎使用）

---

## 五、模块命名规范

| 业务场景 | 命名规则 | 示例 |
|---------|---------|------|
| **标准业务模块** | 业务名称 | `Order`, `User`, `Product`, `Account` |
| **功能模块** | 功能名称 | `Taska`, `Hospital`, `Report` |

---

## 六、实际案例参考

### 6.1 参考模块

项目中已采用单模块架构的示例：

| 模块 | 特点 | 推荐参考点 |
|------|------|-----------|
| `Demo6` | 完整单模块架构 | 入口组织、分层结构、事件系统 |
| `Taska` | 简洁业务模块 | Models/Services/Logics协作 |
| `Hospital` | 多入口模块 | Admin+Api双入口共享业务层 |
| `Account` | 事件驱动架构 | Event双Event设计、跨模块通信 |
| `Order` | 订单业务示例 | 完整业务流程、状态管理 |
| `User` | 用户管理模块 | Model关系映射、Services编排 |

**推荐学习路径**：
1. 从 `Demo6` 了解完整架构
2. 从 `Account` 学习事件系统
3. 从 `Order/User` 学习业务实现

---

## 七、项目整体结构

```
项目根目录/
├── Modules/                        # 模块目录 (所有模块都在此目录下)
│   ├── ABase/                      # 基础服务模块
│   ├── Demo6/                      # Demo6-完整单模块架构示例
│   ├── Account/                    # Account-事件驱动架构示例
│   ├── Taska/                      # Taska-简洁业务模块
│   ├── Hospital/                   # Hospital-多入口模块示例
│   ├── Order/                      # Order-订单业务模块
│   ├── User/                       # User-用户管理模块
│   └── [其他业务模块...]
├── app/                            # 主应用 (禁止修改)
├── config/                         # 配置文件
├── database/                       # 数据库
├── docs/                           # 项目文档
├── public/                         # 公共资源
└── routes/                         # 路由
```

**架构特点**：
- **单模块独立**：每个业务模块完全独立运行
- **多入口集成**：Admin、Api、Web、Merchant在同一模块内
- **无跨模块依赖**：模块间通过Event系统通信

---

## 八、模块管理命令

### 8.1 创建模块

```bash
# 创建新模块（使用module-generator）
php artisan module:make ModuleName

# 创建模块时自动生成完整目录结构
# 参考本Skill第二章目录结构规范
```

### 8.2 模块初始化检查清单

创建新模块时，确保包含：

**基础目录**（必须）：
- [ ] `Database/Migrations/` - 数据库迁移
- [ ] `Models/` - 数据模型
- [ ] `Services/` - 服务层
- [ ] `Logics/` - 逻辑层
- [ ] `Providers/` - 服务提供者
- [ ] `config/` - 配置文件
- [ ] `routes/` - 路由文件

**入口目录**（按需）：
- [ ] `DactAdmin/` - DactAdmin后台（如需要）
- [ ] `Api/` - RESTful API（如需要）
- [ ] `Web/` - Web前端（如需要）
- [ ] `Commands/` - Artisan命令（如需要）

**支撑目录**（可选）：
- [ ] `Events/` - 事件定义
- [ ] `Listeners/` - 事件监听器
- [ ] `QueueJobs/` - 队列任务
- [ ] `Dtos/` - 数据传输对象
- [ ] `Enums/` - 枚举类型
- [ ] `Rules/` - 自定义验证规则
- [ ] `Tests/` - 测试文件

### 8.3 模块配置

**module.json 示例**：

```json
{
  "name": "Order",
  "alias": "order",
  "description": "订单管理模块",
  "keywords": ["order", "management"],
  "priority": 100,
  "providers": [
    "Modules\\Order\\Providers\\OrderServiceProvider"
  ],
  "aliases": {},
  "files": [],
  "requires": []
}
```

**注意**：单模块架构`requires`字段通常为空，模块独立运行。

---

## 九、开发最佳实践

### 9.1 入口层开发

**Admin后台开发**：
- 使用 `dev-dcatadmin` Skill
- DactAdmin/Controllers、Forms、Grids、Actions等
- 共享Models/Services/Logics层

**API接口开发**：
- Api/Controllers 处理RESTful请求
- Api/Resources 格式化响应数据
- Api/Requests 验证请求参数

**Web前端开发**：
- Web/Controllers 处理传统Web请求
- resources/views 视图模板
- 可使用Livewire组件化

### 9.2 业务层开发

**Models层**：
- 使用 `dev-model` Skill
- 定义数据结构、关系映射
- 使用Casts处理复杂数据类型

**Services层**：
- 业务协调、流程编排
- 参数由Controller显性传入
- 禁止读取HTTP/session/cookie

**Logics层**：
- 纯静态方法、无状态
- 易于测试、可缓存
- 优先使用Logics处理计算逻辑

### 9.3 多入口共享业务层示例

**场景**：订单模块的Admin入口和API入口共享创建订单逻辑

```php
// Models/Order.php（共享数据模型）
class Order extends Model {
  protected $fillable = ['user_id', 'product_id', 'quantity', 'status'];
}

// Services/OrderService.php（共享业务逻辑）
class OrderService {
  public function createOrder(int $userId, int $productId, int $quantity): Order {
    $price = OrderLogic::calculatePrice($productId, $quantity);
    $order = Order::create([
      'user_id' => $userId,
      'product_id' => $productId,
      'quantity' => $quantity,
      'total_price' => $price,
      'status' => OrderStatus::PENDING
    ]);
    event(new OrderCreatedEvent($order));
    return $order;
  }
}

// DactAdmin/Controllers/OrderController.php（DactAdmin入口）
class OrderController extends AdminController {
  public function store(Request $request) {
    $order = app(OrderService::class)->createOrder(
      $request->user_id,
      $request->product_id,
      $request->quantity
    );
    return $this->response()->success('创建成功');
  }
}

// Api/Controllers/OrderController.php（API入口）
class OrderController extends ApiController {
  public function store(Request $request) {
    $order = app(OrderService::class)->createOrder(
      $request->input('user_id'),
      $request->input('product_id'),
      $request->input('quantity')
    );
    return new OrderResource($order);
  }
}
```

**关键点**：
- Admin/API入口平行独立运行，不互相调用
- 共享Models/Services/Logics层业务逻辑
- 入口层只负责参数解析和响应格式化
- 参数由入口层显性传给Service层

---

## 十、总结

### 10.1 新架构核心优势

| 优势 | 说明 |
|------|------|
| **代码集中** | 业务逻辑和入口在同一模块，代码定位快 |
| **依赖简单** | 无跨模块依赖，模块独立运行 |
| **易于维护** | 单模块修改不影响其他模块 |
| **团队协作** | 按业务模块分工，职责清晰 |
| **快速开发** | 模块内一站式开发，减少跨模块协调 |

### 10.2 适用场景

- **标准业务系统**：订单、用户、商品等独立业务
- **多端应用**：需要Admin后台 + API接口的业务
- **快速迭代项目**：频繁修改，需要快速定位代码
- **中小型团队**：团队按业务模块分工

---
## 常见问题
1. app目录不需要,不能有
2. composer.json 不需要,不能有
3. 

## 十一、Skill激活时机

本Skill在以下场景自动激活：

- 创建新模块时
- 规划模块目录结构时
- 模块初始化配置时
- 模块架构咨询时

**相关Skills**：
- `dev-dcatadmin` - Admin后台开发
- `dev-model` - Model开发
- `design-database` - 数据库设计
- `dev-seeder` - Seeder开发

---

**最后更新**：2026-04-26
**架构版本**：v3.0（单模块全栈架构规范）
**维护者**：Claude Code AI Assistant
