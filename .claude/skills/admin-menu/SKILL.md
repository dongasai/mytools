---
name: admin-menu
description: 创建或修改模块的后台菜单配置（config/admin_menu.php）。当用户说"添加后台菜单"、"集成菜单"、"创建admin_menu"、"给XX模块加菜单"、"菜单配置"、"后台菜单同步"时触发。涉及模块后台导航菜单的增删改查操作都使用此技能。
---

# admin_menu 集成指南

## 核心概念

每个模块通过 `config/admin_menu.php` 声明自己的后台导航菜单，然后通过同步命令写入数据库。**配置文件是唯一配置源，数据库只存储同步结果。**

---

## 操作一：为模块创建菜单配置

### 1.1 创建文件

在目标模块下创建 `config/admin_menu.php`：

```
Modules/{ModuleName}/config/admin_menu.php
```

### 1.2 申请菜单 ID 段

**ID 段统一在 `Modules/DcatAdminId.md`（项目根目录）中管理和维护**，按业务域划分。

操作步骤：

1. **先查**：打开 `Modules/DcatAdminId.md`，确认模块所属业务域和已有的 ID 分配
2. **再申**：如果模块尚未分配 ID 段，在 `DcatAdminId.md` 对应域下追加一行：
   ```
   //  XXxxx ModuleName 模块 说明
   ```
3. **后取**：顶级父菜单取 ID 段的起始值（如 `18001`），子菜单依次递增

示例 —— 为 Im 模块申请 ID 段后，在 `DcatAdminId.md` 的功能模块区域追加：
```
//  21000 Im 模块 即时通讯
```

### 1.3 编写配置文件

```php
<?php

// {ModuleName}模块后台菜单配置
// 菜单ID范围: XXxxx 系列
return [
    // 顶级父菜单（uri 为空，作为分组）
    [
        'id'          => 18001,
        'parent_id'   => 0,
        'order'       => 18001,
        'title'       => '我的模块',
        'icon'        => 'feather icon-box',
        'uri'         => '',            // 父菜单 uri 必须为空
    ],
    // 子菜单（有 uri，指向具体页面）
    [
        'id'          => 18002,
        'parent_id'   => 18001,
        'order'       => 18002,
        'title'       => '仪表盘',
        'icon'        => 'feather icon-home',
        'uri'         => 'module_xxx/dashboard',  // ⚠️ 必须使用 module_* 格式
    ],
    [
        'id'          => 18003,
        'parent_id'   => 18001,
        'order'       => 18003,
        'title'       => '数据列表',
        'icon'        => '',
        'uri'         => 'module_xxx/list',      // ⚠️ 必须使用 module_* 格式
    ],
];
```

### 1.4 字段说明

| 字段 | 必填 | 说明 |
|------|------|------|
| `id` | 是 | 全局唯一菜单ID，按分配的 ID 段取值 |
| `parent_id` | 是 | 父菜单ID，顶级为 `0` |
| `order` | 否 | 排序值，不填默认取 `id` 值 |
| `title` | 是 | 菜单显示文字 |
| `icon` | 是 | feather icon 类名，子菜单通常为空字符串 `''` |
| `uri` | 是 | 父菜单为空 `''`，子菜单必须使用 `module_*` 格式（见关键规则） |

---

## 操作二：同步菜单到数据库

创建或修改 `admin_menu.php` 后，必须执行同步才能生效。

### 2.1 预览（推荐先执行）

```bash
php artisan admin:sync-menu --module=模块名 --dry-run
```

### 2.2 首次同步（插入新菜单）

```bash
php artisan admin:sync-menu --module=模块名
```

### 2.3 修改后强制更新

修改了 title、icon、parent_id、order 等字段后：

```bash
php artisan admin:sync-menu --module=模块名 --force
```

### 2.4 同步所有模块

```bash
php artisan admin:sync-menu
```

### 2.5 后台页面操作

访问 `/dailianadmin/module_dcatadmin/menu-sync`，页面提供可视化的同步操作。

---

## 操作三：修改已有菜单

1. 编辑 `config/admin_menu.php`，修改对应菜单项的字段
2. 执行 `php artisan admin:sync-menu --module=模块名 --force`

> 不加 `--force` 时，已存在的菜单不会被更新。

---

## 操作四：删除模块菜单

1. 从 `config/admin_menu.php` 中删除对应菜单项
2. 执行 `php artisan admin:sync-menu --delete-orphan --dry-run` 先预览
3. 确认后执行 `php artisan admin:sync-menu --delete-orphan`

> 孤儿菜单 = 数据库中存在但配置文件中已删除的菜单。有子菜单的父菜单不会被删除。

---

## 关键规则

1. **父菜单 uri 为空**：顶级菜单的 `uri` 是 `''`，它只是一个分组容器，不指向任何页面
2. **子菜单 URI 必须使用 `module_*` 格式**：所有子菜单的 URI 必须以 `module_{模块名小写}` 开头，与路由前缀保持一致
   - ✅ 正确：`'uri' => 'module_reward/records'`
   - ✅ 正确：`'uri' => 'module_account/account'`
   - ❌ 错误：`'uri' => 'files'`（缺少 module_* 前缀）
   - ❌ 错误：`'uri' => 'file/files'`（错误的模块名）
   - 模块名小写查看 ServiceProvider 的 `$nameLower` 属性
3. **ID 全局唯一**：不同模块的菜单 ID 不能重复，严格遵守 ID 段分配
4. **同步后才生效**：配置文件修改后不会自动生效，必须执行同步命令
5. **uri 相同的子菜单视为同一菜单**：同步时按 uri 判断子菜单是否存在
6. **配置是唯一真相源**：不要直接在数据库中修改菜单

---

## 常见问题

### 菜单同步后发现不了？

检查模块是否有 `config/admin_menu.php` 文件。`MenuSyncService` 只扫描存在该文件的已启用模块。

### 修改了标题但没变化？

默认同步模式只插入不更新。使用 `--force` 参数强制覆盖。

### 菜单 ID 冲突怎么办？

如果两个模块的菜单 ID 重复，后同步的会失败（数据库主键冲突）。检查并修改 ID 后重新同步。

### 菜单点击后无法跳转？

检查 URI 格式是否正确：
- URI 必须以 `module_{模块名小写}` 开头
- 模块名小写必须与 ServiceProvider 的 `$nameLower` 属性一致
- 例如：ServiceProvider 中 `$nameLower = 'module_afile'`，菜单 URI 应为 `'module_afile/files'`

**错误示例**：
```php
// ❌ 错误 - 缺少 module_* 前缀
'uri' => 'files',

// ❌ 错误 - 模块名不一致
'uri' => 'module_file/files',  // 应该是 module_afile
```

**正确示例**：
```php
// ✅ 正确 - 与 ServiceProvider 的 $nameLower 一致
'uri' => 'module_afile/files',
'uri' => 'module_reward/records',
```
