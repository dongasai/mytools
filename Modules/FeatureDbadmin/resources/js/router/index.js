import { createRouter, createWebHistory } from 'vue-router';
import { Odometer, Link, Grid, DataAnalysis, Search } from '@element-plus/icons-vue';

/**
 * FeatureDbadmin 路由配置
 */
const routes = [
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: {
            title: '仪表盘',
            icon: Odometer,
        },
    },
    {
        path: '/connections',
        name: 'Connections',
        component: () => import('../views/ConnectionManager.vue'),
        meta: {
            title: '连接管理',
            icon: Link,
        },
    },
    {
        path: '/tables',
        name: 'Tables',
        component: () => import('../views/TableManager.vue'),
        meta: {
            title: '表管理',
            icon: Grid,
        },
    },
    {
        path: '/data',
        name: 'DataBrowser',
        component: () => import('../views/DataBrowser.vue'),
        meta: {
            title: '数据浏览',
            icon: DataAnalysis,
        },
    },
    {
        path: '/query',
        name: 'QueryTool',
        component: () => import('../views/QueryTool.vue'),
        meta: {
            title: 'SQL查询工具',
            icon: Search,
        },
    },
];

/**
 * 创建路由实例
 */
const router = createRouter({
    history: createWebHistory('/admin/featuredbadmin/'),
    routes,
});

/**
 * 路由守卫 - 更新页面标题
 */
router.beforeEach((to, from, next) => {
    const defaultTitle = 'FeatureDbadmin';
    const pageTitle = to.meta?.title;
    document.title = pageTitle ? `${pageTitle} - ${defaultTitle}` : defaultTitle;
    next();
});

export default router;
