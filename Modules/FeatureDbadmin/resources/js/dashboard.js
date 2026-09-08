/**
 * FeatureDbadmin Dashboard 入口
 *
 * 数据库管理仪表盘页面
 */
import { createApp } from 'vue'
import {
    ElRow,
    ElCol,
    ElCard,
    ElStatistic,
    ElButton,
    ElTable,
    ElTableColumn,
    ElTag,
    ElEmpty
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import axios from 'axios'
import Dashboard from './Dashboard.vue'

const app = createApp(Dashboard)

// 注册 Element Plus 组件
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)
app.component('ElCard', ElCard)
app.component('ElStatistic', ElStatistic)
app.component('ElButton', ElButton)
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElTag', ElTag)
app.component('ElEmpty', ElEmpty)

// 配置 Axios CSRF Token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content

// 挂载到 DOM 容器
app.mount('#featuredbadmin-dashboard')
