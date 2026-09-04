# iframe 嵌套问题修复

## 问题
Debug 模块页面重构后出现 iframe 多层嵌套问题。

## 原因分析
1. `DebugFrameController` 设置 `$initialPage = ''`（空字符串）
2. `frame.blade.php` 的 iframe src：`/debug?embed=1`（当 initialPage 为空时）
3. `/debug` 路由配置为 301 重定向到 `/debug/frame`
4. iframe 加载 `/debug?embed=1` → 重定向到 `/debug/frame` → frame 又嵌套 iframe → 嵌套循环

## 嵌套链路
```
/debug/frame (主框架)
  └─ iframe src="/debug?embed=1"
       └─ 重定向到 /debug/frame
            └─ iframe src="/debug?embed=1"
                 └─ 重定向到 /debug/frame
                      └─ 无限嵌套...
```

## 解决方案

### 修改 1：DebugFrameController
将初始页面设置为 `'index'`（首页内容页面）：
```php
public function frame(): View
{
    $initialPage = 'index'; // 改为 'index'，不再是空字符串
    return view('debug::layouts.frame', ['initialPage' => $initialPage]);
}
```

### 修改 2：frame.blade.php 导航
将首页导航项的 data-nav 从空字符串改为 `'index'`：
```html
<a class="nav-link" href="#" data-nav="index" onclick="loadPage('index'); return false;">首页</a>
```

### 修改 3：frame.blade.php JavaScript
更新默认 hash 处理逻辑：
```javascript
const hash = window.location.hash.replace('#', '') || 'index';
// 如果没有 hash，默认使用 'index'，不再使用空字符串
```

## 正确的架构

### 两层结构（无嵌套）
```
/debug/frame (主框架 - 包含导航栏)
  └─ iframe src="/debug/index?embed=1" (内容页面 - 首页)
```

### 导航切换
```
点击"路由列表" → iframe src="/debug/routes?embed=1"
点击"服务器信息" → iframe src="/debug/server?embed=1"
点击"首页" → iframe src="/debug/index?embed=1"
```

### iframe 检测跳转
```
直接访问 /debug/routes → 检测不在 iframe → 跳转到 /debug/frame#routes
直接访问 /debug/server → 检测不在 iframe → 跳转到 /debug/frame#server
直接访问 /debug/index → 检测不在 iframe → 跳转到 /debug/frame#index
```

## 关键要点

⚠️ **防止嵌套的核心规则**：
- iframe 的 src **永远**不能是 `/debug` 或 `/debug/frame`
- iframe 的 src **必须**是内容页面路径：`/debug/{page}?embed=1`
- 内容页面包括：`index`, `routes`, `server`

⚠️ **正确的内容页面路径**：
- `/debug/index` - 首页内容（工具卡片）
- `/debug/routes` - 路由列表内容
- `/debug/server` - 服务器信息内容

⚠️ **错误的内容页面路径**：
- ❌ `/debug` - 会重定向到 `/debug/frame`，导致嵌套
- ❌ `/debug/frame` - 主框架本身，不能嵌入 iframe

## 验证结果

通过 playwright 测试验证：
- ✅ 主框架加载正确（导航栏 + iframe）
- ✅ iframe src 是 `/debug/index?embed=1`（无嵌套）
- ✅ 导航切换正常
- ✅ iframe 检测跳转正常
- ✅ 内容页面无重复头部

## 修复时间
2026-07-12 11:50

## 相关文件
- `Modules/Debug/Http/Controllers/DebugFrameController.php`
- `Modules/Debug/resources/views/layouts/frame.blade.php`
- `Modules/Debug/CLAUDE.md`（开发规范更新）