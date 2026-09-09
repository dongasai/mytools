<template>
  <div class="query-tool">
    <!-- 顶部工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <!-- 数据库和模式选择器 -->
        <div class="selector-group">
          <el-select
            v-model="selectedDatabase"
            placeholder="选择数据库"
            size="default"
            @change="handleDatabaseChange"
            style="width: 180px"
          >
            <el-option
              v-for="db in databases"
              :key="db.name"
              :label="db.name"
              :value="db.name"
            />
          </el-select>

          <el-select
            v-model="selectedSchema"
            placeholder="选择模式"
            size="default"
            @change="handleSchemaChange"
            style="width: 180px"
            :disabled="!selectedDatabase"
          >
            <el-option
              v-for="schema in schemas"
              :key="schema.name"
              :label="schema.name"
              :value="schema.name"
            />
          </el-select>
        </div>

        <el-divider direction="vertical" />

        <el-button type="primary" @click="executeQuery" :loading="executing">
          <el-icon><VideoPlay /></el-icon>
          {{ hasSelection ? '执行选中 (Ctrl+Enter)' : '执行 (Ctrl+Enter)' }}
        </el-button>
        <el-button @click="clearEditor">
          <el-icon><Delete /></el-icon>
          清空
        </el-button>
        <el-button @click="formatSql">
          <el-icon><Document /></el-icon>
          格式化
        </el-button>
        <el-button type="success" @click="saveQuery">
          <el-icon><DocumentChecked /></el-icon>
          保存
        </el-button>
        <el-tag v-if="currentQueryName" type="success" size="default" style="margin-left: 8px">
          {{ currentQueryName }}
        </el-tag>
      </div>

      <div class="toolbar-right">
        <el-switch
          v-model="darkTheme"
          @change="toggleTheme"
          active-text="深色"
          inactive-text="浅色"
        />
      </div>
    </div>

    <!-- 主内容区 -->
    <div class="main-content">
      <!-- SQL编辑器区域 -->
      <div class="editor-section">
        <!-- Monaco Editor -->
        <MonacoEditor
          ref="monacoEditor"
          v-model="sqlQuery"
          language="sql"
          :theme="darkTheme ? 'vs-dark' : 'vs'"
          :minimap="false"
          @save="executeQuery"
          @selectionChange="handleSelectionChange"
        />

        <!-- 快捷语句栏 -->
        <div class="quick-actions">
          <span class="quick-label">快捷语句:</span>
          <el-button text size="small" @click="insertSql('SELECT * FROM table_name LIMIT 10;')">
            查询前10条
          </el-button>
          <el-button text size="small" @click="insertSql('SHOW TABLES;')">
            显示所有表
          </el-button>
          <el-button text size="small" @click="insertSql('DESCRIBE table_name;')">
            查看表结构
          </el-button>
          <el-button text size="small" @click="insertSql('SELECT COUNT(*) FROM table_name;')">
            统计记录数
          </el-button>
        </div>
      </div>

      <!-- 查询结果区域 -->
      <div v-if="queryResult.executed" class="result-section">
        <!-- 结果标题栏 -->
        <div class="result-toolbar">
          <div class="result-status">
            <el-tag :type="queryResult.success ? 'success' : 'danger'" size="small">
              {{ queryResult.success ? '成功' : '失败' }}
            </el-tag>
            <span v-if="queryResult.sql" class="result-sql">
              SQL: <code>{{ queryResult.sql }}</code>
            </span>
            <span v-if="queryResult.success" class="result-stats">
              执行: {{ queryResult.executionTime }}ms | 行数: {{ queryResult.rowCount }}
            </span>
          </div>
          <div v-if="queryResult.success && queryResult.columns.length > 0" class="result-actions">
            <el-button text type="primary" size="small" @click="exportResult('csv')">
              导出 CSV
            </el-button>
            <el-button text type="success" size="small" @click="exportResult('json')">
              导出 JSON
            </el-button>
          </div>
        </div>

        <!-- 错误信息 -->
        <div v-if="!queryResult.success" class="error-message">
          <el-alert
            :title="'执行错误'"
            :description="queryResult.error"
            type="error"
            :closable="false"
            show-icon
          />
        </div>

        <!-- 结果表格 -->
        <div v-else-if="queryResult.columns.length > 0" class="result-table-wrapper">
          <el-table
            :data="queryResult.rows"
            border
            size="small"
            style="width: 100%"
          >
            <el-table-column
              v-for="col in queryResult.columns"
              :key="col"
              :prop="col"
              :label="col"
              min-width="120"
            />
          </el-table>
        </div>

        <!-- 无结果 -->
        <div v-else class="no-result">
          <el-empty description="查询无结果" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  VideoPlay,
  Delete,
  Document,
  DocumentChecked
} from '@element-plus/icons-vue'
import axios from 'axios'
import MonacoEditor from '../components/MonacoEditor.vue'

const route = useRoute()

const props = defineProps({
  connectionId: {
    type: [String, Number],
    required: true
  }
})

// ==================== 状态定义 ====================

/** Monaco Editor 引用 */
const monacoEditor = ref(null)

/** SQL 查询语句 */
const sqlQuery = ref('')

/** 执行状态 */
const executing = ref(false)

/** 是否有选中文本 */
const hasSelection = ref(false)

/** 编辑器主题 */
const darkTheme = ref(false)

/** 数据库列表 */
const databases = ref([])

/** 当前选中的数据库 */
const selectedDatabase = ref('')

/** 模式列表 */
const schemas = ref([])

/** 当前选中的模式 */
const selectedSchema = ref('')

/** 当前打开的查询ID（用于更新而不是创建新记录） */
const currentQueryId = ref(null)

/** 当前打开的查询名称 */
const currentQueryName = ref('')

/** 查询结果 */
const queryResult = reactive({
  executed: false,
  success: false,
  sql: '',
  columns: [],
  rows: [],
  rowCount: 0,
  executionTime: 0,
  error: ''
})

// ==================== 初始化 ====================

onMounted(() => {
  // 从 URL 参数恢复数据库和模式
  if (route.query.database) {
    selectedDatabase.value = route.query.database
  }
  if (route.query.schema) {
    selectedSchema.value = route.query.schema
  }

  // 如果有查询 ID，加载保存的查询
  if (route.query.queryId) {
    loadSavedQuery(route.query.queryId)
  }

  // 加载数据库列表
  loadDatabases()
})

// ==================== 数据加载 ====================

/**
 * 加载保存的查询
 */
const loadSavedQuery = async (queryId) => {
  try {
    const response = await axios.get(`/admin/featuredbadmin/query/saved/${queryId}`, {
      params: { connection_id: props.connectionId }
    })

    if (response.data.success && response.data.data) {
      const query = response.data.data

      // 保存当前查询ID（用于更新）
      currentQueryId.value = query.id

      // 保存当前查询名称（用于显示）
      currentQueryName.value = query.name

      // 填充 SQL
      sqlQuery.value = query.sql_query

      // 填充数据库和模式（如果 URL 参数中没有指定）
      if (!route.query.database && query.database) {
        selectedDatabase.value = query.database
      }
      if (!route.query.schema && query.schema) {
        selectedSchema.value = query.schema
      }

      ElMessage.success('已加载保存的查询')
    }
  } catch (error) {
    console.error('加载保存的查询失败:', error)
    ElMessage.error('加载保存的查询失败')
  }
}

/**
 * 加载数据库列表
 */
const loadDatabases = async () => {
  try {
    const response = await axios.get('/admin/featuredbadmin/databases', {
      params: { connection_id: props.connectionId }
    })

    if (response.data.success && response.data.data) {
      databases.value = response.data.data.databases || []

      // 如果只有一个数据库，自动选中
      if (databases.value.length === 1) {
        selectedDatabase.value = databases.value[0].name
        loadSchemas()
      }
    }
  } catch (error) {
    console.error('加载数据库列表失败:', error)
  }
}

/**
 * 加载模式列表
 */
const loadSchemas = async () => {
  if (!selectedDatabase.value) {
    schemas.value = []
    return
  }

  try {
    const response = await axios.get('/admin/featuredbadmin/schemas', {
      params: {
        connection_id: props.connectionId,
        database: selectedDatabase.value
      }
    })

    if (response.data.success && response.data.data) {
      schemas.value = response.data.data.schemas || []

      // 如果只有一个模式，自动选中
      if (schemas.value.length === 1) {
        selectedSchema.value = schemas.value[0].name
      }
    }
  } catch (error) {
    console.error('加载模式列表失败:', error)
  }
}

/**
 * 处理数据库变化
 */
const handleDatabaseChange = () => {
  selectedSchema.value = ''
  schemas.value = []
  loadSchemas()
}

/**
 * 处理模式变化
 */
const handleSchemaChange = () => {
  // 模式变化时可以触发一些操作
}

/**
 * 处理编辑器选择变化
 */
const handleSelectionChange = (selectedText) => {
  hasSelection.value = selectedText && selectedText.trim().length > 0
}

// ==================== SQL 操作 ====================

/**
 * 执行查询（自动判断是否选中）
 */
const executeQuery = async () => {
  // 检查是否有选中的 SQL
  const selectedSql = monacoEditor.value?.getSelection()?.trim()
  const sql = selectedSql || sqlQuery.value.trim()

  if (!sql) {
    ElMessage.warning('请输入 SQL 语句')
    return
  }

  if (!selectedDatabase.value) {
    ElMessage.warning('请选择数据库')
    return
  }

  executing.value = true
  queryResult.executed = false

  try {
    const params = {
      connection_id: props.connectionId,
      sql: sql,
      database: selectedDatabase.value,
      schema: selectedSchema.value || null
    }

    const response = await axios.post('/admin/featuredbadmin/query/execute', params)

    if (response.data.success) {
      queryResult.executed = true
      queryResult.success = true
      queryResult.sql = response.data.sql || ''
      queryResult.columns = response.data.columns || []
      queryResult.rows = response.data.data || []
      queryResult.rowCount = response.data.row_count || 0
      queryResult.executionTime = response.data.execution_time || 0
      queryResult.error = ''

      ElMessage.success('查询执行成功')
    } else {
      throw new Error(response.data.message || '查询执行失败')
    }
  } catch (error) {
    console.error('查询执行失败:', error)
    queryResult.executed = true
    queryResult.success = false
    queryResult.sql = response?.data?.sql || ''
    queryResult.error = error.response?.data?.message || error.message || '查询执行失败'
    queryResult.columns = []
    queryResult.rows = []
    queryResult.rowCount = 0
    queryResult.executionTime = 0
    ElMessage.error('查询执行失败')
  } finally {
    executing.value = false
  }
}

/**
 * 清空编辑器
 */
const clearEditor = () => {
  sqlQuery.value = ''
  queryResult.executed = false
}

/**
 * 格式化 SQL
 */
const formatSql = () => {
  monacoEditor.value?.formatDocument()
  ElMessage.success('SQL 已格式化')
}

/**
 * 保存查询
 */
const saveQuery = async () => {
  const sql = sqlQuery.value.trim()
  if (!sql) {
    ElMessage.warning('请输入 SQL 语句')
    return
  }

  if (!selectedDatabase.value) {
    ElMessage.warning('请选择数据库')
    return
  }

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
    if (error !== 'cancel') {
      console.error('保存查询失败:', error)
      ElMessage.error('保存查询失败')
    }
  }
}

/**
 * 插入 SQL 模板
 */
const insertSql = (sql) => {
  monacoEditor.value?.insertText(sql)
  monacoEditor.value?.focus()
}

/**
 * 导出查询结果
 */
const exportResult = (format) => {
  if (!queryResult.rows || queryResult.rows.length === 0) {
    ElMessage.warning('无数据可导出')
    return
  }

  let content = ''
  let filename = `query_result_${Date.now()}`

  if (format === 'csv') {
    // 导出 CSV
    const headers = queryResult.columns.join(',')
    const rows = queryResult.rows.map(row =>
      queryResult.columns.map(col => {
        const value = row[col]
        // 处理包含逗号或引号的值
        if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
          return `"${value.replace(/"/g, '""')}"`
        }
        return value ?? ''
      }).join(',')
    ).join('\n')

    content = `${headers}\n${rows}`
    filename += '.csv'
  } else if (format === 'json') {
    // 导出 JSON
    content = JSON.stringify(queryResult.rows, null, 2)
    filename += '.json'
  }

  // 创建下载链接
  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)

  ElMessage.success(`已导出 ${format.toUpperCase()} 文件`)
}

/**
 * 切换主题
 */
const toggleTheme = () => {
  monacoEditor.value?.setTheme(darkTheme.value ? 'vs-dark' : 'vs')
}
</script>

<style scoped>
.query-tool {
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
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.selector-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ==================== 主内容区 ==================== */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ==================== 编辑器区域 ==================== */
.editor-section {
  height: 480px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
}

.monaco-container {
  flex: 1;
  overflow: hidden;
}

.quick-actions {
  padding: 8px 16px;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
  display: flex;
  align-items: center;
  gap: 8px;
}

.quick-label {
  font-size: 13px;
  color: #666;
  margin-right: 8px;
}

/* ==================== 结果区域 ==================== */
.result-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.result-toolbar {
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
  background: #fafafa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.result-status {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.result-sql {
  font-size: 13px;
  color: #333;
  display: flex;
  align-items: center;
  gap: 4px;
}

.result-sql code {
  background: #f5f5f5;
  padding: 2px 8px;
  border-radius: 4px;
  font-family: 'Consolas', 'Monaco', monospace;
  font-size: 12px;
  color: #1890ff;
  max-width: 600px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: inline-block;
}

.result-stats {
  font-size: 13px;
  color: #666;
}

.result-actions {
  display: flex;
  gap: 8px;
}

.error-message {
  padding: 16px;
}

.result-table-wrapper {
  flex: 1;
  overflow: auto;
  padding: 16px;
}

.no-result {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ==================== Element Plus 样式 ==================== */
:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table th) {
  background: #fafafa;
  font-weight: 500;
}
</style>