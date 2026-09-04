<template>
  <div class="dashboard">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-row">
      <el-col :span="6">
        <el-card class="stat-card posts-card">
          <div class="stat-icon">
            <el-icon><Document /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.posts }}</div>
            <div class="stat-label">文章总数</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card comments-card">
          <div class="stat-icon">
            <el-icon><ChatDotRound /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.comments }}</div>
            <div class="stat-label">评论总数</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card users-card">
          <div class="stat-icon">
            <el-icon><User /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.users }}</div>
            <div class="stat-label">用户总数</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card active-card">
          <div class="stat-icon">
            <el-icon><TrendCharts /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.active_users }}</div>
            <div class="stat-label">活跃用户</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 图表区域 -->
    <el-row :gutter="20" class="charts-row">
      <el-col :span="16">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span>评论趋势</span>
              <el-button-group>
                <el-button size="small" @click="refreshTrend" :loading="loading">
                  <el-icon><Refresh /></el-icon>
                  刷新
                </el-button>
              </el-button-group>
            </div>
          </template>
          <v-chart class="chart" :option="chartOption" autoresize />
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="pie-card">
          <template #header>
            <span>文章状态分布</span>
          </template>
          <v-chart class="chart" :option="pieOption" autoresize />
        </el-card>
      </el-col>
    </el-row>

    <!-- 最新文章列表 -->
    <el-row>
      <el-col :span="24">
        <el-card class="recent-card">
          <template #header>
            <div class="card-header">
              <span>最新文章</span>
              <el-button size="small" @click="refreshPosts" :loading="loading">
                <el-icon><Refresh /></el-icon>
                刷新
              </el-button>
            </div>
          </template>
          <el-table :data="recentPosts" style="width: 100%">
            <el-table-column prop="title" label="标题" width="300" />
            <el-table-column prop="author" label="作者" width="120" />
            <el-table-column prop="views" label="浏览量" width="100" />
            <el-table-column prop="comments" label="评论数" width="100" />
            <el-table-column prop="status" label="状态" width="100">
              <template #default="scope">
                <el-tag :type="getStatusType(scope.row.status)">
                  {{ getStatusText(scope.row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="created_at" label="发布时间" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, PieChart } from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent
} from 'echarts/components'
import VChart from 'vue-echarts'
import axios from 'axios'
import {
  Document,
  ChatDotRound,
  User,
  TrendCharts,
  Refresh
} from '@element-plus/icons-vue'

// 注册 ECharts 组件
use([
  CanvasRenderer,
  LineChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent
])

// 响应式数据
const loading = ref(false)
const stats = ref({
  posts: 0,
  comments: 0,
  users: 0,
  active_users: 0
})

const recentPosts = ref([])

// 图表数据
const chartData = {
  dates: [],
  values: []
}

// 折线图配置
const chartOption = ref({
  title: {
    text: '近7天评论趋势',
    left: 'center'
  },
  tooltip: {
    trigger: 'axis'
  },
  xAxis: {
    type: 'category',
    data: chartData.dates,
    boundaryGap: false
  },
  yAxis: {
    type: 'value'
  },
  series: [{
    data: chartData.values,
    type: 'line',
    smooth: true,
    areaStyle: {
      color: 'rgba(64, 158, 255, 0.3)'
    },
    lineStyle: {
      color: '#409EFF',
      width: 3
    },
    itemStyle: {
      color: '#409EFF'
    }
  }]
})

// 饼图配置
const pieOption = ref({
  tooltip: {
    trigger: 'item'
  },
  legend: {
    orient: 'vertical',
    left: 'left'
  },
  series: [{
    name: '文章状态',
    type: 'pie',
    radius: '50%',
    data: [
      { value: 0, name: '已发布' },
      { value: 0, name: '草稿' },
      { value: 0, name: '待审核' }
    ],
    emphasis: {
      itemStyle: {
        shadowBlur: 10,
        shadowOffsetX: 0,
        shadowColor: 'rgba(0, 0, 0, 0.5)'
      }
    }
  }]
})

// 获取统计数据
const fetchStats = async () => {
  try {
    const response = await axios.get('/admin/module_demo5/vue-dashboard/stats')
    if (response.data) {
      stats.value = response.data
    }
  } catch (error) {
    console.error('获取统计数据失败:', error)
  }
}

// 获取图表数据
const fetchChartData = async () => {
  try {
    const response = await axios.get('/admin/module_demo5/vue-dashboard/chart')
    if (response.data) {
      const data = response.data
      // 保持响应式引用
      chartData.dates.splice(0, chartData.dates.length, ...(data.dates || []))
      chartData.values.splice(0, chartData.values.length, ...(data.values || []))
    }
  } catch (error) {
    console.error('获取图表数据失败:', error)
  }
}

// 获取最新文章
const fetchRecentPosts = async () => {
  try {
    const response = await axios.get('/admin/module_demo5/vue-dashboard/posts')
    if (response.data) {
      recentPosts.value = response.data
    }
  } catch (error) {
    console.error('获取最新文章失败:', error)
  }
}

// 刷新趋势图
const refreshTrend = async () => {
  loading.value = true
  await fetchChartData()
  loading.value = false
}

// 刷新文章列表
const refreshPosts = async () => {
  loading.value = true
  await fetchRecentPosts()
  loading.value = false
}

// 状态类型
const getStatusType = (status) => {
  const types = {
    'published': 'success',
    'draft': 'info',
    'pending': 'warning'
  }
  return types[status] || 'info'
}

// 状态文本
const getStatusText = (status) => {
  const texts = {
    'published': '已发布',
    'draft': '草稿',
    'pending': '待审核'
  }
  return texts[status] || status
}

// 初始化
onMounted(async () => {
  loading.value = true
  await Promise.all([
    fetchStats(),
    fetchChartData(),
    fetchRecentPosts()
  ])
  loading.value = false
})
</script>

<style scoped>
.dashboard {
  padding: 20px;
  background: #f5f7fa;
  min-height: 100vh;
}

.stats-row {
  margin-bottom: 20px;
}

.stat-card {
  display: flex;
  align-items: center;
  padding: 20px;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  font-size: 30px;
}

.posts-card .stat-icon {
  background: rgba(64, 158, 255, 0.1);
  color: #409EFF;
}

.comments-card .stat-icon {
  background: rgba(103, 194, 58, 0.1);
  color: #67C23A;
}

.users-card .stat-icon {
  background: rgba(230, 162, 60, 0.1);
  color: #E6A23C;
}

.active-card .stat-icon {
  background: rgba(245, 108, 108, 0.1);
  color: #F56C6C;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #303133;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 14px;
  color: #909399;
}

.charts-row {
  margin-bottom: 20px;
}

.chart-card, .pie-card {
  height: 400px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart {
  width: 100%;
  height: 320px;
}

.recent-card {
  margin-top: 20px;
}
</style>