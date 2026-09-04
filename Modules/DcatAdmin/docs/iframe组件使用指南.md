# DcatAdmin 通用 iframe 组件

## 组件位置

`Modules/DcatAdmin/resources/views/components/iframe.blade.php`

## 功能特点

- ✅ 参数化配置，可复用
- ✅ 支持自定义宽度和高度
- ✅ 支持自适应内容高度
- ✅ 包含默认样式
- ✅ 可在整个项目中使用

## 使用方法

### 基础用法

```blade
@include('module_dcatadmin::components.iframe', [
    'src' => route('module_demo5.vue-posts.standalone')
])
```

### 完整参数

```blade
@include('module_dcatadmin::components.iframe', [
    'src' => route('your.route.name'),           // 必需：iframe 地址
    'width' => '100%',                            // 可选：宽度，默认 100%
    'height' => '600px',                          // 可选：高度，默认 800px
    'id' => 'my-iframe',                          // 可选：iframe ID，默认自动生成
    'class' => 'custom-container',                // 可选：容器 CSS 类
    'style' => 'margin: 0; padding: 0;',          // 可选：自定义样式
    'minHeight' => '600px',                       // 可选：最小高度
    'adaptive' => true,                           // 可选：自适应高度，默认 false
])
```

## 使用示例

### 示例 1：Vue 文章管理页面（Demo5 模块）

```php
// VuePostController.php
public function index(Content $content)
{
    return $content
        ->title('Vue 文章管理')
        ->body(view('module_dcatadmin::components.iframe', [
            'src' => route('module_demo5.vue-posts.standalone'),
            'height' => '900px'
        ]));
}
```

### 示例 2：Vue 仪表盘（DcatAdmin 模块）

```php
// VueDashboardController.php
public function index(Content $content)
{
    return $content
        ->title('Vue 仪表盘')
        ->description('基于 Vue 3 的实时数据仪表盘')
        ->body(view('module_dcatadmin::components.iframe', [
            'src' => route('module_dcatadmin.vue-dashboard.standalone'),
            'minHeight' => '100vh'
        ]));
}
```

### 示例 3：Element 组件演示

```php
public function elementsDemo(Content $content)
{
    return $content
        ->title('Element Plus 组件演示')
        ->description('展示 Element Plus 各组件的使用方法')
        ->body(view('module_dcatadmin::components.iframe', [
            'src' => route('module_dcatadmin.elements-demo.standalone'),
            'height' => '1200px',
            'id' => 'elements-demo-iframe'
        ]));
}
```

### 示例 4：自适应高度

```php
public function adaptivePage(Content $content)
{
    return $content
        ->title('自适应页面')
        ->body(view('module_dcatadmin::components.iframe', [
            'src' => route('some.page.standalone'),
            'adaptive' => true,         // 启用自适应
            'minHeight' => 600          // 最小高度 600px
        ]));
}
```

## 参数说明

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `$src` | string | **必需** | iframe 加载的 URL |
| `$width` | string | '100%' | iframe 宽度 |
| `$height` | string | '800px' | iframe 高度 |
| `$id` | string | 自动生成 | iframe ID，用于样式和脚本 |
| `$class` | string | 'vue-iframe-container' | 容器 CSS 类名 |
| `$style` | string | '' | 容器内联样式 |
| `$minHeight` | string/null | null | 最小高度（CSS） |
| `$adaptive` | boolean | false | 是否根据内容自适应高度 |

## 注意事项

### 1. 跨域限制

如果 iframe 加载的内容与父页面不同域，自适应高度功能将无法工作（浏览器安全策略）。

### 2. 命名空间

组件使用 DcatAdmin 模块的命名空间：`module_dcatadmin::components.iframe`

其他模块使用时需要包含完整命名空间：
```blade
@include('module_dcatadmin::components.iframe', [...])
```

### 3. 替换现有模板

建议逐步替换现有的硬编码 iframe 模板：

**替换前**：
```blade
<!-- Modules/Demo5/resources/views/vue/post-list-iframe.blade.php -->
<iframe
    src="{{ route('module_demo5.vue-posts.standalone') }}"
    frameborder="0"
    style="width: 100%; height: 800px; border: none;"
></iframe>
```

**替换后**：
```blade
<!-- Modules/Demo5/resources/views/vue/post-list-iframe.blade.php -->
@include('module_dcatadmin::components.iframe', [
    'src' => route('module_demo5.vue-posts.standalone')
])
```

## 迁移指南

### 步骤 1：更新控制器

将硬编码的 iframe 视图替换为组件：

```php
// 旧代码
return $content->body(view('module_demo5::vue.post-list-iframe'));

// 新代码
return $content->body(view('module_dcatadmin::components.iframe', [
    'src' => route('module_demo5.vue-posts.standalone')
]));
```

### 步骤 2：删除冗余模板

替换后，可以删除旧的 iframe 模板文件：
- `Modules/Demo5/resources/views/vue/post-list-iframe.blade.php`
- `Modules/DcatAdmin/resources/views/vue/iframe.blade.php`
- 其他硬编码的 iframe 模板

### 步骤 3：统一管理

所有 iframe 页面统一使用 DcatAdmin 模块的组件，便于维护和升级。

## 更新记录

- **2026-09-04**: 创建通用 iframe 组件
- **初始版本**: v1.0