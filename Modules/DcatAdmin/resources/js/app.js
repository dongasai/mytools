/**
 * Vue 应用共享配置
 *
 * @file app.js
 * @description 统一的 Vue 应用配置，所有页面共享
 */

import { createApp } from 'vue'

// Element Plus 按需导入
import {
    ElRow,
    ElCol,
    ElCard,
    ElButton,
    ElButtonGroup,
    ElRadioGroup,
    ElRadioButton,
    ElRadio,
    ElCheckbox,
    ElCheckboxGroup,
    ElIcon,
    ElMessage,
    ElSkeleton,
    ElEmpty,
    ElInput,
    ElSelect,
    ElOption,
    ElSwitch,
    ElDatePicker,
    ElUpload,
    ElForm,
    ElFormItem,
    ElTable,
    ElTableColumn,
    ElTag,
    ElProgress,
    ElBadge,
    ElCollapse,
    ElCollapseItem,
    ElTabs,
    ElTabPane,
    ElAlert,
    ElDialog,
    ElDrawer,
    ElPopover,
    ElTooltip,
    ElPopconfirm,
    ElMenu,
    ElMenuItem,
    ElSubMenu,
    ElBreadcrumb,
    ElBreadcrumbItem,
    ElPageHeader,
    ElDropdown,
    ElDropdownMenu,
    ElDropdownItem,
    ElSteps,
    ElStep,
    ElPagination,
    ElBacktop,
    ElContainer,
    ElHeader,
    ElFooter,
    ElMain,
    ElAside,
    ElLink,
} from 'element-plus'

// Element Plus 样式
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'

// Element Plus 图标（按需导入）
import {
    UserFilled,
    ShoppingCartFull,
    Money,
    View,
    Refresh,
    Download,
    Setting,
    ArrowUp,
    ArrowDown,
    Plus,
    Edit,
    Share,
    Delete,
    Search,
} from '@element-plus/icons-vue'

// 中文语言包
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'

// Axios
import axios from 'axios'

/**
 * 创建配置好的 Vue 应用
 *
 * @param {Object} component - Vue 组件
 * @returns {Object} 配置好的 Vue 应用实例
 */
export function createVueApp(component) {
    // 创建应用
    const app = createApp(component)

    // 按需注册 Element Plus 组件
    const components = [
        ElRow,
        ElCol,
        ElCard,
        ElButton,
        ElButtonGroup,
        ElRadioGroup,
        ElRadioButton,
        ElRadio,
        ElCheckbox,
        ElCheckboxGroup,
        ElIcon,
        ElSkeleton,
        ElEmpty,
        ElInput,
        ElSelect,
        ElOption,
        ElSwitch,
        ElDatePicker,
        ElUpload,
        ElForm,
        ElFormItem,
        ElTable,
        ElTableColumn,
        ElTag,
        ElProgress,
        ElBadge,
        ElCollapse,
        ElCollapseItem,
        ElTabs,
        ElTabPane,
        ElAlert,
        ElDialog,
        ElDrawer,
        ElPopover,
        ElTooltip,
        ElPopconfirm,
        ElMenu,
        ElMenuItem,
        ElSubMenu,
        ElBreadcrumb,
        ElBreadcrumbItem,
        ElPageHeader,
        ElDropdown,
        ElDropdownMenu,
        ElDropdownItem,
        ElSteps,
        ElStep,
        ElPagination,
        ElBacktop,
        ElContainer,
        ElHeader,
        ElFooter,
        ElMain,
        ElAside,
        ElLink,
    ]

    components.forEach(comp => {
        app.component(comp.name, comp)
    })

    // 注册使用的图标
    const icons = {
        UserFilled,
        ShoppingCartFull,
        Money,
        View,
        Refresh,
        Download,
        Setting,
        ArrowUp,
        ArrowDown,
        Plus,
        Edit,
        Share,
        Delete,
        Search,
        ArrowDown: ArrowDown,
    }

    Object.entries(icons).forEach(([name, component]) => {
        app.component(name, component)
    })

    // 配置 Axios
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
    axios.defaults.headers.common['Accept'] = 'application/json'

    // 从 meta 标签获取 CSRF Token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token
    }

    // 全局属性
    app.config.globalProperties.$axios = axios
    app.config.globalProperties.$message = ElMessage
    app.config.globalProperties.$ELEMENT = { locale: zhCn }

    return app
}

/**
 * 自动挂载 Vue 应用
 *
 * @param {Object} app - Vue 应用实例
 * @param {string} selector - 挂载选择器（默认 '#vue-app'）
 */
export function mountApp(app, selector = '#vue-app') {
    const mountElement = document.querySelector(selector)
    if (mountElement) {
        app.mount(selector)
    } else {
        console.warn(`未找到挂载点 ${selector}，请确保 DOM 元素存在`)
    }
}

export default { createVueApp, mountApp }