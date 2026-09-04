# FeatureExcel 模块开发指南

> **核心思路**: 参见 `docs/FeatureExcel核心思路.md`（人类维护）
> **详细设计**: 参见 `docs/FeatureExcel模块设计方案.md`

## 模块定位

**工具功能模块层**：提供 Excel/CSV 导入导出的通用引擎能力。

---

## 对外提供能力

根据核心思路，模块对外提供三大能力：

### 1. 约定/基类

- **AbstractImportTemplate** - 导入模板基类
- **AbstractExportTemplate** - 导出模板基类

### 2. 服务

- **ModuleFeatureExcelService** - 对外服务类

### 3. 工具

- **console工具** - Excel表格与模板契合验证

---

## 架构设计

```
业务模块层 → 继承模板基类，定义字段
    ↓
工具功能模块层（FeatureExcel）→ 提供模板基类 + 服务 + 工具
    ↓
核心模块层 → ABase / AFile（基础设施）
```

---

## 模板基类使用

### AbstractImportTemplate

**位置**: `Templates/Base/AbstractImportTemplate.php`

**子类必须实现**: `defineFields()` - 定义字段映射

**子类可选重写**:
- `validateRow()` - 行级业务验证
- `transformRow()` - 行级业务转换

**示例**:
```php
class EnergyDataHourlyImportTemplate extends AbstractImportTemplate
{
    protected string $name = '能源小时数据导入模板';
    protected int $startRow = 3;  // 第2行为示例数据，导入时跳过

    protected function defineFields(): array
    {
        return [
            'meter_id' => FieldMapping::make('A', 'integer')
                ->name('表计ID')
                ->required()
                ->validation(['min:1']),
            // ...
        ];
    }

    public function validateRow(array $row): bool
    {
        return Meter::where('id', $row['meter_id'])->exists();
    }
}
```

### AbstractExportTemplate

**位置**: `Templates/Base/AbstractExportTemplate.php`

**子类必须实现**: `defineFields()` - 定义字段映射

**子类可选重写**: `formatRow()` - 行级业务格式化

**示例**:
```php
class EnergyDataDailyExportTemplate extends AbstractExportTemplate
{
    protected string $name = '能源日数据导出模板';
    protected array $headers = ['ID', '表计ID', '数据日期'];
    protected string $fileNamePattern = 'energy_data_{date}';

    protected function defineFields(): array
    {
        return [
            'id' => FieldMapping::make('A', 'integer'),
            'data_date' => FieldMapping::make('B', 'date')
                ->format('date:Y-m-d'),
            // ...
        ];
    }
}
```

---

## 字段映射构建器

**FieldMapping** - 流式接口定义字段映射

**示例**:
```php
FieldMapping::make('A', 'integer')
    ->name('表计ID')
    ->required()
    ->validation(['min:1'])
    ->transform('int')
    ->timezone('Asia/Shanghai', 'UTC');
```

**构建器方法**:
- `make(column, type)` - 创建字段映射
- `name(string)` - 列名（错误提示用）
- `required()` - 必填标记
- `validation(array)` - inhere/php-validate 验证规则
- `transform(string)` - 数据转换
- `format(string)` - 输出格式（导出用）
- `timezone(from, to)` - 时区转换

---

## 对外服务类使用

### ModuleFeatureExcelService

**位置**: `Services/ModuleFeatureExcelService.php`

#### 导入数据

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;

$template = new EnergyDataHourlyImportTemplate();
$result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

if ($result->isSuccess()) {
    $data = $result->getData();
    EnergyDataService::batchCreateHourlyData($data, $userId);
} else {
    $errors = $result->getErrors();  // 含 Excel 行号
}
```

#### 导出数据

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;

$template = new EnergyDataDailyExportTemplate();
$data = EnergyDataService::getDailyDataRecords($meterId, $startDate, $endDate);

$fingerprint = ModuleFeatureExcelService::generateDataFingerprint(['meter_id' => $meterId], $data);

$url = ModuleFeatureExcelService::export($data, $template, [
    'data_fingerprint' => $fingerprint,
    'tenant_id' => $this->token_merchant_id,
    'user_id' => $this->user_id,
]);
```

---

## Console 工具使用

模块提供三个 Console 命令：

### 1. 模板验证命令

```bash
php artisan featureexcel:validate {file} {template} [--dry-run]
```

**功能**:
- 验证 Excel 文件是否符合模板定义
- 展示模板摘要与字段映射表
- 输出验证错误（行号 + 字段 + 原因）

**参数**:
- `file`: 待验证的 Excel/CSV 文件路径
- `template`: 模板类名（支持 FQCN 或短类名）
- `--dry-run`: 仅预览模板定义，不执行文件验证

**示例**:
```bash
# 完整类名
php artisan featureexcel:validate storage/energy.xlsx "Modules\NtEnergy\FeatureExcelTemplates\EnergyDataHourlyImportTemplate"

# 短类名（自动扫描各模块 ImportTemplates 目录）
php artisan featureexcel:validate storage/energy.xlsx EnergyDataHourlyImportTemplate

# 仅预览模板定义
php artisan featureexcel:validate storage/energy.xlsx EnergyDataHourlyImportTemplate --dry-run
```

### 2. Excel 解析命令

```bash
php artisan feature-excel:parse {path} [--rows=100] [--sheet=0]
```

**功能**:
- 解析 .xlsx/.xls 文件（本地路径或 URL）
- 对象化输出单元格数据（type/value/formatted/calculated）
- 支持大数据文件分块读取

**参数**:
- `path`: Excel 文件路径或 URL
- `--rows`: 最多输出行数（默认 100）
- `--sheet`: 工作表索引（默认 0，第一个）

**示例**:
```bash
# 解析本地文件
php artisan feature-excel:parse public/excel_template/Demo.xlsx

# 限制输出行数和工作表
php artisan feature-excel:parse storage/data.xlsx --rows=50 --sheet=1

# 解析 URL 文件
php artisan feature-excel:parse https://example.com/data.xlsx
```

### 3. 模板生成命令

```bash
php artisan featureexcel:generate-template {template} [--output=public/excel_template] [--filename=]
```

**功能**:
- 根据导入模板定义自动生成 Excel 模板文件
- 包含表头、示例数据和表头批注说明

**参数**:
- `template`: 模板类名（支持 FQCN 或短类名）
- `--output`: 输出目录（默认 `public/excel_template`）
- `--filename`: 自定义文件名（不含扩展名）

**示例**:
```bash
# 生成模板到默认目录
php artisan featureexcel:generate-template DemoOrderImportTemplate

# 指定输出目录和文件名
php artisan featureexcel:generate-template DemoOrderImportTemplate --output=storage/templates --filename=订单导入模板
```

---

## 核心流程

### 导入流程

```
文件上传 → 实例化模板 → ModuleFeatureExcelService::importWithValidation()
    → 引擎处理（验证+转换）→ 返回 ImportResult → 业务入库
```

### 导出流程

```
查询数据 → 实例化模板 → ModuleFeatureExcelService::export()
    → 引擎处理（转换+生成）→ AFile保存 → 返回下载URL
```

---

## 业务模块集成

### 步骤1: 创建模板类

- `Modules/{模块}/FeatureExcelTemplates/{功能}ImportTemplate.php`
- `Modules/{模块}/FeatureExcelTemplates/{功能}ExportTemplate.php`

### 步骤2: 使用服务

业务模块 Service 直接调用 `ModuleFeatureExcelService`。

### 集成示例（FeatureExcelDemo 模块）

**模板定义** (`Modules/FeatureExcelDemo/FeatureExcelTemplates/DemoOrderImportTemplate.php`):

```php
<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\FeatureExcelTemplates;

use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;
use Modules\FeatureExcel\Engines\Template\FieldMapping;

class DemoOrderImportTemplate extends AbstractImportTemplate
{
    protected string $name = '演示订单导入模板';
    protected int $startRow = 3;  // 第2行为示例数据，导入时跳过

    protected function defineFields(): array
    {
        return [
            'order_no' => FieldMapping::make('A', 'string')->name('订单号')->required(),
            'customer_name' => FieldMapping::make('B', 'string')->name('客户姓名')->required(),
            'product_name' => FieldMapping::make('C', 'string')->name('产品名称')->required(),
            'quantity' => FieldMapping::make('D', 'integer')->name('数量')->required(),
            'unit_price' => FieldMapping::make('E', 'float')->name('单价')->required(),
            'order_date' => FieldMapping::make('F', 'date')->name('订单日期')->required(),
            'status' => FieldMapping::make('G', 'string')->name('状态')->setDefault('pending'),
            'remark' => FieldMapping::make('H', 'string')->name('备注')->nullable(),
        ];
    }

    public function validateRow(array $row, int $rowNumber): ?string
    {
        if (isset($row['order_no']) && !preg_match('/^ORD\d{10}$/', $row['order_no'])) {
            return '订单号格式错误';
        }
        return null;
    }

    public function transformRow(array $row): array
    {
        if (isset($row['quantity']) && isset($row['unit_price'])) {
            $row['total_amount'] = $row['quantity'] * $row['unit_price'];
        }
        return $row;
    }
}
```

**生成模板文件**:

```bash
php artisan featureexcel:generate-template DemoOrderImportTemplate
```

**导入数据**:

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcelDemo\FeatureExcelTemplates\DemoOrderImportTemplate;

$template = new DemoOrderImportTemplate();
$result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

if ($result->isSuccess()) {
    $data = $result->getData();
    // 业务处理：入库、调用其他 Service 等
    foreach ($data as $row) {
        // $row['order_no'], $row['customer_name'], $row['total_amount'] ...
    }
}
```

---

## 开发注意事项

### 必须遵守

1. **对象化模板**: 业务模块必须创建模板类，继承模板基类
2. **实现 defineFields()**: 模板类必须实现 `defineFields()` 方法
3. **使用构建器**: 字段映射必须使用 `FieldMapping::make()` 构建器
4. **Service 层静态方法**: 所有 Service 方法必须为静态方法
5. **路径安全验证**: 导入前必须调用 `validateFilePath()`
6. **数据验证**: 导入数据必须通过验证
7. **AFile 集成**: 禁止直接写 storage 目录

### 推荐实践

1. **钩子方法**: 模板类可重写钩子方法封装业务逻辑
2. **模板继承**: 通过继承复用模板定义
3. **单元测试**: 模板类易于单元测试（类型安全）
4. **大数据分块**: 超过 5000 行自动启用分块处理
5. **缓存优化**: 导出使用数据指纹避免重复生成

---

## 核心依赖

- **phpoffice/phpspreadsheet**: ^2.0（Excel 处理）
- **inhere/php-validate**: 验证库（项目已有）
- **Carbon**: 日期时间处理（项目已有）

---

## 测试

模块测试目录：`Modules/FeatureExcel/Tests/`

```bash
# 运行模块所有测试
./vendor/bin/phpunit Modules/FeatureExcel/Tests

# 运行单元测试（TemplateBaseTest，v3 模式 12 用例）
./vendor/bin/phpunit Modules/FeatureExcel/Tests/Unit
```

---

**更新时间**: 2026-08-14
**基于**: `docs/FeatureExcel核心思路.md`