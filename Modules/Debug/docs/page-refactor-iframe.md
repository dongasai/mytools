# Debug 模块页面重构说明

## 重构时间
2026-07-12

## 重构目标
将 Debug 模块的页面架构从"独立页面"改为"顶部二级导航 + iframe 嵌入"方式，解决页面头部重复显示问题。

---

## 架构变更

### 原架构
- 每个页面独立渲染完整 HTML（包含 `<html>`, `<head>`, `<body>`）
- 每个页面独立包含 Bootstrap 样式和 JS
- 用户直接访问 `/debug/routes` 等页面

### 新架构
- **主框架页面** (`/debug/frame`)：包含顶部导航和 iframe 容器
- **内容页面** (`/debug/routes`, `/debug/server`, `/debug/index`)：嵌入 iframe 中
- **iframe 检测**：内容页面如果被直接访问，自动跳转到主框架
- **URL Hash 路由**：通过 hash 跟踪当前页面状态

---

## 新增文件

### 1. DebugFrameController.php
路径：`Modules/Debug/Http/Controllers/DebugFrameController.php`

功能：
- 渲染主框架页面
- 确定初始 iframe URL

### 2. layouts/frame.blade.php
路径：`Modules/Debug/resources/views/layouts/frame.blade.php`

功能：
- Bootstrap navbar（顶部二级导航）
- 导航项：首页、路由列表、服务器信息
- iframe 容器（占满剩余高度）
- JavaScript：loadPage() 函数、hash 路由、active state 管理

### 3. components/layouts/content.blade.php
路径：`Modules/Debug/resources/views/components/layouts/content.blade.php`

功能：
- 基础 HTML 结构
- Bootstrap CSS
- method 样式类（GET/POST/PUT/DELETE 等）
- iframe 检测 JavaScript

### 4. css/frame.css
路径：`Modules/Debug/resources/assets/css/frame.css`

功能：
- navbar 固定高度样式
- iframe 容器高度计算
- loading overlay 样式

---

## 修改文件

### 1. routes/web.php
变更：
- 添加 `/debug/frame` 路由（主框架入口）
- 根路由 `/debug` 重定向到 `/debug/frame`
- 所有内容路由支持 `?embed=1` 参数

路由结构：
```
/debug → 重定向到 /debug/frame
/debug/frame → 主框架页面
/debug/index → 首页内容（iframe 嵌入）
/debug/routes → 路由列表内容（iframe 嵌入）
/debug/server → 服务器信息内容（iframe 嵌入）
```

### 2. master.blade.php
变更：
- 添加 iframe 检测 JavaScript
- 如果不在 iframe 中且没有 embed 参数，跳转到主框架

### 3. routes.blade.php
变更：
- 使用 `<x-debug::layouts.content>` 替代 `<x-debug::layouts.master>`
- 添加隐藏的 `embed` 参数字段

### 4. server.blade.php
变更：
- 使用 `<x-debug::layouts.content>` 替代 `<x-debug::layouts.master>`
- 添加隐藏的 `embed` 参数字段

### 5. index.blade.php
变更：
- 使用 `<x-debug::layouts.content>` 替代 `<x-debug::layouts.master>`
- 工具卡片添加 onclick 事件调用 `parent.loadPage()`

---

## 关键实现细节

### iframe 检测逻辑

**检测方式**：JavaScript window 对象比较
```javascript
const isInIframe = window.self !== window.top;
```

**跳转逻辑**：
- 如果不在 iframe 中
- 且 URL 没有 `embed` 参数
- 则跳转到 `/debug/frame#当前路径`

### 主框架导航逻辑

**loadPage 函数**：
```javascript
function loadPage(page) {
    iframe.src = '/debug/' + page + '?embed=1';
    window.location.hash = page;
    更新导航 active state;
}
```

**URL Hash 跟踪**：
- 点击导航项：更新 hash
- 页面加载：读取 hash 确定初始页面
- iframe 加载完成：同步 active state

### iframe 高度计算

```css
.iframe-container {
    height: calc(100vh - 56px); /* 100vh 减去 navbar 高度 */
    overflow: hidden;
}
```

---

## 使用方式

### 正常访问
1. 访问 `/debug` 或 `/debug/frame`
2. 在主框架中通过顶部导航切换页面
3. 内容在 iframe 中显示，无重复头部

### 直接访问内容页面
1. 访问 `/debug/routes`（无 embed 参数）
2. 自动跳转到 `/debug/frame#routes`
3. 在主框架中显示该页面

### iframe 嵌入模式
1. 访问 `/debug/routes?embed=1`
2. 不跳转，直接显示内容页面（无头部）
3. 用于 iframe 嵌入或其他特殊场景

---

## 技术要点

### 防止双重头部
- 内容页面检测是否在 iframe 中
- 如果不在 iframe 中，跳转到主框架
- 主框架负责显示导航头部
- iframe 内容页面不显示头部

### 保持表单功能
- 过滤表单提交时保留 `embed` 参数
- 使用隐藏字段 `<input type="hidden" name="embed" value="{{ request('embed') }}">`

### 浏览器历史记录
- 使用 hash 跟踪页面状态
- 支持 back/forward 导航
- iframe src 更新同步 hash

---

## 兼容性

### 支持浏览器
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### 依赖
- Bootstrap 5.1.3
- 原生 JavaScript（无 jQuery）
- CSS calc() 函数

---

## 测试要点

1. **主框架加载**
   - `/debug/frame` 正常加载
   - 导航栏显示正确
   - iframe 初始内容正确

2. **iframe 检测**
   - 直接访问内容页面跳转正确
   - embed 参数阻止跳转
   - iframe 中不触发跳转

3. **导航交互**
   - 点击导航项切换正确
   - URL hash 更新正确
   - active state 同步正确

4. **表单提交**
   - 过滤参数保留 embed
   - 结果在 iframe 中显示
   - 无重复头部

5. **浏览器历史**
   - back/forward 导航正确
   - hash 状态恢复正确

---

## 后续优化建议

1. **加载状态指示**
   - 添加 loading overlay
   - iframe 加载完成隐藏

2. **响应式优化**
   - 移动端导航适配
   - iframe 高度动态调整

3. **错误处理**
   - iframe 加载失败提示
   - 无效 hash 跳转默认页

4. **性能优化**
   - 预加载常用页面
   - 缓存页面内容

---

## 维护说明

### 修改内容页面
- 使用 `<x-debug::layouts.content>` 布局
- 表单添加 embed 参数字段
- 确保 iframe 检测有效

### 添加新页面
1. 在主框架导航添加导航项
2. 创建内容页面（使用 content 布局）
3. 添加路由（支持 embed 参数）
4. 更新 loadPage 函数

### 修改导航样式
- 修改 `layouts/frame.blade.php` 的 navbar
- 修改 `css/frame.css` 的样式

---

## 注意事项

- ⚠️ 不要删除 iframe 检测代码
- ⚠️ 不要移除 embed 参数处理
- ⚠️ 不要修改 iframe 高度计算公式
- ⚠️ 保持导航项的 data-nav 属性

---

## 相关文档
- [Debug 模块文档](./CLAUDE.md)
- [开发计划](./DEV.md)
- [项目根文档](../CLAUDE.md)