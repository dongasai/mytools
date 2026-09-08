/**
 * FeatureDbadmin Connection Manager 入口
 *
 * 数据库连接管理页面
 */
import { createApp } from 'vue'
import {
    ElTable,
    ElTableColumn,
    ElButton,
    ElDialog,
    ElForm,
    ElFormItem,
    ElInput,
    ElSelect,
    ElOption,
    ElSwitch,
    ElMessage,
    ElMessageBox,
    ElTag,
    ElRow,
    ElCol,
    ElCard,
    ElIcon
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import axios from 'axios'
import ConnectionManager from './ConnectionManager.vue'

const app = createApp(ConnectionManager)

// 注册 Element Plus 组件
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElButton', ElButton)
app.component('ElDialog', ElDialog)
app.component('ElForm', ElForm)
app.component('ElFormItem', ElFormItem)
app.component('ElInput', ElInput)
app.component('ElSelect', ElSelect)
app.component('ElOption', ElOption)
app.component('ElSwitch', ElSwitch)
app.component('ElTag', ElTag)
app.component('ElRow', ElRow)
app.component('ElCol', ElCol)
app.component('ElCard', ElCard)
app.component('ElIcon', ElIcon)

// 配置 Axios CSRF Token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content

// 挂载到 DOM 容器
app.mount('#featuredbadmin-connection-manager')
