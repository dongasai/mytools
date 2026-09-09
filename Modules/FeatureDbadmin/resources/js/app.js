/**
 * FeatureDbadmin Vue 3 入口文件
 *
 * 提供数据库管理员工具的单页面应用
 */

// Vue 3
import { createApp } from 'vue'

// Vue Router
import router from './router/index.js'

// Pinia
import { createPinia } from 'pinia'

// Element Plus
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'

// Element Plus 图标
import * as ElementPlusIconsVue from '@element-plus/icons-vue'

// Axios
import axios from 'axios'

// 根组件
import App from './App.vue'

/**
 * 配置 Axios
 * 设置 CSRF Token
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
}

/**
 * 创建 Vue 应用实例
 */
const app = createApp(App)

/**
 * 注册 Pinia 状态管理
 */
const pinia = createPinia()
app.use(pinia)

/**
 * 注册 Vue Router
 */
app.use(router)

/**
 * 注册 Element Plus UI 组件库
 */
app.use(ElementPlus)

/**
 * 注册所有 Element Plus 图标组件
 */
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
    app.component(key, component)
}

/**
 * 挂载应用到 DOM
 */
app.mount('#featuredbadmin-app')
