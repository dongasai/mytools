<template>
  <div class="table-structure">
    <div class="structure-header">
      <h3>{{ tableName }} - 表结构</h3>
      <el-button @click="loadStructure">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </div>

    <div v-loading="loading" class="structure-content">
      <!-- 列信息 -->
      <div class="section">
        <div class="section-title">列信息</div>
        <el-table :data="structure.columns" border size="small">
          <el-table-column prop="name" label="列名" width="150" />
          <el-table-column prop="type" label="类型" width="120" />
          <el-table-column label="可空" width="80">
            <template #default="{ row }">
              <el-tag :type="row.nullable === 'YES' ? 'success' : 'danger'" size="small">
                {{ row.nullable }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="default" label="默认值" width="120" />
          <el-table-column label="主键" width="80">
            <template #default="{ row }">
              <el-tag v-if="row.is_primary_key" type="danger" size="small">PK</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="comment" label="注释" min-width="150" />
        </el-table>
      </div>

      <!-- 索引信息 -->
      <div v-if="structure.indexes.length > 0" class="section">
        <div class="section-title">索引信息</div>
        <el-table :data="structure.indexes" border size="small">
          <el-table-column prop="name" label="索引名" width="200" />
          <el-table-column prop="type" label="类型" width="100" />
          <el-table-column label="列" min-width="200">
            <template #default="{ row }">
              {{ row.columns?.join(', ') }}
            </template>
          </el-table-column>
        </el-table>
      </div>

      <!-- 外键信息 -->
      <div v-if="structure.foreignKeys.length > 0" class="section">
        <div class="section-title">外键信息</div>
        <el-table :data="structure.foreignKeys" border size="small">
          <el-table-column prop="name" label="约束名" width="200" />
          <el-table-column prop="column" label="列" width="150" />
          <el-table-column prop="referenced_table" label="引用表" width="150" />
          <el-table-column prop="referenced_column" label="引用列" width="150" />
        </el-table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Refresh } from '@element-plus/icons-vue'
import axios from 'axios'

const props = defineProps({
  connectionId: {
    type: [String, Number],
    required: true
  },
  tableName: {
    type: String,
    required: true
  }
})

const loading = ref(false)
const structure = ref({
  columns: [],
  indexes: [],
  foreignKeys: []
})

const loadStructure = async () => {
  if (!props.connectionId || !props.tableName) return

  loading.value = true
  try {
    const response = await axios.get(`/admin/featuredbadmin/tables/${props.tableName}/structure`, {
      params: { connection_id: props.connectionId }
    })

    if (response.data.success && response.data.data) {
      const data = response.data.data
      structure.value = {
        columns: data.columns || [],
        indexes: data.indexes || [],
        foreignKeys: data.foreignKeys || []
      }
    }
  } catch (error) {
    console.error('加载表结构失败:', error)
    ElMessage.error('加载表结构失败')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStructure()
})

watch(() => [props.connectionId, props.tableName], () => {
  loadStructure()
})
</script>

<style scoped>
.table-structure {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #fff;
}

.structure-header {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.structure-header h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
}

.structure-content {
  flex: 1;
  overflow: auto;
  padding: 16px;
}

.section {
  margin-bottom: 24px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  margin-bottom: 12px;
  padding-left: 8px;
  border-left: 3px solid #409eff;
}

:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table th) {
  background: #fafafa;
  font-weight: 500;
}
</style>