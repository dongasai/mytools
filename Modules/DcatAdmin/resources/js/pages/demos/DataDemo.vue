<template>
  <div class="demo-page">
    <h1>数据展示组件演示</h1>

    <section class="demo-section">
      <h2>Table 表格</h2>

      <h3>基础表格</h3>
      <el-table :data="tableData" style="width: 100%">
        <el-table-column prop="date" label="日期" width="180" />
        <el-table-column prop="name" label="姓名" width="180" />
        <el-table-column prop="address" label="地址" />
      </el-table>

      <h3>带斑马纹表格</h3>
      <el-table :data="tableData" stripe style="width: 100%">
        <el-table-column prop="date" label="日期" width="180" />
        <el-table-column prop="name" label="姓名" width="180" />
        <el-table-column prop="address" label="地址" />
      </el-table>

      <h3>带边框表格</h3>
      <el-table :data="tableData" border style="width: 100%">
        <el-table-column prop="date" label="日期" width="180" />
        <el-table-column prop="name" label="姓名" width="180" />
        <el-table-column prop="address" label="地址" />
      </el-table>

      <h3>带操作列</h3>
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
    </section>

    <section class="demo-section">
      <h2>Tag 标签</h2>

      <h3>基础用法</h3>
      <el-row class="demo-row">
        <el-tag>标签一</el-tag>
        <el-tag type="success">标签二</el-tag>
        <el-tag type="info">标签三</el-tag>
        <el-tag type="warning">标签四</el-tag>
        <el-tag type="danger">标签五</el-tag>
      </el-row>

      <h3>可移除标签</h3>
      <el-row class="demo-row">
        <el-tag v-for="tag in tags" :key="tag" closable @close="handleClose(tag)">
          {{ tag }}
        </el-tag>
      </el-row>

      <h3>动态编辑标签</h3>
      <el-row class="demo-row">
        <el-tag
          v-for="tag in dynamicTags"
          :key="tag"
          closable
          @close="handleDynamicClose(tag)"
        >
          {{ tag }}
        </el-tag>
        <el-input
          v-if="inputVisible"
          ref="inputRef"
          v-model="inputValue"
          size="small"
          @keyup.enter="handleInputConfirm"
          @blur="handleInputConfirm"
          style="width: 100px"
        />
        <el-button v-else size="small" @click="showInput">+ New Tag</el-button>
      </el-row>

      <h3>不同尺寸</h3>
      <el-row class="demo-row">
        <el-tag size="large">默认标签</el-tag>
        <el-tag size="default">中等标签</el-tag>
        <el-tag size="small">小型标签</el-tag>
      </el-row>
    </section>

    <section class="demo-section">
      <h2>Progress 进度条</h2>

      <h3>线性进度条</h3>
      <el-progress :percentage="50" />
      <el-progress :percentage="80" color="#67C23A" style="margin-top: 10px" />
      <el-progress :percentage="100" status="success" style="margin-top: 10px" />
      <el-progress :percentage="30" status="warning" style="margin-top: 10px" />
      <el-progress :percentage="70" status="exception" style="margin-top: 10px" />

      <h3>自定义颜色</h3>
      <el-progress :percentage="percentage" :color="customColor" style="margin-top: 10px" />
      <el-button-group style="margin-top: 10px">
        <el-button @click="decrease">-10</el-button>
        <el-button @click="increase">+10</el-button>
      </el-button-group>

      <h3>环形进度条</h3>
      <el-row class="demo-row">
        <el-progress type="circle" :percentage="25" />
        <el-progress type="circle" :percentage="75" color="#67C23A" />
        <el-progress type="circle" :percentage="100" status="success" />
      </el-row>

      <h3>仪表盘形进度条</h3>
      <el-progress type="dashboard" :percentage="percentage" :color="customColor" />
    </section>

    <section class="demo-section">
      <h2>Badge 标记</h2>

      <h3>基础用法</h3>
      <el-row class="demo-row">
        <el-badge :value="12" class="item">
          <el-button size="small">评论</el-button>
        </el-badge>
        <el-badge :value="3" class="item">
          <el-button size="small">回复</el-button>
        </el-badge>
        <el-badge :value="1" class="item" type="primary">
          <el-button size="small">评论</el-button>
        </el-badge>
        <el-badge :value="200" :max="99" class="item">
          <el-button size="small">评论</el-button>
        </el-badge>
        <el-badge is-dot class="item">查询</el-badge>
      </el-row>
    </section>

    <section class="demo-section">
      <h2>Card 卡片</h2>

      <h3>基础用法</h3>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-card class="box-card">
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
        </el-col>
        <el-col :span="8">
          <el-card>
            <template #header>
              <div class="card-header">
                <span>卡片名称</span>
              </div>
            </template>
            <el-empty description="暂无数据" :image-size="60" />
          </el-card>
        </el-col>
      </el-row>
    </section>

    <section class="demo-section">
      <h2>Collapse 折叠面板</h2>

      <h3>基础用法</h3>
      <el-collapse v-model="activeNames">
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

      <h3>手风琴效果</h3>
      <el-collapse v-model="activeName" accordion>
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
    </section>

    <section class="demo-section">
      <h2>Tabs 标签页</h2>

      <h3>基础用法</h3>
      <el-tabs v-model="activeTab">
        <el-tab-pane label="用户管理" name="first">用户管理</el-tab-pane>
        <el-tab-pane label="配置管理" name="second">配置管理</el-tab-pane>
        <el-tab-pane label="角色管理" name="third">角色管理</el-tab-pane>
        <el-tab-pane label="定时任务补偿" name="fourth">定时任务补偿</el-tab-pane>
      </el-tabs>

      <h3>卡片风格</h3>
      <el-tabs v-model="activeTab2" type="card">
        <el-tab-pane label="用户管理" name="first">用户管理</el-tab-pane>
        <el-tab-pane label="配置管理" name="second">配置管理</el-tab-pane>
        <el-tab-pane label="角色管理" name="third">角色管理</el-tab-pane>
      </el-tabs>

      <h3>卡片风格</h3>
      <el-tabs v-model="activeTab3" type="border-card">
        <el-tab-pane label="用户管理" name="first">用户管理</el-tab-pane>
        <el-tab-pane label="配置管理" name="second">配置管理</el-tab-pane>
        <el-tab-pane label="角色管理" name="third">角色管理</el-tab-pane>
      </el-tabs>
    </section>
  </div>
</template>

<script setup>
import { ref, getCurrentInstance, nextTick } from 'vue'

const { proxy } = getCurrentInstance()
const $message = proxy.$message

const tableData = ref([
  { date: '2016-05-03', name: 'Tom', address: 'No. 189, Grove St, Los Angeles' },
  { date: '2016-05-02', name: 'Jack', address: 'No. 189, Grove St, Los Angeles' },
  { date: '2016-05-04', name: 'Lucy', address: 'No. 189, Grove St, Los Angeles' },
  { date: '2016-05-01', name: 'Jerry', address: 'No. 189, Grove St, Los Angeles' }
])

const handleEdit = (row) => {
  $message.info(`编辑: ${row.name}`)
}

const handleDelete = (row) => {
  $message.warning(`删除: ${row.name}`)
}

const tags = ref(['标签一', '标签二', '标签三'])
const handleClose = (tag) => {
  tags.value.splice(tags.value.indexOf(tag), 1)
}

const dynamicTags = ref(['标签一', '标签二', '标签三'])
const inputVisible = ref(false)
const inputValue = ref('')
const inputRef = ref()

const handleDynamicClose = (tag) => {
  dynamicTags.value.splice(dynamicTags.value.indexOf(tag), 1)
}

const showInput = () => {
  inputVisible.value = true
  nextTick(() => {
    inputRef.value?.focus()
  })
}

const handleInputConfirm = () => {
  if (inputValue.value) {
    dynamicTags.value.push(inputValue.value)
  }
  inputVisible.value = false
  inputValue.value = ''
}

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

const activeNames = ref(['1'])
const activeName = ref('1')
const activeTab = ref('first')
const activeTab2 = ref('first')
const activeTab3 = ref('first')
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