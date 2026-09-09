# SQL 查询工具改进

**时间**: 2026-09-09 16:00

---

## 改进内容

### 1. 显示查询名称

**问题**: 用户打开保存的 SQL 查询后，无法看到当前正在编辑哪个查询

**解决方案**: 在保存按钮旁边显示查询名称

**实现**:
- 添加 `currentQueryName` 状态
- 加载保存的查询时保存查询名称
- 在工具栏使用 `el-tag` 显示查询名称

**代码修改**:

**文件**: `Modules/FeatureDbadmin/resources/js/views/QueryTool.vue`

```javascript
// 状态定义
const currentQueryName = ref('')

// 加载保存的查询时保存名称
const loadSavedQuery = async (queryId) => {
  const response = await axios.get(`/admin/featuredbadmin/query/saved/${queryId}`)
  const query = response.data.data

  currentQueryId.value = query.id
  currentQueryName.value = query.name  // 保存查询名称
  sqlQuery.value = query.sql_query
  // ...
}

// 模板中显示查询名称
<el-button type="success" @click="saveQuery">
  <el-icon><DocumentChecked /></el-icon>
  保存
</el-button>
<el-tag v-if="currentQueryName" type="success" style="margin-left: 8px">
  {{ currentQueryName }}
</el-tag>
```

**效果**:
- ✅ 打开保存的查询时，保存按钮旁边显示查询名称
- ✅ 用户可以清楚地知道当前正在编辑哪个查询
- ✅ 新建查询时不显示标签

---

### 2. 独立标签页管理

**问题**: 打开多个保存的查询时，它们会在同一个标签页中打开，互相覆盖

**解决方案**: 为每个保存的查询创建独立的标签页

**实现**:
- 引入 `tabId` 作为标签的唯一标识
- 保存的查询：`/query/{connectionId}?queryId={queryId}`
- 新的 SQL 编辑器：`/query/{connectionId}?new={timestamp}`
- 修改标签切换和关闭逻辑

**代码修改**:

**文件**: `Modules/FeatureDbadmin/resources/js/App.vue`

#### 1. 修改模板

```vue
<el-tab-pane
  v-for="tab in openTabs"
  :key="tab.tabId || tab.path"
  :label="tab.title"
  :name="tab.tabId || tab.path"
>
```

#### 2. 修改打开保存的查询

```javascript
const openSavedQuery = (data) => {
  const path = `/query/${data.connectionId}`
  const tabId = `${path}?queryId=${data.queryId}`  // 基于 queryId 的唯一标识

  const existingTab = openTabs.value.find(tab => {
    if (data.queryId) {
      return tab.tabId === tabId  // 匹配 tabId
    }
    return tab.path === path
  })

  if (!existingTab) {
    openTabs.value.push({
      tabId: tabId,  // 唯一标识
      path: path,
      title: `${connectionName} - ${data.label}`,
      queryId: data.queryId  // 保存 queryId
    })
  }

  activeTab.value = tabId  // 激活标签
  router.push({ path, query })
}
```

#### 3. 修改打开新编辑器

```javascript
const openQueryTool = (data) => {
  const path = `/query/${data.connectionId}`
  const tabId = `${path}?new=${Date.now()}`  // 基于时间戳的唯一标识

  openTabs.value.push({
    tabId: tabId,  // 每次都是新的
    path: path,
    title: `${connectionName} - SQL编辑器`
  })

  activeTab.value = tabId
  router.push({ path, query })
}
```

#### 4. 修改标签切换

```javascript
const handleTabClick = (tab) => {
  const tabId = tab.paneName
  const targetTab = openTabs.value.find(t => (t.tabId || t.path) === tabId)

  if (targetTab && route.path !== targetTab.path) {
    // 恢复查询参数
    const query = {}
    if (targetTab.queryId) query.queryId = targetTab.queryId
    if (targetTab.database) query.database = targetTab.database
    if (targetTab.schema) query.schema = targetTab.schema

    router.push({ path: targetTab.path, query })
  }
}
```

#### 5. 修改标签关闭

```javascript
const closeTab = (tabId) => {
  const index = openTabs.value.findIndex(tab => (tab.tabId || tab.path) === tabId)
  if (index > -1 && openTabs.value[index].closable) {
    openTabs.value.splice(index, 1)

    if (activeTab.value === tabId && openTabs.value.length > 0) {
      const newTab = openTabs.value[Math.min(index, openTabs.value.length - 1)]
      activeTab.value = newTab.tabId || newTab.path

      // 恢复查询参数
      const query = {}
      if (newTab.queryId) query.queryId = newTab.queryId
      // ...
      router.push({ path: newTab.path, query })
    }
  }
}
```

---

## 使用场景

### 场景1：打开多个保存的查询

1. 用户在连接树中点击"查询用户列表"
2. 打开一个标签页，显示"连接1 - 查询用户列表"
3. 用户再点击"查询订单列表"
4. 打开另一个标签页，显示"连接1 - 查询订单列表"
5. ✅ 两个查询在独立的标签页中，互不干扰

### 场景2：打开新的 SQL 编辑器

1. 用户双击"SQL执行"节点
2. 打开一个标签页，显示"连接1 - SQL编辑器"
3. 用户再次双击"SQL执行"节点
4. 打开另一个标签页，显示"连接1 - SQL编辑器"
5. ✅ 每次都创建新的编辑器标签页

### 场景3：查看查询名称

1. 用户点击保存的查询"查询用户列表"
2. SQL 编辑器加载查询内容
3. ✅ 保存按钮旁边显示"查询用户列表"标签
4. 用户修改 SQL 并保存
5. ✅ 直接更新，无需重新输入名称

---

## 改进效果

### 修改前

| 功能 | 行为 |
|------|------|
| 显示查询名称 | ❌ 无法看到当前查询名称 |
| 打开多个查询 | ❌ 同一个标签页，互相覆盖 |
| 打开新编辑器 | ❌ 复用同一个标签页 |

### 修改后

| 功能 | 行为 |
|------|------|
| 显示查询名称 | ✅ 保存按钮旁边显示查询名称 |
| 打开多个查询 | ✅ 每个查询独立标签页 |
| 打开新编辑器 | ✅ 每次都创建新标签页 |

---

## 技术要点

### 标签唯一标识

- **保存的查询**: `/query/{connectionId}?queryId={queryId}`
- **新的编辑器**: `/query/{connectionId}?new={timestamp}`
- 其他页面（表列表、数据浏览）: 使用路径作为唯一标识

### 标签数据结构

```javascript
{
  tabId: '/query/1?queryId=123',  // 唯一标识（可选）
  path: '/query/1',               // 路由路径
  title: '连接1 - 查询用户列表',  // 标签标题
  icon: 'Search',                 // 图标
  closable: true,                 // 是否可关闭
  queryId: 123,                   // 查询ID（可选）
  database: 'mydb',               // 数据库（可选）
  schema: 'public'                // 模式（可选）
}
```

---

**状态**: ✅ 已完成修改并构建