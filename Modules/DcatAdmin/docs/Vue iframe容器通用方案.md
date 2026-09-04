# Vue iframe 容器通用方案

## 概述

**所有 Vue 页面共用一个 iframe 容器控制器**，避免每个 Vue 页面都写重复的 `index()` 方法。

---

## 架构设计

### 传统方式（❌ 不推荐）

每个 Vue 控制器都有 `index()` 方法：

```php
// VuePostController.php
public function index(Content $content) {
    return $content->body(view('...iframe'));
}

// VueDashboardController.php  
public function index(Content $content) {
    return $content->body(view('...iframe'));
}

// 每个控制器都重复这个方法
```

**问题**：代码重复，维护成本高

---

### 通用容器方式（✅ 推荐）

所有 Vue 页面共用 `VueIframeController`：

```php
// VueIframeController.php（DcatAdmin 模块）
public function render(
    Content $content,
    string $route,      // standalone 页面路由
    string $title,      // 页面标题
    string $description = '',
    int $height = 800
): Content {
    return $content
        ->title($title)
        ->description($description)
        ->body(view('module_dcatadmin::components.iframe', [
            'src' => route($route),
            'height' => $height . 'px'
        ]));
}
```

**优势**：
- ✅ 一个方法服务所有 Vue 页面
- ✅ 集中管理，易于维护
- ✅ 减少重复代码

---

## 使用方法

### 1. 路由配置

使用 `Route::get()` 的 `defaults()` 方法传递参数：

```php
// Modules/Demo5/routes/admin.php

use Modules\DcatAdmin\DcatAdmin\Controllers\VueIframeController;

// Vue 文章管理
Route::get('vue-posts', [VueIframeController::class, 'render'])
    ->defaults('route', 'module_demo5.vue-posts.standalone')  // standalone 路由
    ->defaults('title', 'Vue 文章管理')                        // 页面标题
    ->defaults('description', '基于 Vue 3 的文章管理')         // 页面描述
    ->defaults('height', 900)                                  // iframe 高度
    ->name('module_demo5.vue-posts');

// standalone 页面路由（必需）
Route::get('vue-posts/standalone', [VuePostController::class, 'standalone'])
    ->name('module_demo5.vue-posts.standalone');

// API 路由
Route::get('vue-posts/list', [VuePostController::class, 'list']);
Route::post('vue-posts', [VuePostController::class, 'store']);
Route::put('vue-posts/{id}', [VuePostController::class, 'update']);
Route::delete('vue-posts/{id}', [VuePostController::class, 'destroy']);
```

### 2. 控制器实现

Vue 控制器**只需提供**：
- `standalone()` - 独立 Vue 页面渲染
- API 方法（`list`, `store`, `update`, `destroy` 等）

**不需要** `index()` 方法！

```php
// Modules/Demo5/DcatAdmin/Controllers/VuePostController.php

class VuePostController extends AdminController
{
    // ❌ 不需要 index() 方法

    /**
     * Vue 独立页面
     */
    public function standalone()
    {
        return view('module_demo5::vue.post-list');
    }

    /**
     * API：获取文章列表
     */
    public function list(): JsonResponse
    {
        return response()->json(Demo5Post::all());
    }

    // 其他 API 方法...
}
```

---

## 参数说明

`VueIframeController::render()` 接受以下参数：

| 参数 | 类型 | 必需 | 说明 |
|------|------|------|------|
| `$route` | string | ✅ | standalone 页面的路由名称 |
| `$title` | string | ✅ | 页面标题（显示在浏览器标签） |
| `$description` | string | ❌ | 页面描述（默认空字符串） |

**注意**：iframe 高度自动占满容器，无需手动设置。

### iframe 组件参数

`module_dcatadmin::components.iframe` 组件接受以下参数：

| 参数 | 类型 | 必需 | 说明 |
|------|------|------|------|
| `$src` | string | ✅ | iframe 加载的 URL |
| `$width` | string | ❌ | iframe 宽度，默认 '100%' |
| `$height` | string/null | ❌ | iframe 高度，默认 null（自动占满容器） |
| `$id` | string | ❌ | iframe ID，默认自动生成 |
| `$class` | string | ❌ | 容器 CSS 类名，默认 'vue-iframe-container' |
| `$style` | string | ❌ | 容器内联样式 |
| `$fullHeight` | boolean | ❌ | 是否占满容器高度，默认 true |

---

## 完整示例

### 示例 1：Vue 文章管理

**路由**：
```php
Route::get('vue-posts', [VueIframeController::class, 'render'])
    ->defaults('route', 'module_demo5.vue-posts.standalone')
    ->defaults('title', 'Vue 文章管理')
    ->defaults('description', '基于 Vue 3 的文章管理')
    ->defaults('height', 900)
    ->name('module_demo5.vue-posts');

Route::get('vue-posts/standalone', [VuePostController::class, 'standalone'])
    ->name('module_demo5.vue-posts.standalone');
```

**控制器**：
```php
class VuePostController extends AdminController
{
    public function standalone()
    {
        return view('module_demo5::vue.post-list');
    }

    public function list(): JsonResponse { /* ... */ }
    public function store(): JsonResponse { /* ... */ }
    public function update($id): JsonResponse { /* ... */ }
    public function destroy($id): JsonResponse { /* ... */ }
}
```

---

### 示例 2：Vue 仪表盘

**路由**：
```php
Route::get('vue-dashboard', [VueIframeController::class, 'render'])
    ->defaults('route', 'module_demo5.vue-dashboard.standalone')
    ->defaults('title', 'Vue 仪表盘')
    ->defaults('description', '基于 Vue 3 的实时数据仪表盘')
    ->defaults('height', 800)
    ->name('module_demo5.vue-dashboard');

Route::get('vue-dashboard/standalone', [VueDashboardController::class, 'standalone'])
    ->name('module_demo5.vue-dashboard.standalone');
```

**控制器**：
```php
class VueDashboardController extends AdminController
{
    public function standalone()
    {
        return view('module_demo5::vue.dashboard');
    }

    public function getStats(): JsonResponse { /* ... */ }
    public function getChartData(): JsonResponse { /* ... */ }
    public function getRecentPosts(): JsonResponse { /* ... */ }
}
```

---

## 迁移指南

### 从旧方式迁移到新方式

**步骤 1**：更新路由

```php
// 旧路由
Route::get('vue-posts', [VuePostController::class, 'index'])
    ->name('module_demo5.vue-posts');

// 新路由
use Modules\DcatAdmin\DcatAdmin\Controllers\VueIframeController;

Route::get('vue-posts', [VueIframeController::class, 'render'])
    ->defaults('route', 'module_demo5.vue-posts.standalone')
    ->defaults('title', 'Vue 文章管理')
    ->defaults('description', '基于 Vue 3 的文章管理')
    ->defaults('height', 900)
    ->name('module_demo5.vue-posts');
```

**步骤 2**：删除控制器的 `index()` 方法

```php
// 删除这个方法
public function index(Content $content) {
    return $content->body(view('...iframe'));
}
```

**步骤 3**：保留 `standalone()` 和 API 方法

---

## 文件结构

```
Modules/DcatAdmin/
├── DcatAdmin/Controllers/
│   └── VueIframeController.php     # 通用 iframe 容器控制器
└── resources/views/components/
    └── iframe.blade.php            # 通用 iframe 模板

Modules/Demo5/
├── DcatAdmin/Controllers/
│   ├── VuePostController.php       # 只负责 standalone 和 API
│   └── VueDashboardController.php  # 只负责 standalone 和 API
└── routes/admin.php                # 路由配置（使用 defaults）
```

---

## 注意事项

### 1. 命名空间

`VueIframeController` 在 DcatAdmin 模块，命名空间：
```php
use Modules\DcatAdmin\DcatAdmin\Controllers\VueIframeController;
```

### 2. 路由 defaults 顺序

`defaults()` 参数顺序必须与控制器方法参数顺序一致：
```php
->defaults('route', ...)       // 第1个参数
->defaults('title', ...)       // 第2个参数
->defaults('description', ...) // 第3个参数
->defaults('height', ...)      // 第4个参数
```

### 3. standalone 路由必需

每个 Vue 页面**必须**有对应的 standalone 路由：
```php
Route::get('vue-posts/standalone', ...)
    ->name('module_demo5.vue-posts.standalone');
```

---

## 更新记录

- **2026-09-04**: 创建通用 VueIframeController
- **初始版本**: v1.0