---
name: dev-vue
description: 进行 Vue 开发时必须使用,包括创建 Vue 组件、页面、Vite 构建配置等。当用户提到 Vue、Vue.js、Vue3、Element Plus、Vite 构建或需要在模块中添加 Vue 功能时触发此技能。
---

# Vue 开发规范

## 说明

此技能专用于本项目 Laravel 模块化架构下的 Vue 开发。项目使用 Vue 3 + Element Plus + Vite，采用模块化自动入口发现机制，每个业务模块可以独立开发 Vue 页面。

### 技术栈

- **Vue**: 3.4+
- **构建工具**: Vite 5.0+
- **UI 框架**: Element Plus 2.5+
- **图表**: ECharts 5.4+ + vue-echarts 6.6+
- **HTTP**: Axios 1.6+

### 架构特点

- **模块化 Vue**: 每个模块可以有独立的 Vue 入口和组件
- **自动入口发现**: Vite 自动扫描所有模块的 `resources/js/*.js` 入口文件
- **按需加载**: Element Plus 组件按需注册
- **代码分割**: 自动分割 vue-vendor、element-plus、echarts 等第三方库

## 核心规范

### 1. 目录结构

每个模块的 Vue 相关文件位于 `Modules/{ModuleName}/resources/js/`:

```
Modules/{ModuleName}/
├── resources/
│   ├── js/
│   │   ├── *.js              # Vue 入口文件（自动发现）
│   │   ├── *.vue             # Vue 组件
│   │   ├── components/       # 可复用组件
│   │   └── pages/            # 页面组件
│   └── views/
│       └── vue/              # Blade 视图模板
```

### 2. 入口文件规范

**命名规则**: `{功能名}.js` (如 `dashboard.js`、`post-list.js`)

**必需内容**:
1. 导入 Vue 和 Element Plus
2. 按需注册 Element Plus 组件
3. 配置 Axios CSRF Token
4. 挂载到对应的 DOM 容器

**示例**:

```javascript
/**
 * {ModuleName} {功能名} 入口
 */
import { createApp } from 'vue'
import {
  ElRow, ElCol, ElCard, ElButton
  // 按需导入 Element Plus 组件
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import ComponentName from './ComponentName.vue'

const app = createApp(ComponentName)

// 注册 Element Plus 组件
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)
app.component('ElCard', ElCard)
app.component('ElButton', ElButton)

// 配置 Axios
import axios from 'axios'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content

// 挂载到 DOM 容器（ID 格式：{模块名小写}-{功能名}）
app.mount('#{模块名小写}-{功能名}')
```

### 3. Blade 视图模板

**位置**: `Modules/{ModuleName}/resources/views/vue/{功能名}.blade.php`

**必需内容**:
1. 继承 `module_dcatadmin::layouts.vue-app` 布局
2. 使用 `@vite()` 指令引入入口文件
3. 提供挂载容器

**示例**:

```blade
@extends('module_dcatadmin::layouts.vue-app')

@section('title', '页面标题')

@push('styles')
    @vite(['Modules/{ModuleName}/resources/js/{功能名}.js'])
@endpush

@section('content')
    <div id="{模块名小写}-{功能名}"></div>
@endsection
```

### 4. Vite 自动发现机制

项目 `vite.config.js` 使用 `discoverModuleEntries()` 函数自动扫描所有模块的入口文件：

- **扫描路径**: `Modules/*/resources/js/*.js`
- **自动注册**: 所有 `.js` 文件自动成为 Vite 入口
- **无需配置**: 新增模块入口文件无需修改 Vite 配置

### 5. 构建命令

```bash
# 开发模式（热重载）
npm run dev

# 生产构建
npm run build
```

**构建输出**:
- `public/build/manifest.json` - 资源清单（必需）
- `public/build/assets/` - 打包后的 JS/CSS 文件

**输出文件命名规则**:
- 入口文件: `assets/{模块名小写}-{功能名}-[hash].js`
- 第三方库: `assets/{库名}-[hash].js` (如 `vue-vendor-[hash].js`)

### 6. 代码分割

Vite 配置了自动代码分割，将第三方库分离到独立的 chunk：

- `vue-vendor` - Vue 核心库
- `element-plus` - Element Plus UI 库
- `echarts` - ECharts 图表库
- `axios` - Axios HTTP 库

**优点**:
- 减小主包大小
- 提高缓存命中率
- 并行加载优化

## 常见问题处理

### 1. Vite Manifest 缺失错误

**错误信息**: `Vite manifest not found at: public/build/manifest.json`

**原因**: 前端资源未构建

**解决方案**:
```bash
npm run build
```

### 2. Vue 组件未渲染

**检查项**:
1. 入口文件是否正确导入组件
2. Blade 视图是否使用 `@vite()` 指令
3. DOM 容器 ID 是否与 `app.mount()` 匹配
4. 浏览器控制台是否有错误

### 3. Element Plus 样式问题

**必需样式**:
```javascript
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
```

### 4. CSRF Token 问题

**配置 Axios**:
```javascript
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]').content
```

## 开发流程

### 创建新的 Vue 页面

1. **创建入口文件**: `Modules/{ModuleName}/resources/js/{功能名}.js`
2. **创建 Vue 组件**: `Modules/{ModuleName}/resources/js/{功能名}.vue`
3. **创建 Blade 视图**: `Modules/{ModuleName}/resources/views/vue/{功能名}.blade.php`
4. **添加路由**: 在模块的 `routes/admin.php` 中添加路由
5. **构建**: `npm run build`

### 最佳实践

1. **组件拆分**: 将复杂页面拆分为多个子组件存放在 `components/` 目录
2. **按需导入**: 只导入需要的 Element Plus 组件
3. **命名规范**: 入口文件使用小写字母和连字符（如 `post-list.js`）
4. **容器 ID**: 使用 `{模块名小写}-{功能名}` 格式，确保全局唯一

## 参考示例

- **Demo5 模块**: `Modules/Demo5/resources/js/` - 简单示例
- **DcatAdmin 模块**: `Modules/DcatAdmin/resources/js/` - 复杂示例（包含多个页面和组件）