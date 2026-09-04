# FeatureExcel 模块设计方案

> 基于 `docs/FeatureExcel核心思路.md` 的详细设计

## 一、模块定位

**工具功能模块层**：提供 Excel/CSV 导入导出的通用引擎能力。

业务模块（NtEnergy、NtCarbon等）通过继承模板基类使用引擎。

---

## 二、对外提供能力

根据核心思路，模块对外提供三大能力：

### 1. 约定/基类

**AbstractImportTemplate** - 导入模板基类
**AbstractExportTemplate** - 导出模板基类

业务模块继承这些基类定义具体模板。

### 2. 服务

**ModuleFeatureExcelService** - 对外服务类

提供统一的导入导出 API，业务模块直接调用。

### 3. 工具

**console工具** - Excel表格与模板契合验证

验证 Excel 表格是否符合导入模板定义。

---

## 三、架构设计

### 3.1 职责分离

| 层级 | 职责 | 代码位置 |
|------|------|----------|
| **业务模块** | 继承模板基类<br>定义字段映射<br>实现业务逻辑 | `Modules/NtEnergy/FeatureExcelTemplates/` |
| **FeatureExcel** | 提供模板基类<br>提供对外服务类<br>提供验证工具 | `Templates/Base/` + `Services/` |

### 3.2 引擎分层

```
业务模块层
├── ImportTemplates/EnergyDataImportTemplate
├── ExportTemplates/EnergyDataExportTemplate
└── 继承模板基类，定义字段
      ↓
工具功能模块层（FeatureExcel）
├── Templates/Base/AbstractImportTemplate
├── Templates/Base/AbstractExportTemplate
├── Services/ModuleFeatureExcelService
└── Console/ValidateTemplateCommand
      ↓
核心模块层
└── ABase / AFile（基础设施）
```

---

## 四、模板基类设计

### 4.1 AbstractImportTemplate

**位置**: `Templates/Base/AbstractImportTemplate.php`

**抽象方法**（子类必须实现）:
- `defineFields()` - 定义字段映射

**钩子方法**（子类可选重写）:
- `validateRow()` - 行级业务验证
- `transformRow()` - 行级业务转换

**模板属性**:
- `$name` - 模板名称
- `$format` - 格式（excel/csv）
- `$startRow` - 数据起始行

### 4.2 AbstractExportTemplate

**位置**: `Templates/Base/AbstractExportTemplate.php`

**抽象方法**（子类必须实现）:
- `defineFields()` - 定义字段映射

**钩子方法**（子类可选重写）:
- `formatRow()` - 行级业务格式化

**模板属性**:
- `$name` - 模板名称
- `$headers` - 表头数组
- `$fileNamePattern` - 文件名模式
- `$persist` - 存储策略（临时/持久）

---

## 五、字段映射构建器

### 5.1 FieldMapping 设计

**位置**: `Engines/Template/FieldMapping.php`

**流式接口**:
```php
FieldMapping::make(column, type)
    ->name(string)
    ->required()
    ->validation(array)
    ->transform(string)
    ->timezone(from, to)
```

**构建器方法**:
- `make(column, type)` - 创建字段映射（列号 + 数据类型）
- `name(string)` - 列名（错误提示用）
- `required()` - 必填标记
- `validation(array)` - inhere/php-validate 验证规则
- `transform(string)` - 数据转换（int/float/trim/datetime:format）
- `format(string)` - 输出格式（导出用，number:N / date:format）
- `timezone(from, to)` - 时区转换

---

## 六、对外服务类设计

### 6.1 ModuleFeatureExcelService

**位置**: `Services/ModuleFeatureExcelService.php`

**核心方法**:

**导入**:
```php
ModuleFeatureExcelService::importWithValidation(string $filePath, AbstractImportTemplate $template): ImportResult
```

**导出**:
```php
ModuleFeatureExcelService::export(array $data, AbstractExportTemplate $template, array $options = []): string
```

**辅助方法**:
```php
ModuleFeatureExcelService::generateDataFingerprint(array $parameters, ?array $data = null): string
```

### 6.2 返回对象

**ImportResult**（导入结果）:
- `isSuccess()` - 是否成功
- `getData()` - 获取数据
- `getErrors()` - 获取错误（含 Excel 行号）

---

## 七、Console工具设计

### 7.1 模板验证命令

**位置**: `Console/ValidateTemplateCommand.php`

**命令签名**:
```bash
php artisan featureexcel:validate {file} {template}
```

**功能**:
- 验证 Excel 文件是否符合模板定义
- 输出字段映射差异
- 输出验证错误（行号 + 字段 + 原因）

### 7.2 使用示例

```bash
# 验证能源数据导入文件
php artisan featureexcel:validate storage/energy.xlsx EnergyDataHourlyImportTemplate
```

---

## 八、业务模块使用方式

### 8.1 定义导入模板

**位置**: `Modules/NtEnergy/FeatureExcelTemplates/EnergyDataHourlyImportTemplate.php`

```php
class EnergyDataHourlyImportTemplate extends AbstractImportTemplate
{
    protected string $name = '能源小时数据导入模板';
    protected int $startRow = 2;

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
        // 业务验证逻辑
        return Meter::where('id', $row['meter_id'])->exists();
    }
}
```

### 8.2 定义导出模板

**位置**: `Modules/NtEnergy/FeatureExcelTemplates/EnergyDataDailyExportTemplate.php`

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

### 8.3 使用服务

**导入数据**:
```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\NtEnergy\FeatureExcelTemplates\EnergyDataHourlyImportTemplate;

$template = new EnergyDataHourlyImportTemplate();
$result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

if ($result->isSuccess()) {
    EnergyDataService::batchCreate($result->getData());
}
```

**导出数据**:
```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\NtEnergy\FeatureExcelTemplates\EnergyDataDailyExportTemplate;

$template = new EnergyDataDailyExportTemplate();
$url = ModuleFeatureExcelService::export($data, $template, [
    'tenant_id' => $this->token_merchant_id,
    'user_id' => $this->user_id,
]);
```

---

## 九、核心流程

### 9.1 导入流程

```
文件上传 → 实例化模板
    ↓
ModuleFeatureExcelService::importWithValidation()
    ↓
引擎处理：
    ├─ 路径验证
    ├─ 文件读取（Excel/CSV）
    ├─ 数据验证（ValidationCore）
    ├─ $template->validateRow() 业务验证（钩子）
    ├─ 数据转换（时区/类型）
    └─ $template->transformRow() 业务转换（钩子）
    ↓
返回 ImportResult
    ↓
业务模块处理入库
    ↓
清理临时文件
```

### 9.2 导出流程

```
业务模块查询数据
    ↓
实例化模板
    ↓
ModuleFeatureExcelService::export()
    ↓
引擎处理：
    ├─ 数据转换（时区/格式）
    ├─ $template->formatRow() 业务格式化（钩子）
    ├─ 文件生成（Excel/CSV）
    └─ AFile 保存（临时/持久）
    ↓
返回下载 URL
```

---

## 十、目录结构

### FeatureExcel 模块

```
Modules/FeatureExcel/
├── Templates/
│   └── Base/
│       ├── AbstractImportTemplate.php
│       └── AbstractExportTemplate.php
├── Engines/
│   ├── Template/
│   │   └── FieldMapping.php
│   ├── Import/
│   ├── Export/
│   ├── Validation/
│   └── Transform/
├── Services/
│   └── ModuleFeatureExcelService.php
├── Console/
│   └── ValidateTemplateCommand.php
└── Exceptions/
```

### 业务模块（示例）

```
Modules/NtEnergy/
├── ImportTemplates/
│   └── EnergyDataHourlyImportTemplate.php
├── ExportTemplates/
│   └── EnergyDataDailyExportTemplate.php
└── Services/
    └── EnergyDataService.php
```

---

## 十一、安全与性能

### 11.1 安全机制

- **路径验证**: 导入文件必须位于 storage 目录
- **数据验证**: 使用 ValidationCore 动态验证
- **文件名过滤**: 移除非法字符，防止注入

### 11.2 性能优化

- **大数据分块**: 超过阈值自动分块处理
- **导出缓存**: 数据指纹避免重复生成
- **时区转换**: 模板配置时区参数

---

## 十二、核心优势

| 维度 | 说明 |
|------|------|
| **类型安全** | 强类型模板 + IDE 提示 |
| **业务封装** | 模板类可封装业务逻辑 |
| **易测试** | 模板类易于单元测试 |
| **可扩展** | 通过继承扩展模板 |
| **工具支持** | Console 命令验证模板 |

---

**更新时间**: 2026-08-14
**基于**: `docs/FeatureExcel核心思路.md`