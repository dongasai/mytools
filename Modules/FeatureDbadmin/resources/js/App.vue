<template>
  <div class="dbeaver-layout">
    <!-- 左侧：数据库导航树 -->
    <div class="left-panel" :style="{ width: leftPanelWidth + 'px' }">
      <!-- 连接列表 -->
      <div class="panel-header">
        <span class="panel-title">数据库导航</span>
        <el-button
          type="primary"
          size="small"
          @click="openAddConnectionDialog"
        >
          <el-icon><Plus /></el-icon>
        </el-button>
      </div>

      <div class="tree-container">
        <el-tree
          :props="treeProps"
          node-key="id"
          :expand-on-click-node="false"
          :load="loadNode"
          lazy
          @node-click="handleNodeClick"
          @node-dblclick="handleNodeDblclick"
          class="navigation-tree"
        >
          <template #default="{ node, data }">
            <div class="tree-node">
              <span class="node-icon">
                <!-- 连接节点 -->
                <el-icon v-if="data.type === 'connection'" :class="data.status">
                  <Coin />
                </el-icon>
                <!-- 数据库节点 -->
                <el-icon v-else-if="data.type === 'database'">
                  <Coin />
                </el-icon>
                <!-- 模式节点 -->
                <el-icon v-else-if="data.type === 'schema'">
                  <Folder />
                </el-icon>
                <!-- SQL 执行节点 -->
                <el-icon v-else-if="data.type === 'sql'">
                  <Search />
                </el-icon>
                <!-- 表文件夹节点 -->
                <el-icon v-else-if="data.type === 'table_folder'">
                  <Folder />
                </el-icon>
                <!-- 表节点 -->
                <el-icon v-else-if="data.type === 'table'">
                  <Grid />
                </el-icon>
                <!-- 默认 -->
                <el-icon v-else>
                  <Folder />
                </el-icon>
              </span>
              <span class="node-label">{{ node.label }}</span>
              <span class="node-actions">
                <!-- 连接节点操作 -->
                <template v-if="data.type === 'connection'">
                  <el-button
                    type="text"
                    size="small"
                    @click.stop="refreshConnection(data, node)"
                    title="刷新"
                  >
                    <el-icon><Refresh /></el-icon>
                  </el-button>
                </template>
                <!-- 表节点操作 -->
                <template v-if="data.type === 'table'">
                  <el-button
                    type="text"
                    size="small"
                    @click.stop="openTableData(data)"
                    title="查看数据"
                  >
                    <el-icon><DataLine /></el-icon>
                  </el-button>
                </template>
              </span>
            </div>
          </template>
        </el-tree>
      </div>

      <!-- 调整大小手柄 -->
      <div
        class="resize-handle"
        @mousedown="startResize"
      />
    </div>

    <!-- 右侧：工作区（Tab 页签 + 路由） -->
    <div class="right-panel">
      <!-- Tab 页签栏 -->
      <div class="tabs-header">
        <el-tabs
          v-model="activeTab"
          type="card"
          closable
          @tab-remove="closeTab"
          @tab-click="handleTabClick"
        >
          <el-tab-pane
            v-for="tab in openTabs"
            :key="tab.path"
            :label="tab.title"
            :name="tab.path"
          >
            <template #label>
              <span class="tab-label">
                <el-icon class="tab-icon">
                  <component :is="tab.icon" />
                </el-icon>
                {{ tab.title }}
              </span>
            </template>
          </el-tab-pane>
        </el-tabs>
      </div>

      <!-- Tab 内容区（路由视图） -->
      <div class="tabs-content">
        <router-view v-slot="{ Component, route }">
          <keep-alive>
            <component :is="Component" :key="route.fullPath" />
          </keep-alive>
        </router-view>
      </div>
    </div>

    <!-- 新建连接对话框 -->
    <el-dialog
      v-model="connectionDialog.visible"
      :title="connectionDialog.isEdit ? '编辑连接' : '新建连接'"
      width="600px"
    >
      <ConnectionForm
        :connection="connectionDialog.data"
        :is-edit="connectionDialog.isEdit"
        @save="handleSaveConnection"
        @cancel="connectionDialog.visible = false"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import {
  Plus,
  Coin,
  Grid,
  Folder,
  Refresh,
  DataLine,
  DataAnalysis,
  Search
} from '@element-plus/icons-vue'
import axios from 'axios'
import ConnectionForm from './components/ConnectionForm.vue'

const route = useRoute()
const router = useRouter()

// ==================== 状态定义 ====================

/** 左侧面板宽度 */
const leftPanelWidth = ref(280)

/** 连接名称映射 (connectionId => name) */
const connectionNames = ref({})

/** 连接驱动类型映射 (connectionId => driver) */
const connectionDrivers = ref({})

/** 树配置 */
const treeProps = {
  label: 'label',
  children: 'children',
  isLeaf: (data, node) => {
    // 表节点没有子节点
    // SQL 节点可以有保存的查询子节点
    // 保存的查询节点没有子节点
    return data.type === 'table' || data.type === 'saved_query' || data.type === 'no_saved_queries'
  }
}

/** 打开的 Tab 页签 */
const openTabs = ref([
  { path: '/', title: '欢迎', icon: 'Coin', closable: false }
])

/** 当前激活的 Tab */
const activeTab = ref('/')

/** 连接对话框 */
const connectionDialog = reactive({
  visible: false,
  isEdit: false,
  data: null
})

/** 调整大小状态 */
const resizing = reactive({
  active: false,
  startX: 0,
  startWidth: 0
})

/** 连接数据缓存（用于刷新时重新加载） */
const connectionsCache = ref([])

// ==================== 生命周期 ====================

onMounted(() => {
  // 从 localStorage 加载 Tab
  loadTabsFromStorage()
  // 同步当前路由到 Tab
  syncRouteToTab()
})

// 监听路由变化，同步 Tab
watch(() => route.path, () => {
  syncRouteToTab()
})

// 监听 Tab 变化，保存到 localStorage
watch(openTabs, (newTabs) => {
  saveTabsToStorage(newTabs)
}, { deep: true })

// ==================== Tab 持久化 ====================

/**
 * 保存 Tab 到 localStorage
 */
const saveTabsToStorage = (tabs) => {
  try {
    const data = {
      tabs: tabs,
      activeTab: activeTab.value,
      connectionNames: connectionNames.value
    }
    localStorage.setItem('featuredbadmin-tabs', JSON.stringify(data))
  } catch (error) {
    console.error('保存 Tab 失败:', error)
  }
}

/**
 * 从 localStorage 加载 Tab
 */
const loadTabsFromStorage = () => {
  try {
    const data = localStorage.getItem('featuredbadmin-tabs')
    if (data) {
      const parsed = JSON.parse(data)

      // 恢复连接名称映射
      if (parsed.connectionNames) {
        connectionNames.value = parsed.connectionNames
      }

      // 恢复 Tab 列表
      if (parsed.tabs && Array.isArray(parsed.tabs)) {
        openTabs.value = parsed.tabs
      }

      // 恢复激活的 Tab
      if (parsed.activeTab) {
        activeTab.value = parsed.activeTab
      }
    }
  } catch (error) {
    console.error('加载 Tab 失败:', error)
  }
}

// ==================== 路由与 Tab 同步 ====================

/**
 * 同步路由到 Tab
 */
const syncRouteToTab = () => {
  const currentPath = route.path

  // 检查是否已存在该 Tab
  const existingTab = openTabs.value.find(tab => tab.path === currentPath)

  if (existingTab) {
    activeTab.value = currentPath
  } else {
    // 根据路由路径推断 Tab 标题和图标
    const tabInfo = inferTabFromPath(currentPath, route.params)
    if (tabInfo) {
      openTabs.value.push({
        path: currentPath,
        title: tabInfo.title,
        icon: tabInfo.icon,
        closable: true
      })
      activeTab.value = currentPath
    }
  }
}

/**
 * 根据路径推断 Tab 信息
 */
const inferTabFromPath = (path, params) => {
  // 提取连接ID
  const connectionId = params.connectionId
  const connectionName = connectionId ? (connectionNames.value[connectionId] || `连接${connectionId}`) : ''

  if (path.startsWith('/data/') && path.includes('/edit/')) {
    return {
      title: `${connectionName} - ${params.tableName}编辑`,
      icon: 'DataLine'
    }
  }
  if (path.startsWith('/tables/')) {
    return {
      title: `${connectionName} - 表列表`,
      icon: 'Grid'
    }
  }
  if (path.startsWith('/data/')) {
    return {
      title: `${connectionName} - ${params.tableName}`,
      icon: 'DataLine'
    }
  }
  if (path.startsWith('/query/')) {
    return {
      title: `${connectionName} - SQL编辑器`,
      icon: 'Search'
    }
  }
  if (path.startsWith('/structure/')) {
    return {
      title: `${connectionName} - ${params.tableName}结构`,
      icon: 'Grid'
    }
  }
  if (path === '/connections') {
    return {
      title: '连接管理',
      icon: 'Coin'
    }
  }
  return null
}

// ==================== 懒加载树结构 ====================

/**
 * 懒加载节点数据
 */
const loadNode = async (node, resolve) => {
  try {
    // Level 0: 加载连接列表
    if (node.level === 0) {
      const connections = await loadConnections()
      resolve(connections)
    }
    // 连接节点：根据驱动类型加载子节点
    else if (node.data.type === 'connection') {
      const children = await loadChildrenForConnection(node.data)
      resolve(children)
    }
    // 数据库节点：加载模式或表
    else if (node.data.type === 'database') {
      const children = await loadChildrenForDatabase(node.data)
      resolve(children)
    }
    // 模式节点：加载 SQL + 表
    else if (node.data.type === 'schema') {
      const children = await loadChildrenForSchema(node.data)
      resolve(children)
    }
    // 表文件夹节点：加载表列表
    else if (node.data.type === 'table_folder') {
      const tables = await loadTables(node.data)
      resolve(tables)
    }
    // SQL 执行节点：加载保存的 SQL 查询
    else if (node.data.type === 'sql') {
      const savedQueries = await loadSavedQueries(node.data)
      resolve(savedQueries)
    }
    // 其他节点：无子节点
    else {
      resolve([])
    }
  } catch (error) {
    console.error('加载节点失败:', error)
    ElMessage.error('加载失败: ' + error.message)
    resolve([])
  }
}

/**
 * 加载连接列表
 */
const loadConnections = async () => {
  try {
    const response = await axios.get('/admin/featuredbadmin/connections')
    if (response.data.success && response.data.data) {
      const connections = response.data.data.data

      // 缓存连接数据
      connectionsCache.value = connections

      // 填充映射
      connections.forEach(conn => {
        connectionNames.value[conn.id] = conn.name
        connectionDrivers.value[conn.id] = conn.driver
      })

      return connections.map(conn => ({
        id: `conn-${conn.id}`,
        label: conn.name,
        type: 'connection',
        connectionId: conn.id,
        driver: conn.driver,
        status: conn.is_active ? 'active' : 'inactive'
      }))
    }
    return []
  } catch (error) {
    console.error('加载连接失败:', error)
    return []
  }
}

/**
 * 为连接节点加载子节点（智能适配）
 */
const loadChildrenForConnection = async (connectionNode) => {
  const driver = connectionNode.driver
  const connectionId = connectionNode.connectionId

  // SQLite: 直接显示 SQL + 表
  if (driver === 'sqlite') {
    return [
      {
        id: `conn-${connectionId}-sql`,
        label: 'SQL 执行',
        type: 'sql',
        connectionId: connectionId
      },
      {
        id: `conn-${connectionId}-tables`,
        label: '表',
        type: 'table_folder',
        connectionId: connectionId
      }
    ]
  }

  // MySQL/PostgreSQL: 显示数据库列表
  const databases = await loadDatabases(connectionId)
  return databases
}

/**
 * 为数据库节点加载子节点（智能适配）
 */
const loadChildrenForDatabase = async (databaseNode) => {
  const driver = connectionDrivers.value[databaseNode.connectionId]

  // PostgreSQL: 显示模式列表
  if (driver === 'pgsql') {
    const schemas = await loadSchemas(databaseNode.connectionId, databaseNode.databaseName)
    return schemas
  }

  // MySQL: 显示 SQL + 表
  return [
    {
      id: `conn-${databaseNode.connectionId}-db-${databaseNode.databaseName}-sql`,
      label: 'SQL 执行',
      type: 'sql',
      connectionId: databaseNode.connectionId,
      database: databaseNode.databaseName
    },
    {
      id: `conn-${databaseNode.connectionId}-db-${databaseNode.databaseName}-tables`,
      label: '表',
      type: 'table_folder',
      connectionId: databaseNode.connectionId,
      database: databaseNode.databaseName
    }
  ]
}

/**
 * 为模式节点加载子节点
 */
const loadChildrenForSchema = async (schemaNode) => {
  return [
    {
      id: `conn-${schemaNode.connectionId}-db-${schemaNode.database}-schema-${schemaNode.schemaName}-sql`,
      label: 'SQL 执行',
      type: 'sql',
      connectionId: schemaNode.connectionId,
      database: schemaNode.database,
      schema: schemaNode.schemaName
    },
    {
      id: `conn-${schemaNode.connectionId}-db-${schemaNode.database}-schema-${schemaNode.schemaName}-tables`,
      label: '表',
      type: 'table_folder',
      connectionId: schemaNode.connectionId,
      database: schemaNode.database,
      schema: schemaNode.schemaName
    }
  ]
}

/**
 * 加载数据库列表
 */
const loadDatabases = async (connectionId) => {
  try {
    const response = await axios.get('/admin/featuredbadmin/databases', {
      params: { connection_id: connectionId }
    })

    if (response.data.success && response.data.data) {
      return response.data.data.map(db => ({
        id: `conn-${connectionId}-db-${db}`,
        label: db,
        type: 'database',
        connectionId: connectionId,
        databaseName: db
      }))
    }
    return []
  } catch (error) {
    console.error('加载数据库失败:', error)
    return []
  }
}

/**
 * 加载模式列表
 */
const loadSchemas = async (connectionId, database) => {
  try {
    const response = await axios.get('/admin/featuredbadmin/schemas', {
      params: {
        connection_id: connectionId,
        database: database
      }
    })

    if (response.data.success && response.data.data) {
      return response.data.data.map(schema => ({
        id: `conn-${connectionId}-db-${database}-schema-${schema}`,
        label: schema,
        type: 'schema',
        connectionId: connectionId,
        database: database,
        schemaName: schema
      }))
    }
    return []
  } catch (error) {
    console.error('加载模式失败:', error)
    return []
  }
}

/**
 * 加载表列表
 */
const loadTables = async (parentNode) => {
  try {
    const params = {
      connection_id: parentNode.connectionId
    }

    // 根据节点类型添加参数
    if (parentNode.database) {
      params.database = parentNode.database
    }
    if (parentNode.schema) {
      params.schema = parentNode.schema
    }

    const response = await axios.get('/admin/featuredbadmin/tables', {
      params: params
    })

    if (response.data.success && response.data.data) {
      return response.data.data.map(table => ({
        id: `table-${parentNode.connectionId}-${table}`,
        label: table,
        type: 'table',
        connectionId: parentNode.connectionId,
        tableName: table,
        database: parentNode.database || null,
        schema: parentNode.schema || null
      }))
    }
    return []
  } catch (error) {
    console.error('加载表失败:', error)
    return []
  }
}

/**
 * 加载保存的 SQL 查询
 */
const loadSavedQueries = async (parentNode) => {
  try {
    const params = {
      connection_id: parentNode.connectionId
    }

    // 添加数据库和模式参数
    if (parentNode.database) {
      params.database = parentNode.database
    }
    if (parentNode.schema) {
      params.schema = parentNode.schema
    }

    const response = await axios.get('/admin/featuredbadmin/query/saved', {
      params: params
    })

    if (response.data.success && response.data.data) {
      const queries = response.data.data

      // 如果没有保存的查询，返回提示节点
      if (queries.length === 0) {
        return [{
          id: `no-saved-queries-${parentNode.connectionId}`,
          label: '（暂无保存的查询）',
          type: 'no_saved_queries',
          isLeaf: true
        }]
      }

      // 返回保存的查询节点
      return queries.map(query => ({
        id: `saved-query-${query.id}`,
        label: query.name,
        type: 'saved_query',
        connectionId: parentNode.connectionId,
        database: query.database || parentNode.database || null,
        schema: query.schema || parentNode.schema || null,
        sql: query.sql,
        queryId: query.id,
        isLeaf: true
      }))
    }
    return []
  } catch (error) {
    console.error('加载保存的查询失败:', error)
    return []
  }
}

/**
 * 刷新连接（重新加载子节点）
 */
const refreshConnection = async (data, node) => {
  // 重新加载子节点
  node.loaded = false
  node.loading = false
  node.childNodes = []
  node.expand()
  ElMessage.success('刷新成功')
}

// ==================== 节点点击处理 ====================

/**
 * 处理节点单击
 */
const handleNodeClick = (data, node) => {
  // SQL 执行节点：单击不做任何操作（让节点展开）
  // 双击才打开新编辑器

  // 保存的查询节点：打开 SQL 编辑器并加载查询
  if (data.type === 'saved_query') {
    openSavedQuery(data)
  }
  // 表节点：打开数据浏览
  else if (data.type === 'table') {
    openTableData(data)
  }
  // 表文件夹节点：打开表列表
  else if (data.type === 'table_folder') {
    openTableList(data)
  }
}

/**
 * 处理节点双击
 */
const handleNodeDblclick = (data, node) => {
  // SQL 执行节点：双击打开新 SQL 编辑器
  if (data.type === 'sql') {
    openQueryTool(data)
  }
}

// ==================== Tab 页签管理 ====================

/**
 * 打开 SQL 编辑器 Tab（通过路由跳转）
 */
const openQueryTool = (data) => {
  const connectionName = connectionNames.value[data.connectionId] || `连接${data.connectionId}`
  let path = `/query/${data.connectionId}`

  // 构建查询参数
  const query = {}
  if (data.database) query.database = data.database
  if (data.schema) query.schema = data.schema

  const existingTab = openTabs.value.find(tab => tab.path === path)
  if (!existingTab) {
    openTabs.value.push({
      path: path,
      title: `${connectionName} - SQL编辑器`,
      icon: 'Search',
      closable: true
    })
  }

  router.push({ path, query })
}

/**
 * 打开保存的查询 Tab
 */
const openSavedQuery = (data) => {
  const connectionName = connectionNames.value[data.connectionId] || `连接${data.connectionId}`
  const path = `/query/${data.connectionId}`

  // 构建查询参数
  const query = {}
  if (data.database) query.database = data.database
  if (data.schema) query.schema = data.schema
  if (data.queryId) query.queryId = data.queryId

  const existingTab = openTabs.value.find(tab => tab.path === path)
  if (!existingTab) {
    openTabs.value.push({
      path: path,
      title: `${connectionName} - ${data.label}`,
      icon: 'Search',
      closable: true
    })
  }

  router.push({ path, query })
}

/**
 * 打开表列表 Tab（通过路由跳转）
 */
const openTableList = (data) => {
  const connectionName = connectionNames.value[data.connectionId] || `连接${data.connectionId}`
  let path = `/tables/${data.connectionId}`

  // 构建查询参数
  const query = {}
  if (data.database) query.database = data.database
  if (data.schema) query.schema = data.schema

  const existingTab = openTabs.value.find(tab => tab.path === path)
  if (!existingTab) {
    openTabs.value.push({
      path: path,
      title: `${connectionName} - 表列表`,
      icon: 'Grid',
      closable: true
    })
  }

  router.push({ path, query })
}

/**
 * 打开数据浏览 Tab（通过路由跳转）
 */
const openTableData = (data) => {
  const connectionName = connectionNames.value[data.connectionId] || `连接${data.connectionId}`
  let path = `/data/${data.connectionId}/${data.tableName}`

  // 构建查询参数
  const query = {}
  if (data.database) query.database = data.database
  if (data.schema) query.schema = data.schema

  const existingTab = openTabs.value.find(tab => tab.path === path)
  if (!existingTab) {
    openTabs.value.push({
      path: path,
      title: `${connectionName} - ${data.tableName}`,
      icon: 'DataLine',
      closable: true
    })
  }

  router.push({ path, query })
}

/**
 * Tab 点击切换
 */
const handleTabClick = (tab) => {
  const path = tab.paneName
  if (path && route.path !== path) {
    router.push(path)
  }
}

/**
 * 关闭 Tab
 */
const closeTab = (path) => {
  const index = openTabs.value.findIndex(tab => tab.path === path)
  if (index > -1 && openTabs.value[index].closable) {
    openTabs.value.splice(index, 1)

    // 如果关闭的是当前 Tab，切换到前一个
    if (activeTab.value === path && openTabs.value.length > 0) {
      const newIndex = Math.min(index, openTabs.value.length - 1)
      const newPath = openTabs.value[newIndex].path
      router.push(newPath)
    }
  }
}

// ==================== 连接管理 ====================

/**
 * 打开新建连接对话框
 */
const openAddConnectionDialog = () => {
  connectionDialog.visible = true
  connectionDialog.isEdit = false
  connectionDialog.data = null
}

/**
 * 保存连接
 */
const handleSaveConnection = () => {
  connectionDialog.visible = false
  // 触发树的根节点重新加载
  location.reload()
}

// ==================== 调整面板大小 ====================

/**
 * 开始调整大小
 */
const startResize = (e) => {
  resizing.active = true
  resizing.startX = e.clientX
  resizing.startWidth = leftPanelWidth.value

  document.addEventListener('mousemove', handleResize)
  document.addEventListener('mouseup', stopResize)
}

/**
 * 调整大小中
 */
const handleResize = (e) => {
  if (!resizing.active) return

  const diff = e.clientX - resizing.startX
  const newWidth = resizing.startWidth + diff

  leftPanelWidth.value = Math.min(Math.max(newWidth, 200), 500)
}

/**
 * 停止调整大小
 */
const stopResize = () => {
  resizing.active = false
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
}
</script>

<style scoped>
.dbeaver-layout {
  height: 100vh;
  display: flex;
  background: #f5f5f5;
}

/* ==================== 左侧面板 ==================== */
.left-panel {
  background: #fff;
  border-right: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
  position: relative;
  flex-shrink: 0;
}

.panel-header {
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafafa;
}

.panel-title {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.tree-container {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
}

.navigation-tree {
  padding: 8px;
}

.tree-node {
  flex: 1;
  display: flex;
  align-items: center;
  padding-right: 8px;
}

.node-icon {
  margin-right: 6px;
  font-size: 16px;
}

.node-icon.active {
  color: #67c23a;
}

.node-icon.inactive {
  color: #c0c4cc;
}

.node-label {
  flex: 1;
  font-size: 13px;
}

.node-actions {
  display: none;
  gap: 4px;
}

.tree-node:hover .node-actions {
  display: flex;
}

.resize-handle {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  cursor: col-resize;
  background: transparent;
  transition: background 0.3s;
}

.resize-handle:hover {
  background: #409eff;
}

/* ==================== 右侧面板 ==================== */
.right-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.tabs-header {
  background: #fff;
  border-bottom: 1px solid #e0e0e0;
  min-height: 48px;
  padding: 8px 12px;
  flex-shrink: 0;
}

.tab-label {
  display: flex;
  align-items: center;
  gap: 4px;
}

.tab-icon {
  font-size: 14px;
}

.tabs-content {
  flex: 1;
  overflow: hidden;
  background: #fff;
}

/* ==================== Element Plus 样式覆盖 ==================== */
:deep(.el-tree-node__content) {
  height: 32px;
  border-radius: 4px;
}

:deep(.el-tree-node__content:hover) {
  background: #f5f7fa;
}

:deep(.el-tree-node.is-current > .el-tree-node__content) {
  background: #e6f7ff;
}

/* ==================== Tab 多行布局 ==================== */
:deep(.el-tabs) {
  display: flex;
  flex-direction: column;
  height: auto !important;
}

:deep(.el-tabs__header) {
  margin: 0;
  padding: 0;
  border: none;
  height: auto !important;
}

:deep(.el-tabs__nav-wrap) {
  overflow: visible !important;
  height: auto !important;
}

:deep(.el-tabs__nav-scroll) {
  overflow: visible !important;
  height: auto !important;
}

:deep(.el-tabs__nav) {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 4px 8px;
  border: none !important;
  height: auto !important;
}

:deep(.el-tabs__active-bar) {
  display: none !important;
}

:deep(.el-tabs__item) {
  border: 1px solid #d9d9d9;
  height: 32px;
  line-height: 30px;
  padding: 0 12px;
  border-radius: 3px;
  margin: 0;
  transition: all 0.2s;
  box-sizing: border-box;
  flex-shrink: 0;
}

:deep(.el-tabs__item:hover) {
  background: #f5f5f5;
  border-color: #409eff;
  color: #409eff;
}

:deep(.el-tabs__item.is-active) {
  background: #e6f7ff;
  border-color: #409eff;
  color: #409eff;
  font-weight: 500;
}

:deep(.el-tabs__item .el-icon-close) {
  margin-left: 6px;
  font-size: 12px;
}

:deep(.el-tabs__item .el-icon-close:hover) {
  color: #f56c6c;
}
</style>