/**
 * API 请求封装
 * FeatureDbadmin 模块 - Axios 实例配置
 *
 * 功能:
 * - 封装 axios 实例
 * - 自动从 meta 标签添加 CSRF token
 * - 统一错误处理（使用 ElMessage）
 */

import axios from 'axios'
import { ElMessage } from 'element-plus'

/**
 * 从 meta 标签获取 CSRF Token
 * @returns {string|null} CSRF Token 或 null
 */
const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]')
    return meta ? meta.getAttribute('content') : null
}

/**
 * Axios 实例配置
 * @type {import('axios').AxiosInstance}
 */
const api = axios.create({
    baseURL: '/admin/featuredbadmin',
    timeout: 30000,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})

/**
 * 请求拦截器
 * - 自动添加 CSRF Token
 * - 设置请求头
 */
api.interceptors.request.use(
    (config) => {
        const csrfToken = getCsrfToken()
        if (csrfToken) {
            config.headers['X-CSRF-TOKEN'] = csrfToken
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

/**
 * 响应拦截器
 * - 统一错误处理
 * - 自动提示错误信息
 */
api.interceptors.response.use(
    (response) => {
        return response
    },
    (error) => {
        const { response, message } = error

        // 网络错误
        if (!response) {
            ElMessage.error('网络连接失败，请检查网络')
            return Promise.reject(error)
        }

        // 根据 HTTP 状态码处理
        const status = response.status
        const errorMessage = response.data?.message || message || '请求失败'

        switch (status) {
            case 400:
                ElMessage.error(`请求参数错误: ${errorMessage}`)
                break
            case 401:
                ElMessage.error('登录已过期，请重新登录')
                break
            case 403:
                ElMessage.error('没有权限执行此操作')
                break
            case 404:
                ElMessage.error('请求的资源不存在')
                break
            case 405:
                ElMessage.error('请求方法不被允许')
                break
            case 422:
                // 验证错误，优先显示具体错误信息
                const validationErrors = response.data?.errors
                if (validationErrors) {
                    const firstError = Object.values(validationErrors).flat()[0]
                    ElMessage.error(firstError || '输入数据验证失败')
                } else {
                    ElMessage.error(`数据验证失败: ${errorMessage}`)
                }
                break
            case 429:
                ElMessage.error('请求过于频繁，请稍后再试')
                break
            case 500:
            case 502:
            case 503:
            case 504:
                ElMessage.error(`服务器错误: ${errorMessage}`)
                break
            default:
                ElMessage.error(errorMessage)
        }

        return Promise.reject(error)
    }
)

/**
 * 导出 axios 实例
 * 使用方式:
 * import api from './utils/api'
 * const res = await api.get('/connections')
 * const res = await api.post('/connections/create', data)
 */
export default api

/**
 * 导出原始 axios（用于需要自定义配置的场景）
 */
export { axios }
