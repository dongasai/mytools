/**
 * FeatureDbadmin Data Browser 入口
 * 数据浏览与编辑功能
 */
import { createApp } from 'vue'
import {
  ElTable,
  ElTableColumn,
  ElPagination,
  ElButton,
  ElInput,
  ElDialog,
  ElForm,
  ElFormItem,
  ElSelect,
  ElOption,
  ElMessage,
  ElMessageBox,
  ElTag,
  ElCard,
  ElRow,
  ElCol,
  ElEmpty,
  ElLoading
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import DataBrowser from './DataBrowser.vue'

const app = createApp(DataBrowser)

// 注册 Element Plus 组件
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElPagination', ElPagination)
app.component('ElButton', ElButton)
app.component('ElInput', ElInput)
app.component('ElDialog', ElDialog)
app.component('ElForm', ElForm)
app.component('ElFormItem', ElFormItem)
app.component('ElSelect', ElSelect)
app.component('ElOption', ElOption)
app.component('ElTag', ElTag)
app.component('ElCard', ElCard)
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)
app.component('ElEmpty', ElEmpty)

// 配置 Axios
import axios from 'axios'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || ''

// 挂载到 DOM 容器
app.mount('#featuredbadmin-data-browser')
