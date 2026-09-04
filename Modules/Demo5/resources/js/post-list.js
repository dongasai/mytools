/**
 * Demo5 Vue 文章管理入口
 */
import { createApp } from 'vue'
import {
  ElCard, ElButton, ElTable, ElTableColumn, ElTag,
  ElDialog, ElForm, ElFormItem, ElInput, ElSelect,
  ElOption, ElDatePicker, ElPopconfirm, ElMessage
} from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import PostList from './PostList.vue'

const app = createApp(PostList)

// 注册 Element Plus 组件
app.component('ElCard', ElCard)
app.component('ElButton', ElButton)
app.component('ElTable', ElTable)
app.component('ElTableColumn', ElTableColumn)
app.component('ElTag', ElTag)
app.component('ElDialog', ElDialog)
app.component('ElForm', ElForm)
app.component('ElFormItem', ElFormItem)
app.component('ElInput', ElInput)
app.component('ElSelect', ElSelect)
app.component('ElOption', ElOption)
app.component('ElDatePicker', ElDatePicker)
app.component('ElPopconfirm', ElPopconfirm)

// 配置 Axios
import axios from 'axios'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content

app.mount('#demo5-post-list')