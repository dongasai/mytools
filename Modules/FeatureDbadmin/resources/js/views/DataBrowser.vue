<template>
  <div class="data-browser">
    <!-- 页面标题 -->
    <div class="page-title">数据浏览器</div>

    <!-- 工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-select
          v-model="selectedConnection"
          placeholder="选择数据库连接"
          @change="handleConnectionChange"
          class="toolbar-select"
        >
          <el-option
            v-for="conn in connections"
            :key="conn.id"
            :label="conn.name"
            :value="conn.id"
          />
        </el-select>

        <el-select
          v-model="selectedTable"
          placeholder="选择数据表"
          :disabled="!selectedConnection"
          @change="handleTableChange"
          class="toolbar-select table-select"
        >
          <el-option
            v-for="table in tables"
            :key="table.name"
            :label="table.name"
            :value="table.name"
          >
            <span>{{ table.name }}</span>
            <span class="table-comment">{{ table.comment }}</span>
          </el-option>
        </el-select>

        <el-button
          type="primary"
          :disabled="!selectedTable"
          @click="loadData"
        >
          加载数据
        </el-button>

        <el-button
          type="primary"
          :disabled="!selectedTable"
          @click="openAddDialog"
        >
          新增
        </el-button>

        <el-button
          :disabled="!selectedTable"
          @click="exportData"
        >
          导出
        </el-button>
      </div>

      <!-- 筛选栏 -->
      <div v-if="selectedTable" class="filter-bar">
        <el-input
          v-model="filterText"
          placeholder="输入关键词筛选..."
          clearable
          class="filter-input"
          @keyup.enter="applyFilter"
        />
        <el-button type="primary" @click="applyFilter">筛选</el-button>
        <el-button @click="clearFilter">清除</el-button>
      </div>
    </div>

    <!-- 数据表格 -->
    <div v-if="selectedTable" class="table-container">
      <div class="table-header">
        <span class="table-title">{{ selectedTable }}</span>
        <span class="record-count">共 {{ total }} 条记录</span>
      </div>

      <el-table
        v-loading="loading"
        :data="tableData"
        :cell-style="{ padding: '6px 8px' }"
        :header-cell-style="{ padding: '8px', background: '#fafafa' }"
        border
        size="small"
        style="width: 100%"
        @sort-change="handleSortChange"
        @cell-dblclick="handleCellDblclick"
      >
        <el-table-column type="selection" width="50" />
        <el-table-column
          v-for="col in columns"
          :key="col.name"
          :prop="col.name"
          :label="col.name"
          :sortable="col.sortable ? 'custom' : false"
          min-width="120"
        >
          <template #default="{ row, $index }">
            <div
              v-if="editingCell.row === $index && editingCell.col === col.name"
              class="cell-editor"
            >
              <el-input
                v-model="editingCell.value"
                size="small"
                @blur="saveCellEdit"
                @keyup.enter="saveCellEdit"
              />
            </div>
            <div v-else class="cell-content">
              <template v-if="col.type === 'json'">
                <el-tag size="small" type="info">JSON</el-tag>
              </template>
              <template v-else-if="col.name === 'id' || col.key === 'PRI'">
                <el-tag size="small" type="danger">{{ row[col.name] }}</el-tag>
              </template>
              <template v-else>
                {{ formatCellValue(row[col.name], col.type) }}
              </template>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="120" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" text @click="editRow(row)">编辑</el-button>
            <el-button type="danger" size="small" text @click="deleteRow(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.per_page"
          :page-sizes="[10, 20, 50, 100]"
          :total="total"
          layout="total, sizes, prev, pager, next"
          small
          background
          @size-change="handleSizeChange"
          @current-change="handlePageChange"
        />
      </div>

      <el-empty v-if="!loading && tableData.length === 0" description="暂无数据" />
    </div>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="editDialog.visible"
      :title="editDialog.isEdit ? '编辑数据' : '新增数据'"
      width="60%"
    >
      <el-form :model="editDialog.form" label-width="120px">
        <el-form-item
          v-for="col in editableColumns"
          :key="col.name"
          :label="col.name"
          :prop="col.name"
        >
          <el-input
            v-if="col.type === 'textarea' || col.type === 'text' || col.type === 'longtext'"
            v-model="editDialog.form[col.name]"
            type="textarea"
            :rows="3"
          />
          <el-select
            v-else-if="col.type === 'enum' || col.type === 'set'"
            v-model="editDialog.form[col.name]"
            style="width: 100%"
          >
            <el-option
              v-for="opt in col.options"
              :key="opt"
              :label="opt"
              :value="opt"
            />
          </el-select>
          <el-input v-else v-model="editDialog.form[col.name]" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="editDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveData">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
/**
 * DataBrowser.vue
 * 数据浏览器组件
 *
 * 功能:
 * - 数据表格展示（分页）
 * - 排序和筛选
 * - 行内编辑
 * - 新增/删除数据
 * - 导出数据
 *
 * 关键逻辑:
 * - 分页参数: page, per_page
 * - 排序参数: order_by
 * - 筛选参数: filters
 * - 双击单元格进入编辑模式
 */
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import axios from 'axios'

// ==================== 状态定义 ====================

/** 连接列表 */
const connections = ref([])
/** 表列表 */
const tables = ref([])
/** 选中的连接 */
const selectedConnection = ref('')
/** 选中的表 */
const selectedTable = ref('')
/** 表结构列信息 */
const columns = ref([])
/** 表格数据 */
const tableData = ref([])
/** 总记录数 */
const total = ref(0)
/** 加载状态 */
const loading = ref(false)

/** 筛选文本 */
const filterText = ref('')

/** 分页参数 */
const pagination = reactive({
  page: 1,
  per_page: 20
})

/** 排序参数 */
const sortParams = reactive({
  order_by: '',
  order: ''
})

/** 行内编辑状态 */
const editingCell = reactive({
  row: -1,
  col: '',
  value: ''
})

/** 编辑对话框状态 */
const editDialog = reactive({
  visible: false,
  isEdit: false,
  rowId: null,
  form: {}
})

// ==================== 计算属性 ====================

/** 可编辑的列（排除主键和自动更新字段） */
const editableColumns = computed(() => {
  return columns.value.filter(col => col.name !== 'id' && col.extra !== 'auto_increment')
})

// ==================== 生命周期 ====================

/**
 * 加载连接列表
 */
const loadConnections = async () => {
  try {
    const response = await axios.get('/admin/featuredbadmin/connections')
    if (response.data.success) {
      connections.value = response.data.data || []
    }
  } catch (error) {
    console.error('加载连接列表失败:', error)
    connections.value = [
      { id: 'mysql', name: 'MySQL 主库' },
      { id: 'local', name: '本地数据库' }
    ]
  }
}

loadConnections()

// ==================== 事件处理 ====================

/**
 * 连接选择变化
 */
const handleConnectionChange = async () => {
  selectedTable.value = ''
  tables.value = []
  tableData.value = []

  if (!selectedConnection.value) return

  try {
    const response = await axios.get('/admin/featuredbadmin/tables', {
      params: { connection_id: selectedConnection.value }
    })
    if (response.data.success) {
      tables.value = response.data.data || []
    }
  } catch (error) {
    console.error('加载表列表失败:', error)
    tables.value = [
      { name: 'users', comment: '用户表' },
      { name: 'orders', comment: '订单表' }
    ]
  }
}

/**
 * 表选择变化
 */
const handleTableChange = () => {
  tableData.value = []
  pagination.page = 1
  loadTableColumns()
}

/**
 * 加载表结构列信息
 */
const loadTableColumns = async () => {
  if (!selectedTable.value) return

  try {
    const response = await axios.get('/admin/featuredbadmin/tables', {
      params: {
        connection_id: selectedConnection.value,
        table_name: selectedTable.value
      }
    })
    if (response.data.success) {
      columns.value = response.data.data || []
    }
  } catch (error) {
    console.error('加载表结构失败:', error)
    columns.value = [
      { name: 'id', type: 'bigint', key: 'PRI', sortable: true },
      { name: 'name', type: 'varchar', sortable: true },
      { name: 'email', type: 'varchar', sortable: true },
      { name: 'created_at', type: 'timestamp', sortable: true }
    ]
  }
}

/**
 * 加载数据
 */
const loadData = async () => {
  if (!selectedTable.value) {
    ElMessage.warning('请先选择数据表')
    return
  }

  loading.value = true
  try {
    const params = {
      connection_id: selectedConnection.value,
      table_name: selectedTable.value,
      page: pagination.page,
      per_page: pagination.per_page,
      order_by: sortParams.order_by,
      order: sortParams.order,
      filter: filterText.value
    }

    const response = await axios.get('/admin/featuredbadmin/data', { params })
    if (response.data.success) {
      const data = response.data.data
      tableData.value = data.rows || []
      total.value = data.total || 0
    } else {
      ElMessage.error(response.data.message || '加载失败')
    }
  } catch (error) {
    console.error('加载数据失败:', error)
    ElMessage.error('加载数据失败')
    // 模拟数据
    tableData.value = [
      { id: 1, name: '张三', email: 'zhangsan@example.com', created_at: '2024-01-15 10:30:00' },
      { id: 2, name: '李四', email: 'lisi@example.com', created_at: '2024-01-16 14:20:00' }
    ]
    total.value = 2
  } finally {
    loading.value = false
  }
}

/**
 * 分页大小变化
 */
const handleSizeChange = (size) => {
  pagination.per_page = size
  loadData()
}

/**
 * 页码变化
 */
const handlePageChange = (page) => {
  pagination.page = page
  loadData()
}

/**
 * 排序变化
 */
const handleSortChange = ({ prop, order }) => {
  sortParams.order_by = prop || ''
  sortParams.order = order === 'ascending' ? 'asc' : order === 'descending' ? 'desc' : ''
  loadData()
}

/**
 * 应用筛选
 */
const applyFilter = () => {
  pagination.page = 1
  loadData()
}

/**
 * 清除筛选
 */
const clearFilter = () => {
  filterText.value = ''
  pagination.page = 1
  loadData()
}

/**
 * 双击单元格进入编辑模式
 */
const handleCellDblclick = (row, column, cell, event) => {
  const colName = column.property
  if (!colName) return

  const colInfo = columns.value.find(c => c.name === colName)
  if (colInfo && (colInfo.key === 'PRI' || colInfo.extra === 'auto_increment')) {
    return // 主键和自增字段不允许编辑
  }

  const rowIndex = tableData.value.indexOf(row)
  editingCell.row = rowIndex
  editingCell.col = colName
  editingCell.value = row[colName] || ''
}

/**
 * 保存单元格编辑
 */
const saveCellEdit = async () => {
  if (editingCell.row < 0 || !editingCell.col) return

  const row = tableData.value[editingCell.row]
  const oldValue = row[editingCell.col]

  if (oldValue !== editingCell.value) {
    // 更新数据
    row[editingCell.col] = editingCell.value

    try {
      await axios.post('/admin/featuredbadmin/data', {
        connection_id: selectedConnection.value,
        table_name: selectedTable.value,
        id: row.id,
        data: { [editingCell.col]: editingCell.value }
      })
      ElMessage.success('更新成功')
    } catch (error) {
      console.error('更新失败:', error)
      ElMessage.error('更新失败')
      // 恢复原值
      row[editingCell.col] = oldValue
    }
  }

  editingCell.row = -1
  editingCell.col = ''
  editingCell.value = ''
}

/**
 * 打开新增对话框
 */
const openAddDialog = () => {
  editDialog.isEdit = false
  editDialog.rowId = null
  editDialog.form = {}
  editableColumns.value.forEach(col => {
    editDialog.form[col.name] = col.default || ''
  })
  editDialog.visible = true
}

/**
 * 编辑行
 */
const editRow = (row) => {
  editDialog.isEdit = true
  editDialog.rowId = row.id
  editDialog.form = { ...row }
  editDialog.visible = true
}

/**
 * 保存数据
 */
const saveData = async () => {
  try {
    const url = editDialog.isEdit
      ? '/admin/featuredbadmin/data'
      : '/admin/featuredbadmin/data'

    const params = {
      connection_id: selectedConnection.value,
      table_name: selectedTable.value,
      data: editDialog.form
    }

    if (editDialog.isEdit) {
      params.id = editDialog.rowId
    }

    const response = await axios.post(url, params)
    if (response.data.success) {
      ElMessage.success(editDialog.isEdit ? '更新成功' : '新增成功')
      editDialog.visible = false
      loadData()
    } else {
      ElMessage.error(response.data.message || '操作失败')
    }
  } catch (error) {
    console.error('保存失败:', error)
    ElMessage.error('保存失败')
  }
}

/**
 * 删除行
 */
const deleteRow = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除这条记录吗？', '确认删除', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const response = await axios.post('/admin/featuredbadmin/data', {
      connection_id: selectedConnection.value,
      table_name: selectedTable.value,
      id: row.id
    })

    if (response.data.success) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(response.data.message || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
      ElMessage.error('删除失败')
    }
  }
}

/**
 * 导出数据
 */
const exportData = async () => {
  try {
    const response = await axios.post('/admin/featuredbadmin/data', {
      connection_id: selectedConnection.value,
      table_name: selectedTable.value,
      format: 'csv'
    })

    if (response.data.success) {
      const blob = new Blob([response.data.data.content], { type: 'text/csv' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${selectedTable.value}.csv`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      ElMessage.success('导出成功')
    }
  } catch (error) {
    console.error('导出失败:', error)
    ElMessage.error('导出失败')
  }
}

/**
 * 格式化单元格值
 * @param {any} value - 单元格值
 * @param {string} type - 数据类型
 * @returns {string} 格式化后的值
 */
const formatCellValue = (value, type) => {
  if (value === null || value === undefined) return '-'
  if (type === 'json') return JSON.stringify(value).substring(0, 50)
  if (typeof value === 'string' && value.length > 50) {
    return value.substring(0, 50) + '...'
  }
  return value
}
</script>

<style scoped>
/* ==================== 主容器 ==================== */
.data-browser {
  padding: 16px;
  background: #f5f5f5;
  min-height: 100vh;
}

/* ==================== 页面标题 ==================== */
.page-title {
  font-size: 18px;
  font-weight: 500;
  color: #333;
  margin-bottom: 16px;
  line-height: 32px;
}

/* ==================== 工具栏 ==================== */
.toolbar {
  background: #fff;
  border: 1px solid #e8e8e8;
  padding: 12px 16px;
  margin-bottom: 16px;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toolbar-select {
  width: 180px;
}

.table-select {
  width: 240px;
}

.table-comment {
  float: right;
  color: #999;
  font-size: 12px;
}

/* ==================== 筛选栏 ==================== */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.filter-input {
  width: 280px;
}

/* ==================== 表格容器 ==================== */
.table-container {
  background: #fff;
  border: 1px solid #e8e8e8;
  padding: 0;
}

.table-header {
  padding: 12px 16px;
  border-bottom: 1px solid #e8e8e8;
  background: #fafafa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-title {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.record-count {
  font-size: 13px;
  color: #666;
}

/* ==================== 表格样式 ==================== */
:deep(.el-table) {
  color: #333;
}

:deep(.el-table th) {
  background: #fafafa;
  color: #333;
  font-weight: 500;
  font-size: 13px;
}

:deep(.el-table td) {
  font-size: 13px;
  color: #333;
}

:deep(.el-table--small .el-table__cell) {
  padding: 6px 8px;
}

:deep(.el-table--border) {
  border: 1px solid #e8e8e8;
}

:deep(.el-table--border::after) {
  background-color: #e8e8e8;
}

:deep(.el-table td.el-table__cell) {
  border-bottom: 1px solid #f0f0f0;
}

:deep(.el-table th.el-table__cell) {
  border-bottom: 1px solid #e8e8e8;
}

/* ==================== 单元格编辑 ==================== */
.cell-editor {
  padding: 2px;
}

.cell-content {
  min-height: 22px;
  cursor: pointer;
}

/* ==================== 分页 ==================== */
.pagination-container {
  padding: 12px 16px;
  border-top: 1px solid #e8e8e8;
  display: flex;
  justify-content: flex-end;
  background: #fafafa;
}

:deep(.el-pagination.is-background .el-pager li:not(.is-disabled).is-active) {
  background-color: #1890ff;
}

:deep(.el-pagination.is-background .el-pager li:not(.is-disabled):hover) {
  color: #1890ff;
}

/* ==================== 按钮样式优化 ==================== */
:deep(.el-button--primary) {
  background: #1890ff;
  border-color: #1890ff;
}

:deep(.el-button--primary:hover) {
  background: #40a9ff;
  border-color: #40a9ff;
}

:deep(.el-button--primary:disabled) {
  background: #f5f5f5;
  border-color: #d9d9d9;
  color: #bfbfbf;
}

/* ==================== 选择器样式优化 ==================== */
:deep(.el-select .el-input.is-focus .el-input__wrapper) {
  border-color: #1890ff;
}

:deep(.el-select .el-input__wrapper:hover) {
  border-color: #1890ff;
}

/* ==================== 表格标签样式 ==================== */
:deep(.el-tag) {
  border-radius: 2px;
  font-size: 12px;
  padding: 0 6px;
  height: 20px;
  line-height: 18px;
}

:deep(.el-tag--danger) {
  background: #fff1f0;
  border-color: #ffa39e;
  color: #f5222d;
}

:deep(.el-tag--info) {
  background: #e6f7ff;
  border-color: #91d5ff;
  color: #1890ff;
}

/* ==================== 空状态 ==================== */
:deep(.el-empty) {
  padding: 40px 0;
}
</style>
