<template>
  <div class="component-section">
    <h3>Table 表格</h3>
    <el-table :data="tableData" style="width: 100%">
      <el-table-column prop="date" label="日期" width="180" />
      <el-table-column prop="name" label="姓名" width="180" />
      <el-table-column prop="address" label="地址" />
      <el-table-column label="操作" width="200">
        <template #default="scope">
          <el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
          <el-button size="small" type="danger" @click="handleDelete(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <h3>Tag 标签</h3>
    <el-row :gutter="20" class="demo-row">
      <el-tag>标签一</el-tag>
      <el-tag type="success">标签二</el-tag>
      <el-tag type="info">标签三</el-tag>
      <el-tag type="warning">标签四</el-tag>
      <el-tag type="danger">标签五</el-tag>
      <el-tag closable @close="handleTagClose">可关闭标签</el-tag>
    </el-row>

    <h3>Progress 进度条</h3>
    <el-row :gutter="20" class="demo-row" style="flex-direction: column; gap: 20px">
      <el-progress :percentage="50" />
      <el-progress :percentage="80" color="#67C23A" />
      <el-progress :percentage="percentage" :color="customColor" />
      <el-button-group>
        <el-button @click="decrease">-10</el-button>
        <el-button @click="increase">+10</el-button>
      </el-button-group>
    </el-row>

    <h3>Badge 标记</h3>
    <el-row :gutter="20" class="demo-row">
      <el-badge :value="12" class="item">
        <el-button>评论</el-button>
      </el-badge>
      <el-badge :value="3" class="item">
        <el-button>回复</el-button>
      </el-badge>
      <el-badge :value="1" class="item" type="primary">
        <el-button>评论</el-button>
      </el-badge>
      <el-badge :value="200" :max="99" class="item">
        <el-button>评论</el-button>
      </el-badge>
      <el-badge is-dot class="item">查询</el-badge>
    </el-row>

    <h3>Card 卡片</h3>
    <el-row :gutter="20" class="demo-row">
      <el-card class="box-card" style="width: 300px">
        <template #header>
          <div class="card-header">
            <span>卡片名称</span>
            <el-button class="button" text>操作按钮</el-button>
          </div>
        </template>
        <div v-for="o in 4" :key="o" class="text item">
          {{ '列表内容 ' + o }}
        </div>
      </el-card>
    </el-row>

    <h3>Collapse 折叠面板</h3>
    <el-collapse v-model="activeNames" accordion>
      <el-collapse-item title="一致性 Consistency" name="1">
        <div>与现实生活一致：与现实生活的流程、逻辑保持一致，遵循用户习惯的语言和概念；</div>
      </el-collapse-item>
      <el-collapse-item title="反馈 Feedback" name="2">
        <div>控制反馈：通过界面样式和交互动效让用户可以清晰的感知自己的操作；</div>
      </el-collapse-item>
      <el-collapse-item title="效率 Efficiency" name="3">
        <div>简化流程：设计简洁直观的操作流程；</div>
      </el-collapse-item>
    </el-collapse>

    <h3>Tabs 标签页</h3>
    <el-tabs v-model="activeTab" type="card">
      <el-tab-pane label="用户管理" name="first">用户管理</el-tab-pane>
      <el-tab-pane label="配置管理" name="second">配置管理</el-tab-pane>
      <el-tab-pane label="角色管理" name="third">角色管理</el-tab-pane>
      <el-tab-pane label="定时任务补偿" name="fourth">定时任务补偿</el-tab-pane>
    </el-tabs>

    <h3>Empty 空状态</h3>
    <el-empty description="暂无数据" />
  </div>
</template>

<script setup>
import { ref, getCurrentInstance } from 'vue'

const { proxy } = getCurrentInstance()
const $message = proxy.$message

const tableData = ref([
  { date: '2016-05-03', name: 'Tom', address: 'No. 189, Grove St, Los Angeles' },
  { date: '2016-05-02', name: 'Jack', address: 'No. 189, Grove St, Los Angeles' },
  { date: '2016-05-04', name: 'Lucy', address: 'No. 189, Grove St, Los Angeles' },
  { date: '2016-05-01', name: 'Jerry', address: 'No. 189, Grove St, Los Angeles' }
])

const percentage = ref(20)
const customColor = ref('#409EFF')

const increase = () => {
  percentage.value += 10
  if (percentage.value > 100) {
    percentage.value = 100
  }
}

const decrease = () => {
  percentage.value -= 10
  if (percentage.value < 0) {
    percentage.value = 0
  }
}

const handleEdit = (row) => {
  $message.info(`编辑: ${row.name}`)
}

const handleDelete = (row) => {
  $message.warning(`删除: ${row.name}`)
}

const handleTagClose = () => {
  $message.info('标签关闭')
}

const activeNames = ref('1')
const activeTab = ref('first')
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

.item {
  margin-right: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.text {
  font-size: 14px;
}

.item {
  margin-bottom: 18px;
}
</style>