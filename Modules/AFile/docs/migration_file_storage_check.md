# 文件存储配置检查工具迁移说明

## 迁移原因

将文件存储配置检查工具从 NtMain 模块迁移到 AFile 模块，原因：

1. **功能归属**：文件存储配置检查是 AFile 模块的核心功能，不应放在 NtMain
2. **模块职责**：NtMain 是能碳管理基础数据和协调层，不应包含文件存储相关功能
3. **架构清晰**：文件存储相关功能应集中在 AFile 模块

## 迁移文件

### 移动的文件

| 原路径 | 新路径 |
|--------|--------|
| `Modules/NtMain/Services/FileStorageConfigCheckService.php` | `Modules/AFile/Services/FileStorageConfigCheckService.php` |
| `Modules/NtMain/DcatAdmin/Metrics/FileStorageConfigCheckMetric.php` | `Modules/AFile/DcatAdmin/Metrics/FileStorageConfigCheckMetric.php` |
| `Modules/NtMain/docs/file_storage_config_check.md` | `Modules/AFile/docs/file_storage_config_check.md` |

### 修改的文件

| 文件 | 修改内容 |
|------|----------|
| `Modules/NtMain/DcatAdmin/Controllers/HomeController.php` | 更新 use 语句引用 |
| `Modules/AFile/Services/FileStorageConfigCheckService.php` | 更新命名空间 |
| `Modules/AFile/DcatAdmin/Metrics/FileStorageConfigCheckMetric.php` | 更新命名空间和引用 |

## 命名空间变更

**Service 层**：
```php
// 旧命名空间
namespace Modules\NtMain\Services;

// 新命名空间
namespace Modules\AFile\Services;
```

**Metric 层**：
```php
// 旧命名空间
namespace Modules\NtMain\DcatAdmin\Metrics;

// 新命名空间
namespace Modules\AFile\DcatAdmin\Metrics;
```

## 引用更新

**HomeController.php**：
```php
// 旧引用
use Modules\NtMain\DcatAdmin\Metrics\FileStorageConfigCheckMetric;

// 新引用
use Modules\AFile\DcatAdmin\Metrics\FileStorageConfigCheckMetric;
```

## 验证结果

✅ 所有功能正常：
- Service 检查逻辑正常
- Metric 渲染正常
- 跳转链接正常
- 后台首页显示正常

## 架构改进

### 改进前

```
NtMain 模块（能碳管理）
├── Services/
│   └── FileStorageConfigCheckService.php  ❌ 不应该在这里
└── DcatAdmin/Metrics/
    └── FileStorageConfigCheckMetric.php   ❌ 不应该在这里

AFile 模块（文件管理）
└── （缺失检查功能）
```

### 改进后

```
NtMain 模块（能碳管理）
└── （移除文件存储检查功能）✓

AFile 模块（文件管理）
├── Services/
│   └── FileStorageConfigCheckService.php  ✓ 正确位置
└── DcatAdmin/Metrics/
    └── FileStorageConfigCheckMetric.php   ✓ 正确位置
```

## 收益

1. **职责清晰**：文件存储功能集中在 AFile 模块
2. **维护方便**：相关功能在同一模块，易于维护
3. **架构合理**：NtMain 回归能碳管理职责

---

**迁移时间**：2026-08-18
**维护者**：AI 开发团队