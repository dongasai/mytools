<template>
  <div class="db-dashboard">
    <!-- 极简标题栏 -->
    <div class="db-header">
      <h2 class="db-title">数据库管理仪表盘</h2>
      <el-button type="primary" size="small" @click="refreshData">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </div>

    <!-- 统计区域 - 极简数字展示 -->
    <div class="db-stats">
      <div class="stat-item">
        <div class="stat-value">{{ stats.connections }}</div>
        <div class="stat-label">数据库连接</div>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <div class="stat-value">{{ stats.queries }}</div>
        <div class="stat-label">今日查询</div>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <div class="stat-value">{{ stats.activeConnections }}</div>
        <div class="stat-label">活跃连接</div>
      </div>
    </div>

    <!-- 查询历史 - 紧凑表格 -->
    <div class="db-section">
      <div class="section-header">
        <span class="section-title">最近查询历史</span>
        <el-button link size="small" @click="viewAllQueries">查看全部</el-button>
      </div>
      <el-table
        :data="queryHistory"
        size="small"
        class="db-table"
        v-loading="loading"
        empty-text="暂无查询记录"
      >
        <el-table-column prop="id" label="ID" width="50" />
        <el-table-column prop="connection_name" label="连接" width="100" />
        <el-table-column prop="sql" label="SQL语句" show-overflow-tooltip min-width="320" />
        <el-table-column prop="duration" label="耗时" width="70" align="right">
          <template #default="{ row }">
            <span :class="getDurationClass(row.duration)">{{ row.duration }}ms</span>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="时间" width="130" />
        <el-table-column label="操作" width="50" fixed="right">
          <template #default="{ row }">
            <el-button link size="small" @click="replayQuery(row)">重放</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 快捷操作 - 扁平按钮 -->
    <div class="db-section">
      <div class="section-header">
        <span class="section-title">快捷操作</span>
      </div>
      <div class="quick-actions">
        <el-button plain size="small" @click="goToConnections">连接管理</el-button>
        <el-button plain size="small" @click="goToQuery">SQL查询</el-button>
        <el-button plain size="small" @click="goToTables">表结构</el-button>
        <el-button plain size="small" @click="goToBackup">数据备份</el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

/**
 * 统计数据
 * @type {import('vue').Ref<Object>}
 */
const stats = ref({
  connections: 0,
  queries: 0,
  activeConnections: 0
})

/**
 * 查询历史列表
 * @type {import('vue').Ref<Array>}
 */
const queryHistory = ref([])

/**
 * 加载状态
 * @type {import('vue').Ref<boolean>}
 */
const loading = ref(false)

/**
 * 获取仪表盘统计数据
 */
const fetchStats = async () => {
  try {
    const res = await axios.get('/admin/featuredbadmin/dashboard/stats')
    stats.value = res.data
  } catch (error) {
    ElMessage.error('获取统计数据失败: ' + (error.response?.data?.message || error.message))
  }
}

/**
 * 获取查询历史
 */
const fetchQueryHistory = async () => {
  loading.value = true
  try {
    const res = await axios.get('/admin/featuredbadmin/dashboard/query-history', {
      params: { limit: 10 }
    })
    queryHistory.value = res.data.data || []
  } catch (error) {
    ElMessage.error('获取查询历史失败: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

/**
 * 刷新数据
 */
const refreshData = () => {
  fetchStats()
  fetchQueryHistory()
  ElMessage.success('数据已刷新')
}

/**
 * 获取执行时间样式类
 * @param {number} duration - 执行时间(ms)
 * @returns {string} CSS类名
 */
const getDurationClass = (duration) => {
  if (duration < 100) return 'duration-fast'
  if (duration < 500) return 'duration-normal'
  return 'duration-slow'
}

/**
 * 重放查询
 * @param {Object} row - 查询记录
 */
const replayQuery = (row) => {
  ElMessage.info(`准备重放查询: ${row.sql.substring(0, 50)}...`)
  window.location.href = `/admin/featuredbadmin/query?sql=${encodeURIComponent(row.sql)}`
}

/**
 * 查看全部查询
 */
const viewAllQueries = () => {
  window.location.href = '/admin/featuredbadmin/query-log'
}

/**
 * 跳转到连接管理
 */
const goToConnections = () => {
  window.location.href = '/admin/featuredbadmin/connections'
}

/**
 * 跳转到SQL查询
 */
const goToQuery = () => {
  window.location.href = '/admin/featuredbadmin/query'
}

/**
 * 跳转到表结构管理
 */
const goToTables = () => {
  window.location.href = '/admin/featuredbadmin/tables'
}

/**
 * 跳转到数据备份
 */
const goToBackup = () => {
  window.location.href = '/admin/featuredbadmin/backup'
}

// 组件挂载时获取数据
onMounted(() => {
  fetchStats()
  fetchQueryHistory()
})
</script>

<style scoped>
.db-dashboard {
  padding: 12px;
  background: #fafafa;
  min-height: 100%;
}

.db-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e8e8e8;
}

.db-title {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin: 0;
}

/* 统计区域 - DBeaver风格极简 */
.db-stats {
  display: flex;
  align-items: center;
  background: #fff;
  border: 1px solid #e8e8e8;
  padding: 12px 20px;
  margin-bottom: 12px;
}

.stat-item {
  flex: 1;
  text-align: center;
}

.stat-value {
  font-size: 24px;
  font-weight: 500;
  color: #1890ff;
  line-height: 1.2;
}

.stat-label {
  font-size: 11px;
  color: #666;
  margin-top: 2px;
}

.stat-divider {
  width: 1px;
  height: 32px;
  background: #e8e8e8;
  margin: 0 20px;
}

/* 区域样式 */
.db-section {
  background: #fff;
  border: 1px solid #e8e8e8;
  margin-bottom: 12px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #f5f5f5;
  border-bottom: 1px solid #e8e8e8;
}

.section-title {
  font-size: 12px;
  font-weight: 500;
  color: #333;
}

/* 表格样式 - 紧凑 */
.db-table {
  font-size: 11px;
}

.db-table :deep(.el-table__header th) {
  background: #fafafa;
  color: #333;
  font-weight: 500;
  padding: 6px 10px;
}

.db-table :deep(.el-table__cell) {
  padding: 4px 10px;
}

/* 执行时间颜色 */
.duration-fast { color: #52c41a; }
.duration-normal { color: #faad14; }
.duration-slow { color: #f5222d; }

/* 快捷操作 */
.quick-actions {
  padding: 10px 12px;
  display: flex;
  gap: 8px;
}

.quick-actions .el-button {
  font-size: 11px;
}
</style>