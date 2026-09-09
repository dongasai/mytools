# 数据操作错误拦截优化

## 日期
2026-09-09

## 问题背景

用户在手动插入或编辑数据时，如果输入了错误格式的数据（如日期格式错误），会收到数据库原始错误信息，不够友好且难以理解。

**错误示例**
```
SQLSTATE[22007]: Invalid datetime format: 7 ERROR: invalid input syntax for type date: "2026-05"
```

## 解决方案

在 `DataBrowserController` 的 `insert` 和 `modify` 方法中添加异常捕获，解析数据库错误并返回友好的提示。

### 实现内容

**1. 异常捕获**
```php
try {
    $result = DataBrowserService::insertRow($connectionId, $tableName, $data);
    // ...
} catch (\Illuminate\Database\QueryException $e) {
    $errorMessage = $this->parseDatabaseError($e);
    return response()->json([
        'success' => false,
        'message' => $errorMessage,
    ], 422);
}
```

**2. 错误解析方法 `parseDatabaseError()`**

支持的错误类型：

#### PostgreSQL 错误
- **日期格式错误**
  - 错误代码：`22007`
  - 提示：`日期格式错误：'2026-05' 不是有效的日期格式。请使用 YYYY-MM-DD 格式（如：2026-05-15）`

- **时间戳格式错误**
  - 提示：`时间戳格式错误：请使用 YYYY-MM-DD HH:MM:SS 格式（如：2026-05-15 10:30:00）`

- **整数格式错误**
  - 提示：`整数格式错误：请输入有效的数字`

- **数字格式错误**
  - 提示：`数字格式错误：请输入有效的数字`

#### MySQL 错误
- **日期格式错误**
  - 错误：`Incorrect date value`
  - 提示：`日期格式错误：请使用 YYYY-MM-DD 格式`

- **日期时间格式错误**
  - 错误：`Incorrect datetime value`
  - 提示：`日期时间格式错误：请使用 YYYY-MM-DD HH:MM:SS 格式`

- **整数格式错误**
  - 错误：`Incorrect integer value`
  - 提示：`整数格式错误：请输入有效的整数`

- **数字格式错误**
  - 错误：`Incorrect decimal value`
  - 提示：`数字格式错误：请输入有效的数字`

#### 通用错误
- **唯一键冲突**
  - 提示：`数据重复：该值已存在，请使用其他值`

- **外键约束错误**
  - 提示：`外键约束错误：关联的数据不存在`

- **字段长度超限**
  - 提示：`数据长度超限：输入的数据超过了字段最大长度限制`

- **空值错误**
  - 提示：`必填字段错误：某些必填字段未填写`

### 代码示例

**错误提取**
```php
// 从错误信息中提取具体的无效值
if (preg_match('/parameter \$\d+ = \'([^\']+)\'/', $message, $matches)) {
    $invalidValue = $matches[1];
    return "日期格式错误：'{$invalidValue}' 不是有效的日期格式。";
}
```

**敏感信息过滤**
```php
// 移除 SQL 语句和连接信息
$cleanMessage = preg_replace('/\s+SQL:\s+\[.*/', '', $message);
$cleanMessage = preg_replace('/\(Connection:.*?\)/', '', $cleanMessage);
```

### 用户体验改进

**改进前**
```
SQLSTATE[22007]: Invalid datetime format: 7 ERROR: invalid input syntax for type date: "2026-05"
CONTEXT: unnamed portal parameter $10 = '...' (Connection: feature_dbadmin_2, Host: 192.168.4.101, Port: 34215, Database: postgres, SQL: insert into "featuredbadmin_test_table"...
```

**改进后**
```
日期格式错误：'2026-05' 不是有效的日期格式。请使用 YYYY-MM-DD 格式（如：2026-05-15）
```

### 文件变更

**修改文件**
- `Modules/FeatureDbadmin/DcatAdmin/Controllers/DataBrowserController.php`
  - `insert()` 方法：添加 try-catch
  - `modify()` 方法：添加 try-catch
  - `parseDatabaseError()` 方法：新增错误解析方法

### 测试建议

**测试用例**

1. **日期格式错误测试**
   - 输入：`2026-05`（不完整）
   - 预期：`日期格式错误：'2026-05' 不是有效的日期格式。请使用 YYYY-MM-DD 格式（如：2026-05-15）`

2. **整数格式错误测试**
   - 输入：`abc`（字符串）
   - 预期：`整数格式错误：请输入有效的数字`

3. **唯一键冲突测试**
   - 输入：已存在的邮箱
   - 预期：`数据重复：该值已存在，请使用其他值`

### 扩展性

该方法支持扩展，可以轻松添加更多错误类型的处理：

```php
// 添加新的错误类型
if (str_contains($message, '新的错误类型')) {
    return '友好的错误提示';
}
```

### 安全性

- ✅ 过滤掉 SQL 语句，避免泄露数据库结构
- ✅ 过滤掉连接信息，避免泄露服务器信息
- ✅ 仅返回用户需要知道的错误信息
- ✅ 使用 422 状态码表示数据验证错误

---

## 总结

通过添加错误拦截和解析，显著提升了用户体验：
- 用户不再看到晦涩的数据库错误
- 提供明确的错误原因和正确的格式示例
- 减少用户尝试次数，提高操作效率
- 保护敏感信息不被泄露