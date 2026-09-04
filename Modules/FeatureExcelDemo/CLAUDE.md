# FeatureExcelDemo 模块开发指南

> FeatureExcel 模块完整集成演示 - 最佳实践参考模板

## 模块定位

**演示模块层**：完整展示 FeatureExcel 模块集成方式的参考模板，作为其他业务模块集成的标准示例。

---

## 核心作用

FeatureExcelDemo 演示了以下关键特性：

### 1. 完整的模板类定义

- ✅ **流式字段映射** - 使用 `FieldMapping::make()` 构建器
- ✅ **业务验证钩子** - `validateRow()` 实现业务级验证
- ✅ **数据转换钩子** - `transformRow()` 实现自动计算
- ✅ **数据格式化钩子** - `formatRow()` 实现输出格式化

### 2. Proto API 完整集成

- ✅ **导入 Handler** - `OrderImportHandler` 完整实现
- ✅ **导出 Handler** - `OrderExportHandler` 完整实现
- ✅ **模板下载 Handler** - `OrderDownloadTemplateHandler` 完整实现
- ✅ **列表查询 Handler** - `OrderListHandler` 完整实现

### 3. Service 层业务逻辑

- ✅ **批量创建** - `DemoOrderService::batchCreate($data, $enterpriseId)`（整批单一事务）
- ✅ **列表查询** - `DemoOrderService::list($enterpriseId, $filters, $page, $pageSize)`
- ✅ **状态更新** - `DemoOrderService::updateStatus()`
- ✅ **错误处理** - 统一的错误返回格式

---

## 目录结构

```
Modules/FeatureExcelDemo/
├── Models/
│   └── DemoOrder.php                    # 订单模型
├── Services/
│   └── DemoOrderService.php             # 订单服务（静态方法）
├── FeatureExcelTemplates/               # ⭐ 模板类目录
│   ├── DemoOrderImportTemplate.php      # 导入模板（8字段 + 验证钩子 + 转换钩子）
│   └── DemoOrderExportTemplate.php      # 导出模板（9字段 + 格式化钩子）
├── ApiProto/
│   ├── protos/
│   │   └── orderexcel.proto             # Proto 定义文件
│   └── Handlers/                        # ⭐ Handler 实现
│       ├── OrderImportHandler.php       # 导入 API
│       ├── OrderExportHandler.php       # 导出 API
│       ├── OrderDownloadTemplateHandler.php # 模板下载 API
│       └── OrderListHandler.php         # 列表查询 API
├── Database/
│   ├── Migrations/
│   │   └── ...create_demo_orders_table.php  # 数据表迁移（含 enterprise_id 字段）
│   └── Seeders/
│       └── DemoOrderSeeder.php          # 测试数据（5条）
└── docs/
    └── FeatureExcel使用指南.md          # 详细使用文档
```

---

## 快速开始

### 1. 数据库准备

```bash
# 执行迁移
php artisan module:migrate FeatureExcelDemo

# 填充测试数据
php artisan module:seed FeatureExcelDemo --class=DemoOrderSeeder
```

### 2. 编译 Proto

```bash
# 编译所有 proto 文件
composer proto
```

### 3. 生成模板文件

```bash
# 生成 Excel 导入模板
php artisan featureexcel:generate-template DemoOrderImportTemplate
```

### 4. 测试 API

```bash
# 获取 Token
php artisan enterprise:token

# 测试下载模板
curl -X POST http://localhost/api/proto/feature_excel_demo/order/download_template \
  -H "Authorization: Bearer {token}"
```

---

## 核心文件解析

### 1. 导入模板（DemoOrderImportTemplate）

**位置**: `FeatureExcelTemplates/DemoOrderImportTemplate.php`

**关键特性**:

```php
class DemoOrderImportTemplate extends AbstractImportTemplate
{
    protected string $name = '演示订单导入模板';
    protected int $startRow = 3;  // 数据从第3行开始（第2行为示例数据，导入时跳过）

    // ✅ 必须实现：定义字段映射（A-I列）
    protected function defineFields(): array
    {
        return [
            'order_no' => FieldMapping::make('A', 'string')
                ->name('订单号')
                ->required(),
            'quantity' => FieldMapping::make('D', 'integer')
                ->name('数量')
                ->required()
                ->validation(['min:1']),
            'order_date' => FieldMapping::make('G', 'date')
                ->name('订单日期')
                ->required(),
            'status' => FieldMapping::make('H', 'string')
                ->name('状态')
                ->setDefault('pending'),
            'remark' => FieldMapping::make('I', 'string')
                ->name('备注')
                ->nullable(),
            // ... 共8个字段
        ];
    }

    // ✅ 可选重写：数据验证钩子（订单号格式、数量、单价验证）
    public function validateRow(array $row, int $rowNumber): ?string
    {
        // 订单号格式验证：2位大写字母 + 6位数字
        if (!preg_match('/^[A-Z]{2}\d{6}$/', $row['order_no'])) {
            return '订单号格式错误，应为2位大写字母+6位数字（如 OR000001）';
        }
        // 数量、单价合理性验证...
        return null;
    }

    // ✅ 可选重写：数据转换钩子（自动计算 + 状态反向映射）
    public function transformRow(array $row): array
    {
        // 自动计算总金额
        $row['total_amount'] = $row['quantity'] * $row['unit_price'];
        
        // 状态中文→英文反向映射（导出文件可直接导入）
        $statusMap = ['待处理' => 'pending', '已确认' => 'confirmed', ...];
        $row['status'] = $statusMap[$row['status']] ?? $row['status'];
        
        // 附加原始Excel行号
        $row['_excel_row_number'] = $this->currentRowNumber;
        
        return $row;
    }
}
```

**学习要点**:
- 流式接口定义字段，IDE 友好
- 验证钩子封装复杂业务规则
- 转换钩子实现自动计算

---

### 2. 导出模板（DemoOrderExportTemplate）

**位置**: `FeatureExcelTemplates/DemoOrderExportTemplate.php`

**关键特性**:

```php
class DemoOrderExportTemplate extends AbstractExportTemplate
{
    protected string $name = '演示订单导出模板';
    protected string $sheetName = '订单数据';
    protected array $headers = ['订单号', '客户姓名', ...];  // 表头
    protected string $fileNamePattern = 'orders_{date}';  // 文件名模式

    protected function defineFields(): array
    {
        return [
            'unit_price' => FieldMapping::make('E', 'float')
                ->name('单价')
                ->format('number:2'),  // 保留2位小数
            'order_date' => FieldMapping::make('G', 'date')
                ->name('订单日期')
                ->format('date:Y-m-d'),  // 日期格式化
            // ...
        ];
    }

    // ✅ 可选重写：格式化钩子
    public function formatRow(array $row): array
    {
        // 状态中文化
        $map = ['pending' => '待处理', 'confirmed' => '已确认'];
        $row['status'] = $map[$row['status']] ?? $row['status'];
        return $row;
    }
}
```

**学习要点**:
- 导出使用 `format()` 而非 `validation()`
- 格式化钩子实现数据输出转换

---

### 3. Service 层（DemoOrderService）

**位置**: `Services/DemoOrderService.php`

**关键方法**:

```php
class DemoOrderService
{
    /**
     * 批量创建订单（导入后业务处理）
     * 整批单一事务包裹，行级失败收集错误但不影响其他行提交
     * 
     * @param array $ordersData 订单数据数组
     * @param int $enterpriseId 企业ID（租户ID）
     */
    public static function batchCreate(array $ordersData, int $enterpriseId): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        foreach ($ordersData as $orderData) {
            $rowNumber = $orderData['_excel_row_number'] ?? '未知';
            
            // 检查订单号唯一性（限制在当前租户内）
            if (DemoOrder::where('enterprise_id', $enterpriseId)
                ->where('order_no', $orderData['order_no'])
                ->exists()) {
                $errors[] = "第{$rowNumber}行：订单号已存在";
                $failed++;
                continue;
            }
            
            // 注入商户ID
            $orderData['enterprise_id'] = $enterpriseId;
            DemoOrder::create($orderData);
            $success++;
        }
        DB::commit();

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * 查询订单列表（导出数据源）
     * 
     * @param int $enterpriseId 企业ID（租户ID）
     * @param array $filters 过滤条件
     */
    public static function list(int $enterpriseId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = DemoOrder::query()->where('enterprise_id', $enterpriseId);

        // 状态过滤
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // ... 其他过滤条件

        return [
            'list' => $query->get()->toArray(),
            'total' => $query->count(),
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }
}
```

**学习要点**:
- Service 层必须为静态方法
- 统一的错误返回格式
- 批量操作逐条处理，收集错误

---

### 4. 导入 Handler（OrderImportHandler）

**位置**: `ApiProto/Handlers/OrderImportHandler.php`

**核心流程**:

```php
class OrderImportHandler extends BaseHandler
{
    protected bool $need_token = true;
    protected bool $need_login = true;

    public function handle(\Google\Protobuf\Internal\Message $request): \Google\Protobuf\Internal\Message
    {
        $filePath = $request->getFilePath();

        // 1. 实例化导入模板
        $template = new DemoOrderImportTemplate();

        // 2. 执行导入（引擎验证 + 模板验证 + 数据转换）
        $result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

        if (!$result->isSuccess()) {
            // 验证失败，返回错误（含明细）
            return $this->errorResponse(...);
        }

        // 3. 业务入库（传入商户ID）
        $createResult = DemoOrderService::batchCreate(
            $result->getData(), 
            $this->token_enterprise_id
        );

        // 4. 构建响应（部分失败也返回 successResponse）
        $response = new OrderImportResponse();
        if ($createResult['failed'] > 0 && $createResult['success'] == 0) {
            // 全部失败返回 errorResponse
            return $this->errorResponse($response, 400, '导入失败', ...);
        }
        
        $response->setCode(200);
        $response->setMessage('导入成功');
        $response->getData()->setSuccessCount($createResult['success']);
        // ...

        return $response;
    }
}
```

**学习要点**:
- Handler 职责：协调模板和服务，不直接操作数据库
- 错误信息从 ImportResult 获取
- 返回详细的导入结果（成功数、失败数、错误列表）

---

### 5. 导出 Handler（OrderExportHandler）

**位置**: `ApiProto/Handlers/OrderExportHandler.php`

**核心流程**:

```php
class OrderExportHandler extends BaseHandler
{
    protected bool $need_token = true;
    protected bool $need_login = true;

    public function handle(\Google\Protobuf\Internal\Message $request): \Google\Protobuf\Internal\Message
    {
        // 1. 构建过滤条件
        $filters = [];
        if ($request->hasStatus()) {
            $filters['status'] = $request->getStatus();
        }

        // 2. 查询数据（传入商户ID）
        $result = DemoOrderService::list($this->token_enterprise_id, $filters, 1, 10000);
        $orders = $result['list'];

        if (empty($orders)) {
            return $this->errorResponse(..., '没有符合条件的数据');
        }

        // 3. 实例化导出模板
        $template = new DemoOrderExportTemplate();

        // 4. 执行导出（自动 AFile 保存）
        $fileUrl = ModuleFeatureExcelService::export($orders, $template, [
            'tenant_id' => $this->token_enterprise_id,
            'user_id' => $this->user_id,
            'variables' => ['date' => date('Ymd')],
        ]);

        // 5. 返回下载地址
        $response = new OrderExportResponse();
        $response->setCode(200);
        $response->getData()->setFileUrl($fileUrl);

        return $response;
    }
}
```

**学习要点**:
- Handler 协调查询、导出、返回
- 导出自动保存到 AFile，返回下载地址
- 支持变量替换（`{date}`）

---

## API 接口列表

| 接口 | 路径 | 说明 | Handler |
|------|------|------|---------|
| 下载模板 | `/api/proto/feature_excel_demo/order/download_template` | 生成并下载 Excel 导入模板 | OrderDownloadTemplateHandler |
| 订单导入 | `/api/proto/feature_excel_demo/order/import` | 导入订单数据 | OrderImportHandler |
| 订单导出 | `/api/proto/feature_excel_demo/order/export` | 导出订单数据 | OrderExportHandler |
| 订单列表 | `/api/proto/feature_excel_demo/order/list` | 查询订单列表 | OrderListHandler |

---

## 开发规范

### 必须遵守

1. **模板类位置**: 必须放在 `FeatureExcelTemplates/` 目录
2. **实现 defineFields()**: 模板类必须实现字段定义方法（导入导出列布局需对齐）
3. **使用 FieldMapping 构建器**: 禁止手动创建字段映射数组
4. **Service 层静态方法**: 所有 Service 方法必须为静态
5. **Handler 职责单一**: 仅协调模板和服务，不直接操作数据库
6. **错误统一格式**: 使用 `ImportResult` 和 `errorResponse()`
7. **租户隔离**: 所有数据操作必须传入 `$enterpriseId` 进行隔离

### 推荐实践

1. **验证钩子**: 业务级验证放在 `validateRow()`（如订单号格式、数量范围）
2. **转换钩子**: 自动计算放在 `transformRow()`（如总金额计算、状态值映射）
3. **格式化钩子**: 输出转换放在 `formatRow()`（如状态中文化）
4. **批量处理**: Service 层整批单一事务，收集行级错误
5. **测试数据**: 提供 Seeder 方便功能验证（5条）
6. **列对齐**: 导入导出模板列布局保持一致（A-I列对应）

---

## 其他业务模块集成步骤

参考 FeatureExcelDemo，其他业务模块按以下步骤集成：

### 步骤 1: 创建模板类

```bash
# 创建导入模板
touch Modules/{模块}/FeatureExcelTemplates/{功能}ImportTemplate.php

# 创建导出模板
touch Modules/{模块}/FeatureExcelTemplates/{功能}ExportTemplate.php
```

### 步骤 2: 定义字段映射

继承 `AbstractImportTemplate` 或 `AbstractExportTemplate`，实现 `defineFields()` 方法。

### 步骤 3: 创建 Service 方法

提供批量创建方法和列表查询方法，供 Handler 调用。

### 步骤 4: 创建 Handler

继承 `BaseHandler`，调用 `ModuleFeatureExcelService` 和业务 Service。

### 步骤 5: 编译 Proto

```bash
composer proto
```

### 步骤 6: 测试验证

```bash
# 验证模板
php artisan featureexcel:validate test.xlsx {模板类名}

# 测试 API
php artisan debug:replay-request {request_id}
```

---

## 详细文档

- **[FeatureExcel 使用指南](docs/FeatureExcel使用指南.md)** - 完整 API 使用文档
- **[FeatureExcel 模块文档](../FeatureExcel/CLAUDE.md)** - FeatureExcel 模块开发指南
- **[FeatureExcel 核心](../FeatureExcel/README.md)** - FeatureExcel 快速入门

---

## 测试

```bash
# 运行模块测试
./vendor/bin/phpunit Modules/FeatureExcelDemo/Tests

# 单元测试
./vendor/bin/phpunit Modules/FeatureExcelDemo/Tests/Unit

# 功能测试
./vendor/bin/phpunit Modules/FeatureExcelDemo/Tests/Feature
```

---

**更新时间**: 2026-08-14
**模块版本**: 1.0.0
**演示内容**: FeatureExcel 完整集成示例