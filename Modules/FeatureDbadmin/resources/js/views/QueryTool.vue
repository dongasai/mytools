<template>
  <div class="query-tool">
    <!-- 顶部工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <span class="toolbar-title">SQL 查询工具</span>
        <el-divider direction="vertical" />
        <el-select
          v-model="selectedConnection"
          placeholder="选择连接"
          size="small"
          style="width: 200px"
        >
          <el-option
            v-for="conn in connections"
            :key="conn.id"
            :label="conn.name"
            :value="conn.id"
          />
        </el-select>
      </div>
      <div class="toolbar-right">
        <el-button text type="primary" size="small" @click="executeQuery" :loading="executing">
          <el-icon><VideoPlay /></el-icon> 执行 (Ctrl+Enter)
        </el-button>
        <el-button text size="small" @click="clearEditor">
          <el-icon><Delete /></el-icon> 清空
        </el-button>
        <el-button text type="success" size="small" @click="saveQuery">
          <el-icon><DocumentChecked /></el-icon> 保存
        </el-button>
        <el-button text type="warning" size="small" @click="formatSql">
          <el-icon><Document /></el-icon> 格式化
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
            max-height="400"
          >
            <el-table-column
              v-for="col in queryResult.columns"
              :key="col"
              :prop="col"
              :label="col"
              min-width="120"
              show-overflow-tooltip
            />
          </el-table>
        </div>

        <!-- 无结果 -->
        <div v-else class="empty-result">
          <span>查询成功，无数据返回</span>
        </div>
      </div>

      <!-- 历史和保存查询面板 -->
      <div class="panels-section">
        <!-- 查询历史 -->
        <div class="panel">
          <div class="panel-header" @click="togglePanel('history')">
            <span class="panel-title">查询历史</span>
            <div class="panel-controls">
              <el-button text type="danger" size="small" @click.stop="clearHistory">
                清空
              </el-button>
              <el-icon :class="{ 'rotate-icon': panels.historyCollapsed }">
                <ArrowDown />
              </el-icon>
            </div>
          </div>
          <div v-show="!panels.historyCollapsed" class="panel-body">
            <div v-if="queryHistory.length > 0" class="history-list">
              <div
                v-for="(item, index) in queryHistory"
                :key="index"
                class="history-item"
                @click="loadFromHistory(item)"
              >
                <div class="history-sql">{{ truncateSql(item.sql, 60) }}</div>
                <div class="history-meta">
                  <el-tag :type="item.success ? 'success' : 'danger'" size="small">
                    {{ item.success ? '成功' : '失败' }}
                  </el-tag>
                  <span class="history-time">{{ item.time }}</span>
                </div>
              </div>
            </div>
            <div v-else class="empty-panel">
              <span>暂无查询历史</span>
            </div>
          </div>
        </div>

        <!-- 保存的查询 -->
        <div class="panel">
          <div class="panel-header" @click="togglePanel('saved')">
            <span class="panel-title">保存的查询</span>
            <el-icon :class="{ 'rotate-icon': panels.savedCollapsed }">
              <ArrowDown />
            </el-icon>
          </div>
          <div v-show="!panels.savedCollapsed" class="panel-body">
            <div v-if="savedQueries.length > 0" class="saved-list">
              <div
                v-for="(item, index) in savedQueries"
                :key="index"
                class="saved-item"
              >
                <div class="saved-info" @click="loadSavedQuery(item)">
                  <div class="saved-name">{{ item.name }}</div>
                  <div class="saved-sql">{{ truncateSql(item.sql, 50) }}</div>
                </div>
                <el-button text type="danger" size="small" @click="deleteSavedQuery(index)">
                  删除
                </el-button>
              </div>
            </div>
            <div v-else class="empty-panel">
              <span>暂无保存的查询</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 保存查询对话框 -->
    <el-dialog v-model="saveDialog.visible" title="保存查询" width="400px">
      <el-form :model="saveDialog.form" label-width="60px" size="small">
        <el-form-item label="名称">
          <el-input v-model="saveDialog.form.name" placeholder="输入查询名称" />
        </el-form-item>
        <el-form-item label="描述">
          <el-input
            v-model="saveDialog.form.description"
            type="textarea"
            :rows="2"
            placeholder="输入查询描述（可选）"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button size="small" @click="saveDialog.visible = false">取消</el-button>
        <el-button type="primary" size="small" @click="confirmSaveQuery">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
/**
 * QueryTool.vue
 * SQL 查询工具组件
 *
 * 功能:
 * - Monaco Editor SQL 编辑器（语法高亮、自动补全）
 * - SQL 格式化（sql-formatter）
 * - 查询执行
 * - 结果展示（表格）
 * - 查询历史
 * - 保存查询
 */
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  VideoPlay,
  Delete,
  DocumentChecked,
  Document,
  ArrowDown
} from '@element-plus/icons-vue'
import axios from 'axios'
import * as monaco from 'monaco-editor'
import { format as formatSqlLib } from 'sql-formatter'

// ==================== 状态定义 ====================

/** Monaco Editor 实例 */
let editor = null

/** 编辑器容器引用 */
const editorContainer = ref(null)

/** 连接列表 */
const connections = ref([])

/** 选中的连接 */
const selectedConnection = ref('')

/** 执行状态 */
const executing = ref(false)

/** 查询结果 */
const queryResult = reactive({
  executed: false,
  success: false,
  rows: [],
  columns: [],
  executionTime: 0,
  rowCount: 0,
  error: ''
})

/** 查询历史（本地存储） */
const queryHistory = ref([])

/** 保存的查询 */
const savedQueries = ref([])

/** 保存对话框状态 */
const saveDialog = reactive({
  visible: false,
  form: {
    name: '',
    description: ''
  }
})

/** 面板折叠状态 */
const panels = reactive({
  historyCollapsed: false,
  savedCollapsed: false
})

// ==================== 生命周期 ====================

onMounted(() => {
  loadConnections()
  loadHistoryFromStorage()
  loadSavedQueriesFromStorage()
  initMonacoEditor()
})

onBeforeUnmount(() => {
  if (editor) {
    editor.dispose()
  }
})

// ==================== Monaco Editor 初始化 ====================

/**
 * 初始化 Monaco Editor
 */
const initMonacoEditor = () => {
  if (!editorContainer.value) {
    console.error('Monaco Editor 容器未找到')
    return
  }

  // 创建编辑器实例
  editor = monaco.editor.create(editorContainer.value, {
    value: '',
    language: 'sql',
    theme: 'vs',
    automaticLayout: true,
    minimap: { enabled: false },
    fontSize: 13,
    lineNumbers: 'on',
    roundedSelection: false,
    scrollBeyondLastLine: false,
    readOnly: false,
    wordWrap: 'on',
    folding: true,
    tabSize: 2,
  })

  // 注册快捷键：Ctrl+Enter 执行查询
  editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Enter, () => {
    executeQuery()
  })
}

/**
 * 获取编辑器内容
 * @returns {string} SQL 内容
 */
const getEditorValue = () => {
  return editor ? editor.getValue() : ''
}

/**
 * 设置编辑器内容
 * @param {string} value - SQL 内容
 */
const setEditorValue = (value) => {
  if (editor) {
    editor.setValue(value)
  }
}

/**
 * 插入文本到编辑器
 * @param {string} text - 要插入的文本
 */
const insertText = (text) => {
  if (!editor) return

  const position = editor.getPosition()
  if (position) {
    editor.executeEdits('', [{
      range: new monaco.Range(position.lineNumber, position.column, position.lineNumber, position.column),
      text: text
    }])
  }
}

// ==================== 数据加载 ====================

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

/**
 * 从本地存储加载查询历史
 */
const loadHistoryFromStorage = () => {
  try {
    const stored = localStorage.getItem('featuredbadmin_query_history')
    if (stored) {
      queryHistory.value = JSON.parse(stored)
    }
  } catch (error) {
    console.error('加载查询历史失败:', error)
  }
}

/**
 * 保存查询历史到本地存储
 */
const saveHistoryToStorage = () => {
  try {
    // 只保留最近 20 条
    const recent = queryHistory.value.slice(0, 20)
    localStorage.setItem('featuredbadmin_query_history', JSON.stringify(recent))
  } catch (error) {
    console.error('保存查询历史失败:', error)
  }
}

/**
 * 从本地存储加载保存的查询
 */
const loadSavedQueriesFromStorage = () => {
  try {
    const stored = localStorage.getItem('featuredbadmin_saved_queries')
    if (stored) {
      savedQueries.value = JSON.parse(stored)
    }
  } catch (error) {
    console.error('加载保存的查询失败:', error)
  }
}

/**
 * 保存查询到本地存储
 */
const saveQueriesToStorage = () => {
  try {
    localStorage.setItem('featuredbadmin_saved_queries', JSON.stringify(savedQueries.value))
  } catch (error) {
    console.error('保存查询失败:', error)
  }
}

// ==================== 事件处理 ====================

/**
 * 切换面板折叠状态
 * @param {string} panel - 面板名称
 */
const togglePanel = (panel) => {
  if (panel === 'history') {
    panels.historyCollapsed = !panels.historyCollapsed
  } else if (panel === 'saved') {
    panels.savedCollapsed = !panels.savedCollapsed
  }
}

/**
 * 执行 SQL 查询
 */
const executeQuery = async () => {
  const sql = getEditorValue().trim()

  if (!sql) {
    ElMessage.warning('请输入 SQL 查询语句')
    return
  }

  if (!selectedConnection.value) {
    ElMessage.warning('请选择数据库连接')
    return
  }

  executing.value = true
  queryResult.executed = false

  const startTime = Date.now()

  try {
    const response = await axios.post('/admin/featuredbadmin/query/execute', {
      connection_id: selectedConnection.value,
      sql: sql
    })

    const endTime = Date.now()
    queryResult.executionTime = endTime - startTime

    if (response.data.success) {
      const data = response.data.data
      queryResult.success = true
      queryResult.rows = data.rows || []
      queryResult.columns = data.columns || []
      queryResult.rowCount = data.rows ? data.rows.length : 0
      ElMessage.success(`查询成功，返回 ${queryResult.rowCount} 条记录`)
    } else {
      queryResult.success = false
      queryResult.error = response.data.message || '执行失败'
      ElMessage.error(queryResult.error)
    }
  } catch (error) {
    const endTime = Date.now()
    queryResult.executionTime = endTime - startTime
    queryResult.success = false
    queryResult.error = error.response?.data?.message || error.message || '执行失败'
    ElMessage.error('查询执行失败')
  } finally {
    queryResult.executed = true
    executing.value = false

    // 添加到历史记录
    addToHistory()
  }
}

/**
 * 添加到查询历史
 */
const addToHistory = () => {
  const historyItem = {
    sql: getEditorValue(),
    time: new Date().toLocaleString(),
    success: queryResult.success
  }

  queryHistory.value.unshift(historyItem)
  saveHistoryToStorage()
}

/**
 * 清空编辑器
 */
const clearEditor = () => {
  setEditorValue('')
  queryResult.executed = false
}

/**
 * 插入 SQL 语句
 * @param {string} sql - SQL 语句
 */
const insertSql = (sql) => {
  insertText(sql)
}

/**
 * 格式化 SQL（使用 sql-formatter）
 */
const formatSql = () => {
  const sql = getEditorValue().trim()
  if (!sql) return

  try {
    const formatted = formatSqlLib(sql, {
      language: 'mysql',
      tabWidth: 2,
      keywordCase: 'upper',
      linesBetweenQueries: 2,
    })

    setEditorValue(formatted)
    ElMessage.success('格式化完成')
  } catch (error) {
    console.error('SQL 格式化失败:', error)
    ElMessage.error('格式化失败，请检查 SQL 语法')
  }
}

/**
 * 保存查询
 */
const saveQuery = () => {
  const sql = getEditorValue().trim()
  if (!sql) {
    ElMessage.warning('请先输入 SQL 查询语句')
    return
  }

  saveDialog.form.name = ''
  saveDialog.form.description = ''
  saveDialog.visible = true
}

/**
 * 确认保存查询
 */
const confirmSaveQuery = () => {
  if (!saveDialog.form.name.trim()) {
    ElMessage.warning('请输入查询名称')
    return
  }

  const savedItem = {
    name: saveDialog.form.name,
    description: saveDialog.form.description,
    sql: getEditorValue(),
    connection_id: selectedConnection.value,
    created_at: new Date().toISOString()
  }

  savedQueries.value.unshift(savedItem)
  saveQueriesToStorage()

  saveDialog.visible = false
  ElMessage.success('查询已保存')
}

/**
 * 加载历史查询
 * @param {Object} item - 历史记录项
 */
const loadFromHistory = (item) => {
  setEditorValue(item.sql)
  ElMessage.success('已加载历史查询')
}

/**
 * 清空历史
 */
const clearHistory = () => {
  ElMessageBox.confirm('确定要清空所有查询历史吗？', '确认清空', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    queryHistory.value = []
    saveHistoryToStorage()
    ElMessage.success('历史已清空')
  }).catch(() => {})
}

/**
 * 加载保存的查询
 * @param {Object} item - 保存的查询项
 */
const loadSavedQuery = (item) => {
  setEditorValue(item.sql)
  if (item.connection_id) {
    selectedConnection.value = item.connection_id
  }
  ElMessage.success('已加载保存的查询')
}

/**
 * 删除保存的查询
 * @param {number} index - 索引
 */
const deleteSavedQuery = (index) => {
  ElMessageBox.confirm('确定要删除这个保存的查询吗？', '确认删除', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    savedQueries.value.splice(index, 1)
    saveQueriesToStorage()
    ElMessage.success('已删除')
  }).catch(() => {})
}

/**
 * 导出查询结果
 * @param {string} format - 导出格式
 */
const exportResult = (format) => {
  if (!queryResult.rows || queryResult.rows.length === 0) {
    ElMessage.warning('没有可导出的数据')
    return
  }

  if (format === 'csv') {
    exportToCsv()
  } else if (format === 'json') {
    exportToJson()
  }
}

/**
 * 导出为 CSV
 */
const exportToCsv = () => {
  const columns = queryResult.columns
  const rows = queryResult.rows

  let csv = columns.join(',') + '\n'
  rows.forEach(row => {
    const values = columns.map(col => {
      const value = row[col]
      if (value === null || value === undefined) return ''
      // 处理包含逗号或换行符的值
      const str = String(value)
      if (str.includes(',') || str.includes('\n') || str.includes('"')) {
        return '"' + str.replace(/"/g, '""') + '"'
      }
      return str
    })
    csv += values.join(',') + '\n'
  })

  downloadFile(csv, 'query_result.csv', 'text/csv')
  ElMessage.success('导出 CSV 成功')
}

/**
 * 导出为 JSON
 */
const exportToJson = () => {
  const json = JSON.stringify(queryResult.rows, null, 2)
  downloadFile(json, 'query_result.json', 'application/json')
  ElMessage.success('导出 JSON 成功')
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

/**
 * 截断 SQL 显示
 * @param {string} sql - SQL 语句
 * @param {number} maxLength - 最大长度
 * @returns {string} 截断后的字符串
 */
const truncateSql = (sql, maxLength) => {
  if (!sql) return ''
  const clean = sql.replace(/\s+/g, ' ').trim()
  if (clean.length <= maxLength) return clean
  return clean.substring(0, maxLength) + '...'
}
</script>

<style scoped>
.query-tool {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #fafafa;
}

/* 工具栏 */
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 16px;
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toolbar-title {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.toolbar-right {
  display: flex;
  gap: 4px;
}

/* 主内容区 */
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* 编辑器区域 */
.editor-section {
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 2px;
}

/* Monaco Editor 容器 */
.monaco-container {
  height: 300px;
  border-bottom: 1px solid #e8e8e8;
}

/* 快捷语句栏 */
.quick-actions {
  padding: 8px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f5f5f5;
}

.quick-label {
  font-size: 13px;
  color: #666;
}

/* 结果区域 */
.result-section {
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 2px;
  overflow: hidden;
}

.result-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #fafafa;
  border-bottom: 1px solid #e8e8e8;
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
  gap: 4px;
}

.error-message {
  padding: 12px;
}

.result-table-wrapper {
  padding: 12px;
}

.empty-result {
  padding: 24px;
  text-align: center;
  color: #999;
  font-size: 13px;
}

/* 历史和保存查询面板 */
.panels-section {
  display: flex;
  gap: 16px;
}

.panel {
  flex: 1;
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 2px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #fafafa;
  border-bottom: 1px solid #e8e8e8;
  cursor: pointer;
  user-select: none;
}

.panel-header:hover {
  background: #f5f5f5;
}

.panel-title {
  font-size: 13px;
  font-weight: 500;
  color: #333;
}

.panel-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rotate-icon {
  transform: rotate(180deg);
  transition: transform 0.2s;
}

.panel-body {
  max-height: 300px;
  overflow-y: auto;
}

.history-list,
.saved-list {
  padding: 4px 0;
}

.history-item,
.saved-item {
  padding: 8px 12px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.15s;
}

.history-item:hover,
.saved-item:hover {
  background: #fafafa;
}

.history-item:last-child,
.saved-item:last-child {
  border-bottom: none;
}

.history-sql {
  font-family: 'Consolas', monospace;
  font-size: 12px;
  color: #333;
  margin-bottom: 6px;
  word-break: break-all;
}

.history-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.history-time {
  font-size: 12px;
  color: #999;
}

.saved-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.saved-info {
  flex: 1;
  min-width: 0;
}

.saved-name {
  font-size: 13px;
  font-weight: 500;
  color: #333;
  margin-bottom: 4px;
}

.saved-sql {
  font-family: 'Consolas', monospace;
  font-size: 11px;
  color: #666;
  word-break: break-all;
}

.empty-panel {
  padding: 24px;
  text-align: center;
  color: #999;
  font-size: 13px;
}

/* Element Plus 覆盖 */
.el-divider--vertical {
  height: 16px;
  margin: 0;
}

.el-button + .el-button {
  margin-left: 0;
}

/* 表格紧凑样式 */
.el-table--small {
  font-size: 12px;
}

.el-table--small :deep(th) {
  background: #fafafa;
  font-weight: 500;
  color: #333;
}

.el-table--small :deep(td) {
  padding: 6px 0;
}
</style>
