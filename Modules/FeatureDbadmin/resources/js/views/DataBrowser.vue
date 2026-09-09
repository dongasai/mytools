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
          <template #default="{ row }">
            <div class="cell-content">
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

        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button type="info" size="small" text @click="viewRow(row)">详情</el-button>
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

    <!-- 单元格编辑弹窗 -->
    <el-dialog
      v-model="cellEditDialog.visible"
      :title="cellEditDialog.fullscreen ? '编辑字段 (最大化)' : '编辑字段'"
      :fullscreen="cellEditDialog.fullscreen"
      :width="cellEditDialog.fullscreen ? '100%' : '700px'"
      @close="closeCellEditDialog"
    >
      <!-- 标题栏自定义按钮 -->
      <template #header="{ close, titleId, titleClass }">
        <div class="dialog-header">
          <span :id="titleId" :class="titleClass">编辑字段</span>
          <el-button
            type="text"
            @click="toggleFullscreen"
            class="fullscreen-btn"
          >
            <el-icon>
              <FullScreen v-if="!cellEditDialog.fullscreen" />
              <Close v-else />
            </el-icon>
            {{ cellEditDialog.fullscreen ? '还原' : '最大化' }}
          </el-button>
        </div>
      </template>

      <el-form label-width="100px">
        <el-form-item label="字段名">
          <el-input :model-value="cellEditDialog.colName" disabled />
        </el-form-item>

        <el-form-item label="字段类型">
          <el-tag size="small">{{ cellEditDialog.colType }}</el-tag>
          <el-tag v-if="cellEditDialog.nullable" size="small" type="warning" style="margin-left: 8px">可空</el-tag>
        </el-form-item>

        <el-form-item label="值">
          <!-- 可空字段：支持设置 NULL -->
          <div class="edit-field-container">
            <!-- 布尔类型 -->
            <template v-if="cellEditDialog.colType === 'boolean'">
              <el-switch
                v-model="cellEditDialog.newValue"
                active-text="是"
                inactive-text="否"
              />
            </template>

            <!-- 文本类型 -->
            <template v-else-if="isTextType(cellEditDialog.colType)">
              <el-input
                v-model="cellEditDialog.newValue"
                type="textarea"
                :rows="cellEditDialog.fullscreen ? 20 : 5"
                placeholder="请输入值"
              />
            </template>

            <!-- 数字类型 -->
            <template v-else-if="isNumberType(cellEditDialog.colType)">
              <el-input-number
                v-model="cellEditDialog.newValue"
                style="width: 100%"
                placeholder="请输入值"
              />
            </template>

            <!-- 默认输入框 -->
            <template v-else>
              <el-input
                v-model="cellEditDialog.newValue"
                :type="cellEditDialog.fullscreen ? 'textarea' : 'text'"
                :rows="cellEditDialog.fullscreen ? 10 : 1"
                placeholder="请输入值"
                clearable
              />
            </template>

            <!-- 操作按钮 -->
            <div class="field-actions">
              <!-- 可空字段的"设为 null"按钮 -->
              <el-button
                v-if="cellEditDialog.nullable"
                size="small"
                :type="cellEditDialog.newValue === null ? 'warning' : 'default'"
                @click="setCellToNull"
              >
                {{ cellEditDialog.newValue === null ? '已设为 null' : '设为 null' }}
              </el-button>

              <!-- 重置按钮 -->
              <el-button
                size="small"
                @click="resetValue"
              >
                重置为原值
              </el-button>
            </div>
          </div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="closeCellEditDialog">取消</el-button>
        <el-button type="primary" @click="saveCellEdit" :loading="cellEditDialog.saving">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Download, Refresh, Search, FullScreen, Close } from '@element-plus/icons-vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const props = defineProps({
  connectionId: {
    type: [String, Number],
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

/** 单元格编辑弹窗 */
const cellEditDialog = reactive({
  visible: false,
  row: null,
  colName: '',
  colType: '',
  nullable: false,
  oldValue: null,
  newValue: null,
  saving: false,
  fullscreen: false
})

// ==================== 生命周期 ====================

onMounted(() => {
  loadTableColumns()
  loadData()
})

// 监听连接ID和表名变化
watch(() => [props.connectionId, props.tableName], () => {
  loadTableColumns()
  loadData()
})

// 监听查询参数变化
watch(() => route.query, () => {
  loadTableColumns()
  loadData()
}, { deep: true })

// ==================== 数据加载 ====================

/**
 * 加载表结构列信息
 */
const loadTableColumns = async () => {
  if (!props.tableName) return

  try {
    const params = {
      connection_id: props.connectionId
    }

    // 从查询参数中读取 database 和 schema
    if (route.query.database) params.database = route.query.database
    if (route.query.schema) params.schema = route.query.schema

    const response = await axios.get(`/admin/featuredbadmin/tables/${props.tableName}/structure`, {
      params: params
    })

    if (response.data.success && response.data.data) {
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

    // 从查询参数中读取 database 和 schema
    if (route.query.database) params.database = route.query.database
    if (route.query.schema) params.schema = route.query.schema

    const response = await axios.get(`/admin/featuredbadmin/data/${props.tableName}`, { params })

    if (response.data.success && response.data.data) {
      tableData.value = response.data.data.data || []
      total.value = response.data.data.total || 0
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
 * 双击单元格打开编辑弹窗
 */
const handleCellDblclick = (row, column, cell, event) => {
  const colName = column.property
  if (!colName) return

  const colInfo = columns.value.find(c => c.name === colName)
  if (!colInfo) return

  // 主键和自增字段不允许编辑
  if (colInfo.key === 'PRI' || colInfo.extra === 'auto_increment') {
    ElMessage.warning('主键和自增字段不允许编辑')
    return
  }

  // 打开编辑弹窗
  cellEditDialog.visible = true
  cellEditDialog.row = row
  cellEditDialog.colName = colName
  cellEditDialog.colType = colInfo.type
  cellEditDialog.nullable = colInfo.nullable === 'YES'
  cellEditDialog.oldValue = row[colName]
  cellEditDialog.newValue = row[colName]
  cellEditDialog.saving = false
}

/**
 * 关闭单元格编辑弹窗
 */
const closeCellEditDialog = () => {
  cellEditDialog.visible = false
  cellEditDialog.row = null
  cellEditDialog.colName = ''
  cellEditDialog.colType = ''
  cellEditDialog.nullable = false
  cellEditDialog.oldValue = null
  cellEditDialog.newValue = null
  cellEditDialog.saving = false
  cellEditDialog.fullscreen = false
}

/**
 * 切换全屏模式
 */
const toggleFullscreen = () => {
  cellEditDialog.fullscreen = !cellEditDialog.fullscreen
}

/**
 * 设置单元格值为 null
 */
const setCellToNull = () => {
  cellEditDialog.newValue = null
}

/**
 * 重置为原始值
 */
const resetValue = () => {
  cellEditDialog.newValue = cellEditDialog.oldValue
}

/**
 * 保存单元格编辑
 */
const saveCellEdit = async () => {
  if (!cellEditDialog.row || !cellEditDialog.colName) return

  // 检查值是否改变
  if (cellEditDialog.oldValue === cellEditDialog.newValue) {
    ElMessage.info('值未改变，无需保存')
    closeCellEditDialog()
    return
  }

  cellEditDialog.saving = true

  try {
    const params = {
      connection_id: props.connectionId,
      database: route.query.database || null,
      schema: route.query.schema || null
    }

    // 使用 _pk 字段（后端返回的主键值）
    const pk = cellEditDialog.row._pk || cellEditDialog.row.id

    // 准备提交数据，处理布尔类型
    const submitValue = cellEditDialog.colType === 'boolean'
      ? (cellEditDialog.newValue === null ? null : (cellEditDialog.newValue ? 1 : 0))
      : cellEditDialog.newValue

    await axios.put(`/admin/featuredbadmin/data/${props.tableName}/row/${pk}`, {
      connection_id: props.connectionId,
      data: { [cellEditDialog.colName]: submitValue },
      database: route.query.database || null,
      schema: route.query.schema || null
    })

    // 更新表格数据
    cellEditDialog.row[cellEditDialog.colName] = cellEditDialog.newValue

    ElMessage.success('更新成功')
    closeCellEditDialog()
  } catch (error) {
    console.error('更新失败:', error)
    ElMessage.error(error.response?.data?.message || '更新失败')
  } finally {
    cellEditDialog.saving = false
  }
}

/**
 * 打开新增 Tab
 */
const openAddDialog = () => {
  const query = {}
  if (route.query.database) query.database = route.query.database
  if (route.query.schema) query.schema = route.query.schema

  router.push({
    path: `/data/${props.connectionId}/${props.tableName}/edit`,
    query: query
  })
}

/**
 * 查看详情 - 打开新 Tab
 */
const viewRow = (row) => {
  const query = {}
  if (route.query.database) query.database = route.query.database
  if (route.query.schema) query.schema = route.query.schema

  // 使用 _pk 字段（后端返回的主键值）
  const pk = row._pk || row.id
  router.push({
    path: `/data/${props.connectionId}/${props.tableName}/view/${pk}`,
    query: query
  })
}

/**
 * 编辑行 - 打开新 Tab
 */
const editRow = (row) => {
  const query = {}
  if (route.query.database) query.database = route.query.database
  if (route.query.schema) query.schema = route.query.schema

  // 使用 _pk 字段（后端返回的主键值）
  const pk = row._pk || row.id
  router.push({
    path: `/data/${props.connectionId}/${props.tableName}/edit/${pk}`,
    query: query
  })
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

    const params = {
      connection_id: props.connectionId
    }

    // 从查询参数中读取 database 和 schema
    if (route.query.database) params.database = route.query.database
    if (route.query.schema) params.schema = route.query.schema

    // 使用 _pk 字段（后端返回的主键值）
    const pk = row._pk || row.id
    const response = await axios.delete(`/admin/featuredbadmin/data/${props.tableName}/row/${pk}`, {
      params: params
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
    const params = {
      connection_id: props.connectionId,
      format: 'csv'
    }

    // 从查询参数中读取 database 和 schema
    if (route.query.database) params.database = route.query.database
    if (route.query.schema) params.schema = route.query.schema

    const response = await axios.get(`/admin/featuredbadmin/data/${props.tableName}/export`, {
      params: params
    })

    if (response.data.success && response.data.data) {
      const blob = new Blob([response.data.data.data], { type: 'text/csv' })
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
 * 判断是否为文本类型
 */
const isTextType = (type) => {
  return ['text', 'longtext', 'mediumtext', 'blob', 'longblob'].includes(type)
}

/**
 * 判断是否为数字类型
 */
const isNumberType = (type) => {
  return ['int', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'].includes(type)
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

/* ==================== 编辑弹窗 ==================== */
.edit-field-container {
  width: 100%;
}

.field-actions {
  margin-top: 8px;
  display: flex;
  gap: 8px;
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.fullscreen-btn {
  font-size: 14px;
  color: #606266;
}

.fullscreen-btn:hover {
  color: #409eff;
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