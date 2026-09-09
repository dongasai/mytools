<template>
  <div class="data-editor">
    <div class="editor-header">
      <h3>{{ isEdit ? '编辑数据' : '新增数据' }} - {{ tableName }}</h3>
      <div class="header-actions">
        <el-button @click="handleCancel">取消</el-button>
        <el-button type="primary" @click="handleSave" :loading="saving">
          <el-icon><Check /></el-icon>
          保存
        </el-button>
      </div>
    </div>

    <div v-loading="loading" class="editor-content">
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="150px"
        label-position="right"
      >
        <el-form-item
          v-for="col in columns"
          :key="col.name"
          :label="col.name"
          :prop="col.name"
        >
          <!-- 主键和自增字段只读 -->
          <template v-if="col.isPrimaryKey || col.isAutoIncrement">
            <el-input v-model="form[col.name]" disabled />
            <span class="field-hint">
              <el-tag size="small" type="info">
                {{ col.isPrimaryKey ? '主键' : '自增' }}
              </el-tag>
            </span>
          </template>

          <!-- 可编辑字段 -->
          <template v-else>
            <div class="field-container">
              <div class="field-input">
                <!-- 文本域 -->
                <template v-if="isTextType(col.type)">
                  <el-input
                    v-model="form[col.name]"
                    type="textarea"
                    :rows="5"
                    :placeholder="getPlaceholder(col)"
                  />
                </template>

                <!-- 枚举类型 -->
                <template v-else-if="col.type === 'enum' || col.type === 'set'">
                  <el-select
                    v-model="form[col.name]"
                    :placeholder="getPlaceholder(col)"
                    style="width: 100%"
                  >
                    <el-option
                      v-for="opt in parseEnumOptions(col)"
                      :key="opt"
                      :label="opt"
                      :value="opt"
                    />
                  </el-select>
                </template>

                <!-- 布尔类型 -->
                <template v-else-if="isBooleanType(col)">
                  <el-switch
                    v-model="form[col.name]"
                    active-text="是"
                    inactive-text="否"
                  />
                </template>

                <!-- 数字类型 -->
                <template v-else-if="isNumberType(col.type)">
                  <el-input-number
                    v-model="form[col.name]"
                    :placeholder="getPlaceholder(col)"
                    style="width: 100%"
                  />
                </template>

                <!-- 默认输入框 -->
                <template v-else>
                  <el-input
                    v-model="form[col.name]"
                    :placeholder="getPlaceholder(col)"
                    clearable
                  />
                </template>
              </div>

              <!-- 可空字段的"设为 null"按钮 -->
              <div v-if="col.nullable === 'YES'" class="field-actions">
                <el-button
                  size="small"
                  :type="form[col.name] === null ? 'warning' : 'default'"
                  @click="setFieldToNull(col.name)"
                >
                  {{ form[col.name] === null ? '已设为 null' : '设为 null' }}
                </el-button>
              </div>

              <!-- 字段信息 -->
              <div class="field-info">
                <el-tag size="small" effect="plain">{{ col.type }}</el-tag>
                <el-tag v-if="col.nullable === 'YES'" size="small" type="warning">可空</el-tag>
                <span v-if="col.comment" class="field-comment">{{ col.comment }}</span>
              </div>
            </div>
          </template>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Check } from '@element-plus/icons-vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

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

// 判断是否为编辑模式（有 rowId 参数）
const isEdit = computed(() => !!route.params.rowId)
const rowId = computed(() => route.params.rowId)

const loading = ref(false)
const saving = ref(false)
const formRef = ref(null)

const columns = ref([])
const form = reactive({})
const rules = reactive({})

/**
 * 加载表结构
 */
const loadColumns = async () => {
  try {
    const response = await axios.get(`/admin/featuredbadmin/tables/${props.tableName}/structure`, {
      params: { connection_id: props.connectionId }
    })

    if (response.data.success && response.data.data) {
      columns.value = response.data.data.columns || []

      // 初始化表单和验证规则
      columns.value.forEach(col => {
        // 初始化表单值
        if (isBooleanType(col)) {
          // 布尔类型：转换数字/字符串为布尔值
          if (col.default !== null && col.default !== undefined) {
            // 支持多种格式：1/0, true/false, "true"/"false"
            form[col.name] = col.default === 1 || col.default === true || col.default === '1' || col.default === 'true'
          } else {
            // 可空字段默认为 null，否则为 false
            form[col.name] = col.nullable === 'YES' ? null : false
          }
        } else {
          form[col.name] = col.default || null
        }

        // 设置验证规则（非空字段）
        if (col.nullable === 'NO' && !col.is_primary_key && col.extra !== 'auto_increment') {
          rules[col.name] = [
            { required: true, message: `${col.name} 不能为空`, trigger: 'blur' }
          ]
        }
      })
    }
  } catch (error) {
    console.error('加载表结构失败:', error)
    ElMessage.error('加载表结构失败')
  }
}

/**
 * 加载行数据（编辑模式）
 */
const loadRowData = async () => {
  if (!isEdit.value) return

  loading.value = true
  try {
    const response = await axios.get(`/admin/featuredbadmin/data/${props.tableName}/row/${rowId.value}`, {
      params: { connection_id: props.connectionId }
    })

    if (response.data.success && response.data.data) {
      // 填充表单
      const rowData = response.data.data.data

      // 转换布尔类型的值
      columns.value.forEach(col => {
        if (isBooleanType(col)) {
          // 布尔类型：保持 null 或转换为布尔值
          if (rowData[col.name] === null || rowData[col.name] === undefined) {
            form[col.name] = null
          } else {
            // 支持多种格式：1/0, true/false, "true"/"false"
            form[col.name] = rowData[col.name] === 1 || rowData[col.name] === true || rowData[col.name] === '1' || rowData[col.name] === 'true'
          }
        } else {
          form[col.name] = rowData[col.name]
        }
      })
    }
  } catch (error) {
    console.error('加载数据失败:', error)
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

/**
 * 判断是否为布尔类型
 */
const isBooleanType = (col) => {
  // 直接判断类型是否为 boolean
  return col.type === 'boolean'
}

/**
 * 判断是否为文本类型
 */
const isTextType = (type) => {
  return ['text', 'longtext', 'mediumtext', 'blob', 'longblob'].includes(type)
}

/**
 * 判断是否为数字类型
 */
const isNumberType = (type) => {
  return ['int', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'].includes(type)
}

/**
 * 解析枚举选项
 */
const parseEnumOptions = (col) => {
  // 这里需要从列的额外信息中解析枚举值
  // 简化处理，实际应该从后端获取
  return []
}

/**
 * 获取占位符
 */
const getPlaceholder = (col) => {
  if (col.comment) {
    return col.comment
  }
  return `请输入 ${col.name}`
}

/**
 * 设置字段为 null
 */
const setFieldToNull = (fieldName) => {
  form[fieldName] = null
}

/**
 * 保存数据
 */
const handleSave = async () => {
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  // 准备提交数据，转换布尔值为数字
  const submitData = {}
  columns.value.forEach(col => {
    if (isBooleanType(col)) {
      // 布尔类型：null 保持 null，true -> 1, false -> 0
      if (form[col.name] === null) {
        submitData[col.name] = null
      } else {
        submitData[col.name] = form[col.name] ? 1 : 0
      }
    } else {
      submitData[col.name] = form[col.name]
    }
  })

  saving.value = true
  try {
    let response
    if (isEdit.value) {
      // 编辑
      response = await axios.put(`/admin/featuredbadmin/data/${props.tableName}/row/${rowId.value}`, {
        connection_id: props.connectionId,
        data: submitData
      })
    } else {
      // 新增
      response = await axios.post(`/admin/featuredbadmin/data/${props.tableName}/row`, {
        connection_id: props.connectionId,
        data: submitData
      })
    }

    if (response.data.success) {
      ElMessage.success(isEdit.value ? '更新成功' : '新增成功')

      // 返回数据浏览页
      router.push({
        path: `/data/${props.connectionId}/${props.tableName}`
      })
    } else {
      ElMessage.error(response.data.message || '保存失败')
    }
  } catch (error) {
    console.error('保存失败:', error)
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
  }
}

/**
 * 取消编辑
 */
const handleCancel = () => {
  // 返回数据浏览页
  router.push({
    path: `/data/${props.connectionId}/${props.tableName}`
  })
}

onMounted(() => {
  loadColumns().then(() => {
    loadRowData()
  })
})

watch(() => [props.connectionId, props.tableName], () => {
  loadColumns()
})
</script>

<style scoped>
.data-editor {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #fff;
}

.editor-header {
  padding: 16px 24px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafafa;
}

.editor-header h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.editor-content {
  flex: 1;
  overflow: auto;
  padding: 24px;
  max-width: 900px;
}

.field-container {
  width: 100%;
}

.field-input {
  width: 100%;
}

.field-actions {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.field-info {
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.field-comment {
  font-size: 12px;
  color: #666;
  font-style: italic;
}

.field-hint {
  margin-left: 8px;
}

:deep(.el-form-item) {
  margin-bottom: 24px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
  color: #333;
}

:deep(.el-textarea__inner) {
  font-family: 'Consolas', 'Monaco', monospace;
}
</style>