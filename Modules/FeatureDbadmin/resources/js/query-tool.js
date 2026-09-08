/**
 * FeatureDbadmin Query Tool 入口
 *
 * SQL 查询工具 - Monaco Editor + sql-formatter
 */
import { createApp } from 'vue'
import {
  ElSelect,
  ElOption,
  ElButton,
  ElTable,
  ElTableColumn,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElTag,
  ElMessage,
  ElMessageBox,
  ElCard,
  ElRow,
  ElCol,
  ElTabs,
  ElTabPane,
  ElAlert,
  ElEmpty,
  ElDivider,
  ElIcon
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import QueryTool from './QueryTool.vue'

const app = createApp(QueryTool)

// 注册 Element Plus 组件
app.component('ElSelect', ElSelect)
app.component('ElOption', ElOption)
app.component('ElButton', ElButton)
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElDialog', ElDialog)
app.component('ElForm', ElForm)
app.component('ElFormItem', ElFormItem)
app.component('ElInput', ElInput)
app.component('ElTag', ElTag)
app.component('ElCard', ElCard)
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)
app.component('ElTabs', ElTabs)
app.component('ElTabPane', ElTabPane)
app.component('ElAlert', ElAlert)
app.component('ElEmpty', ElEmpty)
app.component('ElDivider', ElDivider)
app.component('ElIcon', ElIcon)

// 配置 Axios
import axios from 'axios'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || ''

// 挂载到 DOM 容器
app.mount('#featuredbadmin-query-tool')
