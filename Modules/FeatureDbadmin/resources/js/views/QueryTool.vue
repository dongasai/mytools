<template>
  <div class="query-tool">
    <!-- 顶部工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-button type="primary" @click="executeQuery" :loading="executing">
          <el-icon><VideoPlay /></el-icon>
          执行 (Ctrl+Enter)
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
      </div>
    </div>

    <!-- 主内容区 -->
    <div class="main-content">
      <!-- SQL编辑器区域 -->
      <div class="editor-section">
        <!-- Monaco Editor 容器 -->
        <div ref="editorContainer" class="monaco-container"></div>

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
import { ref, reactive, onMounted, onBeforeUnmount, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  VideoPlay,
  Delete,
  Document,
  DocumentChecked
} from '@element-plus/icons-vue'
import axios from 'axios'

const props = defineProps({
  connectionId: {
    type: Number,
    required: true
  }
})

// ==================== 状态定义 ====================

/** Monaco Editor 实例 */
const editorContainer = ref(null)
let editorInstance = null

/** 执行状态 */
const executing = ref(false)

/** 查询结果 */
const queryResult = reactive({
  executed: false,
  success: false,
  columns: [],
  rows: [],
  rowCount: 0,
  executionTime: 0,
  error: ''
})

// ==================== 生命周期 ====================

onMounted(() => {
  // 初始化 Monaco Editor
  // 注意：实际项目中需要引入 Monaco Editor
  // import * as monaco from 'monaco-editor'

  // 这里使用简化的 textarea 代替
  const textarea = document.createElement('textarea')
  textarea.style.cssText = `
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    resize: none;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 14px;
    line-height: 1.5;
    padding: 12px;
  `
  textarea.placeholder = '输入 SQL 查询语句...\n\n快捷键: Ctrl+Enter 执行查询'

  editorContainer.value.appendChild(textarea)
  editorInstance = textarea

  // 添加快捷键监听
  textarea.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'Enter') {
      e.preventDefault()
      executeQuery()
    }
  })
})

onBeforeUnmount(() => {
  if (editorInstance) {
    editorInstance.remove()
  }
})

// ==================== SQL 操作 ====================

/**
 * 执行查询
 */
const executeQuery = async () => {
  const sql = editorInstance?.value?.trim()
  if (!sql) {
    ElMessage.warning('请输入 SQL 语句')
    return
  }

  executing.value = true
  const startTime = Date.now()

  try {
    const response = await axios.post('/admin/featuredbadmin/query/execute', {
      connection_id: props.connectionId,
      sql: sql
    })

    const endTime = Date.now()

    queryResult.executed = true
    queryResult.success = response.data.success
    queryResult.executionTime = endTime - startTime
    queryResult.error = response.data.error || ''

    if (response.data.success) {
      queryResult.columns = response.data.columns || []
      queryResult.rows = response.data.rows || []
      queryResult.rowCount = response.data.rows?.length || 0
    } else {
      queryResult.columns = []
      queryResult.rows = []
      queryResult.rowCount = 0
    }
  } catch (error) {
    console.error('执行失败:', error)
    queryResult.executed = true
    queryResult.success = false
    queryResult.error = error.response?.data?.message || error.message || '执行失败'
    queryResult.executionTime = Date.now() - startTime
  } finally {
    executing.value = false
  }
}

/**
 * 清空编辑器
 */
const clearEditor = () => {
  if (editorInstance) {
    editorInstance.value = ''
  }
  queryResult.executed = false
}

/**
 * 格式化 SQL
 */
const formatSql = () => {
  ElMessage.info('SQL 格式化功能需要集成格式化库')
}

/**
 * 保存查询
 */
const saveQuery = async () => {
  const sql = editorInstance?.value?.trim()
  if (!sql) {
    ElMessage.warning('请输入 SQL 语句')
    return
  }

  try {
    const response = await axios.post('/admin/featuredbadmin/query/save', {
      connection_id: props.connectionId,
      sql: sql,
      name: `查询 ${new Date().toLocaleString()}`
    })

    if (response.data.success) {
      ElMessage.success('查询已保存')
    } else {
      ElMessage.error('保存失败')
    }
  } catch (error) {
    console.error('保存失败:', error)
    ElMessage.error('保存失败')
  }
}

/**
 * 插入 SQL 模板
 */
const insertSql = (sql) => {
  if (editorInstance) {
    const cursorPos = editorInstance.selectionStart
    const textBefore = editorInstance.value.substring(0, cursorPos)
    const textAfter = editorInstance.value.substring(cursorPos)
    editorInstance.value = textBefore + sql + textAfter
    editorInstance.focus()
  }
}

/**
 * 导出结果
 */
const exportResult = (format) => {
  let content = ''
  let filename = `query-result.${format}`

  if (format === 'csv') {
    const headers = queryResult.columns.join(',')
    const rows = queryResult.rows.map(row =>
      queryResult.columns.map(col => row[col]).join(',')
    )
    content = [headers, ...rows].join('\n')
  } else if (format === 'json') {
    content = JSON.stringify(queryResult.rows, null, 2)
  }

  const blob = new Blob([content], { type: 'text/plain' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)

  ElMessage.success('导出成功')
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

/* ==================== 主内容区 ==================== */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ==================== 编辑器区域 ==================== */
.editor-section {
  height: 300px;
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