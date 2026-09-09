# SQL 查询保存逻辑改进

**时间**: 2026-09-09 15:40

---

## 问题描述

用户打开已保存的 SQL 查询后，再次点击"保存"按钮时：
- ❌ 会弹出输入框要求输入查询名称
- ❌ 会创建一条新的查询记录，而不是更新原有记录
- ❌ 用户无法区分"更新"和"另存为"操作

---

## 解决方案

### 实现逻辑

1. **加载保存的查询时**
   - 保存查询ID到 `currentQueryId` 状态
   - 加载 SQL 内容到编辑器

2. **保存查询时**
   - 检查 `currentQueryId`
   - 如果有 ID：直接更新，不要求输入名字
   - 如果无 ID：创建新查询，要求输入名字

---

## 代码修改

### 前端修改

**文件**: `Modules/FeatureDbadmin/resources/js/views/QueryTool.vue`

#### 1. 添加状态

```javascript
/** 当前打开的查询ID（用于更新而不是创建新记录） */
const currentQueryId = ref(null)
```

#### 2. 加载查询时保存ID

```javascript
const loadSavedQuery = async (queryId) => {
  try {
    const response = await axios.get(`/admin/featuredbadmin/query/saved/${queryId}`, {
      params: { connection_id: props.connectionId }
    })

    if (response.data.success && response.data.data) {
      const query = response.data.data

      // 保存当前查询ID（用于更新）
      currentQueryId.value = query.id

      // 填充 SQL
      sqlQuery.value = query.sql_query

      // ...
    }
  } catch (error) {
    // ...
  }
}
```

#### 3. 保存时检查ID

```javascript
const saveQuery = async () => {
  // ...验证逻辑

  try {
    // 如果有当前查询ID，直接更新，不要求输入名字
    if (currentQueryId.value) {
      const response = await axios.put(`/admin/featuredbadmin/query/saved/${currentQueryId.value}`, {
        connection_id: props.connectionId,
        sql: sql,
        database: selectedDatabase.value,
        schema: selectedSchema.value || null
      })

      if (response.data.success) {
        ElMessage.success('查询更新成功')
      }
    } else {
      // 没有查询ID，创建新查询，需要输入名字
      const { value: name } = await ElMessageBox.prompt('请输入查询名称', '保存查询', {
        confirmButtonText: '保存',
        cancelButtonText: '取消',
        inputPattern: /\S+/,
        inputErrorMessage: '查询名称不能为空'
      })

      const response = await axios.post('/admin/featuredbadmin/query/save', {
        connection_id: props.connectionId,
        name: name,
        sql: sql,
        database: selectedDatabase.value,
        schema: selectedSchema.value || null
      })

      if (response.data.success) {
        // 保存成功后，保存查询ID
        currentQueryId.value = response.data.data.id
        ElMessage.success('查询保存成功')
      }
    }
  } catch (error) {
    // ...
  }
}
```

### 后端修改

**文件**: `Modules/FeatureDbadmin/DcatAdmin/Controllers/QueryToolController.php`

#### 添加更新方法

```php
/**
 * 更新保存的查询
 *
 * @param int $id 查询ID
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function update(int $id, Request $request)
{
    $savedQuery = SavedQuery::find($id);

    if (!$savedQuery) {
        return response()->json([
            'success' => false,
            'message' => '查询不存在',
        ], 404);
    }

    $validated = $request->validate([
        'connection_id' => 'nullable|integer|min:1',
        'database' => 'nullable|string',
        'schema' => 'nullable|string',
        'sql' => 'nullable|string',
        'description' => 'nullable|string|max:1000',
        'tags' => 'nullable|array',
        'is_public' => 'boolean',
    ]);

    // 更新字段
    if (isset($validated['connection_id'])) {
        $connection = Connection::find($validated['connection_id']);
        $savedQuery->connection_id = $validated['connection_id'];
        $savedQuery->connection_name = $connection ? $connection->name : 'unknown';
    }

    if (isset($validated['database'])) {
        $savedQuery->database = $validated['database'];
    }

    if (isset($validated['schema'])) {
        $savedQuery->schema = $validated['schema'];
    }

    if (isset($validated['sql'])) {
        $savedQuery->sql_query = $validated['sql'];
    }

    // ... 其他字段

    $savedQuery->save();

    return response()->json([
        'success' => true,
        'message' => '查询更新成功',
        'data' => $savedQuery,
    ]);
}
```

### 路由修改

**文件**: `Modules/FeatureDbadmin/routes/admin.php`

```php
// 添加更新路由
Route::put('query/saved/{id}', [Controllers\QueryToolController::class, 'update']);
```

---

## 使用场景

### 场景1：打开已保存的查询并修改

1. 用户在连接树中点击"查询用户列表"
2. SQL 编辑器加载查询内容
3. 用户修改 SQL
4. 点击"保存"按钮
5. ✅ **直接更新成功**，无需输入名称

### 场景2：新建查询

1. 用户在 SQL 编辑器中输入新的 SQL
2. 点击"保存"按钮
3. ✅ **弹出输入框**，要求输入查询名称
4. 输入名称后创建新记录

### 场景3：另存为（未来功能）

如果需要"另存为"功能，可以：
- 添加"另存为"按钮
- 点击时清除 `currentQueryId`
- 然后调用保存逻辑，会要求输入新名称

---

## API 接口

### 更新查询

```
PUT /admin/featuredbadmin/query/saved/{id}
参数：
  - connection_id: int (optional)
  - database: string (optional)
  - schema: string (optional)
  - sql: string (optional)
  - description: string (optional)
  - tags: array (optional)
  - is_public: boolean (optional)

返回：
{
  "success": true,
  "message": "查询更新成功",
  "data": {
    "id": 1,
    "name": "查询用户",
    "sql_query": "SELECT * FROM users LIMIT 20",
    ...
  }
}
```

---

## 改进效果

### 修改前

| 操作 | 行为 |
|------|------|
| 打开已保存查询 | 加载 SQL |
| 点击保存 | ❌ 弹出输入框 |
| 输入名称 | ❌ 创建新记录 |

### 修改后

| 操作 | 行为 |
|------|------|
| 打开已保存查询 | 加载 SQL + 保存查询ID |
| 点击保存 | ✅ 直接更新原有记录 |
| 新建查询保存 | ✅ 弹出输入框，创建新记录 |

---

## 用户体验优化

1. **智能判断**
   - 自动识别是"更新"还是"新建"
   - 无需用户选择操作类型

2. **减少输入**
   - 更新时无需重复输入名称
   - 一键保存

3. **明确反馈**
   - 更新成功：提示"查询更新成功"
   - 保存成功：提示"查询保存成功"

---

**状态**: ✅ 已完成修改并构建