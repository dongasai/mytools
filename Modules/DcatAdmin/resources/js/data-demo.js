import { createVueApp, mountApp } from './app.js'
import DataDemo from './pages/demos/DataDemo.vue'

const app = createVueApp(DataDemo)
mountApp(app, '#vue-data-demo')
