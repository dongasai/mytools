# SQL 执行错误处理改进

**时间**: 2026-09-09 15:35

---

## 问题描述

用户执行错误 SQL 时，后端抛出致命错误（QueryException），导致：
- 返回 500 错误状态
- 错误信息包含完整的技术堆栈
- 用户体验差

示例错误：
```
"SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax..."
```

---

## 解决方案

### 1. QueryService 层捕获异常

**修改文件**: `Modules/FeatureDbadmin/Services/QueryService.php`

**修改内容**: 在 `executeQuery()` 方法中添加 try-catch

```php
try {
    // 根据查询类型执行
    if ($queryType === QueryType::SELECT) {
        $data = DB::connection($connectionName)->select($sql);
        $rowCount = count($data);
        if (!empty($data)) {
            $columns = array_keys((array) $data[0]);
        }
    } else {
        $affected = DB::connection($connectionName)->statement($sql);
        $rowCount = $affected ? 1 : 0;
    }
} catch (\Exception $e) {
    // 捕获 SQL 执行错误
    $status = QueryStatus::FAILED;
    $errorMessage = $e->getMessage();
    $data = [];
    $rowCount = 0;
    $columns = [];
}
```

**效果**:
- 捕获所有 SQL 执行异常
- 记录错误消息
- 保存失败历史记录
- 返回正常的 QueryResultDto

---

### 2. Controller 层捕获异常

**修改文件**: `Modules/FeatureDbadmin/DcatAdmin/Controllers/QueryToolController.php`

**修改内容**: 在 `execute()` 方法中添加 try-catch

```php
public function execute(Request $request)
{
    try {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'sql' => 'required|string|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $sql = $validated['sql'];

        // 执行查询（QueryService 内部会验证 SQL 安全性并捕获异常）
        $result = QueryService::executeQuery($connectionId, $sql);

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'sql' => $sql,
            'data' => $result->data,
            'row_count' => $result->rowCount,
            'execution_time' => $result->executionTime,
            'columns' => $result->columns,
        ]);
    } catch (\Exception $e) {
        // 捕获未预期的异常
        return response()->json([
            'success' => false,
            'message' => '服务器错误: ' . $e->getMessage(),
            'sql' => $request->input('sql', ''),
            'data' => [],
            'row_count' => 0,
            'execution_time' => 0,
            'columns' => [],
        ], 500);
    }
}
```

**效果**:
- 双重保护：Service 层捕获 SQL 错误，Controller 层捕获其他错误
- 所有错误都返回正常的 JSON 响应
- 保持一致的响应格式

---

## 错误返回格式

### SQL 语法错误
```json
{
  "success": false,
  "message": "SQL 执行失败: SQLSTATE[42000]: Syntax error...",
  "sql": "FROM",
  "data": [],
  "row_count": 0,
  "execution_time": 0,
  "columns": []
}
```

### SQL 安全验证失败
```json
{
  "success": false,
  "message": "SQL 包含危险关键字，执行被拒绝",
  "sql": "DROP TABLE users;",
  "data": [],
  "row_count": 0,
  "execution_time": 0,
  "columns": []
}
```

### 连接不存在
```json
{
  "success": false,
  "message": "连接不存在",
  "sql": "SELECT * FROM users",
  "data": [],
  "row_count": 0,
  "execution_time": 0,
  "columns": []
}
```

---

## 测试场景

1. **SQL 语法错误**
   - 执行 `FROM` （不完整的 SQL）
   - 预期：返回 success=false，错误消息清晰

2. **SQL 安全错误**
   - 执行 `DROP TABLE users`
   - 预期：返回 success=false，提示危险关键字

3. **正常 SQL**
   - 执行 `SELECT * FROM users LIMIT 10`
   - 预期：返回 success=true，包含数据

---

## 改进效果

### 修改前
- ❌ 返回 HTTP 500 错误
- ❌ 错误信息包含完整堆栈
- ❌ 前端收到异常响应

### 修改后
- ✅ 返回 HTTP 200 正常响应
- ✅ 错误信息清晰友好
- ✅ 前端正常显示错误提示
- ✅ 记录错误历史到数据库
- ✅ 保持响应格式一致性

---

**状态**: ✅ 已完成修改，无需构建前端