import { createRouter, createWebHashHistory } from 'vue-router'
import { Coin, DataLine, Search, Grid } from '@element-plus/icons-vue'

/**
 * FeatureDbadmin 路由配置
 */
const routes = [
    {
        path: '/',
        name: 'Welcome',
        component: () => import('../views/Welcome.vue'),
        meta: {
            title: '欢迎',
            icon: Coin,
        },
    },
    {
        path: '/connections',
        name: 'Connections',
        component: () => import('../views/ConnectionManager.vue'),
        meta: {
            title: '连接管理',
            icon: Coin,
        },
    },
    {
        path: '/tables/:connectionId',
        name: 'TableList',
        component: () => import('../views/TableList.vue'),
        meta: {
            title: '表列表',
            icon: Grid,
        },
        props: true,
    },
    {
        path: '/data/:connectionId/:tableName',
        name: 'DataBrowser',
        component: () => import('../views/DataBrowser.vue'),
        meta: {
            title: '数据浏览',
            icon: DataLine,
        },
        props: true,
    },
    {
        path: '/data/:connectionId/:tableName/edit/:rowId?',
        name: 'DataEditor',
        component: () => import('../views/DataEditor.vue'),
        meta: {
            title: '数据编辑',
            icon: DataLine,
        },
        props: true,
    },
    {
        path: '/data/:connectionId/:tableName/view/:rowId',
        name: 'DataViewer',
        component: () => import('../views/DataViewer.vue'),
        meta: {
            title: '数据详情',
            icon: DataLine,
        },
        props: true,
    },
    {
        path: '/query/:connectionId',
        name: 'QueryTool',
        component: () => import('../views/QueryTool.vue'),
        meta: {
            title: 'SQL编辑器',
            icon: Search,
        },
        props: true,
    },
    {
        path: '/structure/:connectionId/:tableName',
        name: 'TableStructure',
        component: () => import('../views/TableStructure.vue'),
        meta: {
            title: '表结构',
            icon: Grid,
        },
        props: true,
    },
]

/**
 * 创建路由实例
 *
 * 使用 Hash 模式，base 为空（Vue 是独立系统）
 */
const router = createRouter({
    history: createWebHashHistory(),
    routes,
})

export default router