<template>
    <div class="vue-dashboard">
        <!-- 统计卡片区域 -->
        <el-row :gutter="20" class="stats-row">
            <el-col :xs="24" :sm="12" :md="6" :lg="6">
                <StatCard
                    title="用户总数"
                    :value="stats.users.value"
                    :trend="stats.users.trend"
                    icon="UserFilled"
                    color="#409EFF"
                />
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6">
                <StatCard
                    title="订单数量"
                    :value="stats.orders.value"
                    :trend="stats.orders.trend"
                    icon="ShoppingCartFull"
                    color="#67C23A"
                />
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6">
                <StatCard
                    title="总收入"
                    :value="stats.revenue.value"
                    :trend="stats.revenue.trend"
                    icon="Money"
                    color="#E6A23C"
                    prefix="¥"
                />
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6">
                <StatCard
                    title="访问量"
                    :value="stats.visits.value"
                    :trend="stats.visits.trend"
                    icon="View"
                    color="#F56C6C"
                />
            </el-col>
        </el-row>

        <!-- 图表区域 -->
        <el-row :gutter="20" class="chart-row">
            <el-col :span="24">
                <el-card class="chart-card" shadow="hover">
                    <template #header>
                        <div class="chart-header">
                            <span class="chart-title">近7天访问趋势</span>
                            <el-radio-group v-model="timeRange" size="small">
                                <el-radio-button label="7days">近7天</el-radio-button>
                                <el-radio-button label="30days">近30天</el-radio-button>
                            </el-radio-group>
                        </div>
                    </template>
                    <ChartLine :data="chartData" :loading="chartLoading" />
                </el-card>
            </el-col>
        </el-row>

        <!-- 快捷操作 -->
        <el-row :gutter="20" class="action-row">
            <el-col :xs="24" :sm="12" :md="8" :lg="8">
                <el-card shadow="hover" class="action-card">
                    <div class="action-content">
                        <el-icon class="action-icon" :size="40" color="#409EFF"><Refresh /></el-icon>
                        <div class="action-info">
                            <h4>刷新数据</h4>
                            <p>获取最新统计数据</p>
                        </div>
                        <el-button type="primary" @click="refreshData" :loading="loading">
                            立即刷新
                        </el-button>
                    </div>
                </el-card>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="8">
                <el-card shadow="hover" class="action-card">
                    <div class="action-content">
                        <el-icon class="action-icon" :size="40" color="#67C23A"><Download /></el-icon>
                        <div class="action-info">
                            <h4>导出报表</h4>
                            <p>下载数据报表</p>
                        </div>
                        <el-button type="success" @click="exportReport">
                            导出
                        </el-button>
                    </div>
                </el-card>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="8">
                <el-card shadow="hover" class="action-card">
                    <div class="action-content">
                        <el-icon class="action-icon" :size="40" color="#E6A23C"><Setting /></el-icon>
                        <div class="action-info">
                            <h4>设置</h4>
                            <p>仪表盘配置</p>
                        </div>
                        <el-button type="warning" @click="openSettings">
                            配置
                        </el-button>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, getCurrentInstance } from 'vue'
import { ElMessage } from 'element-plus'
import StatCard from '../components/StatCard.vue'
import ChartLine from '../components/ChartLine.vue'

const { proxy } = getCurrentInstance()
const axios = proxy.$axios

// 加载状态
const loading = ref(false)
const chartLoading = ref(false)

// 时间范围
const timeRange = ref('7days')

// 统计数据
const stats = reactive({
    users: { value: 0, trend: 0 },
    orders: { value: 0, trend: 0 },
    revenue: { value: 0, trend: 0 },
    visits: { value: 0, trend: 0 },
})

// 图表数据
const chartData = reactive({
    dates: [],
    values: [],
})

/**
 * 获取统计数据
 */
const fetchStats = async () => {
    const response = await axios.get('/admin/module_dcatadmin/vue-dashboard/stats')
    const data = response.data

    stats.users = data.users || { value: 0, trend: 0 }
    stats.orders = data.orders || { value: 0, trend: 0 }
    stats.revenue = data.revenue || { value: 0, trend: 0 }
    stats.visits = data.visits || { value: 0, trend: 0 }
}

/**
 * 获取图表数据
 */
const fetchChartData = async () => {
    const response = await axios.get('/admin/module_dcatadmin/vue-dashboard/chart')
    const data = response.data

    // 使用 splice 保持响应式引用
    chartData.dates.splice(0, chartData.dates.length, ...(data.dates || []))
    chartData.values.splice(0, chartData.values.length, ...(data.values || []))
}

/**
 * 刷新数据
 */
const refreshData = async () => {
    loading.value = true
    try {
        await Promise.all([fetchStats(), fetchChartData()])
        ElMessage.success('数据已刷新')
    } catch (error) {
        console.error('刷新数据失败:', error)
        ElMessage.error('刷新数据失败')
    } finally {
        loading.value = false
    }
}

/**
 * 导出报表
 */
const exportReport = () => {
    ElMessage.success('报表导出中...')
    // 实际实现：调用导出API或生成Excel
}

/**
 * 打开设置
 */
const openSettings = () => {
    ElMessage.info('设置功能开发中')
}

// 组件挂载时获取数据
onMounted(async () => {
    loading.value = true
    try {
        await Promise.all([fetchStats(), fetchChartData()])
    } catch (error) {
        console.error('加载数据失败:', error)
        ElMessage.error('加载数据失败，请刷新页面重试')
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.vue-dashboard {
    padding: 20px;
}

.stats-row {
    margin-bottom: 20px;
}

.chart-row {
    margin-bottom: 20px;
}

.chart-card {
    border-radius: 8px;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-title {
    font-size: 16px;
    font-weight: 500;
    color: #303133;
}

.action-card {
    border-radius: 8px;
}

.action-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.action-icon {
    flex-shrink: 0;
}

.action-info {
    flex: 1;
}

.action-info h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #303133;
}

.action-info p {
    margin: 0;
    font-size: 13px;
    color: #909399;
}

@media (max-width: 768px) {
    .vue-dashboard {
        padding: 10px;
    }

    .action-content {
        flex-direction: column;
        text-align: center;
    }
}
</style>