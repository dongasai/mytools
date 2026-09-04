<template>
  <div class="component-section">
    <h3>Message 消息提示</h3>
    <el-row :gutter="20" class="demo-row">
      <el-button @click="openMessage('info')">消息</el-button>
      <el-button @click="openMessage('success')">成功</el-button>
      <el-button @click="openMessage('warning')">警告</el-button>
      <el-button @click="openMessage('error')">错误</el-button>
    </el-row>

    <h3>Alert 警告</h3>
    <el-row :gutter="20" class="demo-row" style="flex-direction: column; gap: 10px">
      <el-alert title="成功提示" type="success" />
      <el-alert title="消息提示" type="info" />
      <el-alert title="警告提示" type="warning" />
      <el-alert title="错误提示" type="error" />
      <el-alert title="不可关闭的" type="success" :closable="false" />
      <el-alert title="带有辅助性文字介绍" type="success" description="这是一句描述性文字" />
    </el-row>

    <h3>Notification 通知</h3>
    <el-row :gutter="20" class="demo-row">
      <el-button @click="openNotification('success')">成功</el-button>
      <el-button @click="openNotification('warning')">警告</el-button>
      <el-button @click="openNotification('info')">消息</el-button>
      <el-button @click="openNotification('error')">错误</el-button>
    </el-row>

    <h3>Loading 加载</h3>
    <el-row :gutter="20" class="demo-row">
      <el-button @click="showLoading">显示加载</el-button>
      <el-button v-loading.fullscreen.lock="fullscreenLoading">全屏加载（点击切换）</el-button>
    </el-row>

    <h3>Dialog 对话框</h3>
    <el-row :gutter="20" class="demo-row">
      <el-button @click="dialogVisible = true">打开 Dialog</el-button>
    </el-row>

    <el-dialog v-model="dialogVisible" title="提示" width="30%">
      <span>这是一段信息</span>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="dialogVisible = false">确定</el-button>
        </span>
      </template>
    </el-dialog>

    <h3>Drawer 抽屉</h3>
    <el-row :gutter="20" class="demo-row">
      <el-button @click="drawer = true">打开 Drawer</el-button>
    </el-row>

    <el-drawer v-model="drawer" title="我是标题">
      <span>我来啦!</span>
    </el-drawer>

    <h3>Popover 弹出框</h3>
    <el-row :gutter="20" class="demo-row">
      <el-popover placement="top-start" title="标题" :width="200" trigger="hover" content="这是一段content,这是一段content,这是一段content,这是一段content。">
        <template #reference>
          <el-button>hover 激活</el-button>
        </template>
      </el-popover>

      <el-popover placement="bottom" title="标题" :width="200" trigger="click" content="这是一段content,这是一段content,这是一段content,这是一段content。">
        <template #reference>
          <el-button>click 激活</el-button>
        </template>
      </el-popover>
    </el-row>

    <h3>Tooltip 文字提示</h3>
    <el-row :gutter="20" class="demo-row">
      <el-tooltip content="Top center" placement="top">
        <el-button>上边</el-button>
      </el-tooltip>
      <el-tooltip content="Bottom center" placement="bottom">
        <el-button>下边</el-button>
      </el-tooltip>
      <el-tooltip content="Right center" placement="right">
        <el-button>右边</el-button>
      </el-tooltip>
    </el-row>

    <h3>Popconfirm 气泡确认框</h3>
    <el-row :gutter="20" class="demo-row">
      <el-popconfirm title="确定删除吗？">
        <template #reference>
          <el-button>删除</el-button>
        </template>
      </el-popconfirm>
    </el-row>
  </div>
</template>

<script setup>
import { ref, getCurrentInstance } from 'vue'
import { ElLoading } from 'element-plus'

const { proxy } = getCurrentInstance()
const $message = proxy.$message
const $notify = proxy.$notify

const openMessage = (type) => {
  $message({
    message: `这是一条${type}消息`,
    type: type
  })
}

const openNotification = (type) => {
  $notify({
    title: '标题',
    message: `这是一条${type}通知`,
    type: type
  })
}

const fullscreenLoading = ref(false)

const showLoading = () => {
  const loading = ElLoading.service({
    lock: true,
    text: 'Loading',
    background: 'rgba(0, 0, 0, 0.7)'
  })
  setTimeout(() => {
    loading.close()
  }, 2000)
}

const dialogVisible = ref(false)
const drawer = ref(false)
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