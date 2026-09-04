import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { resolve } from 'path'
import fs from 'fs'

// 自动发现所有模块的 Vue 入口文件
function discoverModuleEntries() {
    const entries = []
    const modulesPath = 'Modules'

    if (fs.existsSync(modulesPath)) {
        const modules = fs.readdirSync(modulesPath)

        modules.forEach(module => {
            const jsPath = `${modulesPath}/${module}/resources/js`
            if (fs.existsSync(jsPath)) {
                // 查找模块 resources/js 目录下的所有 .js 入口文件
                const files = fs.readdirSync(jsPath)
                files.forEach(file => {
                    if (file.endsWith('.js')) {
                        entries.push(`${jsPath}/${file}`)
                    }
                })
            }
        })
    }

    return entries
}

// 自定义插件：重命名 CSS 文件以匹配模块名（暂时禁用）
function renameCssFiles() {
    return {
        name: 'rename-css-files',
        apply: 'build',
        generateBundle(options, bundle) {
            // 简化方案：直接从文件名推断模块信息
            // 由于 Vite 限制，这个插件暂时禁用
            return
        }
    }
}

export default defineConfig({
    plugins: [
        laravel({
            input: discoverModuleEntries(),
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        renameCssFiles(),
    ],
    server: {
        host: '0.0.0.0', // 允许外部访问
        hmr: {
            host: 'sanniu.l4164.xiaobei.fun', // HMR 主机
        },
        cors: true, // 启用 CORS
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@use "sass:color";`,
            },
        },
    },
    build: {
        // 代码分割配置
        rollupOptions: {
            output: {
                // 自定义入口文件名格式：模块-页面-[hash].js
                entryFileNames: (chunkInfo) => {
                    // 从 chunkInfo.facadeModuleId 解析模块名和页面名
                    // 例如：/path/to/Modules/Demo5/resources/js/dashboard.js
                    const match = chunkInfo.facadeModuleId?.match(/Modules\/([^/]+)\/resources\/js\/([^/]+)\.js/)
                    if (match) {
                        const moduleName = match[1].toLowerCase() // Demo5 -> demo5
                        const pageName = match[2]                  // dashboard
                        return `assets/${moduleName}-${pageName}-[hash].js`
                    }
                    // 默认格式
                    return 'assets/[name]-[hash].js'
                },
                // 自定义 chunk 文件名（vendor、element-plus 等）
                chunkFileNames: 'assets/[name]-[hash].js',
                // 自定义资源文件名（CSS、图片等）
                assetFileNames: 'assets/[name]-[hash].[ext]',
                // 手动分割代码块
                manualChunks: {
                    // Vue 核心
                    'vue-vendor': ['vue'],
                    // Element Plus 按需组件会自动提取
                    'element-plus': ['element-plus'],
                    // ECharts
                    'echarts': ['echarts', 'vue-echarts'],
                    // Axios
                    'axios': ['axios'],
                },
            },
        },
        // 提高 chunk 大小警告限制
        chunkSizeWarningLimit: 1000,
    },
})