<template>
  <div class="demo-page">
    <h1>基础组件演示</h1>

    <section class="demo-section">
      <h2>Button 按钮</h2>
      <p>按钮组件提供了多种类型、尺寸和状态</p>

      <h3>按钮类型</h3>
      <el-row :gutter="10" class="demo-row">
        <el-button>默认按钮</el-button>
        <el-button type="primary">主要按钮</el-button>
        <el-button type="success">成功按钮</el-button>
        <el-button type="warning">警告按钮</el-button>
        <el-button type="danger">危险按钮</el-button>
        <el-button type="info">信息按钮</el-button>
      </el-row>

      <h3>朴素按钮</h3>
      <el-row :gutter="10" class="demo-row">
        <el-button type="primary" plain>主要按钮</el-button>
        <el-button type="success" plain>成功按钮</el-button>
        <el-button type="warning" plain>警告按钮</el-button>
        <el-button type="danger" plain>危险按钮</el-button>
      </el-row>

      <h3>按钮尺寸</h3>
      <el-row :gutter="10" class="demo-row">
        <el-button size="large">大型按钮</el-button>
        <el-button>默认按钮</el-button>
        <el-button size="small">小型按钮</el-button>
      </el-row>

      <h3>按钮形状</h3>
      <el-row :gutter="10" class="demo-row">
        <el-button type="primary" round>圆角按钮</el-button>
        <el-button type="primary" :icon="Search" circle />
        <el-button type="primary" :icon="Edit" circle />
        <el-button type="primary" :icon="Delete" circle />
      </el-row>

      <h3>按钮状态</h3>
      <el-row :gutter="10" class="demo-row">
        <el-button type="primary" loading>加载中</el-button>
        <el-button type="primary" disabled>禁用状态</el-button>
      </el-row>
    </section>

    <section class="demo-section">
      <h2>Icon 图标</h2>
      <p>Element Plus 提供了一套常用的图标集合</p>

      <el-row :gutter="20" class="demo-row">
        <div class="icon-item" v-for="icon in icons" :key="icon.name">
          <el-icon :size="30">
            <component :is="icon.component" />
          </el-icon>
          <div>{{ icon.name }}</div>
        </div>
      </el-row>
    </section>

    <section class="demo-section">
      <h2>Layout 布局</h2>
      <p>通过 row 和 col 组件，使用 24 分栏布局</p>

      <h3>基础布局</h3>
      <el-row :gutter="20">
        <el-col :span="24">
          <div class="grid-content bg-purple-dark">col-24</div>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="12">
          <div class="grid-content bg-purple">col-12</div>
        </el-col>
        <el-col :span="12">
          <div class="grid-content bg-purple-light">col-12</div>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="8">
          <div class="grid-content bg-purple">col-8</div>
        </el-col>
        <el-col :span="8">
          <div class="grid-content bg-purple-light">col-8</div>
        </el-col>
        <el-col :span="8">
          <div class="grid-content bg-purple">col-8</div>
        </el-col>
      </el-row>
      <el-row :gutter="20">
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

      <h3>响应式布局</h3>
      <el-row :gutter="10">
        <el-col :xs="24" :sm="12" :md="8" :lg="6">
          <div class="grid-content bg-purple">响应式</div>
        </el-col>
        <el-col :xs="24" :sm="12" :md="8" :lg="6">
          <div class="grid-content bg-purple-light">响应式</div>
        </el-col>
        <el-col :xs="24" :sm="12" :md="8" :lg="6">
          <div class="grid-content bg-purple">响应式</div>
        </el-col>
        <el-col :xs="24" :sm="12" :md="8" :lg="6">
          <div class="grid-content bg-purple-light">响应式</div>
        </el-col>
      </el-row>
    </section>

    <section class="demo-section">
      <h2>Container 布局容器</h2>
      <p>用于布局的容器组件，方便快速搭建页面的基本结构</p>

      <el-container style="height: 300px; border: 1px solid #eee">
        <el-header style="background-color: #b3c0d1">
          <div style="line-height: 60px">Header</div>
        </el-header>
        <el-container>
          <el-aside width="200px" style="background-color: #d3dce6">
            <div style="line-height: 200px; text-align: center">Aside</div>
          </el-aside>
          <el-main style="background-color: #e9eef3">
            <div style="line-height: 200px; text-align: center">Main</div>
          </el-main>
        </el-container>
      </el-container>
    </section>
  </div>
</template>

<script setup>
import { Search, Edit, Delete, Check, Plus, Upload, Download, Setting, User, Star, Phone, Message, House } from '@element-plus/icons-vue'
import { shallowRef } from 'vue'

const icons = [
  { name: 'Search', component: shallowRef(Search) },
  { name: 'Edit', component: shallowRef(Edit) },
  { name: 'Delete', component: shallowRef(Delete) },
  { name: 'Check', component: shallowRef(Check) },
  { name: 'Plus', component: shallowRef(Plus) },
  { name: 'Upload', component: shallowRef(Upload) },
  { name: 'Download', component: shallowRef(Download) },
  { name: 'Setting', component: shallowRef(Setting) },
  { name: 'User', component: shallowRef(User) },
  { name: 'Star', component: shallowRef(Star) },
  { name: 'Phone', component: shallowRef(Phone) },
  { name: 'Message', component: shallowRef(Message) },
  { name: 'House', component: shallowRef(House) },
]
</script>

<style scoped>
.demo-page {
  padding: 20px;
  background: white;
}

h1 {
  font-size: 28px;
  margin-bottom: 20px;
  color: #303133;
  border-bottom: 2px solid #409EFF;
  padding-bottom: 10px;
}

.demo-section {
  margin-bottom: 40px;
}

h2 {
  font-size: 22px;
  margin: 30px 0 15px;
  color: #303133;
}

h3 {
  font-size: 16px;
  margin: 20px 0 10px;
  color: #606266;
}

p {
  color: #909399;
  margin-bottom: 15px;
}

.demo-row {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.grid-content {
  border-radius: 4px;
  min-height: 36px;
  text-align: center;
  line-height: 36px;
  color: white;
  margin-bottom: 10px;
}

.bg-purple-dark {
  background: #99a9bf;
}

.bg-purple {
  background: #d3dce6;
}

.bg-purple-light {
  background: #e5e9f2;
}

.icon-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  border: 1px solid #EBEEF5;
  border-radius: 4px;
  cursor: pointer;
}

.icon-item:hover {
  background: #F5F7FA;
}

.icon-item div {
  margin-top: 10px;
  font-size: 14px;
  color: #606266;
}
</style>