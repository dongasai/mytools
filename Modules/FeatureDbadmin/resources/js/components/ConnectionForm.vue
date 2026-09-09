<template>
  <el-form
    ref="formRef"
    :model="form"
    :rules="rules"
    label-width="120px"
    size="default"
  >
    <el-form-item label="连接名称" prop="name">
      <el-input v-model="form.name" placeholder="请输入连接名称" />
    </el-form-item>

    <el-form-item label="驱动类型" prop="driver">
      <el-select v-model="form.driver" placeholder="请选择驱动类型" style="width: 100%">
        <el-option label="MySQL" value="mysql" />
        <el-option label="PostgreSQL" value="pgsql" />
        <el-option label="SQLite" value="sqlite" />
      </el-select>
    </el-form-item>

    <template v-if="form.driver !== 'sqlite'">
      <el-form-item label="主机地址" prop="host">
        <el-input v-model="form.host" placeholder="127.0.0.1" />
      </el-form-item>

      <el-form-item label="端口" prop="port">
        <el-input-number
          v-model="form.port"
          :min="1"
          :max="65535"
          style="width: 100%"
        />
      </el-form-item>
    </template>

    <el-form-item label="数据库" prop="database">
      <el-input v-model="form.database" placeholder="数据库名称或路径" />
    </el-form-item>

    <template v-if="form.driver !== 'sqlite'">
      <el-form-item label="用户名" prop="username">
        <el-input v-model="form.username" placeholder="数据库用户名" />
      </el-form-item>

      <el-form-item label="密码" prop="password">
        <el-input
          v-model="form.password"
          type="password"
          placeholder="数据库密码"
          show-password
        />
      </el-form-item>

      <el-form-item label="字符集">
        <el-input v-model="form.charset" placeholder="utf8mb4" />
      </el-form-item>

      <el-form-item v-if="form.driver === 'mysql'" label="排序规则">
        <el-input v-model="form.collation" placeholder="utf8mb4_unicode_ci" />
      </el-form-item>
    </template>

    <el-form-item label="描述">
      <el-input
        v-model="form.description"
        type="textarea"
        :rows="3"
        placeholder="连接描述（可选）"
      />
    </el-form-item>

    <el-form-item label="启用">
      <el-switch v-model="form.is_active" />
    </el-form-item>

    <el-form-item>
      <el-button type="primary" @click="handleSubmit">保存</el-button>
      <el-button @click="handleTest" :loading="testing">
        测试连接
      </el-button>
      <el-button @click="$emit('cancel')">取消</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>
import { ref, reactive, watch, onMounted, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import axios from 'axios'

const props = defineProps({
  connection: {
    type: Object,
    default: null
  },
  isEdit: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['save', 'cancel'])

const formRef = ref(null)
const testing = ref(false)
const isInitializing = ref(false) // 标记是否正在初始化

const form = reactive({
  name: '',
  driver: 'mysql',
  host: '127.0.0.1',
  port: 3306,
  database: '',
  username: '',
  password: '',
  charset: 'utf8mb4',
  collation: '',
  description: '',
  is_active: true
})

const rules = {
  name: [
    { required: true, message: '请输入连接名称', trigger: 'blur' },
    { max: 100, message: '连接名称最多100个字符', trigger: 'blur' }
  ],
  driver: [
    { required: true, message: '请选择驱动类型', trigger: 'change' }
  ],
  host: [
    { required: true, message: '请输入主机地址', trigger: 'blur' }
  ],
  database: [
    { required: true, message: '请输入数据库名称', trigger: 'blur' }
  ],
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' }
  ]
}

/**
 * 初始化表单数据
 */
const initForm = async () => {
  isInitializing.value = true // 开始初始化

  if (props.connection) {
    Object.assign(form, {
      name: props.connection.name || '',
      driver: props.connection.driver || 'mysql',
      host: props.connection.host || '127.0.0.1',
      port: props.connection.port || 3306,
      database: props.connection.database || '',
      username: props.connection.username || '',
      password: props.connection.password || '',
      charset: props.connection.charset || 'utf8mb4',
      collation: props.connection.collation || '',
      description: props.connection.description || '',
      is_active: props.connection.is_active ?? true
    })
  }

  // 使用 nextTick 确保 watch 触发后再重置标志
  await nextTick()
  isInitializing.value = false // 初始化完成
}

/**
 * 监听驱动变化，调整端口
 *
 * 只在用户主动改变驱动时才修改端口，初始化时不触发
 */
watch(() => form.driver, (newDriver, oldDriver) => {
  // 初始化时不触发
  if (isInitializing.value) return

  // 只有在驱动类型实际改变时才修改端口
  if (newDriver !== oldDriver && oldDriver !== undefined) {
    if (newDriver === 'mysql') {
      form.port = 3306
    } else if (newDriver === 'pgsql') {
      form.port = 5432
    }
  }
})

/**
 * 测试连接
 */
const handleTest = async () => {
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  testing.value = true
  try {
    const response = await axios.post('/admin/featuredbadmin/connections/test-config', form)

    if (response.data.success) {
      ElMessage.success(`连接测试成功！版本: ${response.data.version}`)
    } else {
      ElMessage.error(`连接测试失败: ${response.data.message}`)
    }
  } catch (error) {
    console.error('测试失败:', error)
    ElMessage.error('连接测试失败')
  } finally {
    testing.value = false
  }
}

/**
 * 提交表单
 */
const handleSubmit = async () => {
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  try {
    let response
    if (props.isEdit && props.connection?.id) {
      // 编辑
      response = await axios.put(`/admin/featuredbadmin/connections/${props.connection.id}`, form)
    } else {
      // 新建
      response = await axios.post('/admin/featuredbadmin/connections', form)
    }

    if (response.data.success) {
      ElMessage.success(props.isEdit ? '连接更新成功' : '连接创建成功')
      emit('save')
    } else {
      ElMessage.error(response.data.message || '保存失败')
    }
  } catch (error) {
    console.error('保存失败:', error)
    ElMessage.error('保存失败')
  }
}

onMounted(() => {
  initForm()
})

watch(() => props.connection, () => {
  initForm()
})
</script>

<style scoped>
:deep(.el-form-item__label) {
  font-weight: 500;
}
</style>