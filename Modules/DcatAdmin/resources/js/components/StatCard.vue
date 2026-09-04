<template>
    <el-card class="stat-card" shadow="hover" :body-style="{ padding: '20px' }">
        <div class="stat-content">
            <div class="stat-icon" :style="{ backgroundColor: iconBgColor }">
                <el-icon :size="28" :color="iconColor">
                    <component :is="icon" />
                </el-icon>
            </div>
            <div class="stat-info">
                <div class="stat-title">{{ title }}</div>
                <div class="stat-value">
                    <span class="stat-prefix" v-if="prefix">{{ prefix }}</span>
                    <span class="stat-number">{{ formattedValue }}</span>
                </div>
                <div class="stat-trend" :class="{ 'trend-up': trend > 0, 'trend-down': trend < 0 }">
                    <el-icon v-if="trend > 0" class="trend-icon"><ArrowUp /></el-icon>
                    <el-icon v-else-if="trend < 0" class="trend-icon"><ArrowDown /></el-icon>
                    <span class="trend-text">{{ Math.abs(trend) }}%</span>
                    <span class="trend-label">较上周</span>
                </div>
            </div>
        </div>
    </el-card>
</template>

<script setup>
import { computed } from 'vue'

/**
 * StatCard 组件 - 统计卡片
 *
 * @component StatCard
 * @description 用于展示统计数据的卡片组件
 */

const props = defineProps({
    /** 卡片标题 */
    title: {
        type: String,
        required: true,
    },
    /** 数值 */
    value: {
        type: Number,
        default: 0,
    },
    /** 趋势百分比（正负数） */
    trend: {
        type: Number,
        default: 0,
    },
    /** Element Plus 图标名称 */
    icon: {
        type: String,
        default: 'DataLine',
    },
    /** 主题色 */
    color: {
        type: String,
        default: '#409EFF',
    },
    /** 数值前缀（如 ¥） */
    prefix: {
        type: String,
        default: '',
    },
    /** 数值后缀（如 人） */
    suffix: {
        type: String,
        default: '',
    },
})

/** 图标背景色（透明度降低） */
const iconBgColor = computed(() => {
    // 将 hex 颜色转为 rgba，添加 0.15 透明度
    const hex = props.color.replace('#', '')
    const r = parseInt(hex.substring(0, 2), 16)
    const g = parseInt(hex.substring(2, 4), 16)
    const b = parseInt(hex.substring(4, 6), 16)
    return `rgba(${r}, ${g}, ${b}, 0.15)`
})

/** 图标颜色 */
const iconColor = computed(() => props.color)

/** 格式化数值 */
const formattedValue = computed(() => {
    let val = props.value
    // 大于10000时显示为万
    if (val >= 10000) {
        return (val / 10000).toFixed(1) + '万'
    }
    // 大于1000时添加千分位
    if (val >= 1000) {
        return val.toLocaleString()
    }
    return val.toString()
})
</script>

<style scoped>
.stat-card {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
    min-width: 0;
}

.stat-title {
    font-size: 14px;
    color: #606266;
    margin-bottom: 8px;
}

.stat-value {
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.stat-prefix {
    font-size: 18px;
    font-weight: 500;
    color: #303133;
}

.stat-number {
    font-size: 24px;
    font-weight: 600;
    color: #303133;
    line-height: 1.2;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
    font-size: 13px;
}

.trend-icon {
    font-size: 12px;
}

.trend-up {
    color: #67C23A;
}

.trend-down {
    color: #F56C6C;
}

.trend-text {
    font-weight: 500;
}

.trend-label {
    color: #909399;
    margin-left: 4px;
}

@media (max-width: 768px) {
    .stat-content {
        flex-direction: column;
        text-align: center;
    }

    .stat-icon {
        margin: 0 auto;
    }
}
</style>
