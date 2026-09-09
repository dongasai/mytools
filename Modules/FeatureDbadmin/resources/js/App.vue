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
          :data="connectionsTree"
          :props="treeProps"
          node-key="id"
          :expand-on-click-node="false"
          :default-expand-all="false"
          @node-click="handleNodeClick"
          class="navigation-tree"
        >
          <template #default="{ node, data }">
            <div class="tree-node">
              <span class="node-icon">
                <!-- 连接节点 -->
                <el-icon v-if="data.type === 'connection'" :class="data.status">
                  <Database />
                </el-icon>
                <!-- 表节点 -->
                <el-icon v-else-if="data.type === 'table'">
                  <Grid />
                </el-icon>
                <!-- 视图节点 -->
                <el-icon v-else-if="data.type === 'view'">
                  <View />
                </el-icon>
                <!-- 文件夹节点 -->
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
                    @click.stop="refreshConnection(data)"
                    title="刷新"
                  >
                    <el-icon><Refresh /></el-icon>
                  </el-button>
                  <el-button
                    type="text"
                    size="small"
                    @click.stop="editConnection(data)"
                    title="编辑"
                  >
                    <el-icon><Edit /></el-icon>
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

    <!-- 右侧：工作区（Tab 页签） -->
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
            :key="tab.id"
            :label="tab.title"
            :name="tab.id"
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

      <!-- Tab 内容区 -->
      <div class="tabs-content">
        <!-- 欢迎页 -->
        <div v-if="activeTab === 'welcome'" class="welcome-page">
          <div class="welcome-content">
            <el-icon :size="80" color="#409eff"><Database /></el-icon>
            <h2>数据库管理员工具</h2>
            <p>基于 DBeaver 设计理念</p>
            <div class="quick-actions">
              <el-button type="primary" size="large" @click="openAddConnectionDialog">
                <el-icon><Plus /></el-icon>
                新建连接
              </el-button>
            </div>
          </div>
        </div>

        <!-- 数据浏览 Tab -->
        <div v-else-if="activeTab.startsWith('data-')" class="tab-content">
          <DataBrowser
            :connection-id="currentTab.connectionId"
            :table-name="currentTab.tableName"
          />
        </div>

        <!-- SQL 编辑器 Tab -->
        <div v-else-if="activeTab.startsWith('query-')" class="tab-content">
          <QueryTool :connection-id="currentTab.connectionId" />
        </div>

        <!-- 表结构 Tab -->
        <div v-else-if="activeTab.startsWith('structure-')" class="tab-content">
          <TableStructure
            :connection-id="currentTab.connectionId"
            :table-name="currentTab.tableName"
          />
        </div>

        <!-- 连接管理 Tab -->
        <div v-else-if="activeTab === 'connections'" class="tab-content">
          <ConnectionManager @refresh="loadConnections" />
        </div>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import {
  Plus,
  Database,
  Grid,
  View,
  Folder,
  Refresh,
  Edit,
  DataLine,
  Search
} from '@element-plus/icons-vue'
import axios from 'axios'
import DataBrowser from './views/DataBrowser.vue'
import QueryTool from './views/QueryTool.vue'
import TableStructure from './views/TableStructure.vue'
import ConnectionManager from './views/ConnectionManager.vue'
import ConnectionForm from './components/ConnectionForm.vue'

// ==================== 状态定义 ====================

/** 左侧面板宽度 */
const leftPanelWidth = ref(280)

/** 连接树数据 */
const connectionsTree = ref([])

/** 树配置 */
const treeProps = {
  label: 'label',
  children: 'children'
}

/** 打开的 Tab 页签 */
const openTabs = ref([
  { id: 'welcome', title: '欢迎', icon: 'Database', closable: false }
])

/** 当前激活的 Tab */
const activeTab = ref('welcome')

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

// ==================== 计算属性 ====================

/** 当前 Tab 数据 */
const currentTab = computed(() => {
  return openTabs.value.find(tab => tab.id === activeTab.value) || {}
})

// ==================== 生命周期 ====================

onMounted(() => {
  loadConnections()
})

// ==================== 数据加载 ====================

/**
 * 加载连接列表
 */
const loadConnections = async () => {
  try {
    const response = await axios.get('/admin/featuredbadmin/connections')
    if (response.data.data) {
      // 构建树形数据
      connectionsTree.value = response.data.data.map(conn => ({
        id: `conn-${conn.id}`,
        label: conn.name,
        type: 'connection',
        connectionId: conn.id,
        status: conn.is_active ? 'active' : 'inactive',
        children: [
          {
            id: `conn-${conn.id}-tables`,
            label: '表',
            type: 'folder',
            connectionId: conn.id,
            children: [] // 表列表将延迟加载
          },
          {
            id: `conn-${conn.id}-views`,
            label: '视图',
            type: 'folder',
            connectionId: conn.id,
            children: []
          }
        ]
      }))
    }
  } catch (error) {
    console.error('加载连接失败:', error)
    ElMessage.error('加载连接失败')
  }
}

/**
 * 刷新连接（加载表列表）
 */
const refreshConnection = async (node) => {
  try {
    const response = await axios.get('/admin/featuredbadmin/tables', {
      params: { connection_id: node.connectionId }
    })

    if (response.data.data) {
      // 找到表节点
      const tablesNode = connectionsTree.value
        .find(n => n.id === `conn-${node.connectionId}`)
        ?.children.find(n => n.type === 'folder' && n.label === '表')

      if (tablesNode) {
        tablesNode.children = response.data.data.map(table => ({
          id: `table-${node.connectionId}-${table}`,
          label: table,
          type: 'table',
          connectionId: node.connectionId,
          tableName: table
        }))
      }
    }

    ElMessage.success('刷新成功')
  } catch (error) {
    console.error('刷新失败:', error)
    ElMessage.error('刷新失败')
  }
}

// ==================== 节点点击处理 ====================

/**
 * 处理节点点击
 */
const handleNodeClick = (data, node) => {
  // 连接节点：刷新
  if (data.type === 'connection') {
    refreshConnection(data)
  }
  // 表节点：打开数据浏览
  else if (data.type === 'table') {
    openTableData(data)
  }
}

// ==================== Tab 页签管理 ====================

/**
 * 打开数据浏览 Tab
 */
const openTableData = (data) => {
  const tabId = `data-${data.connectionId}-${data.tableName}`
  const tabTitle = `数据: ${data.tableName}`

  // 检查是否已打开
  const existingTab = openTabs.value.find(tab => tab.id === tabId)
  if (existingTab) {
    activeTab.value = tabId
    return
  }

  // 添加新 Tab
  openTabs.value.push({
    id: tabId,
    title: tabTitle,
    icon: 'DataLine',
    connectionId: data.connectionId,
    tableName: data.tableName,
    closable: true
  })

  activeTab.value = tabId
}

/**
 * 关闭 Tab
 */
const closeTab = (tabId) => {
  const index = openTabs.value.findIndex(tab => tab.id === tabId)
  if (index > -1 && openTabs.value[index].closable) {
    openTabs.value.splice(index, 1)

    // 如果关闭的是当前 Tab，切换到前一个
    if (activeTab.value === tabId && openTabs.value.length > 0) {
      const newIndex = Math.min(index, openTabs.value.length - 1)
      activeTab.value = openTabs.value[newIndex].id
    }
  }
}

/**
 * Tab 点击
 */
const handleTabClick = (tab) => {
  activeTab.value = tab.paneName
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
 * 编辑连接
 */
const editConnection = (data) => {
  connectionDialog.visible = true
  connectionDialog.isEdit = true
  connectionDialog.data = data
}

/**
 * 保存连接
 */
const handleSaveConnection = () => {
  connectionDialog.visible = false
  loadConnections()
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

  // 限制最小和最大宽度
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

/* 调整大小手柄 */
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

/* Tab 页签栏 */
.tabs-header {
  background: #fff;
  border-bottom: 1px solid #e0e0e0;
}

.tab-label {
  display: flex;
  align-items: center;
  gap: 4px;
}

.tab-icon {
  font-size: 14px;
}

/* Tab 内容区 */
.tabs-content {
  flex: 1;
  overflow: hidden;
  background: #fff;
}

.tab-content {
  height: 100%;
  overflow: auto;
}

/* 欢迎页 */
.welcome-page {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.welcome-content {
  text-align: center;
  color: #666;
}

.welcome-content h2 {
  margin-top: 20px;
  color: #333;
}

.welcome-content p {
  margin-top: 10px;
  font-size: 14px;
}

.quick-actions {
  margin-top: 30px;
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

:deep(.el-tabs--card > .el-tabs__header) {
  margin: 0;
  border-bottom: 1px solid #e0e0e0;
}

:deep(.el-tabs--card > .el-tabs__header .el-tabs__nav) {
  border: none;
}

:deep(.el-tabs--card > .el-tabs__header .el-tabs__item) {
  border: none;
  border-right: 1px solid #e0e0e0;
  height: 36px;
  line-height: 36px;
}

:deep(.el-tabs--card > .el-tabs__header .el-tabs__item.is-active) {
  background: #f5f5f5;
  border-bottom: 2px solid #409eff;
}

:deep(.el-tabs__content) {
  display: none; /* 隐藏默认内容，使用自定义内容区 */
}
</style>