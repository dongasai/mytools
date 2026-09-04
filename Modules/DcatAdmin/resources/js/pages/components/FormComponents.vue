<template>
  <div class="component-section">
    <h3>Input 输入框</h3>
    <el-row :gutter="20" class="demo-row">
      <el-input v-model="input" placeholder="请输入内容" style="width: 200px" />
      <el-input v-model="input" placeholder="禁用状态" disabled style="width: 200px" />
      <el-input v-model="input" placeholder="可清空" clearable style="width: 200px" />
      <el-input v-model="input" type="password" placeholder="密码框" show-password style="width: 200px" />
    </el-row>

    <h3>Select 选择器</h3>
    <el-row :gutter="20" class="demo-row">
      <el-select v-model="selectValue" placeholder="请选择" style="width: 200px">
        <el-option label="选项一" value="1" />
        <el-option label="选项二" value="2" />
        <el-option label="选项三" value="3" />
      </el-select>

      <el-select v-model="multiSelect" multiple placeholder="多选" style="width: 300px">
        <el-option label="选项一" value="1" />
        <el-option label="选项二" value="2" />
        <el-option label="选项三" value="3" />
      </el-select>
    </el-row>

    <h3>Radio 单选框</h3>
    <el-row :gutter="20" class="demo-row">
      <el-radio-group v-model="radio">
        <el-radio :label="1">选项A</el-radio>
        <el-radio :label="2">选项B</el-radio>
        <el-radio :label="3">选项C</el-radio>
      </el-radio-group>
    </el-row>

    <h3>Checkbox 复选框</h3>
    <el-row :gutter="20" class="demo-row">
      <el-checkbox-group v-model="checkList">
        <el-checkbox label="选项A" />
        <el-checkbox label="选项B" />
        <el-checkbox label="选项C" />
      </el-checkbox-group>
    </el-row>

    <h3>Switch 开关</h3>
    <el-row :gutter="20" class="demo-row">
      <el-switch v-model="switchValue" />
      <el-switch v-model="switchValue" active-text="开" inactive-text="关" />
      <el-switch v-model="switchValue" active-color="#13ce66" inactive-color="#ff4949" />
    </el-row>

    <h3>DatePicker 日期选择器</h3>
    <el-row :gutter="20" class="demo-row">
      <el-date-picker v-model="date" type="date" placeholder="选择日期" style="width: 200px" />
      <el-date-picker v-model="dateRange" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" style="width: 300px" />
    </el-row>

    <h3>Upload 上传</h3>
    <el-row :gutter="20" class="demo-row">
      <el-upload action="#" :auto-upload="false">
        <el-button type="primary">点击上传</el-button>
        <template #tip>
          <div class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
        </template>
      </el-upload>
    </el-row>

    <h3>Form 表单</h3>
    <el-form :model="form" label-width="80px" style="max-width: 500px">
      <el-form-item label="用户名">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item label="活动区域">
        <el-select v-model="form.region" placeholder="请选择活动区域">
          <el-option label="区域一" value="shanghai" />
          <el-option label="区域二" value="beijing" />
        </el-select>
      </el-form-item>
      <el-form-item label="活动时间">
        <el-date-picker v-model="form.date" type="date" placeholder="选择日期" />
      </el-form-item>
      <el-form-item label="即时配送">
        <el-switch v-model="form.delivery" />
      </el-form-item>
      <el-form-item label="活动性质">
        <el-checkbox-group v-model="form.type">
          <el-checkbox label="美食/餐厅线上活动" name="type" />
          <el-checkbox label="地推活动" name="type" />
        </el-checkbox-group>
      </el-form-item>
      <el-form-item label="特殊资源">
        <el-radio-group v-model="form.resource">
          <el-radio label="线上品牌商赞助" />
          <el-radio label="线下场地免费" />
        </el-radio-group>
      </el-form-item>
      <el-form-item label="活动形式">
        <el-input v-model="form.desc" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submitForm">立即创建</el-button>
        <el-button>取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, getCurrentInstance } from 'vue'

const { proxy } = getCurrentInstance()
const $message = proxy.$message

const input = ref('')
const selectValue = ref('')
const multiSelect = ref([])
const radio = ref(1)
const checkList = ref(['选项A'])
const switchValue = ref(true)
const date = ref('')
const dateRange = ref([])

const form = ref({
  name: '',
  region: '',
  date: '',
  delivery: false,
  type: [],
  resource: '',
  desc: ''
})

const submitForm = () => {
  $message.success('表单提交成功！')
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