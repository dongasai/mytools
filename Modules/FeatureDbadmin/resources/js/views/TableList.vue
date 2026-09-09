<template>
  <div class="table-list">
    <div class="list-header">
      <h3>表列表 - {{ connectionName }}</h3>
      <div class="header-actions">
        <el-button @click="refresh">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button
          v-if="tables.length === 0"
          type="primary"
          @click="createTestTable"
          :loading="creating"
        >
          <el-icon><Plus /></el-icon>
          创建测试表
        </el-button>
      </div>
    </div>

    <!-- 表为空提示 -->
    <el-empty
      v-if="!loading && tables.length === 0"
      description="当前数据库为空"
    >
      <el-button type="primary" @click="createTestTable" :loading="creating">
        创建测试表
      </el-button>
    </el-empty>

    <!-- 表列表 -->
    <el-table
      v-else
      v-loading="loading"
      :data="tables"
      border
      size="small"
      @row-click="handleRowClick"
      style="cursor: pointer"
    >
      <el-table-column prop="name" label="表名" min-width="200">
        <template #default="{ row }">
          <div class="table-name">
            <el-icon><Grid /></el-icon>
            <span>{{ row.name }}</span>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="行数" width="120">
        <template #default="{ row }">
          {{ row.rowCount || '-' }}
        </template>
      </el-table-column>
      <el-table-column label="大小" width="120">
        <template #default="{ row }">
          {{ row.size || '-' }}
        </template>
      </el-table-column>
      <el-table-column label="操作" width="200" fixed="right">
        <template #default="{ row }">
          <el-button
            type="primary"
            size="small"
            text
            @click.stop="openData(row)"
          >
            查看数据
          </el-button>
          <el-button
            type="primary"
            size="small"
            text
            @click.stop="openStructure(row)"
          >
            查看结构
          </el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Grid, Refresh, Plus } from '@element-plus/icons-vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const props = defineProps({
  connectionId: {
    type: [String, Number],
    required: true
  }
})

const loading = ref(false)
const creating = ref(false)
const tables = ref([])
const connectionName = ref('')

const loadTables = async () => {
  loading.value = true
  try {
    // 构建请求参数
    const params = {
      connection_id: props.connectionId
    }

    // 从查询参数中读取 database 和 schema
    if (route.query.database) {
      params.database = route.query.database
    }
    if (route.query.schema) {
      params.schema = route.query.schema
    }

    // 加载表列表
    const response = await axios.get('/admin/featuredbadmin/tables', {
      params: params
    })

    if (response.data.data) {
      tables.value = response.data.data.map(name => ({
        name: name,
        rowCount: null,
        size: null
      }))
    }

    // 加载连接信息
    const connResponse = await axios.get('/admin/featuredbadmin/connections')
    if (connResponse.data.data) {
      const conn = connResponse.data.data.find(c => c.id == props.connectionId)
      if (conn) {
        connectionName.value = conn.name
      }
    }
  } catch (error) {
    console.error('加载表列表失败:', error)
    ElMessage.error('加载表列表失败')
  } finally {
    loading.value = false
  }
}

const refresh = () => {
  loadTables()
}

/**
 * 创建测试表
 */
const createTestTable = async () => {
  try {
    await ElMessageBox.confirm(
      '将在数据库中创建一个包含各种字段类型的测试表，用于测试数据管理功能。是否继续？',
      '创建测试表',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'info'
      }
    )

    creating.value = true
    const response = await axios.post('/admin/featuredbadmin/test-table', {
      connection_id: props.connectionId
    })

    if (response.data.success) {
      ElMessage.success('测试表创建成功')
      loadTables()
    } else {
      ElMessage.error(response.data.message || '创建失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('创建测试表失败:', error)
      ElMessage.error('创建测试表失败')
    }
  } finally {
    creating.value = false
  }
}

const handleRowClick = (row) => {
  // 双击行打开数据浏览
  openData(row)
}

const openData = (row) => {
  const query = {}
  if (route.query.database) query.database = route.query.database
  if (route.query.schema) query.schema = route.query.schema

  router.push({
    path: `/data/${props.connectionId}/${row.name}`,
    query: query
  })
}

const openStructure = (row) => {
  const query = {}
  if (route.query.database) query.database = route.query.database
  if (route.query.schema) query.schema = route.query.schema

  router.push({
    path: `/structure/${props.connectionId}/${row.name}`,
    query: query
  })
}

onMounted(() => {
  loadTables()
})

// 监听连接ID变化
watch(() => props.connectionId, () => {
  loadTables()
})

// 监听查询参数变化
watch(() => route.query, () => {
  loadTables()
}, { deep: true })
</script>

<style scoped>
.table-list {
  height: 100%;
  padding: 20px;
  background: #fff;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.list-header h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
}

.table-name {
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-name .el-icon {
  color: #409eff;
}

:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table th) {
  background: #fafafa;
  font-weight: 500;
}

:deep(.el-table__row:hover) {
  background: #f5f7fa;
}
</style>