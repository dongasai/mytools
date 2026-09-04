<template>
  <div class="component-section">
    <h3>NavMenu 导航菜单</h3>
    <el-menu :default-active="activeIndex" mode="horizontal" @select="handleSelect">
      <el-menu-item index="1">处理中心</el-menu-item>
      <el-sub-menu index="2">
        <template #title>工作台</template>
        <el-menu-item index="2-1">选项1</el-menu-item>
        <el-menu-item index="2-2">选项2</el-menu-item>
        <el-menu-item index="2-3">选项3</el-menu-item>
      </el-sub-menu>
      <el-menu-item index="3" disabled>消息中心</el-menu-item>
      <el-menu-item index="4">订单管理</el-menu-item>
    </el-menu>

    <h3>Tabs 标签页</h3>
    <el-tabs v-model="activeTab" type="border-card">
      <el-tab-pane label="用户管理" name="first">
        <el-table :data="userData" style="width: 100%">
          <el-table-column prop="name" label="姓名" />
          <el-table-column prop="age" label="年龄" />
        </el-table>
      </el-tab-pane>
      <el-tab-pane label="配置管理" name="second">配置管理</el-tab-pane>
      <el-tab-pane label="角色管理" name="third">角色管理</el-tab-pane>
    </el-tabs>

    <h3>Breadcrumb 面包屑</h3>
    <el-breadcrumb separator="/">
      <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item><a href="/">活动管理</a></el-breadcrumb-item>
      <el-breadcrumb-item>活动列表</el-breadcrumb-item>
      <el-breadcrumb-item>活动详情</el-breadcrumb-item>
    </el-breadcrumb>

    <h3>PageHeader 页头</h3>
    <el-page-header @back="goBack" content="详情页面">
      <template #extra>
        <el-button type="primary">操作按钮</el-button>
      </template>
    </el-page-header>

    <h3>Dropdown 下拉菜单</h3>
    <el-row :gutter="20" class="demo-row">
      <el-dropdown>
        <el-button type="primary">
          下拉菜单<el-icon class="el-icon--right"><arrow-down /></el-icon>
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item>黄金糕</el-dropdown-item>
            <el-dropdown-item>狮子头</el-dropdown-item>
            <el-dropdown-item>螺蛳粉</el-dropdown-item>
            <el-dropdown-item>双皮奶</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>

      <el-dropdown split-button type="primary" @click="handleClick">
        下拉菜单
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item>黄金糕</el-dropdown-item>
            <el-dropdown-item>狮子头</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </el-row>

    <h3>Steps 步骤条</h3>
    <el-steps :active="activeStep" finish-status="success">
      <el-step title="步骤 1" description="这是一段很长的描述性文字" />
      <el-step title="步骤 2" description="这是一段很长的描述性文字" />
      <el-step title="步骤 3" description="这是一段很长的描述性文字" />
    </el-steps>

    <el-button @click="nextStep" style="margin-top: 20px">下一步</el-button>

    <h3>Pagination 分页</h3>
    <el-pagination
      v-model:current-page="currentPage"
      v-model:page-size="pageSize"
      :page-sizes="[10, 20, 30, 40]"
      :background="true"
      layout="total, sizes, prev, pager, next, jumper"
      :total="100"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />

    <h3>Backtop 回到顶部</h3>
    <el-backtop :right="100" :bottom="100" />
    <p>滚动页面到底部，可以看到右下角出现"回到顶部"按钮</p>
  </div>
</template>

<script setup>
import { ref, getCurrentInstance } from 'vue'
import { ArrowDown } from '@element-plus/icons-vue'

const { proxy } = getCurrentInstance()
const $message = proxy.$message

const activeIndex = ref('1')
const handleSelect = (key, keyPath) => {
  console.log(key, keyPath)
}

const activeTab = ref('first')

const userData = ref([
  { name: 'Tom', age: 20 },
  { name: 'Jack', age: 25 }
])

const goBack = () => {
  $message.info('返回上一页')
}

const handleClick = () => {
  $message.success('按钮点击')
}

const activeStep = ref(0)
const nextStep = () => {
  if (activeStep.value++ > 2) activeStep.value = 0
}

const currentPage = ref(1)
const pageSize = ref(10)
const handleSizeChange = (val) => {
  console.log(`${val} items per page`)
}
const handleCurrentChange = (val) => {
  console.log(`current page: ${val}`)
}
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
</style>