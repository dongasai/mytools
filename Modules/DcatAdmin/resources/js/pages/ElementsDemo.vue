<template>
  <div class="elements-demo">
    <el-page-header @back="goBack" content="Element Plus 组件演示" />

    <el-tabs v-model="activeTab" class="demo-tabs">
      <!-- 基础组件 -->
      <el-tab-pane label="基础组件" name="basic">
        <BasicComponents />
      </el-tab-pane>

      <!-- 表单组件 -->
      <el-tab-pane label="表单组件" name="form">
        <FormComponents />
      </el-tab-pane>

      <!-- 数据展示 -->
      <el-tab-pane label="数据展示" name="data">
        <DataComponents />
      </el-tab-pane>

      <!-- 通知组件 -->
      <el-tab-pane label="通知组件" name="notice">
        <NoticeComponents />
      </el-tab-pane>

      <!-- 导航组件 -->
      <el-tab-pane label="导航组件" name="navigation">
        <NavigationComponents />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import BasicComponents from './components/BasicComponents.vue'
import FormComponents from './components/FormComponents.vue'
import DataComponents from './components/DataComponents.vue'
import NoticeComponents from './components/NoticeComponents.vue'
import NavigationComponents from './components/NavigationComponents.vue'

const activeTab = ref('basic')

const goBack = () => {
  window.history.back()
}
</script>

<style scoped>
.elements-demo {
  padding: 20px;
}

.demo-tabs {
  margin-top: 20px;
}
</style>