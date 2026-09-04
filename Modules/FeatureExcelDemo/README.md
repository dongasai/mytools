# FeatureExcelDemo 模块

> FeatureExcel 完整集成演示 - 最佳实践参考模板

## 模块定位

演示模块，完整展示 FeatureExcel 模块的集成方式，作为其他业务模块集成的标准示例。

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
composer proto
```

### 3. 生成模板文件

```bash
php artisan featureexcel:generate-template DemoOrderImportTemplate
```

### 4. 测试 API

```bash
# 获取 Token
TOKEN=$(php artisan enterprise:token)

# 下载导入模板
curl -X POST http://localhost/api/proto/featureexceldemo/order/download-template \
  -H "Authorization: Bearer $TOKEN"

# 导入订单
curl -X POST http://localhost/api/proto/featureexceldemo/order/import \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"file_path": "storage/uploads/orders.xlsx"}'

# 导出订单
curl -X POST http://localhost/api/proto/featureexceldemo/order/export \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

## 核心示例

### 导入模板定义

```php
class DemoOrderImportTemplate extends AbstractImportTemplate
{
    protected string $name = '演示订单导入模板';
    protected int $startRow = 2;

    protected function defineFields(): array
    {
        return [
            'order_no' => FieldMapping::make('A', 'string')->name('订单号')->required(),
            'quantity' => FieldMapping::make('D', 'integer')->name('数量')->required(),
            'unit_price' => FieldMapping::make('E', 'float')->name('单价')->required(),
            // ... 共 8 个字段
        ];
    }

    // 业务验证钩子
    public function validateRow(array $row, int $rowNumber): ?string
    {
        if (!preg_match('/^ORD\d{10}$/', $row['order_no'])) {
            return '订单号格式错误';
        }
        return null;
    }

    // 数据转换钩子
    public function transformRow(array $row): array
    {
        $row['total_amount'] = $row['quantity'] * $row['unit_price'];
        return $row;
    }
}
```

### 导出模板定义

```php
class DemoOrderExportTemplate extends AbstractExportTemplate
{
    protected string $name = '演示订单导出模板';
    protected array $headers = ['订单号', '客户姓名', ...];
    protected string $fileNamePattern = 'orders_{date}';

    protected function defineFields(): array
    {
        return [
            'unit_price' => FieldMapping::make('E', 'float')->name('单价')->format('number:2'),
            'order_date' => FieldMapping::make('G', 'date')->name('订单日期')->format('date:Y-m-d'),
            // ...
        ];
    }

    // 格式化钩子
    public function formatRow(array $row): array
    {
        $map = ['pending' => '待处理', 'confirmed' => '已确认'];
        $row['status'] = $map[$row['status']] ?? $row['status'];
        return $row;
    }
}
```

## API 接口

| 接口 | 路径 | 说明 |
|------|------|------|
| 下载模板 | `/api/proto/featureexceldemo/order/download-template` | 生成并下载 Excel 导入模板 |
| 订单导入 | `/api/proto/featureexceldemo/order/import` | 导入订单数据 |
| 订单导出 | `/api/proto/featureexceldemo/order/export` | 导出订单数据 |
| 订单列表 | `/api/proto/featureexceldemo/order/list` | 查询订单列表 |

## 演示特性

### ✅ 模板类定义

- 流式字段映射构建（FieldMapping）
- 业务验证钩子（validateRow）
- 数据转换钩子（transformRow）
- 数据格式化钩子（formatRow）

### ✅ Proto API 集成

- 导入 Handler（DemoOrderImportHandler）
- 导出 Handler（DemoOrderExportHandler）
- 模板下载 Handler（DemoOrderDownloadTemplateHandler）
- 列表查询 Handler（DemoOrderListHandler）

### ✅ Service 层实现

- 批量创建（DemoOrderService::batchCreate）
- 列表查询（DemoOrderService::list）
- 统一错误处理

## 其他模块集成参考

参考 FeatureExcelDemo 模块结构，其他业务模块按以下步骤集成：

1. 创建模板类继承 `AbstractImportTemplate` 或 `AbstractExportTemplate`
2. 实现 `defineFields()` 方法定义字段映射
3. 可选重写钩子方法（validateRow/transformRow/formatRow）
4. Service 层提供批量创建和列表查询方法
5. Handler 协调模板和服务，调用 `ModuleFeatureExcelService`

## 详细文档

- **[模块开发指南](CLAUDE.md)** - 完整架构和开发规范
- **[FeatureExcel 使用指南](docs/FeatureExcel使用指南.md)** - API 详细使用文档
- **[FeatureExcel 模块](../FeatureExcel/)** - FeatureExcel 核心模块

---

**模块版本**: 1.0.0
**更新时间**: 2026-08-14
