# Vue 仪表盘 - 构建和使用说明

## 文件结构

```
Modules/DcatAdmin/
├── DcatAdmin/
│   ├── Controllers/
│   │   ├── VueDashboardController.php      # Vue 仪表盘页面控制器
│   │   └── DashboardApiController.php      # 数据 API 控制器
│   └── menu.md                             # 菜单配置说明
├── resources/
│   ├── views/vue/
│   │   └── dashboard.blade.php             # Blade 视图
│   └── vue/
│       ├── app.js                          # Vue 入口文件
│       ├── pages/
│       │   └── Dashboard.vue               # 仪表盘主组件
│       └── components/
│           ├── StatCard.vue                # 统计卡片组件
│           └── ChartLine.vue               # 折线图组件
├── routes/
│   └── admin.php                           # 路由配置（已添加）
├── package.json                            # npm 配置
└── vite.config.js                          # Vite 配置
```

## 功能特性

- **4个统计卡片**: 用户数、订单数、收入、访问量
- **折线图**: 显示近7天趋势（使用 ECharts）
- **Element Plus UI**: 完整的组件支持
- **响应式设计**: 支持移动端适配
- **自动 CSRF 保护**: 继承 Dcat Admin 认证

## 技术栈

- Vue 3 + Composition API
- Element Plus 2.5
- ECharts 5.4
- Axios
- Vite 5

## 快速开始

### 1. 安装依赖

```bash
cd Modules/DcatAdmin
npm install
```

### 2. 开发模式

```bash
npm run dev
```

Vite 开发服务器将在 `http://localhost:5173` 启动。

### 3. 生产构建

```bash
npm run build
```

构建输出目录: `public/build/dcatadmin-vue/`

## 路由配置

已自动添加以下路由：

| 路由 | 控制器 | 说明 |
|------|--------|------|
| `/admin/module_dcatadmin/vue-dashboard` | VueDashboardController | 仪表盘页面 |
| `/admin/api/dashboard/stats` | DashboardApiController | 统计数据 API |
| `/admin/api/dashboard/chart` | DashboardApiController | 图表数据 API |

## 菜单配置

在 Dcat Admin 后台菜单中添加：

- **标题**: Vue 仪表盘
- **图标**: fa-bar-chart
- **URI**: `module_dcatadmin/vue-dashboard`
- **权限**: 管理员权限

运行菜单同步命令：
```bash
php artisan admin:sync-menu
```

## API 响应格式

### 统计数据接口

```json
{
    "users": { "value": 1234, "trend": 12.5 },
    "orders": { "value": 567, "trend": -5.2 },
    "revenue": { "value": 89012, "trend": 8.7 },
    "visits": { "value": 34567, "trend": 15.3 }
}
```

### 图表数据接口

```json
{
    "dates": ["09-01", "09-02", ...],
    "values": [234, 345, ...]
}
```

## 自定义开发

### 修改统计数据源

编辑 `DashboardApiController.php`：

```php
public function getStats(): JsonResponse
{
    // 替换为真实的数据查询逻辑
    return response()->json([
        'users' => ['value' => User::count(), 'trend' => 12.5],
        // ...
    ]);
}
```

### 修改图表数据

```php
public function getChartData(): JsonResponse
{
    // 从数据库获取真实数据
    $stats = VisitLog::recentDays(7)->get();
    return response()->json([
        'dates' => $stats->pluck('date'),
        'values' => $stats->pluck('count'),
    ]);
}
```

## 注意事项

1. **首次构建**: 必须先运行 `npm run build` 生成构建文件
2. **CSRF Token**: 从 Dcat Admin 的 meta 标签自动获取
3. **样式**: 自动继承 Dcat Admin 的主题变量
4. **认证**: 继承 Dcat Admin 的管理员认证机制

## 调试

检查浏览器控制台：
- Vue 应用挂载到 `#vue-dashboard` 元素
- 全局配置在 `window.DcatAdminVueConfig`
- API 请求自动携带 CSRF Token
