# DcatAdmin 控制器开发经验总结

**总结日期**: 2026-09-09
**来源项目**: FeatureDbadmin 模块重构

---

## 一、控制器继承规范

### 核心规则

**所有后台控制器必须继承 `Modules\DcatAdmin\DcatAdmin\AdminController`**

```php
// ✅ 正确
use Modules\DcatAdmin\DcatAdmin\AdminController;

class HomeController extends AdminController
{
    // ...
}

// ❌ 错误
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    // ...
}
```

### AdminController 提供的能力

`Modules\DcatAdmin\DcatAdmin\AdminController` 继承自 `Dcat\Admin\Http\Controllers\AdminController`，提供了：

1. **统一的响应方法**：
   - `success($data, $message)` - 成功 JSON 响应
   - `error($message, $code)` - 失败 JSON 响应

2. **预设的 RESTful 方法**：
   - `index(Content $content)` - 列表页
   - `store(Request $request)` - 创建资源
   - `update(Request $request, $id)` - 更新资源
   - `destroy($id)` - 删除资源
   - `show($id)` - 查看详情
   - `edit($id)` - 编辑页
   - `create()` - 创建页

---

## 二、方法名冲突问题

### 问题描述

继承 AdminController 后，如果子类定义了与父类同名的方法，会产生方法签名冲突错误：

```
Declaration of App\Http\Controllers\HomeController::index(Content $content)
must be compatible with
Dcat\Admin\Http\Controllers\AdminController::index(Content $content)
```

### 解决方案

#### 方案 1：使用不同的方法名（推荐）

| 用途 | RESTful 方法名（冲突） | 替代方法名（安全） |
|------|---------------------|-----------------|
| 单页应用入口 | `index` | `home`, `main`, `dashboard` |
| 创建资源 | `store` | `save`, `create` |
| 更新资源 | `update` | `modify`, `edit` |
| 删除资源 | `destroy` | `remove`, `delete` |
| 新增数据 | `create` | `insert`, `add` |

**示例：**

```php
class HomeController extends AdminController
{
    // ✅ 使用 home 避免与 index 冲突
    public function home(Content $content)
    {
        return $content
            ->title('数据库管理员工具')
            ->body(view('featuredbadmin::vue.app'));
    }
}

class ConnectionController extends AdminController
{
    // ✅ 使用 save 避免与 store 冲突
    public function save(Request $request)
    {
        // 创建逻辑
    }

    // ✅ 使用 modify 避免与 update 冲突
    public function modify(Request $request, int $id)
    {
        // 更新逻辑
    }

    // ✅ 使用 remove 避免与 destroy 冲突
    public function remove(int $id)
    {
        // 删除逻辑
    }
}
```

#### 方案 2：完全匹配父类签名（不推荐）

```php
// 必须完全匹配父类的方法签名
public function index(Content $content)
{
    // 实现
}
```

### 路由配置同步更新

修改控制器方法名后，路由配置也需同步更新：

```php
// routes/admin.php

// ✅ 正确
Route::get('/', [HomeController::class, 'home']);
Route::post('connections', [ConnectionController::class, 'save']);
Route::put('connections/{id}', [ConnectionController::class, 'modify']);
Route::delete('connections/{id}', [ConnectionController::class, 'remove']);

// ❌ 错误（已废弃）
Route::get('/', [HomeController::class, 'index']);
Route::post('connections', [ConnectionController::class, 'store']);
Route::put('connections/{id}', [ConnectionController::class, 'update']);
Route::delete('connections/{id}', [ConnectionController::class, 'destroy']);
```

---

## 三、视图布局层判断原则

### 核心原则

**判断逻辑应在视图布局层处理，控制器应保持简洁**

### 错误示例

```php
// ❌ 控制器中判断请求类型
public function index(Request $request, Content $content)
{
    // standalone 或 pjax：直接返回 Vue 视图
    if ($request->get('standalone') || $request->pjax()) {
        return view('featuredbadmin::vue.app');
    }

    // 普通请求：用 Content 包装后台布局
    return $content
        ->title('数据库管理员工具')
        ->body(view('featuredbadmin::vue.app'));
}
```

### 正确示例

```php
// ✅ 控制器只返回视图
public function home(Content $content)
{
    return $content
        ->title('数据库管理员工具')
        ->description('统一的数据库管理平台')
        ->body(view('featuredbadmin::vue.app'));
}
```

### 视图布局层处理

判断逻辑由视图布局文件 `vue-app.blade.php` 统一处理：

```blade
{{-- Modules/DcatAdmin/resources/views/layouts/vue-app.blade.php --}}

@if(request()->get('standalone'))
    {{-- 模式1：独立页面（iframe 内部），无后台布局 --}}
    <!DOCTYPE html>
    <html>
        <head>...</head>
        <body>
            @yield('content')
        </body>
    </html>
@else
    {{-- 模式2和模式3：渲染 iframe 容器 --}}
    <div class="vue-iframe-container">
        <iframe src="{{ url()->current() }}?standalone=1"></iframe>
    </div>
@endif
```

### 三种请求模式

| 模式 | 判断条件 | 说明 |
|------|---------|------|
| 独立页面 | `?standalone=1` | iframe 内部，无后台布局包裹 |
| Pjax 请求 | `request()->pjax()` | 无后台布局包裹 |
| 普通请求 | 其他 | Controller 用 Content 包装后台布局 |

---

## 四、Vue SPA 架构模式

### 单一入口控制器模式

**所有页面路由指向同一个控制器方法，由 Vue Router 处理前端路由**

```php
// routes/admin.php

// ✅ HTML 路由：所有路径都指向同一个方法
Route::get('/', [HomeController::class, 'home']);
Route::get('dashboard', [HomeController::class, 'home']);
Route::get('connections', [HomeController::class, 'home']);
Route::get('tables', [HomeController::class, 'home']);
Route::get('data', [HomeController::class, 'home']);
Route::get('query', [HomeController::class, 'home']);
```

### HTML 和 API 路由分离

```php
// ✅ 推荐的路由结构
Route::group(['prefix' => 'featuredbadmin'], function () {

    // ===== HTML 路由（Vue 应用入口）=====
    Route::get('/', [HomeController::class, 'home']);
    Route::get('dashboard', [HomeController::class, 'home']);
    Route::get('connections', [HomeController::class, 'home']);
    // ... 其他 HTML 路由

    // ===== JSON API 路由（带 /api/ 前缀）=====
    Route::group(['prefix' => 'api'], function () {
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('connections', [ConnectionController::class, 'list']);
        Route::post('connections', [ConnectionController::class, 'save']);
        // ... 其他 API 路由
    });
});
```

### 控制器职责分离

| 控制器 | 职责 | 返回类型 |
|--------|------|---------|
| HomeController | Vue SPA 入口 | HTML（Blade 视图） |
| DashboardController | 仪表盘 API | JSON |
| ConnectionController | 连接管理 API | JSON |
| TableController | 表管理 API | JSON |
| DataBrowserController | 数据浏览 API | JSON |
| QueryToolController | 查询工具 API | JSON |

---

## 五、控制器开发最佳实践

### 1. 控制器最小化原则

控制器应该**只做协调工作**，不包含业务逻辑：

```php
// ✅ 正确：控制器只负责协调
public function save(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'driver' => 'required|in:mysql,pgsql,sqlite',
    ]);

    $connection = Connection::create($validated);

    return response()->json([
        'success' => true,
        'data' => $connection,
    ]);
}

// ❌ 错误：控制器包含业务逻辑
public function save(Request $request)
{
    $name = $request->input('name');
    $driver = $request->input('driver');

    // 复杂的业务逻辑不应该在控制器中
    if ($driver === 'mysql') {
        $dsn = "mysql:host={$host};dbname={$database}";
        // ... 更多逻辑
    }

    // ... 更多业务逻辑
}
```

### 2. 使用 Services 层处理业务逻辑

```php
// ConnectionController.php
public function save(Request $request)
{
    $validated = $request->validate([...]);

    // ✅ 调用 Service 处理业务逻辑
    $connection = DatabaseService::createConnection($validated);

    return response()->json([
        'success' => true,
        'data' => $connection,
    ]);
}

// Services/DatabaseService.php
class DatabaseService
{
    public static function createConnection(array $data): Connection
    {
        // 业务逻辑在这里
        $connection = Connection::create($data);
        $connection->testConnection();
        // ... 更多业务逻辑
        return $connection;
    }
}
```

### 3. 统一的响应格式

使用 AdminController 提供的响应方法：

```php
// ✅ 使用 AdminController 的响应方法
public function list()
{
    $connections = DatabaseService::getConnections();

    return $this->success($connections, '获取成功');
}

public function save(Request $request)
{
    // ... 验证和创建逻辑

    if (!$connection) {
        return $this->error('创建失败', 400);
    }

    return $this->success($connection, '创建成功');
}
```

### 4. 验证规则独立化

```php
// ✅ 创建独立的 Validation 类
// Validations/ConnectionValidation.php
class ConnectionValidation extends AbstractValidator
{
    protected array $rules = [
        'name' => 'required|string|max:100|unique:feature_dbadmin_connections,name',
        'driver' => 'required|in:mysql,pgsql,sqlite',
        'host' => 'required_if:driver,mysql,pgsql|string|max:100',
        'port' => 'nullable|integer|min:1|max:65535',
    ];
}

// ConnectionController.php
public function save(Request $request)
{
    $validated = (new ConnectionValidation($request->all()))->validate();

    // ... 创建逻辑
}
```

---

## 六、常见问题与解决方案

### 问题 1：方法签名冲突

**错误信息：**
```
Declaration must be compatible with AdminController->index(Content $content)
```

**解决方案：**
使用不同的方法名（如 `home`, `save`, `modify`, `remove`）

### 问题 2：路由找不到视图

**错误信息：**
```
View [featuredbadmin::vue.app] not found.
```

**解决方案：**
1. 确认视图文件存在：`resources/views/vue/app.blade.php`
2. 确认模块视图已注册（ServiceProvider 中）
3. 使用正确的视图命名空间：`模块名::视图路径`

### 问题 3：API 路由 404

**原因：** API 路由未添加 `/api/` 前缀

**解决方案：**

```php
// ✅ 正确：API 路由带 /api/ 前缀
Route::group(['prefix' => 'api'], function () {
    Route::get('connections', [ConnectionController::class, 'list']);
});

// 访问地址：/admin/featuredbadmin/api/connections
```

---

## 七、开发检查清单

创建新的 DcatAdmin 控制器时，请检查：

- [ ] 继承 `Modules\DcatAdmin\DcatAdmin\AdminController`
- [ ] 避免使用 RESTful 预设方法名（`index`, `store`, `update`, `destroy`）
- [ ] 控制器方法名清晰表达意图（`home`, `save`, `modify`, `remove`）
- [ ] 路由配置与方法名匹配
- [ ] HTML 路由和 API 路由分离
- [ ] API 路由使用 `/api/` 前缀
- [ ] 控制器不包含业务逻辑（放入 Services 层）
- [ ] 使用独立的 Validation 类进行验证
- [ ] 视图判断逻辑在布局层处理，不在控制器中
- [ ] 使用 AdminController 的 `success()` 和 `error()` 方法返回响应

---

## 八、快速参考

### 推荐的控制器模板

```php
<?php

namespace Modules\{Module}\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Illuminate\Http\Request;

/**
 * {功能}控制器
 *
 * 提供 {功能描述}
 */
class {Feature}Controller extends AdminController
{
    /**
     * {功能}列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = {Feature}Service::getList();

        return $this->success($data);
    }

    /**
     * 创建 {功能}
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $validated = $request->validate([...]);

        $item = {Feature}Service::create($validated);

        return $this->success($item, '创建成功');
    }

    /**
     * 更新 {功能}
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function modify(Request $request, int $id)
    {
        $validated = $request->validate([...]);

        $item = {Feature}Service::update($id, $validated);

        return $this->success($item, '更新成功');
    }

    /**
     * 删除 {功能}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(int $id)
    {
        {Feature}Service::delete($id);

        return $this->success(null, '删除成功');
    }
}
```

### 推荐的路由模板

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\{Module}\DcatAdmin\Controllers;

Route::group([
    'prefix' => '{module}',
], function () {

    // ===== HTML 路由（Vue 应用入口）=====
    Route::get('/', [Controllers\HomeController::class, 'home']);
    Route::get('{page}', [Controllers\HomeController::class, 'home']);

    // ===== JSON API 路由 =====
    Route::group(['prefix' => 'api'], function () {
        // {功能} API
        Route::get('{feature}', [Controllers\{Feature}Controller::class, 'list']);
        Route::post('{feature}', [Controllers\{Feature}Controller::class, 'save']);
        Route::put('{feature}/{id}', [Controllers\{Feature}Controller::class, 'modify']);
        Route::delete('{feature}/{id}', [Controllers\{Feature}Controller::class, 'remove']);
    });
});
```

---

**总结完成时间**: 2026-09-09 08:45
**维护者**: AI 开发团队