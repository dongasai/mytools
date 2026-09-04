<template>
    <div class="chart-line-container">
        <div v-if="loading" class="chart-loading">
            <el-skeleton animated :rows="5" />
        </div>
        <div v-else-if="hasData" ref="chartRef" class="chart-line"></div>
        <div v-else class="chart-empty">
            <el-empty description="暂无数据" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
// ECharts 按需导入
import * as echarts from 'echarts/core'
import { LineChart } from 'echarts/charts'
import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
} from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

// 注册必需的组件
echarts.use([
    LineChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    CanvasRenderer,
])

/**
 * ChartLine 组件 - 折线图
 *
 * @component ChartLine
 * @description 使用 ECharts 绘制的折线图组件
 */

const props = defineProps({
    /** 图表数据 { dates: string[], values: number[] } */
    data: {
        type: Object,
        default: () => ({ dates: [], values: [] }),
    },
    /** 加载状态 */
    loading: {
        type: Boolean,
        default: false,
    },
    /** 图表标题 */
    title: {
        type: String,
        default: '',
    },
    /** Y轴单位 */
    unit: {
        type: String,
        default: '',
    },
    /** 主题色 */
    color: {
        type: String,
        default: '#409EFF',
    },
})

const chartRef = ref(null)
let chartInstance = null

/** 是否有数据 */
const hasData = computed(() => {
    return props.data.dates?.length > 0 && props.data.values?.length > 0
})

/**
 * 初始化图表
 */
const initChart = () => {
    if (!chartRef.value) return

    // 如果已存在实例，先销毁
    if (chartInstance) {
        chartInstance.dispose()
    }

    // 创建新实例
    chartInstance = echarts.init(chartRef.value, null, {
        renderer: 'canvas',
    })

    // 设置图表配置
    setChartOption()

    // 监听窗口大小变化
    window.addEventListener('resize', handleResize)
}

/**
 * 设置图表配置
 */
const setChartOption = () => {
    if (!chartInstance) return

    const option = {
        // 背景色透明
        backgroundColor: 'transparent',

        // 网格配置
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            top: '10%',
            containLabel: true,
        },

        // 提示框
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            borderColor: '#EBEEF5',
            borderWidth: 1,
            textStyle: {
                color: '#606266',
            },
            formatter: (params) => {
                const data = params[0]
                const value = data.value
                const unit = props.unit || ''
                return `
                    <div style="font-weight: 500; margin-bottom: 4px;">${data.name}</div>
                    <div style="color: ${props.color};">
                        <span style="display: inline-block; width: 10px; height: 10px;
                            background: ${props.color}; border-radius: 50%; margin-right: 6px;"></span>
                        ${value}${unit}
                    </div>
                `
            },
        },

        // X轴
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: props.data.dates,
            axisLine: {
                lineStyle: {
                    color: '#DCDFE6',
                },
            },
            axisTick: {
                show: false,
            },
            axisLabel: {
                color: '#606266',
                fontSize: 12,
            },
        },

        // Y轴
        yAxis: {
            type: 'value',
            axisLine: {
                show: false,
            },
            axisTick: {
                show: false,
            },
            splitLine: {
                lineStyle: {
                    color: '#EBEEF5',
                    type: 'dashed',
                },
            },
            axisLabel: {
                color: '#909399',
                fontSize: 12,
                formatter: (value) => {
                    if (value >= 1000) {
                        return (value / 1000) + 'k'
                    }
                    return value
                },
            },
        },

        // 数据系列
        series: [
            {
                name: '访问量',
                type: 'line',
                smooth: true,
                symbol: 'circle',
                symbolSize: 8,
                showSymbol: false,
                lineStyle: {
                    width: 3,
                    color: props.color,
                },
                itemStyle: {
                    color: props.color,
                    borderWidth: 2,
                    borderColor: '#fff',
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        {
                            offset: 0,
                            color: props.color + '40', // 25% 透明度
                        },
                        {
                            offset: 1,
                            color: props.color + '05', // 2% 透明度
                        },
                    ]),
                },
                data: props.data.values,
                emphasis: {
                    focus: 'series',
                    showSymbol: true,
                },
            },
        ],

        // 动画配置
        animation: true,
        animationDuration: 1000,
        animationEasing: 'cubicOut',
    }

    chartInstance.setOption(option)
}

/**
 * 处理窗口大小变化
 */
const handleResize = () => {
    if (chartInstance) {
        chartInstance.resize()
    }
}

// 监听数据变化
watch(
    () => props.data,
    () => {
        nextTick(() => {
            if (!chartInstance && chartRef.value) {
                initChart()
            } else if (chartInstance) {
                setChartOption()
            }
        })
    },
    { deep: true }
)

// 组件挂载
onMounted(() => {
    nextTick(() => {
        if (hasData.value) {
            initChart()
        }
    })
})

// 组件卸载
onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    if (chartInstance) {
        chartInstance.dispose()
        chartInstance = null
    }
})
</script>

<style scoped>
.chart-line-container {
    width: 100%;
    height: 350px;
}

.chart-line {
    width: 100%;
    height: 100%;
}

.chart-loading {
    padding: 20px;
    height: 100%;
}

.chart-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

@media (max-width: 768px) {
    .chart-line-container {
        height: 280px;
    }
}
</style>
