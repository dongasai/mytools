import { createVueApp, mountApp } from './app.js'
import BasicDemo from './pages/demos/BasicDemo.vue'

const app = createVueApp(BasicDemo)
mountApp(app, '#vue-basic-demo')
