# FeatureDbadmin 架构模式说明

## 架构特点

### 1. 只有后台，没有 Web，没有 API

- ✅ **Dcat Admin 超管后台**：唯一入口
- ❌ **Web 前台**：不存在
- ❌ **API 分组**：不存在 ApiProto

### 2. 后台控制器输出混合

- **HTML 页面**：传统 Dcat Admin Grid/Form/Show
- **JSON 接口**：供 Vue 组件使用（不是 API 分组）

### 3. 页面使用 Vue 实现

- Vue 组件嵌入到 Dcat Admin 页面中
- 控制器提供 JSON 数据接口
- 使用 Mix/Vite 编译 Vue 组件

---

## 架构对比

### 传统模块架构

```
Models → Services → Logics → Controllers
                              ├── DcatAdmin (HTML)
                              └── ApiProto (API)
```

### FeatureDbadmin 架构

```
Models → Services → Logics → Controllers (DcatAdmin)
                              ├── HTML 页面（传统 Grid/Form）
                              └── JSON 接口（供 Vue 使用）
```

---

## 控制器设计模式

### 混合返回模式

```php
<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TableController extends AdminController
{
    protected $title = '数据库表';

    /**
     * HTML 页面 - 表列表
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->body(view('featuredbadmin::tables.index', [
                'connections' => DatabaseService::getConnections(),
            ]));
    }

    /**
     * JSON 接口 - 获取表列表（供 Vue 使用）
     */
    public function list(Request $request)
    {
        $connectionId = $request->get('connection_id');

        $tables = TableService::getAllTables($connectionId);

        return response()->json([
            'data' => $tables,
            'total' => count($tables),
        ]);
    }

    /**
     * JSON 接口 - 获取表结构（供 Vue 使用）
     */
    public function structure(Request $request, string $tableName)
    {
        $connectionId = $request->get('connection_id');

        $structure = TableService::getTableStructure($tableName, $connectionId);

        return response()->json([
            'data' => $structure,
        ]);
    }
}
```

---

## Vue 页面集成方案

### 方案1: 使用 Dcat Admin 自定义页面

#### 步骤1: 创建 Vue 组件

```vue
<!-- resources/js/components/TableManager.vue -->
<template>
  <div class="table-manager">
    <!-- 连接选择 -->
    <div class="connection-selector">
      <select v-model="selectedConnection" @change="loadTables">
        <option v-for="conn in connections" :key="conn.id" :value="conn.id">
          {{ conn.name }}
        </option>
      </select>
    </div>

    <!-- 表列表 -->
    <table class="table table-striped">
      <thead>
        <tr>
          <th>表名</th>
          <th>引擎</th>
          <th>行数</th>
          <th>大小</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="table in tables" :key="table.name">
          <td>{{ table.name }}</td>
          <td>{{ table.engine }}</td>
          <td>{{ table.row_count }}</td>
          <td>{{ table.size }}</td>
          <td>
            <button @click="viewStructure(table.name)" class="btn btn-sm btn-primary">
              查看结构
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      connections: [],
      selectedConnection: null,
      tables: [],
    };
  },
  mounted() {
    this.loadConnections();
  },
  methods: {
    async loadConnections() {
      const response = await fetch('/admin/featuredbadmin/connections/list');
      const data = await response.json();
      this.connections = data.data;

      if (this.connections.length > 0) {
        this.selectedConnection = this.connections[0].id;
        this.loadTables();
      }
    },
    async loadTables() {
      const response = await fetch(
        `/admin/featuredbadmin/tables/list?connection_id=${this.selectedConnection}`
      );
      const data = await response.json();
      this.tables = data.data;
    },
    async viewStructure(tableName) {
      // 打开表结构详情模态框
    },
  },
};
</script>
```

#### 步骤2: 编译 Vue 组件

**webpack.mix.js**

```javascript
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .vue()
   .sass('resources/sass/app.scss', 'public/css');
```

**resources/js/app.js**

```javascript
import Vue from 'vue';
import TableManager from './components/TableManager.vue';

// 注册全局组件
Vue.component('table-manager', TableManager);

// 初始化 Vue 应用
document.addEventListener('DOMContentLoaded', () => {
  const app = new Vue({
    el: '#vue-app',
  });
});
```

#### 步骤3: Dcat Admin 视图

**Modules/FeatureDbadmin/resources/views/tables/index.blade.php**

```blade
@extends('admin::content')

@section('content')
<div id="vue-app">
    <table-manager></table-manager>
</div>

<!-- 引入编译后的 Vue 资源 -->
<script src="{{ asset('js/app.js') }}"></script>
@endsection
```

---

### 方案2: 使用 Dcat Admin + Vue SFC（推荐）

#### 控制器返回 Vue 组件页面

```php
public function index(Content $content)
{
    return $content
        ->title($this->title)
        ->body(view('featuredbadmin::tables.vue-index', [
            'connections' => json_encode(DatabaseService::getConnections()),
        ]));
}
```

#### 视图文件

```blade
<!-- resources/views/tables/vue-index.blade.php -->
<div id="table-manager-app">
  <!-- Vue 组件会挂载到这里 -->
</div>

<script>
  // 传递数据给 Vue
  window.initialData = {
    connections: {!! $connections !!}
  };
</script>

<script src="{{ asset('js/featuredbadmin/tables.js') }}"></script>
```

---

## 路由设计

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureDbadmin\DcatAdmin\Controllers;

Route::group([
    'prefix' => 'featuredbadmin',
    'middleware' => ['admin', 'admin.permission:check']
], function () {

    // ===== 仪表盘 =====
    Route::get('dashboard', [Controllers\DashboardController::class, 'index']);

    // ===== 连接管理 =====
    Route::get('connections', [Controllers\ConnectionController::class, 'index']); // HTML 页面
    Route::get('connections/list', [Controllers\ConnectionController::class, 'list']); // JSON 接口
    Route::get('connections/create', [Controllers\ConnectionController::class, 'create']); // HTML 页面
    Route::post('connections', [Controllers\ConnectionController::class, 'store']); // JSON 接口
    Route::get('connections/{id}/edit', [Controllers\ConnectionController::class, 'edit']); // HTML 页面
    Route::put('connections/{id}', [Controllers\ConnectionController::class, 'update']); // JSON 接口
    Route::delete('connections/{id}', [Controllers\ConnectionController::class, 'destroy']); // JSON 接口
    Route::post('connections/{id}/test', [Controllers\ConnectionController::class, 'test']); // JSON 接口

    // ===== 表管理 =====
    Route::get('tables', [Controllers\TableController::class, 'index']); // HTML 页面（Vue）
    Route::get('tables/list', [Controllers\TableController::class, 'list']); // JSON 接口
    Route::get('tables/{name}/structure', [Controllers\TableController::class, 'structure']); // JSON 接口
    Route::get('tables/{name}/export', [Controllers\TableController::class, 'export']); // JSON 接口

    // ===== 数据浏览 =====
    Route::get('data/{table}', [Controllers\DataBrowserController::class, 'index']); // HTML 页面（Vue）
    Route::get('data/{table}/list', [Controllers\DataBrowserController::class, 'list']); // JSON 接口
    Route::get('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'row']); // JSON 接口
    Route::post('data/{table}/row', [Controllers\DataBrowserController::class, 'create']); // JSON 接口
    Route::put('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'update']); // JSON 接口
    Route::delete('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'delete']); // JSON 接口

    // ===== SQL 查询工具 =====
    Route::get('query', [Controllers\QueryToolController::class, 'index']); // HTML 页面（Vue）
    Route::post('query/execute', [Controllers\QueryToolController::class, 'execute']); // JSON 接口
    Route::get('query/history', [Controllers\QueryToolController::class, 'history']); // JSON 接口
    Route::get('query/saved', [Controllers\QueryToolController::class, 'saved']); // JSON 接口
    Route::post('query/save', [Controllers\QueryToolController::class, 'save']); // JSON 接口
});
```

---

## Vue 页面功能规划

### 1. 仪表盘（Dashboard）

**页面**: `resources/views/dashboard/index.blade.php`
**Vue 组件**: `resources/js/components/Dashboard.vue`

**功能**:
- 数据库连接统计卡片
- 最近查询历史列表
- 表数量统计
- 快捷操作入口

### 2. 连接管理（Connections）

**页面**: `resources/views/connections/index.blade.php`
**Vue 组件**: `resources/js/components/ConnectionManager.vue`

**功能**:
- 连接列表（卡片或表格）
- 创建/编辑连接表单
- 测试连接功能
- 切换连接

### 3. 表管理（Tables）

**页面**: `resources/views/tables/index.blade.php`
**Vue 组件**: `resources/js/components/TableManager.vue`

**功能**:
- 表列表（支持搜索、筛选）
- 表结构查看（模态框）
- 表结构导出
- 收藏表功能

### 4. 数据浏览（Data Browser）

**页面**: `resources/views/data/index.blade.php`
**Vue 组件**: `resources/js/components/DataBrowser.vue`

**功能**:
- 数据表格展示（分页、排序、筛选）
- 行内编辑
- 数据新增/删除
- 数据导出

### 5. SQL 查询工具（Query Tool）

**页面**: `resources/views/query/index.blade.php`
**Vue 组件**: `resources/js/components/QueryTool.vue`

**功能**:
- SQL 编辑器（Monaco Editor / CodeMirror）
- 查询执行
- 结果展示（表格）
- 查询历史
- 保存查询

---

## 前端技术栈

### Vue 生态

- **Vue 3**: 前端框架
- **Vue Router**: 路由管理（可选）
- **Pinia**: 状态管理（可选）
- **Element Plus / Ant Design Vue**: UI 组件库

### 编辑器组件

- **Monaco Editor**: SQL 编辑器（VS Code 同款）
- **CodeMirror**: 轻量级代码编辑器

### 构建工具

- **Vite**: 现代化构建工具（推荐）
- **Laravel Mix**: 传统构建工具

---

## 目录结构更新

```
FeatureDbadmin/
├── DcatAdmin/
│   ├── Controllers/           # 控制器
│   │   ├── DashboardController.php
│   │   ├── ConnectionController.php
│   │   ├── TableController.php
│   │   ├── DataBrowserController.php
│   │   └── QueryToolController.php
│   └── Repositories/          # 数据仓库（可选）
│
├── resources/
│   ├── views/                 # Blade 视图
│   │   ├── dashboard/
│   │   ├── connections/
│   │   ├── tables/
│   │   ├── data/
│   │   └── query/
│   ├── js/                    # JavaScript
│   │   ├── components/        # Vue 组件
│   │   │   ├── Dashboard.vue
│   │   │   ├── ConnectionManager.vue
│   │   │   ├── TableManager.vue
│   │   │   ├── DataBrowser.vue
│   │   │   └── QueryTool.vue
│   │   └── app.js             # 入口文件
│   └── sass/                  # 样式文件
│
├── Services/                  # 业务服务层
├── Logics/                    # 逻辑层
├── Models/                    # 数据模型层
└── Enums/                     # 枚举类
```

---

## 开发优先级调整

### 阶段1: 基础设施 + 后端服务
**时间**: 3 天

- [ ] 创建数据库迁移（5 张表）
- [ ] 创建 Models
- [ ] 创建 Services（DatabaseService、TableService、QueryService）
- [ ] 创建 Logics
- [ ] 创建 Enums 和 DTOs

### 阶段2: 后端 JSON 接口
**时间**: 2 天

- [ ] ConnectionController（JSON 接口）
- [ ] TableController（JSON 接口）
- [ ] DataBrowserController（JSON 接口）
- [ ] QueryToolController（JSON 接口）

### 阶段3: Vue 前端页面
**时间**: 5 天

- [ ] 配置 Vite/Mix
- [ ] 创建 Vue 组件
  - [ ] Dashboard.vue
  - [ ] ConnectionManager.vue
  - [ ] TableManager.vue
  - [ ] DataBrowser.vue
  - [ ] QueryTool.vue
- [ ] 集成 UI 组件库
- [ ] 集成 Monaco Editor

### 阶段4: Blade 视图和路由
**时间**: 1 天

- [ ] 创建 Blade 视图
- [ ] 配置路由

### 阶段5: 测试和优化
**时间**: 2 天

- [ ] 功能测试
- [ ] 性能优化
- [ ] UI 美化

---

**更新时间**: 2026-09-08
**维护者**: 开发团队