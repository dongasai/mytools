/**
 * 仪表盘页面入口
 *
 * @file dashboard.js
 * @description 极简入口文件，仅导入配置和组件
 */

import { createVueApp, mountApp } from './app.js'
import Dashboard from './pages/Dashboard.vue'

// 创建并挂载应用
const app = createVueApp(Dashboard)
mountApp(app, '#vue-dashboard')