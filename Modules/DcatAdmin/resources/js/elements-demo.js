/**
 * Element Plus 组件演示入口
 */

import { createVueApp, mountApp } from './app.js'
import ElementsDemo from './pages/ElementsDemo.vue'

const app = createVueApp(ElementsDemo)
mountApp(app, '#vue-elements-demo')