# FeatureExcel 完整使用指南

> FeatureExcelDemo 模块完整演示文档

---

## 一、模块概述

FeatureExcelDemo 是 FeatureExcel 的完整演示模块，展示了：

- ✅ 流式字段映射构建
- ✅ 业务验证钩子（validateRow）
- ✅ 数据转换钩子（transformRow）
- ✅ 数据格式化钩子（formatRow）
- ✅ Proto API 完整集成
- ✅ Service 层业务逻辑
- ✅ 测试数据

---

## 二、目录结构

```
Modules/FeatureExcelDemo/
├── Models/
│   └── DemoOrder.php                # 订单模型
├── Services/
│   └── DemoOrderService.php         # 订单服务（批量创建、列表查询）
├── FeatureExcelTemplates/
│   ├── DemoOrderImportTemplate.php  # 导入模板（8字段A-I列）
│   └── DemoOrderExportTemplate.php  # 导出模板（9字段A-I列）
├── ApiProto/
│   ├── protos/
│   │   └── orderexcel.proto         # Proto定义
│   └── Handlers/
│       ├── OrderImportHandler.php   # 导入API
│       ├── OrderExportHandler.php   # 导出API
│       ├── OrderListHandler.php     # 列表API
│       └── OrderDownloadTemplateHandler.php # 模板下载API
├── Database/
│   ├── Migrations/
│   │   └── ...create_demo_orders_table.php
│   └── Seeders/
│       └── DemoOrderSeeder.php      # 测试数据（5条）
└── docs/
    └── FeatureExcel使用指南.md      # 本文档
```

---

## 三、快速开始

### 3.1 数据库准备

```bash
# 执行迁移
php artisan module:migrate FeatureExcelDemo

# 填充测试数据
php artisan module:seed FeatureExcelDemo --class=DemoOrderSeeder
```

### 3.2 编译 Proto

```bash
# 编译 proto 文件
composer proto
```

---

## 四、API 接口使用

### 4.1 下载导入模板

**接口路径**：`POST /api/proto/feature_excel_demo/order/download_template`

**请求示例**：
```bash
curl -X POST \
  http://localhost/api/proto/feature_excel_demo/order/download_template \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{}'
```

**响应示例**：
```json
{
  "code": 200,
  "message": "模板生成成功",
  "data": {
    "file_url": "http://localhost/storage/excel_templates/订单导入模板_20260814103000.xlsx",
    "file_name": "订单导入模板_20260814103000.xlsx",
    "file_size": 6656,
    "field_count": 8
  }
}
```

**特点**：
- ✅ 自动根据模板定义生成 Excel 文件
- ✅ 包含表头和示例数据
- ✅ 返回真实可下载的文件地址
- ✅ 文件存储在 storage 目录，自动清理

---

### 4.2 订单导入

**接口路径**：`POST /api/proto/feature_excel_demo/order/import`

**请求示例**：
```bash
curl -X POST \
  http://localhost/api/proto/feature_excel_demo/order/import \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "file_path": "storage/uploads/orders.xlsx"
  }'
```

**响应示例**：
```json
{
  "code": 200,
  "message": "导入成功",
  "data": {
    "success_count": 3,
    "failed_count": 2,
    "errors": ["第5行：订单号 OR000001 已存在", "第6行：数量必须大于等于1"]
  }
}
```

**说明**：
- 全部失败返回 errorResponse（code != 200）
- 部分失败/成功返回 successResponse（code = 200），错误在 data.errors 中

**Excel 文件格式**（A-I列，与导出模板对齐）：
| A-订单号 | B-客户姓名 | C-产品名称 | D-数量 | E-单价 | F-总金额 | G-订单日期 | H-状态 | I-备注 |
|--------|-----------|-----------|--------|--------|---------|-----------|-------|-------|
| OR000001 | 张三 | 产品A | 10 | 100.00 | | 2026-08-01 | pending | 测试订单 |

**状态值说明**：
- 支持英文直接输入：`pending`, `confirmed`, `shipped`, `completed`
- 支持中文反向映射：`待处理`→`pending`, `已确认`→`confirmed`, `已发货`→`shipped`, `已完成`→`completed`
- 导出文件的状态列是中文，可直接用于导入

### 4.3 订单导出

**接口路径**：`POST /api/proto/feature_excel_demo/order/export`

**请求示例**：
```bash
curl -X POST \
  http://localhost/api/proto/feature_excel_demo/order/export \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed",
    "date_from": "2026-08-01",
    "date_to": "2026-08-31"
  }'
```

**响应示例**：
```json
{
  "code": 200,
  "message": "导出成功",
  "data": {
    "file_url": "http://localhost/storage/exports/orders_20260814.xlsx",
    "total": 10
  }
}
```

### 4.4 订单列表

**接口路径**：`POST /api/proto/feature_excel_demo/order/list`

**请求示例**：
```bash
curl -X POST \
  http://localhost/api/proto/feature_excel_demo/order/list \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed",
    "page": 1,
    "page_size": 20
  }'
```

---

## 五、代码直接调用

### 5.1 导入订单

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcelDemo\FeatureExcelTemplates\DemoOrderImportTemplate;
use Modules\FeatureExcelDemo\Services\DemoOrderService;

// 1. 实例化模板
$template = new DemoOrderImportTemplate();

// 2. 执行导入（含验证）
$result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

if ($result->isSuccess()) {
    // 3. 批量创建订单（传入商户ID）
    $enterpriseId = 1; // 从Token或上下文获取
    $createResult = DemoOrderService::batchCreate($result->getData(), $enterpriseId);
    
    echo "成功：{$createResult['success']} 条\n";
    echo "失败：{$createResult['failed']} 条\n";
    if (!empty($createResult['errors'])) {
        foreach ($createResult['errors'] as $error) {
            echo "错误：$error\n";
        }
    }
} else {
    // 验证错误处理
    foreach ($result->getErrors() as $error) {
        echo "错误：{$error['message']}\n";
    }
}
```

**注意**：`batchCreate` 使用整批单一事务，行级失败仅记录错误，成功行统一提交。

### 5.2 导出订单

```php
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcelDemo\FeatureExcelTemplates\DemoOrderExportTemplate;
use Modules\FeatureExcelDemo\Services\DemoOrderService;

// 1. 查询数据（传入商户ID）
$enterpriseId = 1; // 从Token或上下文获取
$result = DemoOrderService::list($enterpriseId, ['status' => 'completed'], 1, 1000);
$orders = $result['list'];

// 2. 实例化模板
$template = new DemoOrderExportTemplate();

// 3. 执行导出
$url = ModuleFeatureExcelService::export($orders, $template, [
    'tenant_id' => $enterpriseId,
    'variables' => ['date' => date('Ymd')],
]);

echo "导出成功：{$url}\n";
```

### 5.3 查询订单

```php
use Modules\FeatureExcelDemo\Services\DemoOrderService;

// 查询已完成订单（传入商户ID）
$enterpriseId = 1;
$result = DemoOrderService::list($enterpriseId, [
    'status' => 'completed',
    'date_from' => '2026-08-01',
    'date_to' => '2026-08-31',
], 1, 20);

foreach ($result['list'] as $order) {
    echo "订单号：{$order['order_no']}\n";
    echo "客户：{$order['customer_name']}\n";
    echo "总金额：{$order['total_amount']}\n";
}
```

---

## 六、核心特性详解

### 6.1 流式字段映射

导入导出使用相同的列布局（A-I列对齐）：

```php
// 导入字段定义（A-I列布局）
FieldMapping::make('A', 'string')     // A列 - 订单号
    ->name('订单号')
    ->required();

FieldMapping::make('G', 'date')         // G列 - 订单日期
    ->name('订单日期')
    ->required();

FieldMapping::make('H', 'string')         // H列 - 状态
    ->name('状态')
    ->setDefault('pending');            // 默认值

FieldMapping::make('I', 'string')         // I列 - 备注
    ->name('备注')
    ->nullable();

// 导出字段定义
FieldMapping::make('E', 'float')         // E列 - 单价
    ->name('单价')
    ->format('number:2');                // 格式化：保留2位小数

FieldMapping::make('G', 'date')          // G列 - 订单日期
    ->name('订单日期')
    ->format('date:Y-m-d');             // 日期格式化
```

### 6.2 业务验证钩子

```php
public function validateRow(array $row, int $rowNumber): ?string
{
    // 保存行号供transformRow使用
    $this->currentRowNumber = $rowNumber;
    
    // 订单号格式验证（2位大写字母 + 6位数字，如 OR000001）
    if (isset($row['order_no']) && !preg_match('/^[A-Z]{2}\d{6}$/', (string) $row['order_no'])) {
        return "第{$rowNumber}行：订单号格式错误，应为2位大写字母+6位数字";
    }
    
    // 数量合理性验证（必须大于等于1）
    if (isset($row['quantity']) && (int) $row['quantity'] < 1) {
        return "第{$rowNumber}行：数量必须大于等于1";
    }
    
    // 单价合理性验证（必须大于0）
    if (isset($row['unit_price']) && (float) $row['unit_price'] <= 0) {
        return "第{$rowNumber}行：单价必须大于0";
    }
    
    return null; // 返回 null 表示验证通过
}
```

### 6.3 数据转换钩子

```php
public function transformRow(array $row): array
{
    // 自动计算总金额
    if (isset($row['quantity']) && isset($row['unit_price'])) {
        $row['total_amount'] = $row['quantity'] * $row['unit_price'];
    }
    
    // 状态中文→英文反向映射（与导出formatRow对应，导出文件可直接导入）
    if (isset($row['status']) && is_string($row['status'])) {
        $statusMap = [
            '待处理' => 'pending',
            '已确认' => 'confirmed',
            '已发货' => 'shipped',
            '已完成' => 'completed',
        ];
        // 如果输入是中文，映射为英文；否则保持原值（支持直接输入英文）
        $row['status'] = $statusMap[$row['status']] ?? $row['status'];
    }
    
    // 设置默认值
    if (empty($row['status'])) {
        $row['status'] = 'pending';
    }
    
    // 附加原始Excel行号，用于后续错误提示
    $row['_excel_row_number'] = $this->currentRowNumber;
    
    return $row;
}
```

### 6.4 数据格式化钩子

```php
public function formatRow(array $row): array
{
    // 状态中文化（导出时显示中文，便于阅读）
    $statusMap = [
        'pending' => '待处理',
        'confirmed' => '已确认',
        'shipped' => '已发货',
        'completed' => '已完成',
    ];
    $row['status'] = $statusMap[$row['status']] ?? $row['status'];
    
    return $row;
}
```

---

## 七、Console 命令

```bash
# 验证 Excel 文件是否符合模板
php artisan featureexcel:validate storage/orders.xlsx DemoOrderImportTemplate

# 预览模板定义（不执行验证）
php artisan featureexcel:validate storage/orders.xlsx DemoOrderImportTemplate --dry-run

# 生成模板文件
php artisan featureexcel:generate-template DemoOrderImportTemplate
```

---

## 八、常见问题

### Q1: 钩子方法为什么不执行？

**A**: 检查方法修饰符，必须是 `public`（Logic 层跨类调用需要）

### Q2: 如何调试导入错误？

**A**: 
1. 使用 Console 命令验证文件
2. 检查 ImportResult 的错误信息
3. 查看 Laravel 日志

### Q3: 大数据导入性能如何？

**A**: FeatureExcel 自动分块处理，超过阈值自动启用，无需手动优化

---

## 九、最佳实践

1. **字段定义**：所有必填字段添加 `required()`，导入导出列布局保持一致
2. **业务验证**：在 `validateRow` 中处理引擎无法覆盖的业务规则（如订单号格式）
3. **数据转换**：在 `transformRow` 中处理自动计算、状态值反向映射
4. **整批事务**：Service 层使用单一事务包裹，行级失败仅记录错误
5. **错误处理**：使用 `ImportResult` 统一处理错误，区分验证失败和业务失败
6. **测试数据**：使用 Seeder 创建测试数据（5条），便于功能验证

---

**更新时间**：2026-08-14 11:00
---

## 十、API 接口完整列表

| 接口 | 路径 | 说明 |
|------|------|------|
| 下载模板 | `/api/proto/feature_excel_demo/order/download_template` | 生成并下载 Excel 导入模板 |
| 订单导入 | `/api/proto/feature_excel_demo/order/import` | 导入订单数据 |
| 订单导出 | `/api/proto/feature_excel_demo/order/export` | 导出订单数据 |
| 订单列表 | `/api/proto/feature_excel_demo/order/list` | 查询订单列表 |

---

## 十一、完整使用流程

### 11.1 典型导入流程

```bash
# 1. 获取 Token
TOKEN=$(php artisan enterprise:token)

# 2. 下载模板
curl -X POST \
  http://localhost/api/proto/feature_excel_demo/order/download_template \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}' | jq -r '.data.file_url'

# 3. 填写 Excel 数据

# 4. 上传文件
FILE_PATH=$(curl -X POST \
  http://localhost/api/proto/application/file/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@orders.xlsx" | jq -r '.data.path')

# 5. 执行导入
curl -X POST \
  http://localhost/api/proto/feature_excel_demo/order/import \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"file_path\": \"$FILE_PATH\"}"
```

---

**更新时间**：2026-08-14 11:00
