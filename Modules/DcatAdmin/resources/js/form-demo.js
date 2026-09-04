import { createVueApp, mountApp } from './app.js'
import FormDemo from './pages/demos/FormDemo.vue'

const app = createVueApp(FormDemo)
mountApp(app, '#vue-form-demo')
