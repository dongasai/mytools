<template>
  <div class="post-list">
    <!-- 工具栏 -->
    <el-card class="toolbar-card">
      <el-button type="primary" @click="handleCreate">
        <el-icon><Plus /></el-icon>
        新建文章
      </el-button>
    </el-card>

    <!-- 文章列表 -->
    <el-card class="table-card">
      <el-table :data="posts" style="width: 100%" v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="标题" width="300" />
        <el-table-column prop="author" label="作者" width="120" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="published_at" label="发布时间" width="180" />
        <el-table-column label="操作" fixed="right">
          <template #default="scope">
            <el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
            <el-popconfirm
              title="确定删除此文章吗？"
              @confirm="handleDelete(scope.row.id)"
            >
              <template #reference>
                <el-button size="small" type="danger">删除</el-button>
              </template>
            </el-popconfirm>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 创建/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      @close="resetForm"
    >
      <el-form :model="form" label-width="80px">
        <el-form-item label="标题">
          <el-input v-model="form.title" />
        </el-form-item>
        <el-form-item label="内容">
          <el-input v-model="form.content" type="textarea" :rows="5" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="草稿" value="draft" />
            <el-option label="已发布" value="published" />
            <el-option label="已归档" value="archived" />
          </el-select>
        </el-form-item>
        <el-form-item label="发布时间">
          <el-date-picker
            v-model="form.published_at"
            type="datetime"
            placeholder="选择发布时间"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import axios from 'axios'

// 响应式数据
const loading = ref(false)
const submitting = ref(false)
const posts = ref([])
const dialogVisible = ref(false)
const dialogTitle = ref('新建文章')
const form = ref({
  id: null,
  title: '',
  content: '',
  status: 'draft',
  published_at: null,
})

// 获取文章列表
const fetchPosts = async () => {
  loading.value = true
  try {
    const response = await axios.get('/admin/module_demo5/vue-posts/list')
    if (response.data) {
      posts.value = response.data
    }
  } catch (error) {
    console.error('获取文章列表失败:', error)
    ElMessage.error('获取文章列表失败')
  } finally {
    loading.value = false
  }
}

// 新建文章
const handleCreate = () => {
  dialogTitle.value = '新建文章'
  form.value = {
    id: null,
    title: '',
    content: '',
    status: 'draft',
    published_at: new Date(),
  }
  dialogVisible.value = true
}

// 编辑文章
const handleEdit = (row) => {
  dialogTitle.value = '编辑文章'
  form.value = {
    id: row.id,
    title: row.title,
    content: row.content,
    status: row.status,
    published_at: row.published_at ? new Date(row.published_at) : null,
  }
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  submitting.value = true
  try {
    const data = {
      title: form.value.title,
      content: form.value.content,
      status: form.value.status,
      published_at: form.value.published_at
        ? new Date(form.value.published_at).toISOString()
        : null,
    }

    if (form.value.id) {
      // 更新
      await axios.put(`/admin/module_demo5/vue-posts/${form.value.id}`, data)
      ElMessage.success('更新成功')
    } else {
      // 创建
      await axios.post('/admin/module_demo5/vue-posts', data)
      ElMessage.success('创建成功')
    }

    dialogVisible.value = false
    await fetchPosts()
  } catch (error) {
    console.error('保存失败:', error)
    ElMessage.error('保存失败')
  } finally {
    submitting.value = false
  }
}

// 删除文章
const handleDelete = async (id) => {
  try {
    await axios.delete(`/admin/module_demo5/vue-posts/${id}`)
    ElMessage.success('删除成功')
    await fetchPosts()
  } catch (error) {
    console.error('删除失败:', error)
    ElMessage.error('删除失败')
  }
}

// 重置表单
const resetForm = () => {
  form.value = {
    id: null,
    title: '',
    content: '',
    status: 'draft',
    published_at: null,
  }
}

// 状态类型
const getStatusType = (status) => {
  const types = {
    'published': 'success',
    'draft': 'info',
    'archived': 'warning',
  }
  return types[status] || 'info'
}

// 状态文本
const getStatusText = (status) => {
  const texts = {
    'published': '已发布',
    'draft': '草稿',
    'archived': '已归档',
  }
  return texts[status] || status
}

// 初始化
onMounted(() => {
  fetchPosts()
})
</script>

<style scoped>
.post-list {
  padding: 20px;
  background: #f5f7fa;
  min-height: 100vh;
}

.toolbar-card {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
}
</style>