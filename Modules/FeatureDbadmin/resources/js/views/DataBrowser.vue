<template>
  <div class="data-browser">
    <!-- 工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-button type="primary" @click="openAddDialog">
          <el-icon><Plus /></el-icon>
          新增
        </el-button>

        <el-button @click="exportData">
          <el-icon><Download /></el-icon>
          导出
        </el-button>

        <el-button @click="refreshData">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>

      <!-- 筛选栏 -->
      <div class="filter-bar">
        <el-input
          v-model="filterText"
          placeholder="输入关键词筛选..."
          clearable
          class="filter-input"
          @keyup.enter="applyFilter"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
        <el-button type="primary" @click="applyFilter">筛选</el-button>
        <el-button @click="clearFilter">清除</el-button>
      </div>
    </div>

    <!-- 数据表格 -->
    <div class="table-container">
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Download, Refresh, Search } from '@element-plus/icons-vue'
import axios from 'axios'

const props = defineProps({
  connectionId: {
    type: Number,
    required: true
  },
  tableName: {
    type: String,
    required: true
  }
})

// ==================== 状态定义 ====================

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

onMounted(() => {
  loadTableColumns()
  loadData()
})

watch(() => [props.connectionId, props.tableName], () => {
  loadTableColumns()
  loadData()
})

// ==================== 数据加载 ====================

/**
 * 加载表结构列信息
 */
const loadTableColumns = async () => {
  if (!props.tableName) return

  try {
    const response = await axios.get(`/admin/featuredbadmin/tables/${props.tableName}/structure`, {
      params: {
        connection_id: props.connectionId
      }
    })

    if (response.data.data) {
      columns.value = response.data.data.columns || []
    }
  } catch (error) {
    console.error('加载表结构失败:', error)
  }
}

/**
 * 加载数据
 */
const loadData = async () => {
  if (!props.tableName) return

  loading.value = true
  try {
    const params = {
      connection_id: props.connectionId,
      page: pagination.page,
      per_page: pagination.per_page,
      order_by: sortParams.order_by,
      order: sortParams.order,
      filter: filterText.value
    }

    const response = await axios.get(`/admin/featuredbadmin/data/${props.tableName}`, { params })
    if (response.data) {
      tableData.value = response.data.data || []
      total.value = response.data.total || 0
    }
  } catch (error) {
    console.error('加载数据失败:', error)
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

/**
 * 刷新数据
 */
const refreshData = () => {
  loadData()
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
      await axios.put(`/admin/featuredbadmin/data/${props.tableName}/row/${row.id}`, {
        connection_id: props.connectionId,
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
      ? `/admin/featuredbadmin/data/${props.tableName}/row/${editDialog.rowId}`
      : `/admin/featuredbadmin/data/${props.tableName}/row`

    const params = {
      connection_id: props.connectionId,
      data: editDialog.form
    }

    const response = editDialog.isEdit
      ? await axios.put(url, params)
      : await axios.post(url, params)

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

    const response = await axios.delete(`/admin/featuredbadmin/data/${props.tableName}/row/${row.id}`, {
      params: {
        connection_id: props.connectionId
      }
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
    const response = await axios.get(`/admin/featuredbadmin/data/${props.tableName}/export`, {
      params: {
        connection_id: props.connectionId,
        format: 'csv'
      }
    })

    if (response.data.data) {
      const blob = new Blob([response.data.data], { type: 'text/csv' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${props.tableName}.csv`
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
.data-browser {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #fff;
}

/* ==================== 工具栏 ==================== */
.toolbar {
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
  background: #fafafa;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

/* ==================== 筛选栏 ==================== */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-input {
  width: 280px;
}

/* ==================== 表格容器 ==================== */
.table-container {
  flex: 1;
  overflow: auto;
  padding: 16px;
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
  padding: 12px 0;
  display: flex;
  justify-content: flex-end;
}

/* ==================== Element Plus 样式优化 ==================== */
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
  border: 1px solid #e0e0e0;
}

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
</style>