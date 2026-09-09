# Dcat Admin 后台布局机制分析

## 问题现象

访问 `/admin/featuredbadmin/?standalone=1` 仍然存在后台布局

## 根本原因

### Dcat Admin 布局机制

当控制器返回 `Content` 对象时，框架会自动包裹后台布局：

```
控制器返回 Content → 框架拦截 → 应用后台布局（sidebar、header） → 渲染 body 内容
```

### 视图层有两层布局

1. **框架层布局**：由 `Content` 对象触发（Dcat Admin 框架层面）
2. **视图文件布局**：由 blade 模板处理（应用层面）

## 解决方案

### 视图层布局判断

视图继承 `module_dcatadmin::layouts.vue-app`，布局内判断：

```blade
@if(request()->get('standalone'))
    {{-- 独立页面模式：完整 HTML --}}
@else
    {{-- iframe 容器模式：iframe 嵌入 --}}
@endif
```

### 控制器层判断

standalone 模式需要绕过 `Content` 对象，避免触发框架后台布局：

```php
public function home(Request $request, Content $content)
{
    // standalone 模式：直接返回视图（无框架后台布局）
    if ($request->get('standalone')) {
        return view('featuredbadmin::vue.app');
    }

    // 普通模式：返回 Content（有框架后台布局）
    return $content
        ->title('数据库管理员工具')
        ->body(view('featuredbadmin::vue.app'));
}
```

## 关键区别

- `return view()` → 不触发框架后台布局 ✅
- `return $content->body(view())` → 触发框架后台布局 ❌

## 完整流程

### standalone=1 模式

```
请求 → 控制器判断 standalone → 返回 view() → 视图层渲染完整 HTML → 无后台布局 ✅
```

### 普通模式

```
请求 → 控制器返回 Content → 框架包裹后台布局 → 视图层渲染 iframe 容器 → 后台布局 + iframe ✅
```

## 总结

视图层判断处理的是**视图文件布局**（完整 HTML vs iframe 容器）。
控制器层判断处理的是**框架后台布局**（是否触发后台布局包裹）。