<template>
  <div class="connection-manager">
    <!-- 左侧连接树 -->
    <div class="left-panel">
      <div class="panel-header">
        <span class="panel-title">连接列表</span>
        <el-button link size="small" @click="showCreateDialog" class="icon-btn">
          <el-icon><Plus /></el-icon>
        </el-button>
      </div>

      <div class="tree-container" v-loading="loading">
        <el-tree
          :data="treeData"
          :props="treeProps"
          node-key="id"
          :highlight-current="true"
          @node-click="handleNodeClick"
          :expand-on-click-node="false"
          :default-expand-all="true"
          v-if="treeData.length > 0"
        >
          <template #default="{ node, data }">
            <span class="custom-tree-node">
              <span class="node-label">
                <el-icon v-if="data.type === 'driver'" class="driver-icon"><Database /></el-icon>
                <el-icon v-else-if="data.type === 'connection'" class="connection-icon"><Connection /></el-icon>
                {{ node.label }}
              </span>
              <el-switch
                v-if="data.type === 'connection'"
                v-model="data.is_active"
                @change="toggleConnectionStatus(data.raw)"
                :loading="data.raw?.statusLoading"
                size="small"
              />
            </span>
          </template>
        </el-tree>
        <div v-else class="empty-placeholder">
          暂无连接配置
        </div>
      </div>
    </div>

    <!-- 右侧详情面板 -->
    <div class="right-panel">
      <div class="panel-header">
        <span class="panel-title">连接详情</span>
        <div class="toolbar" v-if="selectedConnection">
          <el-button
            link
            size="small"
            :loading="selectedConnection.testing"
            @click="testConnection(selectedConnection.id)"
            class="icon-btn"
          >
            <el-icon><CircleCheck /></el-icon>
            测试
          </el-button>
          <el-button
            link
            size="small"
            @click="editConnection(selectedConnection)"
            class="icon-btn"
          >
            <el-icon><Edit /></el-icon>
            编辑
          </el-button>
          <el-button
            link
            size="small"
            @click="deleteConnection(selectedConnection.id)"
            class="icon-btn danger"
          >
            <el-icon><Delete /></el-icon>
            删除
          </el-button>
        </div>
      </div>

      <!-- 详情内容 -->
      <div class="detail-content" v-if="selectedConnection">
        <div class="info-section">
          <div class="info-row">
            <span class="info-label">连接名称</span>
            <span class="info-value">{{ selectedConnection.name }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">驱动类型</span>
            <span class="info-value">
              <el-tag :type="getDriverTagType(selectedConnection.driver)" size="small">
                {{ selectedConnection.driver }}
              </el-tag>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">主机地址</span>
            <span class="info-value">{{ selectedConnection.host }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">端口</span>
            <span class="info-value">{{ selectedConnection.port }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">数据库</span>
            <span class="info-value">{{ selectedConnection.database }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">用户名</span>
            <span class="info-value">{{ selectedConnection.username }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">字符集</span>
            <span class="info-value">{{ selectedConnection.charset || 'utf8mb4' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">状态</span>
            <span class="info-value">
              <el-tag :type="selectedConnection.is_active ? 'success' : 'info'" size="small">
                {{ selectedConnection.is_active ? '已启用' : '已禁用' }}
              </el-tag>
            </span>
          </div>
          <div class="info-row" v-if="selectedConnection.remark">
            <span class="info-label">备注</span>
            <span class="info-value">{{ selectedConnection.remark }}</span>
          </div>
        </div>
      </div>

      <!-- 空状态 -->
      <div class="empty-detail" v-else>
        <el-icon class="empty-icon"><Select /></el-icon>
        <p>请从左侧选择一个连接查看详情</p>
      </div>
    </div>

    <!-- 创建/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEditing ? '编辑连接' : '新建连接'"
      width="500px"
      :close-on-click-modal="false"
      custom-class="compact-dialog"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="80px"
        class="compact-form"
      >
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name" placeholder="连接名称" size="small" />
        </el-form-item>

        <el-form-item label="驱动" prop="driver">
          <el-select v-model="form.driver" placeholder="选择驱动" style="width: 100%" size="small">
            <el-option label="MySQL" value="mysql" />
            <el-option label="PostgreSQL" value="pgsql" />
            <el-option label="SQLite" value="sqlite" />
            <el-option label="SQL Server" value="sqlsrv" />
          </el-select>
        </el-form-item>

        <template v-if="form.driver !== 'sqlite'">
          <el-form-item label="主机" prop="host">
            <el-input v-model="form.host" placeholder="主机地址" size="small" />
          </el-form-item>

          <el-form-item label="端口" prop="port">
            <el-input-number
              v-model="form.port"
              :min="1"
              :max="65535"
              style="width: 100%"
              size="small"
            />
          </el-form-item>

          <el-form-item label="数据库" prop="database">
            <el-input v-model="form.database" placeholder="数据库名" size="small" />
          </el-form-item>

          <el-form-item label="用户名" prop="username">
            <el-input v-model="form.username" placeholder="用户名" size="small" />
          </el-form-item>

          <el-form-item label="密码" prop="password">
            <el-input
              v-model="form.password"
              type="password"
              placeholder="密码"
              show-password
              size="small"
            />
          </el-form-item>
        </template>

        <template v-else>
          <el-form-item label="文件" prop="database">
            <el-input v-model="form.database" placeholder="SQLite文件路径" size="small" />
          </el-form-item>
        </template>

        <el-form-item label="字符集">
          <el-input v-model="form.charset" placeholder="默认: utf8mb4" size="small" />
        </el-form-item>

        <el-form-item label="启用">
          <el-switch v-model="form.is_active" size="small" />
        </el-form-item>

        <el-form-item label="备注">
          <el-input
            v-model="form.remark"
            type="textarea"
            :rows="2"
            placeholder="备注信息"
            size="small"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button size="small" @click="dialogVisible = false">取消</el-button>
        <el-button type="success" size="small" @click="testBeforeSave" :loading="testing">
          测试
        </el-button>
        <el-button type="primary" size="small" @click="saveConnection" :loading="saving">
          保存
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

/**
 * 连接列表数据
 * @type {import('vue').Ref<Array>}
 */
const connections = ref([])

/**
 * 当前选中的连接
 * @type {import('vue').Ref<Object|null>}
 */
const selectedConnection = ref(null)

/**
 * 加载状态
 * @type {import('vue').Ref<boolean>}
 */
const loading = ref(false)

/**
 * 对话框显示状态
 * @type {import('vue').Ref<boolean>}
 */
const dialogVisible = ref(false)

/**
 * 是否为编辑模式
 * @type {import('vue').Ref<boolean>}
 */
const isEditing = ref(false)

/**
 * 测试连接中状态
 * @type {import('vue').Ref<boolean>}
 */
const testing = ref(false)

/**
 * 保存中状态
 * @type {import('vue').Ref<boolean>}
 */
const saving = ref(false)

/**
 * 表单引用
 * @type {import('vue').Ref<Object>}
 */
const formRef = ref(null)

/**
 * 表单数据
 * @type {Object}
 */
const form = reactive({
  id: null,
  name: '',
  driver: 'mysql',
  host: '127.0.0.1',
  port: 3306,
  database: '',
  username: '',
  password: '',
  charset: 'utf8mb4',
  is_active: true,
  remark: ''
})

/**
 * 表单验证规则
 * @type {Object}
 */
const rules = {
  name: [{ required: true, message: '请输入名称', trigger: 'blur' }],
  driver: [{ required: true, message: '请选择驱动', trigger: 'change' }],
  host: [{ required: true, message: '请输入主机', trigger: 'blur' }],
  database: [{ required: true, message: '请输入数据库', trigger: 'blur' }],
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }]
}

/**
 * 树形数据配置
 * @type {Object}
 */
const treeProps = {
  label: 'label',
  children: 'children'
}

/**
 * 计算树形数据 - 按驱动类型分组
 * @type {import('vue').ComputedRef<Array>}
 */
const treeData = computed(() => {
  const driverGroups = {}

  // 按驱动类型分组
  connections.value.forEach(conn => {
    if (!driverGroups[conn.driver]) {
      driverGroups[conn.driver] = []
    }
    driverGroups[conn.driver].push(conn)
  })

  // 转换为树形结构
  const result = Object.entries(driverGroups).map(([driver, conns]) => ({
    id: `driver-${driver}`,
    label: getDriverLabel(driver),
    type: 'driver',
    children: conns.map(conn => ({
      id: conn.id,
      label: conn.name,
      type: 'connection',
      is_active: conn.is_active,
      raw: conn
    }))
  }))

  return result
})

/**
 * 获取驱动类型标签
 * @param {string} driver - 驱动类型
 * @returns {string} 驱动标签
 */
const getDriverLabel = (driver) => {
  const labels = {
    mysql: 'MySQL',
    pgsql: 'PostgreSQL',
    sqlite: 'SQLite',
    sqlsrv: 'SQL Server'
  }
  return labels[driver] || driver
}

/**
 * 获取驱动类型标签样式
 * @param {string} driver - 驱动类型
 * @returns {string} 标签类型
 */
const getDriverTagType = (driver) => {
  const types = {
    mysql: 'success',
    pgsql: 'primary',
    sqlite: 'info',
    sqlsrv: 'warning'
  }
  return types[driver] || 'info'
}

/**
 * 处理树节点点击
 * @param {Object} data - 节点数据
 */
const handleNodeClick = (data) => {
  if (data.type === 'connection') {
    selectedConnection.value = data.raw
  }
}

/**
 * 获取连接列表
 */
const fetchConnections = async () => {
  loading.value = true
  try {
    const res = await axios.get('/admin/featuredbadmin/connections')
    connections.value = (res.data.data || []).map(conn => ({
      ...conn,
      testing: false,
      statusLoading: false
    }))
  } catch (error) {
    ElMessage.error('获取连接列表失败: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

/**
 * 显示创建对话框
 */
const showCreateDialog = () => {
  isEditing.value = false
  resetForm()
  dialogVisible.value = true
}

/**
 * 编辑连接
 * @param {Object} conn - 连接数据
 */
const editConnection = (conn) => {
  isEditing.value = true
  Object.assign(form, {
    id: conn.id,
    name: conn.name,
    driver: conn.driver,
    host: conn.host || '127.0.0.1',
    port: conn.port || 3306,
    database: conn.database,
    username: conn.username,
    password: '',
    charset: conn.charset || 'utf8mb4',
    is_active: conn.is_active,
    remark: conn.remark || ''
  })
  dialogVisible.value = true
}

/**
 * 重置表单
 */
const resetForm = () => {
  Object.assign(form, {
    id: null,
    name: '',
    driver: 'mysql',
    host: '127.0.0.1',
    port: 3306,
    database: '',
    username: '',
    password: '',
    charset: 'utf8mb4',
    is_active: true,
    remark: ''
  })
  if (formRef.value) {
    formRef.value.resetFields()
  }
}

/**
 * 保存连接
 */
const saveConnection = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (!valid) return

    saving.value = true
    try {
      let res
      if (isEditing.value) {
        // 更新连接 - 使用 PUT 方法
        res = await axios.put(`/admin/featuredbadmin/connections/${form.id}`, form)
      } else {
        // 创建连接 - 使用 POST 方法
        res = await axios.post('/admin/featuredbadmin/connections', form)
      }

      if (res.data.success) {
        ElMessage.success(isEditing.value ? '更新成功' : '创建成功')
        dialogVisible.value = false
        await fetchConnections()
        // 重新选中
        if (isEditing.value && selectedConnection.value?.id === form.id) {
          const updated = connections.value.find(c => c.id === form.id)
          if (updated) selectedConnection.value = updated
        }
      } else {
        ElMessage.error(res.data.message || '操作失败')
      }
    } catch (error) {
      ElMessage.error('保存失败: ' + (error.response?.data?.message || error.message))
    } finally {
      saving.value = false
    }
  })
}

/**
 * 测试连接
 * @param {number} id - 连接ID
 */
const testConnection = async (id) => {
  const conn = connections.value.find(c => c.id === id)
  if (conn) conn.testing = true

  try {
    const res = await axios.post(`/admin/featuredbadmin/connections/${id}/test`)
    if (res.data.success) {
      ElMessage.success('连接测试成功')
    } else {
      ElMessage.error('连接测试失败: ' + res.data.message)
    }
  } catch (error) {
    ElMessage.error('连接测试失败: ' + (error.response?.data?.message || error.message))
  } finally {
    if (conn) conn.testing = false
  }
}

/**
 * 保存前测试连接
 */
const testBeforeSave = async () => {
  testing.value = true
  try {
    const res = await axios.post('/admin/featuredbadmin/connections/test-config', form)
    if (res.data.success) {
      ElMessage.success('连接配置测试成功')
    } else {
      ElMessage.error('连接配置测试失败: ' + res.data.message)
    }
  } catch (error) {
    ElMessage.error('测试失败: ' + (error.response?.data?.message || error.message))
  } finally {
    testing.value = false
  }
}

/**
 * 切换连接状态
 * @param {Object} conn - 连接数据
 */
const toggleConnectionStatus = async (conn) => {
  try {
    // 使用 PUT 方法更新连接状态
    const res = await axios.put(`/admin/featuredbadmin/connections/${conn.id}`, {
      is_active: conn.is_active
    })
    if (res.data.success) {
      ElMessage.success(conn.is_active ? '连接已启用' : '连接已禁用')
    } else {
      conn.is_active = !conn.is_active
      ElMessage.error(res.data.message || '操作失败')
    }
  } catch (error) {
    conn.is_active = !conn.is_active
    ElMessage.error('状态切换失败: ' + (error.response?.data?.message || error.message))
  }
}

/**
 * 删除连接
 * @param {number} id - 连接ID
 */
const deleteConnection = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除此连接吗？', '确认删除', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    // 使用 DELETE 方法
    const res = await axios.delete(`/admin/featuredbadmin/connections/${id}`)
    if (res.data.success) {
      ElMessage.success('连接删除成功')
      if (selectedConnection.value?.id === id) {
        selectedConnection.value = null
      }
      fetchConnections()
    } else {
      ElMessage.error(res.data.message || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败: ' + (error.response?.data?.message || error.message))
    }
  }
}

// 组件挂载时获取数据
onMounted(() => {
  fetchConnections()
})
</script>

<style scoped>
.connection-manager {
  display: flex;
  height: 100vh;
  background: #fafafa;
  font-size: 13px;
}

/* 左侧面板 */
.left-panel {
  width: 20%;
  min-width: 200px;
  max-width: 280px;
  background: #fff;
  border-right: 1px solid #e8e8e8;
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f5f5f5;
  border-bottom: 1px solid #e8e8e8;
}

.panel-title {
  font-size: 13px;
  font-weight: 500;
  color: #333;
}

.icon-btn {
  padding: 0;
  min-width: auto;
  color: #1890ff;
}

.icon-btn.danger {
  color: #ff4d4f;
}

.tree-container {
  flex: 1;
  overflow-y: auto;
}

.custom-tree-node {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding-right: 8px;
}

.node-label {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #333;
}

.driver-icon {
  color: #1890ff;
}

.connection-icon {
  color: #52c41a;
}

.empty-placeholder {
  padding: 32px 16px;
  text-align: center;
  color: #999;
  font-size: 12px;
}

/* 右侧面板 */
.right-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.toolbar {
  display: flex;
  gap: 8px;
}

.detail-content {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
}

.info-section {
  background: #fff;
  border: 1px solid #e8e8e8;
}

.info-row {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  line-height: 32px;
  height: 32px;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  width: 100px;
  padding: 0 12px;
  color: #666;
  font-size: 12px;
  background: #fafafa;
  border-right: 1px solid #f0f0f0;
}

.info-value {
  flex: 1;
  padding: 0 12px;
  color: #333;
  font-size: 12px;
}

/* 空详情 */
.empty-detail {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: #999;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  color: #d9d9d9;
}

.empty-detail p {
  font-size: 13px;
  margin: 0;
}

/* 紧凑表单 */
.compact-form :deep(.el-form-item) {
  margin-bottom: 16px;
}

.compact-form :deep(.el-form-item__label) {
  font-size: 12px;
  color: #666;
}

.compact-form :deep(.el-input__inner) {
  font-size: 12px;
}

/* 对话框 */
:deep(.el-dialog) {
  border-radius: 2px;
}

:deep(.el-dialog__header) {
  padding: 12px 16px;
  background: #f5f5f5;
  border-bottom: 1px solid #e8e8e8;
}

:deep(.el-dialog__title) {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

:deep(.el-dialog__body) {
  padding: 16px;
}

:deep(.el-dialog__footer) {
  padding: 12px 16px;
  border-top: 1px solid #e8e8e8;
}

/* 树形组件样式 */
:deep(.el-tree-node__content) {
  height: 32px;
  line-height: 32px;
}

:deep(.el-tree-node__expand-icon) {
  font-size: 12px;
  color: #999;
}

:deep(.el-tree-node__label) {
  font-size: 12px;
}
</style>
