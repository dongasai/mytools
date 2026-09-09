<template>
  <div class="connection-manager">
    <div class="manager-header">
      <h3>连接管理</h3>
      <el-button type="primary" @click="openAddDialog">
        <el-icon><Plus /></el-icon>
        新建连接
      </el-button>
    </div>

    <el-table :data="connections" border size="small" v-loading="loading">
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="连接名称" width="180" />
      <el-table-column prop="driver" label="驱动" width="100">
        <template #default="{ row }">
          <el-tag size="small">{{ row.driver.toUpperCase() }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="host" label="主机" width="150">
        <template #default="{ row }">
          {{ row.host }}:{{ row.port }}
        </template>
      </el-table-column>
      <el-table-column prop="database" label="数据库" width="150" />
      <el-table-column prop="is_active" label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.is_active ? 'success' : 'info'" size="small">
            {{ row.is_active ? '激活' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="description" label="描述" min-width="150" />
      <el-table-column label="操作" width="200" fixed="right">
        <template #default="{ row }">
          <el-button
            type="primary"
            size="small"
            text
            @click="testConnection(row)"
          >
            测试
          </el-button>
          <el-button
            type="primary"
            size="small"
            text
            @click="editConnection(row)"
          >
            编辑
          </el-button>
          <el-button
            type="danger"
            size="small"
            text
            @click="deleteConnection(row)"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 新建/编辑连接对话框 -->
    <el-dialog
      v-model="dialog.visible"
      :title="dialog.isEdit ? '编辑连接' : '新建连接'"
      width="600px"
    >
      <ConnectionForm
        :connection="dialog.data"
        :is-edit="dialog.isEdit"
        @save="handleSave"
        @cancel="dialog.visible = false"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import axios from 'axios'
import ConnectionForm from '../components/ConnectionForm.vue'

const emit = defineEmits(['refresh'])

const loading = ref(false)
const connections = ref([])

const dialog = reactive({
  visible: false,
  isEdit: false,
  data: null
})

const loadConnections = async () => {
  loading.value = true
  try {
    const response = await axios.get('/admin/featuredbadmin/connections')
    if (response.data.success && response.data.data) {
      connections.value = response.data.data.data
    }
  } catch (error) {
    console.error('加载失败:', error)
    ElMessage.error('加载连接列表失败')
  } finally {
    loading.value = false
  }
}

const openAddDialog = () => {
  dialog.visible = true
  dialog.isEdit = false
  dialog.data = null
}

const editConnection = (row) => {
  dialog.visible = true
  dialog.isEdit = true
  dialog.data = row
}

const testConnection = async (row) => {
  try {
    const response = await axios.post(`/admin/featuredbadmin/connections/${row.id}/test`)

    if (response.data.success) {
      ElMessage.success(`连接成功！版本: ${response.data.version}`)
    } else {
      ElMessage.error(`连接失败: ${response.data.message}`)
    }
  } catch (error) {
    console.error('测试失败:', error)
    ElMessage.error('连接测试失败')
  }
}

const deleteConnection = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除这个连接吗？', '确认删除', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const response = await axios.delete(`/admin/featuredbadmin/connections/${row.id}`)

    if (response.data.success) {
      ElMessage.success('删除成功')
      loadConnections()
      emit('refresh')
    } else {
      ElMessage.error('删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
      ElMessage.error('删除失败')
    }
  }
}

const handleSave = () => {
  dialog.visible = false
  loadConnections()
  emit('refresh')
}

onMounted(() => {
  loadConnections()
})
</script>

<style scoped>
.connection-manager {
  height: 100%;
  padding: 20px;
  background: #fff;
}

.manager-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.manager-header h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
}

:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table th) {
  background: #fafafa;
  font-weight: 500;
}
</style>