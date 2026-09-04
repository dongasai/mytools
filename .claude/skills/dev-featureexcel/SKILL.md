---
name: dev-featureexcel
description: FeatureExcel 模块对接开发指南。用于引导业务模块集成 Excel/CSV 导入导出能力：创建导入/导出模板类、定义字段映射、接入 ModuleFeatureExcelService、使用 Console 工具验证。当用户提到"Excel导入"、"Excel导出"、"导入模板"、"导出模板"、"批量导入"、"表格导入导出"、"FeatureExcel"、"对接 FeatureExcel"，或需要为业务模块添加导入导出功能时触发此 skill。即使任务表面是创建模板类或写导入导出 Service 方法，也应按本 skill 的对接规范执行。
---

# FeatureExcel 对接开发指南

指导业务模块（NtEnergy、NtMaterial 等）通过**模板类驱动**方式集成 FeatureExcel 导入导出引擎。

## 模块概览

FeatureExcel 是工具功能模块，业务模块对接只需两件事：

1. **创建模板类**：继承 `AbstractImportTemplate` 或 `AbstractExportTemplate`，实现 `defineFields()`
2. **调用统一服务**：`ModuleFeatureExcelService` 的静态方法

引擎内部（Excel/CSV 双引擎、分块读取、DataValidator、三级 Transform）对业务模块透明。

对接参考实现：`Modules/FeatureExcelDemo/`（完整示例，含模板、Service、Proto Handler）。

---

## 一、创建导入模板类

### 1.1 文件位置与命名

```
Modules/{业务模块}/FeatureExcelTemplates/{功能}ImportTemplate.php
```

- 命名：`{功能}ImportTemplate` / `{功能}ExportTemplate`（如 `EnergyDataHourlyImportTemplate`）
- 同模块内模板类名必须唯一，短类名重复会导致 Console 命令解析歧义

### 1.2 骨架

```php
<?php

declare(strict_types=1);

namespace Modules\NtEnergy\FeatureExcelTemplates;

use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;
use Modules\FeatureExcel\Engines\Template\FieldMapping;

class EnergyDataHourlyImportTemplate extends AbstractImportTemplate
{
    protected string $name = '能源小时数据导入模板';
    protected int $startRow = 3;  // 数据起始行：表头第1行，第2行为示例数据

    protected function defineFields(): array
    {
        return [
            'meter_id' => FieldMapping::make('A', 'integer')
                ->name('表计ID')
                ->required()
                ->validation(['min:1']),
            'data_value' => FieldMapping::make('B', 'float')
                ->name('能耗值')
                ->required(),
            'data_time' => FieldMapping::make('C', 'datetime')
                ->name('数据时间')
                ->required(),
        ];
    }
}
```

### 1.3 可覆盖属性（编译期常量）

| 属性 | 默认值 | 说明 |
|------|--------|------|
| `$name` | '' | 模板名称（错误提示、模板文件生成用） |
| `$description` | '' | 模板描述 |
| `$format` | 'excel' | 格式：'excel' 或 'csv'，决定引擎选择 |
| `$sheet` | 0 | 工作表索引 |
| `$startRow` | 2 | 数据起始行（1 起始计数） |
| `$headerRow` | 1 | 表头行 |

属性覆盖仅支持编译期常量；需要运行时计算的属性在子类构造函数中调用对应 `setXxx()` 设置（构造后调用 `parent::__construct()` 会注入字段，注意顺序）。

### 1.4 业务钩子（可选重写）

**validateRow** — 行级业务验证，在引擎标准验证 + 类型转换**之后**逐行调用：

```php
/**
 * @param  array  $row  单行数据（已完成字段级验证和类型转换）
 * @param  int  $rowNumber  Excel 实际行号
 * @return string|null null 通过；字符串为失败原因
 */
public function validateRow(array $row, int $rowNumber): ?string
{
    if (! Meter::where('id', $row['meter_id'])->exists()) {
        return "表计ID {$row['meter_id']} 不存在";
    }

    return null;
}
```

**transformRow** — 行级业务转换，在 validateRow 通过后逐行调用（单位换算、字段补全、值映射）：

```php
public function transformRow(array $row): array
{
    $row['total'] = $row['quantity'] * $row['unit_price'];

    return $row;
}
```

**行号定位模式（推荐）** — 让 Service 层错误提示定位到 Excel 原始行号：用私有属性在 `validateRow` 中暂存行号，`transformRow` 注入 `_excel_row_number` 字段，Service 入库时读取该字段生成行级错误：

```php
private ?int $currentRowNumber = null;

public function validateRow(array $row, int $rowNumber): ?string
{
    $this->currentRowNumber = $rowNumber;
    // ...业务验证
    return null;
}

public function transformRow(array $row): array
{
    $row['_excel_row_number'] = $this->currentRowNumber;
    // ...业务转换
    return $row;
}
```

签名是**硬约定**：`validateRow(array $row, int $rowNumber): ?string`、`transformRow(array $row): array`，与基类不一致会导致 FatalError。

---

## 二、创建导出模板类

### 2.1 骨架

```php
<?php

declare(strict_types=1);

namespace Modules\NtEnergy\FeatureExcelTemplates;

use Modules\FeatureExcel\Templates\Base\AbstractExportTemplate;
use Modules\FeatureExcel\Engines\Template\FieldMapping;

class EnergyDataDailyExportTemplate extends AbstractExportTemplate
{
    protected string $name = '能源日数据导出模板';
    protected array $headers = ['ID', '表计ID', '数据日期', '能耗值'];
    protected string $fileNamePattern = 'energy_data_{date}';
    protected bool $persist = false;  // false=临时文件，true=持久存储

    protected function defineFields(): array
    {
        return [
            'id' => FieldMapping::make('A', 'integer'),
            'meter_id' => FieldMapping::make('B', 'integer')->name('表计ID'),
            'data_date' => FieldMapping::make('C', 'date')
                ->name('数据日期')
                ->format('date:Y-m-d'),
        ];
    }
}
```

### 2.2 可覆盖属性

| 属性 | 默认值 | 说明 |
|------|--------|------|
| `$name` | '' | 模板名称 |
| `$format` | 'excel' | 'excel' 或 'csv' |
| `$sheetName` | 'Sheet1' | 工作表名 |
| `$headers` | [] | 表头数组（与 defineFields 列序一致） |
| `$styles` | [] | 样式配置 |
| `$fileNamePattern` | '' | 文件名模式，支持 `{变量}` 占位（如 `{date}`），由 `generateFileName()` 替换 |
| `$storeName` | '' | 固定存储名（设置后跳过模式生成） |
| `$persist` | false | 存储策略：false=临时，true=持久 |
| `$cacheTtl` | 86400 | 缓存有效期（秒），配合数据指纹使用 |

### 2.3 业务钩子

**formatRow** — 行级业务格式化，在引擎字段级格式化**之前**逐行调用：

```php
public function formatRow(array $row): array
{
    $row['status_name'] = OrderStatus::from($row['status'])->label();

    return $row;
}
```

---

## 三、FieldMapping 流式接口

字段映射的唯一构建方式（`Engines/Template/FieldMapping`）：

```php
FieldMapping::make($column, $type)   // 列号（A/B/C...）+ 数据类型
    ->name($name)                    // 列名（错误提示用）
    ->required()                     // 必填（->required(false) 取消）
    ->validation($rules)             // inhere/php-validate 规则数组
    ->transform($transform)          // 数据转换（如 'int'、'trim'）
    ->format($format)                // 输出格式（导出用，如 'date:Y-m-d'、'number:2'）
    ->nullable()                     // 允许为空
    ->defaultValue($default)         // 默认值（注意：方法名是 defaultValue）
    ->timezone($from, $to);          // 时区转换（如 'Asia/Shanghai' → 'UTC'）
```

支持的数据类型：`integer`、`float`、`string`、`date`、`datetime`、`boolean`。

`defineFields()` 返回的数组 **key 是业务字段名**（入库用的字段名），value 是 FieldMapping 实例。

---

## 四、调用 ModuleFeatureExcelService

全静态方法，业务模块 Service 层直接调用（Handler 不直接调用 FeatureExcel，经业务 Service 转发）。

### 4.1 导入（标准流程，推荐）

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;

$template = new EnergyDataHourlyImportTemplate();
$result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

if ($result->isSuccess()) {
    $data = $result->getData();       // 行数组，已含业务字段 key
    // 业务入库
} else {
    $errors = $result->getErrors();   // 二维数组（行索引 → 错误消息数组），需双层 foreach 展平
}
```

流程：路径验证 → 文件读取 → DataValidator 标准验证 → 类型转换 → `validateRow` 钩子 → `transformRow` 钩子。

**批量入库模式（推荐）** — Service 层用整批单一事务 + 行级失败收集，Handler 按结果分三档响应：

```php
// Service 层：整批单一事务，失败行收集错误不中断其他行
public static function batchCreate(array $data, int $enterpriseId): array
{
    $success = 0;
    $failed = 0;
    $errors = [];

    DB::beginTransaction();

    foreach ($data as $row) {
        $rowNumber = $row['_excel_row_number'] ?? '未知';

        // 行级业务校验（字段存在性、唯一性等），失败收集错误并 continue
        if (DemoOrder::where('enterprise_id', $enterpriseId)->where('order_no', $row['order_no'])->exists()) {
            $errors[] = "第{$rowNumber}行：订单号 {$row['order_no']} 已存在";
            $failed++;
            continue;
        }

        $row['enterprise_id'] = $enterpriseId;  // 注入租户ID实现隔离
        DemoOrder::create($row);
        $success++;
    }

    DB::commit();

    return compact('success', 'failed', 'errors');
}
```

Handler 侧响应分档：全失败 → `errorResponse`；部分失败 → `successResponse` 并提示"部分导入成功：X 条成功，Y 条失败"；全成功 → `successResponse`。成功数、失败数、错误明细放入响应 Data。

### 4.2 导入（跳过验证）

```php
$data = ModuleFeatureExcelService::import($filePath, $template);  // 仅 transformRow，无验证
```

### 4.3 导出

```php
$fingerprint = ModuleFeatureExcelService::generateDataFingerprint(
    ['meter_id' => $meterId, 'start' => $startDate, 'end' => $endDate],
    $data
);

$url = ModuleFeatureExcelService::export($data, $template, [
    'data_fingerprint' => $fingerprint,  // 相同指纹命中缓存，避免重复生成
    'tenant_id' => $tenantId,            // 商户ID（SaaS 端用 $this->token_enterprise_id）
    'user_id' => $userId,
    're_id' => $relatedBusinessId,       // 关联业务ID（可选）
    'variables' => ['date' => now()->format('Y-m-d')],  // 文件名 {变量} 替换（可选）
]);

// 返回文件下载 URL（相对路径），经 AFile 保存，禁止业务模块直接写 storage
```

流程：`formatRow` 钩子 → 引擎字段级格式化 → 文件生成 → AFile 保存 → 返回下载 URL。

---

## 五、Console 工具验证

| 命令 | 用途 |
|------|------|
| `php artisan featureexcel:generate-template {template} [--output=] [--filename=]` | 按导入模板生成 Excel 文件（表头+示例行） |
| `php artisan featureexcel:validate {file} {template} [--dry-run]` | 验证表格文件与模板契合度，输出行级错误 |
| `php artisan feature-excel:parse {path} [--rows=100] [--sheet=0]` | 解析表格文件对象化输出（调试用） |

注意：
- `{template}` 支持 FQCN 或短类名。短类名扫描目录：validate 扫 `Modules/*/ImportTemplates`、`ExportTemplates`；generate-template 扫 `Modules/*/FeatureExcelTemplates/*.php`。**目录不统一**，跨命令使用时直接传 FQCN 最稳妥
- 命令前缀不统一：前两个是 `featureexcel:`，parse 是 `feature-excel:`
- 对接完成后用 validate 命令实测一份真实表格，验证模板定义正确
- **模板文件存储位置**：generate-template 生成的文件默认输出到 `public/excel_templates/`（`--output` 默认值，前端下载模板的固定目录），生成后需将模板文件提交入库

---

## 六、对接检查清单

1. **分层**：模板类放业务模块 `FeatureExcelTemplates/` 目录；Handler/Controller 不直接调用 FeatureExcel，经业务 Service 层调用 `ModuleFeatureExcelService`
2. **基类**：继承 `AbstractImportTemplate` / `AbstractExportTemplate`（不是引擎层数据对象 `ImportTemplate`/`ExportTemplate`）
3. **defineFields**：抽象方法必须实现，key 为业务字段名
4. **钩子签名**：`validateRow(array $row, int $rowNumber): ?string`、`transformRow(array $row): array`、`formatRow(array $row): array`
5. **构建器**：字段映射只用 `FieldMapping::make()` 流式接口；默认值方法名是 `defaultValue()`
6. **静态调用**：`ModuleFeatureExcelService` 方法全部静态，不实例化
7. **指纹**：导出前用 `generateDataFingerprint()` 生成指纹并传入 options，保证缓存一致性
8. **大文件**：超过 5000 行引擎自动分块，无需业务处理；max_rows 默认 10 万
9. **路径安全**：引擎统一按相对路径解析——`file_path` 传入相对路径（相对临时盘根目录，由 upload_temporary 返回），引擎剥前导斜杠并禁止 `..` 目录遍历，失败转 VALIDATION_FAILED 业务错误码（不会抛 500）
10. **AFile 集成**：导出文件由引擎经 AFile 保存，返回相对 URL；禁止业务模块直接写 storage
11. **验证**：新增模板类后用 `featureexcel:generate-template` 生成模板文件 + `featureexcel:validate` 实测，并补充模板类单元测试
12. **导入模板表格**：generate-template 生成的模板表格文件落到 `public/excel_templates/`（默认 `--output`），前端从此目录下载模板；生成后需将模板文件提交入库
13. **模板下载接口**：每个导入功能配套 download_template Handler，返回 `public/excel_templates/` 中模板文件的 URL
14. **行号定位**：导入模板实现 `_excel_row_number` 行号传递，Service 批量入库的错误提示定位到 Excel 原始行号
15. **列对齐**：导入/导出模板列布局对齐（同列号），值映射双向对称，保证导出文件可直接回导

---

## 七、参考实现

完整对接示例（按需查阅，不必通读）：

- `Modules/FeatureExcelDemo/FeatureExcelTemplates/DemoOrderImportTemplate.php` — 导入模板（含全部钩子 + 行号定位模式）
- `Modules/FeatureExcelDemo/FeatureExcelTemplates/DemoOrderExportTemplate.php` — 导出模板（与导入模板 A–I 列对齐，导出文件可直接回导）
- `Modules/FeatureExcelDemo/Services/DemoOrderService.php` — Service 层集成（batchCreate 批量入库 + 行级错误收集）
- `Modules/FeatureExcelDemo/ApiProto/Handlers/OrderImportHandler.php` — 导入 Handler（错误展平 + 三档响应）
- `Modules/FeatureExcelDemo/ApiProto/Handlers/OrderExportHandler.php` — 导出 Handler（过滤条件 → 查询 → export）
- `Modules/FeatureExcelDemo/ApiProto/Handlers/OrderDownloadTemplateHandler.php` — 模板下载 Handler（读取 `public/excel_templates/` 返回 URL）
- `Modules/FeatureExcelDemo/ApiProto/protos/orderexcel.proto` — Proto 定义（含 download_template 接口）
- `Modules/NtEnergy/Templates/Import/EnergyDataHourlyImportTemplate.php` — 真实业务模板（含 Meter 业务验证）
- `Modules/NtEnergy/Services/EnergyDataService.php` — 真实业务 Service 集成（:298 附近）
