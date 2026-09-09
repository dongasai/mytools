<template>
  <div class="data-viewer">
    <!-- 顶部工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-button type="primary" @click="editData">
          <el-icon><Edit /></el-icon>
          编辑
        </el-button>

        <el-button @click="refreshData" :loading="loading">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>

        <el-button @click="goBack">
          <el-icon><Back /></el-icon>
          返回
        </el-button>
      </div>
    </div>

    <!-- 数据详情 -->
    <div class="data-detail" v-loading="loading">
      <el-descriptions :column="1" border>
        <el-descriptions-item
          v-for="col in columns"
          :key="col.name"
          :label="col.name"
          label-class-name="detail-label"
        >
          <template #label>
            <div class="label-content">
              <span class="label-name">{{ col.name }}</span>
              <el-tag v-if="col.key === 'PRI'" type="danger" size="small">主键</el-tag>
              <el-tag v-if="col.extra === 'auto_increment'" type="info" size="small">自增</el-tag>
              <el-tag v-if="col.nullable === 'NO'" type="warning" size="small">必填</el-tag>
            </div>
          </template>

          <div class="value-content">
            <!-- NULL 值 -->
            <el-tag v-if="rowData[col.name] === null" type="info">NULL</el-tag>

            <!-- 布尔值 -->
            <el-tag v-else-if="col.type === 'boolean' || col.type === 'tinyint(1)'" :type="rowData[col.name] ? 'success' : 'danger'">
              {{ rowData[col.name] ? '是' : '否' }}
            </el-tag>

            <!-- JSON 数据 -->
            <pre v-else-if="col.type === 'json' || col.type === 'jsonb'" class="json-content">{{ formatJson(rowData[col.name]) }}</pre>

            <!-- 长文本 -->
            <el-input
              v-else-if="isLongText(col.type, rowData[col.name])"
              :model-value="String(rowData[col.name])"
              type="textarea"
              :rows="5"
              readonly
            />

            <!-- 普通值 -->
            <span v-else class="normal-value">{{ rowData[col.name] }}</span>
          </div>

          <!-- 字段信息提示 -->
          <div class="field-info">
            <span class="field-type">{{ col.type }}</span>
            <span v-if="col.comment" class="field-comment">{{ col.comment }}</span>
          </div>
        </el-descriptions-item>
      </el-descriptions>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Edit, Back, Refresh } from '@element-plus/icons-vue'
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
  },
  rowId: {
    type: [String, Number],
    required: true
  }
})

// ==================== 状态定义 ====================

/** 表结构列信息 */
const columns = ref([])

/** 行数据 */
const rowData = ref({})

/** 加载状态 */
const loading = ref(false)

// ==================== 生命周期 ====================

onMounted(() => {
  loadColumns()
  loadRowData()
})

watch(() => [props.connectionId, props.tableName, props.rowId], () => {
  loadColumns()
  loadRowData()
})

// ==================== 数据加载 ====================

/**
 * 加载表结构列信息
 */
const loadColumns = async () => {
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
 * 加载行数据
 */
const loadRowData = async () => {
  if (!props.rowId) return

  loading.value = true
  try {
    const params = {
      connection_id: props.connectionId
    }

    // 从查询参数中读取 database 和 schema
    if (route.query.database) params.database = route.query.database
    if (route.query.schema) params.schema = route.query.schema

    const response = await axios.get(`/admin/featuredbadmin/data/${props.tableName}/row/${props.rowId}`, {
      params: params
    })

    if (response.data.success && response.data.data) {
      rowData.value = response.data.data.data || {}
    }
  } catch (error) {
    console.error('加载数据失败:', error)
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

// ==================== 操作方法 ====================

/**
 * 编辑数据
 */
const editData = () => {
  const query = {}
  if (route.query.database) query.database = route.query.database
  if (route.query.schema) query.schema = route.query.schema

  router.push({
    path: `/data/${props.connectionId}/${props.tableName}/edit/${props.rowId}`,
    query: query
  })
}

/**
 * 刷新数据
 */
const refreshData = async () => {
  await loadRowData()
  ElMessage.success('数据已刷新')
}

/**
 * 返回
 */
const goBack = () => {
  router.back()
}

// ==================== 辅助方法 ====================

/**
 * 判断是否为长文本
 */
const isLongText = (type, value) => {
  if (!value) return false

  const longTypes = ['text', 'longtext', 'mediumtext', 'blob', 'longblob']
  if (longTypes.includes(type)) return true

  // 如果值超过 100 个字符，也显示为文本域
  if (typeof value === 'string' && value.length > 100) return true

  return false
}

/**
 * 格式化 JSON
 */
const formatJson = (value) => {
  if (!value) return ''

  try {
    // 如果是字符串，尝试解析
    const obj = typeof value === 'string' ? JSON.parse(value) : value
    return JSON.stringify(obj, null, 2)
  } catch (e) {
    return String(value)
  }
}
</script>

<style scoped>
.data-viewer {
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
}

/* ==================== 数据详情 ==================== */
.data-detail {
  flex: 1;
  overflow: auto;
  padding: 20px;
}

/* ==================== 标签内容 ==================== */
.label-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.label-name {
  font-weight: 500;
}

/* ==================== 值内容 ==================== */
.value-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.normal-value {
  word-break: break-all;
}

.json-content {
  background: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
  font-size: 13px;
  line-height: 1.5;
  max-height: 300px;
  overflow: auto;
}

/* ==================== 字段信息 ==================== */
.field-info {
  display: flex;
  gap: 12px;
  margin-top: 4px;
  font-size: 12px;
  color: #909399;
}

.field-type {
  font-family: 'Consolas', 'Monaco', monospace;
}

.field-comment {
  color: #606266;
}

/* ==================== Element Plus 样式覆盖 ==================== */
:deep(.el-descriptions) {
  margin-bottom: 16px;
}

:deep(.el-descriptions__label) {
  width: 200px;
  font-weight: 500;
}

:deep(.el-descriptions__content) {
  word-break: break-all;
}

:deep(.el-tag) {
  margin-right: 4px;
}

:deep(.el-textarea__inner) {
  font-family: 'Consolas', 'Monaco', monospace;
  font-size: 13px;
  line-height: 1.5;
}
</style>