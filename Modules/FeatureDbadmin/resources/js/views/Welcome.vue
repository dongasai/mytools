<template>
  <div class="welcome-page">
    <div class="welcome-content">
      <el-icon :size="80" color="#409eff"><Coin /></el-icon>
      <h2>数据库管理员工具</h2>
      <p>基于 DBeaver 设计理念</p>
      <div class="quick-actions">
        <el-button type="primary" size="large" @click="openConnections">
          <el-icon><Coin /></el-icon>
          管理连接
        </el-button>
      </div>

      <div class="features">
        <div class="feature-item">
          <el-icon :size="32"><DataLine /></el-icon>
          <h3>数据浏览</h3>
          <p>查看和编辑表数据</p>
        </div>
        <div class="feature-item">
          <el-icon :size="32"><Search /></el-icon>
          <h3>SQL 编辑器</h3>
          <p>执行自定义 SQL 查询</p>
        </div>
        <div class="feature-item">
          <el-icon :size="32"><Grid /></el-icon>
          <h3>表结构</h3>
          <p>查看表结构和索引</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { Coin, DataLine, Search, Grid } from '@element-plus/icons-vue'

const router = useRouter()

const openConnections = () => {
  router.push('/connections')
}
</script>

<style scoped>
.welcome-page {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.welcome-content {
  text-align: center;
  color: #fff;
}

.welcome-content h2 {
  margin-top: 20px;
  font-size: 32px;
  font-weight: 600;
}

.welcome-content p {
  margin-top: 10px;
  font-size: 16px;
  opacity: 0.9;
}

.quick-actions {
  margin-top: 30px;
}

.quick-actions .el-button {
  font-size: 16px;
  padding: 12px 32px;
}

.features {
  margin-top: 60px;
  display: flex;
  gap: 40px;
  justify-content: center;
}

.feature-item {
  text-align: center;
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  backdrop-filter: blur(10px);
  min-width: 150px;
}

.feature-item h3 {
  margin-top: 12px;
  font-size: 16px;
  font-weight: 500;
}

.feature-item p {
  margin-top: 6px;
  font-size: 13px;
  opacity: 0.8;
}
</style>