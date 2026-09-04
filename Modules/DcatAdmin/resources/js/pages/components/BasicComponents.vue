<template>
  <div class="component-section">
    <h3>Button 按钮</h3>
    <el-row :gutter="20" class="demo-row">
      <el-button>默认按钮</el-button>
      <el-button type="primary">主要按钮</el-button>
      <el-button type="success">成功按钮</el-button>
      <el-button type="warning">警告按钮</el-button>
      <el-button type="danger">危险按钮</el-button>
      <el-button type="info">信息按钮</el-button>
    </el-row>

    <el-row :gutter="20" class="demo-row">
      <el-button type="primary" plain>朴素按钮</el-button>
      <el-button type="primary" round>圆角按钮</el-button>
      <el-button type="primary" circle :icon="Plus" />
      <el-button type="primary" loading>加载中</el-button>
      <el-button type="primary" disabled>禁用</el-button>
    </el-row>

    <h3>Icon 图标</h3>
    <el-row :gutter="20" class="demo-row">
      <el-icon :size="30"><Edit /></el-icon>
      <el-icon :size="30" color="#409EFF"><Share /></el-icon>
      <el-icon :size="30" color="#67C23A"><Delete /></el-icon>
      <el-icon :size="30" color="#E6A23C"><Search /></el-icon>
      <el-icon :size="30" color="#F56C6C"><Setting /></el-icon>
    </el-row>

    <h3>Link 文字链接</h3>
    <el-row :gutter="20" class="demo-row">
      <el-link href="https://element-plus.org" target="_blank">默认链接</el-link>
      <el-link type="primary">主要链接</el-link>
      <el-link type="success">成功链接</el-link>
      <el-link type="warning">警告链接</el-link>
      <el-link type="danger">危险链接</el-link>
      <el-link type="info">信息链接</el-link>
    </el-row>

    <h3>Layout 布局</h3>
    <el-row :gutter="20" class="demo-row">
      <el-col :span="6">
        <div class="grid-content bg-purple">col-6</div>
      </el-col>
      <el-col :span="6">
        <div class="grid-content bg-purple-light">col-6</div>
      </el-col>
      <el-col :span="6">
        <div class="grid-content bg-purple">col-6</div>
      </el-col>
      <el-col :span="6">
        <div class="grid-content bg-purple-light">col-6</div>
      </el-col>
    </el-row>

    <h3>Container 布局容器</h3>
    <el-container class="demo-container">
      <el-header>Header</el-header>
      <el-container>
        <el-aside width="200px">Aside</el-aside>
        <el-main>Main</el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { Plus, Edit, Share, Delete, Search, Setting } from '@element-plus/icons-vue'
</script>

<style scoped>
.component-section {
  padding: 20px 0;
}

h3 {
  margin: 20px 0 10px;
  color: #303133;
  border-bottom: 1px solid #EBEEF5;
  padding-bottom: 10px;
}

.demo-row {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.grid-content {
  border-radius: 4px;
  min-height: 36px;
  text-align: center;
  line-height: 36px;
  color: white;
}

.bg-purple {
  background: #99a9bf;
}

.bg-purple-light {
  background: #b3c0d1;
}

.demo-container {
  border: 1px solid #eee;
  height: 200px;
}

.el-header, .el-footer {
  background-color: #b3c0d1;
  color: #333;
  text-align: center;
  line-height: 60px;
}

.el-aside {
  background-color: #d3dce6;
  color: #333;
  text-align: center;
  line-height: 200px;
}

.el-main {
  background-color: #e9eef3;
  color: #333;
  text-align: center;
  line-height: 160px;
}
</style>