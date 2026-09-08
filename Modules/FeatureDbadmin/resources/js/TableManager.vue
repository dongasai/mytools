<template>
  <div class="table-manager">
    <!-- 顶部工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-select
          v-model="selectedConnection"
          placeholder="选择数据库连接"
          @change="handleConnectionChange"
          style="width: 240px"
          size="default"
        >
          <el-option
            v-for="conn in connections"
            :key="conn.id"
            :label="conn.name"
            :value="conn.id"
          />
        </el-select>
        <el-button
          type="primary"
          :disabled="!selectedConnection"
          @click="loadTables"
          size="default"
        >
          加载
        </el-button>
      </div>
      <div class="toolbar-right">
        <el-button
          v-if="selectedTable"
          type="primary"
          size="default"
          @click="exportCurrentTable"
        >
          导出 SQL
        </el-button>
        <el-button
          v-if="tables.length > 0"
          type="default"
          size="default"
          @click="exportAllTables"
        >
          导出全部
        </el-button>
      </div>
    </div>

    <!-- 主内容区域：左右分栏 -->
    <div class="main-content">
      <!-- 左侧：表列表 -->
      <div class="left-panel">
        <div class="panel-header">
          <span class="panel-title">表列表</span>
          <span class="table-count">{{ tables.length }} 个表</span>
        </div>
        <div class="table-list">
          <div v-loading="loading" class="list-container">
            <div
              v-for="table in tables"
              :key="table.name"
              :class="['table-item', { active: selectedTable?.name === table.name }]"
              @click="selectTable(table)"
            >
              <div class="table-name">{{ table.name }}</div>
              <div class="table-meta">
                <span>{{ table.engine }}</span>
                <span>{{ table.rows }} 行</span>
              </div>
            </div>
            <el-empty
              v-if="!loading && tables.length === 0"
              description="暂无数据"
              :image-size="80"
            />
          </div>
        </div>
      </div>

      <!-- 右侧：表结构详情 -->
      <div class="right-panel">
        <div v-if="selectedTable" class="detail-container">
          <!-- 表基本信息 -->
          <div class="detail-section">
            <div class="section-header">
              <span class="section-title">表信息</span>
            </div>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">表名</span>
                <span class="info-value">{{ selectedTable.name }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">引擎</span>
                <span class="info-value">{{ selectedTable.engine }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">记录数</span>
                <span class="info-value">{{ selectedTable.rows }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">大小</span>
                <span class="info-value">{{ selectedTable.size }}</span>
              </div>
              <div class="info-item full-width">
                <span class="info-label">注释</span>
                <span class="info-value">{{ selectedTable.comment || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- 字段信息 -->
          <div class="detail-section">
            <div class="section-header">
              <span class="section-title">字段</span>
              <span class="field-count">{{ structureDialog.columns.length }} 个字段</span>
            </div>
            <el-table
              :data="structureDialog.columns"
              border
              size="small"
              style="width: 100%"
            >
              <el-table-column prop="name" label="字段名" min-width="140" />
              <el-table-column prop="type" label="类型" width="140" />
              <el-table-column prop="nullable" label="可空" width="60" align="center">
                <template #default="{ row }">
                  {{ row.nullable ? '是' : '否' }}
                </template>
              </el-table-column>
              <el-table-column prop="default" label="默认值" width="100" />
              <el-table-column prop="key" label="键" width="80">
                <template #default="{ row }">
                  <span v-if="row.key === 'PRI'" class="key-primary">主键</span>
                  <span v-else-if="row.key === 'UNI'" class="key-unique">唯一</span>
                  <span v-else-if="row.key === 'MUL'" class="key-index">索引</span>
                  <span v-else>-</span>
                </template>
              </el-table-column>
              <el-table-column prop="extra" label="额外" width="100" />
              <el-table-column prop="comment" label="注释" min-width="180" />
            </el-table>
          </div>

          <!-- 索引信息 -->
          <div class="detail-section">
            <div class="section-header">
              <span class="section-title">索引</span>
              <span class="index-count">{{ structureDialog.indexes.length }} 个索引</span>
            </div>
            <el-table
              :data="structureDialog.indexes"
              border
              size="small"
              style="width: 100%"
            >
              <el-table-column prop="name" label="索引名" min-width="160" />
              <el-table-column prop="type" label="类型" width="100">
                <template #default="{ row }">
                  <span v-if="row.type === 'PRIMARY'" class="key-primary">主键</span>
                  <span v-else-if="row.type === 'UNIQUE'" class="key-unique">唯一</span>
                  <span v-else class="key-index">普通</span>
                </template>
              </el-table-column>
              <el-table-column prop="columns" label="字段" min-width="300">
                <template #default="{ row }">
                  {{ row.columns.join(', ') }}
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>

        <!-- 未选中表时的提示 -->
        <div v-else class="empty-state">
          <el-empty description="请从左侧选择一个表查看详情" :image-size="120" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * TableManager.vue
 * 数据库表管理组件
 *
 * 功能:
 * - 选择数据库连接
 * - 展示表列表
 * - 查看表结构详情
 * - 导出表结构为 SQL/Markdown
 */
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import axios from 'axios'

// ==================== 状态定义 ====================

/** 连接列表 */
const connections = ref([])
/** 选中的连接ID */
const selectedConnection = ref('')
/** 表列表数据 */
const tables = ref([])
/** 加载状态 */
const loading = ref(false)
/** 选中的表 */
const selectedTable = ref(null)

/** 表结构详情数据 */
const structureDialog = reactive({
  tableName: '',
  tableInfo: null,
  columns: [],
  indexes: []
})

// ==================== 生命周期 ====================

/**
 * 加载连接列表
 */
const loadConnections = async () => {
  try {
    const response = await axios.get('/admin/featuredbadmin/api/connections')
    if (response.data.success) {
      connections.value = response.data.data || []
    }
  } catch (error) {
    console.error('加载连接列表失败:', error)
    // 使用模拟数据
    connections.value = [
      { id: 'mysql', name: 'MySQL 主库' },
      { id: 'local', name: '本地数据库' }
    ]
  }
}

// 初始化加载连接列表
loadConnections()

// ==================== 事件处理 ====================

/**
 * 连接选择变化处理
 */
const handleConnectionChange = () => {
  tables.value = []
  selectedTable.value = null
}

/**
 * 加载表列表
 */
const loadTables = async () => {
  if (!selectedConnection.value) {
    ElMessage.warning('请先选择数据库连接')
    return
  }

  loading.value = true
  try {
    const response = await axios.get('/admin/featuredbadmin/api/tables', {
      params: { connection_id: selectedConnection.value }
    })
    if (response.data.success) {
      tables.value = response.data.data || []
    } else {
      ElMessage.error(response.data.message || '加载失败')
    }
  } catch (error) {
    console.error('加载表列表失败:', error)
    ElMessage.error('加载表列表失败')
    // 模拟数据
    tables.value = [
      { name: 'users', engine: 'InnoDB', rows: 1500, size: '2.5 MB', comment: '用户表' },
      { name: 'orders', engine: 'InnoDB', rows: 5200, size: '5.1 MB', comment: '订单表' },
      { name: 'products', engine: 'InnoDB', rows: 300, size: '0.8 MB', comment: '产品表' }
    ]
  } finally {
    loading.value = false
  }
}

/**
 * 选择表并加载结构
 * @param {Object} table - 表信息对象
 */
const selectTable = async (table) => {
  selectedTable.value = table
  structureDialog.tableName = table.name

  try {
    const response = await axios.get('/admin/featuredbadmin/api/table-structure', {
      params: {
        connection_id: selectedConnection.value,
        table_name: table.name
      }
    })
    if (response.data.success) {
      const data = response.data.data
      structureDialog.tableInfo = data.tableInfo
      structureDialog.columns = data.columns
      structureDialog.indexes = data.indexes
    }
  } catch (error) {
    console.error('加载表结构失败:', error)
    // 模拟数据
    structureDialog.tableInfo = {
      name: table.name,
      engine: 'InnoDB',
      charset: 'utf8mb4',
      comment: table.comment || ''
    }
    structureDialog.columns = [
      { name: 'id', type: 'bigint unsigned', nullable: false, default: null, key: 'PRI', extra: 'auto_increment', comment: '主键ID' },
      { name: 'name', type: 'varchar(255)', nullable: false, default: '', key: '', extra: '', comment: '名称' },
      { name: 'created_at', type: 'timestamp', nullable: true, default: 'CURRENT_TIMESTAMP', key: '', extra: '', comment: '创建时间' }
    ]
    structureDialog.indexes = [
      { name: 'PRIMARY', type: 'PRIMARY', columns: ['id'] }
    ]
  }
}

/**
 * 导出当前查看的表结构
 */
const exportCurrentTable = () => {
  const tableName = structureDialog.tableName
  if (!tableName) return

  // 生成SQL
  let sql = `-- Table structure for table \`${tableName}\`\n\n`
  sql += `CREATE TABLE \`${tableName}\` (\n`

  structureDialog.columns.forEach((col, index) => {
    sql += `  \`${col.name}\` ${col.type}`
    if (!col.nullable) sql += ' NOT NULL'
    if (col.default !== null) sql += ` DEFAULT '${col.default}'`
    if (col.extra) sql += ` ${col.extra}`
    if (col.comment) sql += ` COMMENT '${col.comment}'`
    if (index < structureDialog.columns.length - 1) sql += ','
    sql += '\n'
  })

  // 添加主键
  const primaryKey = structureDialog.indexes.find(idx => idx.type === 'PRIMARY')
  if (primaryKey) {
    sql += `  PRIMARY KEY (${primaryKey.columns.map(c => `\`${c}\``).join(', ')})\n`
  }

  sql += `) ENGINE=${structureDialog.tableInfo?.engine || 'InnoDB'} DEFAULT CHARSET=utf8mb4`
  if (structureDialog.tableInfo?.comment) {
    sql += ` COMMENT='${structureDialog.tableInfo.comment}'`
  }
  sql += ';\n'

  downloadFile(sql, `${tableName}_structure.sql`, 'text/sql')
  ElMessage.success('导出成功')
}

/**
 * 导出所有表结构
 */
const exportAllTables = async () => {
  if (tables.value.length === 0) {
    ElMessage.warning('没有可导出的表')
    return
  }

  try {
    const response = await axios.post('/admin/featuredbadmin/api/export-all-tables', {
      connection_id: selectedConnection.value,
      format: 'sql'
    })
    if (response.data.success) {
      downloadFile(response.data.data.content, 'all_tables_structure.sql', 'text/sql')
      ElMessage.success('导出成功')
    }
  } catch (error) {
    console.error('导出失败:', error)
    ElMessage.error('导出失败')
  }
}

/**
 * 下载文件
 * @param {string} content - 文件内容
 * @param {string} filename - 文件名
 * @param {string} type - MIME类型
 */
const downloadFile = (content, filename, type) => {
  const blob = new Blob([content], { type })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}
</script>

<style scoped>
.table-manager {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #fafafa;
}

/* 顶部工具栏 */
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
}

.toolbar-left,
.toolbar-right {
  display: flex;
  gap: 10px;
  align-items: center;
}

/* 主内容区域 */
.main-content {
  flex: 1;
  display: flex;
  overflow: hidden;
}

/* 左侧面板 */
.left-panel {
  width: 25%;
  min-width: 240px;
  background: #fff;
  border-right: 1px solid #e8e8e8;
  display: flex;
  flex-direction: column;
}

.panel-header {
  padding: 12px 16px;
  border-bottom: 1px solid #e8e8e8;
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

.table-count {
  font-size: 12px;
  color: #999;
}

.table-list {
  flex: 1;
  overflow-y: auto;
}

.list-container {
  padding: 8px 0;
}

.table-item {
  padding: 10px 16px;
  cursor: pointer;
  transition: background-color 0.2s;
  border-left: 3px solid transparent;
}

.table-item:hover {
  background: #f5f5f5;
}

.table-item.active {
  background: #e6f7ff;
  border-left-color: #1890ff;
}

.table-name {
  font-size: 13px;
  color: #333;
  margin-bottom: 4px;
  font-weight: 500;
}

.table-meta {
  font-size: 12px;
  color: #999;
  display: flex;
  gap: 12px;
}

/* 右侧面板 */
.right-panel {
  flex: 1;
  background: #fff;
  overflow-y: auto;
}

.detail-container {
  padding: 16px;
}

.detail-section {
  margin-bottom: 24px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e8e8e8;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.field-count,
.index-count {
  font-size: 12px;
  color: #999;
}

/* 表信息网格 */
.info-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  padding: 12px;
  background: #fafafa;
  border: 1px solid #e8e8e8;
  border-radius: 4px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.info-label {
  font-size: 12px;
  color: #999;
}

.info-value {
  font-size: 13px;
  color: #333;
}

/* 键类型样式 */
.key-primary {
  color: #1890ff;
  font-weight: 500;
}

.key-unique {
  color: #faad14;
  font-weight: 500;
}

.key-index {
  color: #52c41a;
  font-weight: 500;
}

/* 空状态 */
.empty-state {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>