/**
 * Demo5 Vue Dashboard 入口
 */
import { createApp } from 'vue'
import {
  ElRow, ElCol, ElCard, ElButton, ElButtonGroup, ElIcon,
  ElTable, ElTableColumn, ElTag
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import Dashboard from './Dashboard.vue'

const app = createApp(Dashboard)

// 注册 Element Plus 组件
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)
app.component('ElCard', ElCard)
app.component('ElButton', ElButton)
app.component('ElButtonGroup', ElButtonGroup)
app.component('ElIcon', ElIcon)
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElTag', ElTag)

// 配置 Axios
import axios from 'axios'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content

app.mount('#demo5-dashboard')