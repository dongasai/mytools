# FeatureExcel 模块

> Excel/CSV 导入导出引擎模块 - 提供模板类驱动的数据导入导出能力

## 核心特性

- 🎯 **模板类驱动** - 通过继承模板基类定义字段映射，IDE 友好，类型安全
- ⚡ **双引擎支持** - Excel（.xlsx/.xls）和 CSV 格式自动识别
- 🔒 **验证引擎** - 集成 inhere/php-validate，支持行级业务验证钩子
- 📊 **大数据处理** - 超过 5000 行自动分块，内存稳定
- 💾 **导出优化** - 数据指纹缓存，避免重复生成
- 🛡️ **安全机制** - 路径验证、数据验证、AFile 集成

## 快速开始

### 1. 创建导入模板

在业务模块中创建模板类：

```php
// Modules/NtEnergy/FeatureExcelTemplates/EnergyDataImportTemplate.php

use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;
use Modules\FeatureExcel\Engines\Template\FieldMapping;

class EnergyDataImportTemplate extends AbstractImportTemplate
{
    protected string $name = '能源数据导入模板';
    protected int $startRow = 2;

    protected function defineFields(): array
    {
        return [
            'meter_id' => FieldMapping::make('A', 'integer')
                ->name('表计ID')
                ->required()
                ->validation(['min:1']),
            'value' => FieldMapping::make('B', 'float')
                ->name('数值')
                ->required(),
            'data_time' => FieldMapping::make('C', 'datetime')
                ->name('数据时间')
                ->required(),
        ];
    }

    // 可选：业务验证钩子
    public function validateRow(array $row, int $rowNumber): ?string
    {
        if (!Meter::where('id', $row['meter_id'])->exists()) {
            return "表计ID {$row['meter_id']} 不存在";
        }
        return null;
    }
}
```

### 2. 导入数据

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;

$template = new EnergyDataImportTemplate();
$result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

if ($result->isSuccess()) {
    $data = $result->getData();
    // 业务入库
    EnergyDataService::batchCreate($data);
} else {
    $errors = $result->getErrors();  // ['行号' => ['错误信息']]
}
```

### 3. 导出数据

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;

$template = new EnergyDataExportTemplate();
$data = EnergyDataService::getRecords($meterId, $startDate, $endDate);

$url = ModuleFeatureExcelService::export($data, $template, [
    'tenant_id' => $this->token_merchant_id,
    'user_id' => $this->user_id,
]);
```

## Console 命令

### 生成模板文件

```bash
# 根据模板类生成 Excel 文件（含表头和示例数据）
php artisan featureexcel:generate-template EnergyDataImportTemplate

# 指定输出目录和文件名
php artisan featureexcel:generate-template DemoOrderImportTemplate --output=storage/templates --filename=订单导入
```

### 验证导入文件

```bash
# 验证 Excel 文件是否符合模板定义
php artisan featureexcel:validate storage/data.xlsx EnergyDataImportTemplate

# 仅预览模板定义（不验证文件）
php artisan featureexcel:validate storage/data.xlsx EnergyDataImportTemplate --dry-run
```

### 解析 Excel 文件

```bash
# 解析文件并输出对象化数据（调试用）
php artisan feature-excel:parse public/excel_template/Demo.xlsx

# 限制行数和工作表
php artisan feature-excel:parse storage/data.xlsx --rows=50 --sheet=1

# 解析 URL 文件
php artisan feature-excel:parse https://example.com/data.xlsx
```

## 字段映射构建器

使用流式接口定义字段：

```php
FieldMapping::make('A', 'integer')     // 列号 + 数据类型
    ->name('表计ID')                    // 列名（错误提示）
    ->required()                        // 必填
    ->validation(['min:1'])             // 验证规则
    ->transform('int')                  // 数据转换
    ->timezone('Asia/Shanghai', 'UTC')  // 时区转换
    ->nullable()                        // 允许为空
    ->defaultValue(0);                  // 默认值
```

**支持的数据类型**：
- `integer` - 整数
- `float` - 浮点数
- `string` - 字符串
- `date` - 日期
- `datetime` - 日期时间
- `boolean` - 布尔值

## 典型应用

已集成模块：
- **NtEnergy** - 能源数据导入导出
- **FeatureExcelDemo** - 演示订单导入导出

集成步骤：
1. 创建模板类继承 `AbstractImportTemplate` 或 `AbstractExportTemplate`
2. 实现 `defineFields()` 方法定义字段映射
3. 可选重写钩子方法（`validateRow`、`transformRow`、`formatRow`）
4. 调用 `ModuleFeatureExcelService` 进行导入导出

## 配置

配置文件：`Modules/FeatureExcel/config/feature_excel.php`

```php
[
    'max_upload_size' => 10 * 1024 * 1024,  // 最大上传 10MB
    'max_rows' => 100000,                   // 单次导入最大行数
    'chunk_threshold' => 5000,              // 分块处理阈值
    'chunk_size' => 1000,                   // 每批处理行数
]
```

## 开发文档

- **[模块开发指南](CLAUDE.md)** - 详细架构设计、使用方法、开发规范
- **[核心思路](docs/FeatureExcel核心思路.md)** - 设计思路和原理解析
- **[设计方案](docs/FeatureExcel模块设计方案.md)** - 详细设计方案

## 测试

```bash
# 运行模块所有测试
./vendor/bin/phpunit Modules/FeatureExcel/Tests

# 运行单元测试
./vendor/bin/phpunit Modules/FeatureExcel/Tests/Unit

# 运行单个测试
./vendor/bin/phpunit --filter=testDefineFields
```

## 核心依赖

- **phpoffice/phpspreadsheet**: ^2.0（Excel 处理）
- **inhere/php-validate**: 验证库
- **Carbon**: 日期时间处理

---

**模块版本**: 1.0.0
**更新时间**: 2026-08-14
**PHP 版本**: 8.4+
**Laravel 版本**: 12.x