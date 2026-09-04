# DEMO5 模块

> 这是一个演示模块化最佳实践的完整功能模块，包含服务层架构、事件系统、队列任务等现代Laravel开发模式

## 核心元素

- **表前缀**: 该模块使用的数据库表前缀为 `demo5_`

- **架构模式**: 服务层 + 逻辑层分离，事件驱动架构 + 钩子系统
- **核心特性**:
  - 完整的服务层架构（Services + Logics）
  - 事件驱动系统（Events + Listeners）
  - 钩子系统（Hooks）- 灵活的扩展机制
  - 队列任务处理（QueueJobs）
  - 数据传输对象（DTOs）
  - 枚举类型管理（Enums）
  - 自定义模型类型转换（Casts）
  - Artisan命令工具

## 目录结构

```
Modules/Demo5/
├── Commands/               # Artisan命令
├── Casts/                  # 自定义类型转换
├── config/                 # 配置文件
│   └── config.php          # 模块配置
├── Database/               # 数据库相关
│   ├── factories/          # 模型工厂
│   ├── migrations/         # 数据库迁移
│   └── Seeders/            # 数据库填充
├── DTOs/                   # 数据传输对象
├── Enums/                  # 枚举类型
├── Events/                 # 事件定义
├── Hooks/                  # 钩子系统
│   ├── Definitions/        # 钩子定义
│   ├── Handlers/           # 钩子处理器
│   ├── Parameters/         # 钩子参数
│   └── Results/            # 钩子结果
├── Listeners/              # 事件监听器
├── Logics/                 # 业务逻辑层（静态方法）
├── Models/                 # Eloquent模型
├── Providers/              # 服务提供者
│   ├── Demo5ServiceProvider.php   # 主服务提供者
│   ├── EventServiceProvider.php    # 事件服务提供者
│   └── RouteServiceProvider.php     # 路由服务提供者
├── QueueJobs/              # 队列任务
├── Resources/              # API资源
├── Rules/                  # 自定义验证规则
├── Services/               # 服务层
├── resources/              # 资源文件
│   ├── assets/             # 前端资源
│   │   ├── js/             # JavaScript文件
│   │   └── sass/           # 样式文件
│   └── views/              # 视图文件
│       ├── components/     # 组件视图
│       └── layouts/        # 布局视图
├── Tests/                  # 测试文件
│   ├── Feature/            # 功能测试
│   └── Unit/               # 单元测试
└── module.json             # 模块配置文件
```

## 架构说明

### 服务层 vs 逻辑层

#### Services (服务层)
- **职责**: 协调不同组件，处理业务流程，依赖注入
- **特点**: 实例化，可注入依赖，处理复杂业务编排
- **示例**: `PostService` 协调文章创建、验证、事件触发、队列任务

#### Logics (逻辑层)
- **职责**: 纯函数计算，无状态，高性能
- **特点**: 静态方法，无副作用，易于测试
- **组成**:
  - `PostLogic`: 文章相关计算（阅读时间、字数统计、摘要生成等）
  - `ValidationLogic`: 数据验证（文章数据、搜索参数、分页等）
  - `StatisticsLogic`: 统计分析（增长率、参与度、质量趋势等）

#### Admin/Repositories (Dcat Admin 数据访问工具)
- **职责**: Dcat Admin 后台的数据访问组件
- **特点**: 继承Dcat Admin的EloquentRepository，提供Grid/Filter支持
- **说明**: 仅用于Dcat Admin后台，不是核心业务层
- **示例**: `PostRepository` 为后台表格提供数据查询和过滤功能

### 事件驱动架构

#### 事件流
1. **PostCreatedEvent**: 文章创建时触发
2. **LogPostCreatedListener**: 记录创建日志
3. **NotifyPostPublishedListener**: 发送发布通知
4. **ProcessPostPublishingJob**: 异步处理发布后任务

### 使用示例

#### 基础用法
```php
// 使用服务层
$postService = app(PostService::class);
$post = $postService->createPost($data);

// 使用逻辑层
$readingTime = PostLogic::calculateReadingTime($content);
$isValid = ValidationLogic::validatePostData($data);
$growthRate = StatisticsLogic::calculateGrowthRate($current, $previous);
```

#### Artisan命令
```bash
# 生成演示数据
php artisan module_demo5:generate-demo-data --posts=50 --truncate
```

## 数据库表

### demo5_posts
- `id`: 主键
- `title`: 文章标题
- `content`: 文章内容
- `status`: 文章状态 (published/draft/archived)
- `user_id`: 用户ID
- `metadata`: JSON元数据
- `published_at`: 发布时间
- `word_count`: 字数统计
- `reading_time`: 阅读时间
- `created_at/updated_at`: 时间戳

## 扩展指南

### 添加新的逻辑类
1. 在 `Logics/` 目录创建新类
2. 使用静态方法实现业务逻辑
3. 在 `Services/` 中调用逻辑层方法

### 添加新的事件
1. 在 `Events/` 目录创建事件类
2. 在 `Listeners/` 目录创建监听器
3. 在 `EventServiceProvider.php` 中注册映射

### 添加新的队列任务
1. 在 `QueueJobs/` 目录创建任务类
2. 实现 `ShouldQueue` 接口
3. 在服务中分发任务

## 最佳实践

1. **逻辑优先**: 优先使用Logics层的静态方法
2. **服务协调**: 复杂业务流程使用Services层
3. **事件解耦**: 使用事件系统降低组件耦合
4. **队列异步**: 耗时操作使用队列任务
5. **DTO传参**: 复杂数据使用DTO封装
6. **枚举状态**: 使用枚举管理状态类型
7. **类型转换**: 使用Casts处理复杂数据类型

