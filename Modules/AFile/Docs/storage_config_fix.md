# 存储配置修复工具

## 功能说明

在存储配置管理列表中，为本地存储配置添加"修复配置"按钮。当配置出现问题时，按钮会自动显示。

## 技术实现

### 继承项目基类

```php
use Modules\DcatAdmin\DcatAdmin\RowAction;

class FixStorageConfigAction extends RowAction
{
    protected $htmlClasses = ['action-warning'];  // 样式类

    public function title()
    {
        return '<i class="fa fa-wrench"></i> 修复配置';
    }

    // 不需要覆盖 render2() 或 html() 方法
    // 基类会自动调用 Dcat 原生渲染，绑定点击事件
}
```

### AJAX 方式实现

- **继承**：项目 RowAction 基类
- **方式**：AJAX 方式（修改数据状态）
- **特点**：条件显示，自动刷新 Grid

### 核心方法

| 方法 | 用途 | 说明 |
|------|------|------|
| `title()` | 按钮标题 | 显示图标 + 文字（必须） |
| `allowed()` | 显示条件 | 只在配置有问题时返回 true |
| `handle()` | AJAX 处理 | 执行修复逻辑 |
| `confirm()` | 确认弹窗 | 操作前确认 |

### 样式规范

- **样式类**：`action-warning`（映射到 `btn-outline-warning`）
- **图标**：`fa-wrench`（扳手）
- **尺寸**：自动使用 `btn-sm`

### 重要说明

**不要覆盖 render2() 或 html() 方法！**

- ✅ 正确：只实现 `title()` 方法
- ❌ 错误：覆盖 `render2()` 方法会破坏点击事件

Dcat 原生渲染会自动：
1. 使用 `title()` 生成按钮文字
2. 使用 `$htmlClasses` 生成样式
3. 绑定点击事件处理 AJAX 请求

## 架构设计

## 检测和修复的问题

### 1. 路径不在项目目录内

**问题**：存储路径指向了错误的位置（如从其他项目复制的配置）

**自动修复**：
- `local` 存储：修正为 `storage_path('app')`
- `public` 存储：修正为 `storage_path('app/public')`
- 其他存储：修正为 `storage_path('app/{name}')`

### 2. 路径不存在

**问题**：配置的路径目录不存在

**自动修复**：创建目录（权限 755）

### 3. 缺少 root 配置

**问题**：本地存储缺少 `root` 字段

**自动修复**：添加默认路径 `storage_path('app')`

## 显示条件

修复按钮只在以下情况显示：
- ✓ 存储驱动为 `local`（本地存储）
- ✓ 配置路径不在项目目录内
- 或 ✓ 配置路径不存在

**不显示的情况**：
- ✗ 非本地存储（OSS、S3 等）
- ✗ 配置正确，无需修复

## 使用方法

### 在后台管理界面

1. 访问：`/admin/module_afile/storage-configs`
2. 找到有问题的本地存储配置行
3. 点击黄色的"修复配置"按钮
4. 确认修复操作
5. 系统自动修复并刷新页面

### 测试方法

模拟配置错误来测试：

```bash
# 修改 ID=1 的存储路径为错误值
php artisan tinker --execute="
DB::table('file_storage_configs')->where('id', 1)->update([
    'config' => json_encode(['root' => '/wrong/path/storage/app'])
]);
echo '已设置错误路径，访问后台查看修复按钮';
"
```

访问后台列表页面，可以看到黄色的"修复配置"按钮。

修复后：

```bash
# 验证修复结果
php artisan tinker --execute="
\$config = DB::table('file_storage_configs')->find(1);
echo 'Root: ' . json_decode(\$config->config, true)['root'] . PHP_EOL;
"
```

## 实现文件

- **Action**：`Modules/AFile/DcatAdmin/Actions/FixStorageConfigAction.php`
- **Controller**：`Modules/AFile/DcatAdmin/Controllers/StorageConfigController.php`

## 技术要点

### 1. 正确的渲染方式

**不要覆盖渲染方法**，只需要实现 `title()`：

```php
// ✅ 正确
public function title()
{
    return '<i class="fa fa-wrench"></i> 修复配置';
}

// ❌ 错误 - 会破坏点击事件
public function render2()
{
    return '<button>修复配置</button>';
}
```

**原理**：
- 基类的 `render2()` 调用 Dcat 原生渲染
- Dcat 会使用 `title()` 和 `$htmlClasses` 自动生成按钮
- 自动绑定 AJAX 点击事件

### 2. 条件显示（allowed 方法）

使用 `allowed()` 方法控制 Action 显示：

```php
public function allowed(): bool
{
    $row = $this->getRow();

    if (!$row) {
        return false;
    }

    $config = $row->config;
    $driver = $row->driver;

    // 只检查本地存储
    if ($driver !== 'local') {
        return false;
    }

    // 检查路径问题
    return !str_starts_with($config['root'], base_path()) || !is_dir($config['root']);
}
```

**关键点**：
- ✓ 检查 $row 是否存在
- ✓ 只处理 local 驱动
- ✓ 检查路径在项目目录内
- ✓ 检查目录是否存在

### 3. 智能修复逻辑

根据存储名称确定正确路径：

```php
if ($name === 'public' || $isDefault) {
    $newPath = storage_path('app/public');
} elseif ($name === 'local') {
    $newPath = storage_path('app');
} else {
    $newPath = storage_path('app/' . $name);
}
```

### 4. 目录创建

自动创建不存在的目录：

```php
if (!is_dir($config['root'])) {
    mkdir($config['root'], 0755, true);
}
```

### 5. 响应规范

AJAX 方式必须返回 Response 对象并刷新：

```php
public function handle(): Response
{
    try {
        // 修复逻辑
        DB::table('file_storage_configs')
            ->where('id', $id)
            ->update(['config' => json_encode($config)]);

        // 必须 refresh()
        return $this->response()->success('修复成功')->refresh();
    } catch (\Exception $e) {
        return $this->response()->error($e->getMessage())->refresh();
    }
}
```

### 6. 安全确认

操作前需要用户确认：

```php
public function confirm()
{
    return [
        '确定要修复此存储配置吗？',
        '将自动修正路径配置并创建必要目录',
    ];
}
```

### 7. 样式类映射

**必须使用 `render2()` 而不是 `render()`**：

```php
// ❌ 错误 - 基类 render() 是 final 方法
public function render()
{
    return '<button>...</button>';
}

// ✅ 正确 - 使用 render2()
public function render2()
{
    $title = $this->title();

    return <<<HTML
<a {$this->formatHtmlAttributes()} class="btn btn-sm btn-outline-warning" title="修复配置">
    {$title}
</a>
HTML;
}
```

## 按钮样式

- **颜色**：黄色（warning）
- **图标**：扳手图标（fa-wrench）
- **位置**：在"测试连接"按钮前

## 扩展建议

如需添加更多检测规则，修改 `if()` 方法：

```php
// 检查路径权限
if (!is_readable($config['root']) || !is_writable($config['root'])) {
    return true;
}

// 检查磁盘空间
$freeSpace = disk_free_space($config['root']);
if ($freeSpace < 1024 * 1024 * 100) { // 小于 100MB
    return true;
}
```

---

**创建时间**：2026-08-18
**维护者**：AI 开发团队
## 重构改进

### 从原生基类到项目基类

**改进前**：
```php
use Dcat\Admin\Grid\RowAction;

class FixStorageConfigAction extends RowAction
{
    protected function html()  // ❌ 使用 html()
    {
        return '<a class="btn btn-warning">...</a>';  // ❌ 实心按钮
    }
    
    public function if()  // ❌ 使用 if()
    {
        // ...
    }
}
```

**改进后**：
```php
use Modules\DcatAdmin\DcatAdmin\RowAction;

class FixStorageConfigAction extends RowAction
{
    protected $htmlClasses = ['action-warning'];  // ✓ 样式类
    
    public function allowed(): bool  // ✓ 使用 allowed()
    {
        // ...
    }
    
    public function render2()  // ✓ 使用 render2()
    {
        return '<a class="btn btn-outline-warning">...</a>';  // ✓ 轮廓按钮
    }
}
```

### 改进要点

| 改进项 | 原实现 | 新实现 | 原因 |
|--------|--------|--------|------|
| 基类 | Dcat 原生 | 项目基类 | 获得权限控制能力 |
| 显示判断 | `if()` | `allowed()` | 符合项目命名规范 |
| HTML渲染 | `html()` | `render2()` | 基类 render() 是 final |
| 按钮样式 | `btn-warning` | `btn-outline-warning` | 轮廓样式更轻量 |
| 响应刷新 | 缺失 | 包含 `refresh()` | AJAX 必须刷新 Grid |

### 验证结果

```bash
✓ 语法正确
✓ 继承项目基类
✓ 实现所有必需方法
✓ 使用轮廓样式
✓ 包含 refresh() 调用
```

---

**更新时间**：2026-08-18  
**维护者**：AI 开发团队

**样式类映射**：

```php
protected $htmlClasses = ['action-warning'];
```

映射关系：
- `action-success` → `btn-outline-success`（绿色）
- `action-warning` → `btn-outline-warning`（黄色）
- `action-danger` → `btn-outline-danger`（红色）
- `action-info` → `btn-outline-primary`（蓝色）

### 8. 为什么不覆盖 render2()

**原因**：Dcat 原生渲染会自动处理点击事件

```
基类 render() 流程：
├─ 检查 allowed()
├─ 检查 isShow()
└─ 调用 render2()
    └─ 调用 Dcat 原生渲染
        ├─ 使用 title() 生成文字
        ├─ 使用 $htmlClasses 生成样式
        └─ 绑定 AJAX 点击事件 ← 关键！
```

如果覆盖 `render2()`：
- ❌ 破坏点击事件绑定
- ❌ 按钮点击无反应
- ❌ AJAX 请求无法发送

正确的做法：
- ✅ 只实现 `title()` 方法
- ✅ 设置 `$htmlClasses` 属性
- ✅ 让基类自动渲染和绑定事件

