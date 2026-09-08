/**
 * FeatureDbadmin Table Manager 入口
 * 数据库表管理功能
 */
import { createApp } from 'vue'
import {
  ElTable,
  ElTableColumn,
  ElButton,
  ElSelect,
  ElOption,
  ElDialog,
  ElDescriptions,
  ElDescriptionsItem,
  ElTag,
  ElMessage,
  ElLoading,
  ElEmpty,
  ElCard,
  ElRow,
  ElCol
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import TableManager from './TableManager.vue'

const app = createApp(TableManager)

// 注册 Element Plus 组件
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElButton', ElButton)
app.component('ElSelect', ElSelect)
app.component('ElOption', ElOption)
app.component('ElDialog', ElDialog)
app.component('ElDescriptions', ElDescriptions)
app.component('ElDescriptionsItem', ElDescriptionsItem)
app.component('ElTag', ElTag)
app.component('ElEmpty', ElEmpty)
app.component('ElCard', ElCard)
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)

// 配置 Axios
import axios from 'axios'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || ''

// 挂载到 DOM 容器
app.mount('#featuredbadmin-table-manager')
