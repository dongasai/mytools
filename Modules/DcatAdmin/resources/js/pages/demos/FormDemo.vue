<template>
  <div class="demo-page">
    <h1>表单组件演示</h1>

    <section class="demo-section">
      <h2>Input 输入框</h2>

      <h3>基础用法</h3>
      <el-input v-model="input" placeholder="请输入内容" style="width: 300px" />

      <h3>禁用状态</h3>
      <el-input v-model="input" placeholder="禁用状态" disabled style="width: 300px" />

      <h3>可清空</h3>
      <el-input v-model="input" placeholder="可清空" clearable style="width: 300px" />

      <h3>密码框</h3>
      <el-input v-model="password" type="password" placeholder="请输入密码" show-password style="width: 300px" />

      <h3>文本域</h3>
      <el-input v-model="textarea" type="textarea" :rows="3" placeholder="请输入内容" style="width: 300px" />

      <h3>复合型输入框</h3>
      <el-input v-model="input3" placeholder="请输入内容" style="width: 300px">
        <template #prepend>Http://</template>
      </el-input>

      <el-input v-model="input4" placeholder="请输入内容" style="width: 300px; margin-top: 10px">
        <template #append>.com</template>
      </el-input>
    </section>

    <section class="demo-section">
      <h2>Select 选择器</h2>

      <h3>基础用法</h3>
      <el-select v-model="selectValue" placeholder="请选择" style="width: 300px">
        <el-option label="选项一" value="1" />
        <el-option label="选项二" value="2" />
        <el-option label="选项三" value="3" />
        <el-option label="选项四" value="4" />
        <el-option label="选项五" value="5" />
      </el-select>

      <h3>禁用状态</h3>
      <el-select v-model="selectValue" disabled placeholder="禁用状态" style="width: 300px">
        <el-option label="选项一" value="1" />
        <el-option label="选项二" value="2" />
      </el-select>

      <h3>可清空</h3>
      <el-select v-model="selectValue" clearable placeholder="可清空" style="width: 300px">
        <el-option label="选项一" value="1" />
        <el-option label="选项二" value="2" />
      </el-select>

      <h3>多选</h3>
      <el-select v-model="multiSelect" multiple placeholder="请选择" style="width: 300px">
        <el-option label="选项一" value="1" />
        <el-option label="选项二" value="2" />
        <el-option label="选项三" value="3" />
      </el-select>

      <h3>分组</h3>
      <el-select v-model="groupValue" placeholder="请选择" style="width: 300px">
        <el-option-group label="热门城市">
          <el-option label="北京" value="beijing" />
          <el-option label="上海" value="shanghai" />
        </el-option-group>
        <el-option-group label="城市名">
          <el-option label="南京" value="nanjing" />
          <el-option label="杭州" value="hangzhou" />
        </el-option-group>
      </el-select>
    </section>

    <section class="demo-section">
      <h2>Radio 单选框</h2>

      <h3>基础用法</h3>
      <el-radio-group v-model="radio">
        <el-radio :label="1">选项A</el-radio>
        <el-radio :label="2">选项B</el-radio>
        <el-radio :label="3">选项C</el-radio>
      </el-radio-group>

      <h3>禁用状态</h3>
      <el-radio-group v-model="radio" disabled>
        <el-radio :label="1">选项A</el-radio>
        <el-radio :label="2">选项B</el-radio>
      </el-radio-group>

      <h3>按钮样式</h3>
      <el-radio-group v-model="radio">
        <el-radio-button :label="1">选项A</el-radio-button>
        <el-radio-button :label="2">选项B</el-radio-button>
        <el-radio-button :label="3">选项C</el-radio-button>
      </el-radio-group>
    </section>

    <section class="demo-section">
      <h2>Checkbox 复选框</h2>

      <h3>基础用法</h3>
      <el-checkbox v-model="checked">备选项</el-checkbox>

      <h3>禁用状态</h3>
      <el-checkbox v-model="checked" disabled>禁用</el-checkbox>
      <el-checkbox v-model="checked1" disabled>禁用</el-checkbox>

      <h3>多选框组</h3>
      <el-checkbox-group v-model="checkList">
        <el-checkbox label="选项A" />
        <el-checkbox label="选项B" />
        <el-checkbox label="选项C" />
      </el-checkbox-group>

      <h3>按钮样式</h3>
      <el-checkbox-group v-model="checkList">
        <el-checkbox-button label="选项A" />
        <el-checkbox-button label="选项B" />
        <el-checkbox-button label="选项C" />
      </el-checkbox-group>
    </section>

    <section class="demo-section">
      <h2>Switch 开关</h2>

      <h3>基础用法</h3>
      <el-switch v-model="switchValue" />

      <h3>文字描述</h3>
      <el-switch v-model="switchValue" active-text="按月付费" inactive-text="按年付费" />

      <h3>自定义颜色</h3>
      <el-switch v-model="switchValue" active-color="#13ce66" inactive-color="#ff4949" />

      <h3>禁用状态</h3>
      <el-switch v-model="switchValue" disabled />
    </section>

    <section class="demo-section">
      <h2>DatePicker 日期选择器</h2>

      <h3>选择日</h3>
      <el-date-picker v-model="date" type="date" placeholder="选择日期" style="width: 300px" />

      <h3>选择日期时间</h3>
      <el-date-picker v-model="datetime" type="datetime" placeholder="选择日期时间" style="width: 300px" />

      <h3>选择日期范围</h3>
      <el-date-picker v-model="dateRange" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" style="width: 400px" />

      <h3>选择月份</h3>
      <el-date-picker v-model="month" type="month" placeholder="选择月" style="width: 300px" />

      <h3>选择年</h3>
      <el-date-picker v-model="year" type="year" placeholder="选择年" style="width: 300px" />
    </section>

    <section class="demo-section">
      <h2>Upload 上传</h2>

      <h3>点击上传</h3>
      <el-upload
        action="#"
        :auto-upload="false"
      >
        <el-button type="primary">点击上传</el-button>
        <template #tip>
          <div class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
        </template>
      </el-upload>

      <h3>拖拽上传</h3>
      <el-upload
        class="upload-demo"
        drag
        action="#"
        :auto-upload="false"
      >
        <el-icon class="el-icon--upload"><upload-filled /></el-icon>
        <div class="el-upload__text">
          将文件拖到此处，或<em>点击上传</em>
        </div>
        <template #tip>
          <div class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
        </template>
      </el-upload>
    </section>

    <section class="demo-section">
      <h2>Form 表单</h2>

      <el-form :model="form" label-width="80px" style="max-width: 600px">
        <el-form-item label="用户名">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="密码">
          <el-input v-model="form.password" type="password" show-password />
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
            <el-checkbox label="美食/餐厅线上活动" />
            <el-checkbox label="地推活动" />
            <el-checkbox label="线下主题活动" />
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
          <el-button type="primary" @click="onSubmit">立即创建</el-button>
          <el-button>取消</el-button>
        </el-form-item>
      </el-form>
    </section>
  </div>
</template>

<script setup>
import { ref, getCurrentInstance } from 'vue'
import { UploadFilled } from '@element-plus/icons-vue'

const { proxy } = getCurrentInstance()
const $message = proxy.$message

const input = ref('')
const password = ref('')
const textarea = ref('')
const input3 = ref('')
const input4 = ref('')

const selectValue = ref('')
const multiSelect = ref([])
const groupValue = ref('')

const radio = ref(1)
const checked = ref(true)
const checked1 = ref(false)
const checkList = ref(['选项A'])

const switchValue = ref(true)

const date = ref('')
const datetime = ref('')
const dateRange = ref([])
const month = ref('')
const year = ref('')

const form = ref({
  name: '',
  password: '',
  region: '',
  date: '',
  delivery: false,
  type: [],
  resource: '',
  desc: ''
})

const onSubmit = () => {
  $message.success('表单提交成功！')
}
</script>

<style scoped>
.demo-page {
  padding: 20px;
  background: white;
}

h1 {
  font-size: 28px;
  margin-bottom: 20px;
  color: #303133;
  border-bottom: 2px solid #409EFF;
  padding-bottom: 10px;
}

.demo-section {
  margin-bottom: 40px;
}

h2 {
  font-size: 22px;
  margin: 30px 0 15px;
  color: #303133;
}

h3 {
  font-size: 16px;
  margin: 20px 0 10px;
  color: #606266;
}

p {
  color: #909399;
  margin-bottom: 15px;
}

.el-select, .el-date-editor {
  margin-bottom: 10px;
}
</style>