<template>
  <div class="field-editor">
    <!-- 布尔类型 -->
    <template v-if="isBooleanType">
      <el-switch
        v-model="localValue"
        active-text="是"
        inactive-text="否"
      />
    </template>

    <!-- JSON 类型 -->
    <template v-else-if="isJsonType">
      <div class="json-editor">
        <el-input
          v-model="localValue"
          type="textarea"
          :rows="rows"
          :placeholder="placeholder"
          :class="{ 'json-error': jsonError }"
        />

        <!-- JSON 错误提示 -->
        <div v-if="jsonError" class="json-error-tip">
          <el-icon><WarningFilled /></el-icon>
          <span>{{ jsonError }}</span>
        </div>

        <!-- JSON 操作按钮 -->
        <div class="json-actions">
          <el-button
            size="small"
            @click="formatJson"
          >
            <el-icon><Document /></el-icon>
            格式化
          </el-button>
          <el-button
            size="small"
            @click="validateJson"
          >
            <el-icon><CircleCheck /></el-icon>
            校验
          </el-button>
          <el-button
            size="small"
            @click="minifyJson"
          >
            <el-icon><Minus /></el-icon>
            压缩
          </el-button>
        </div>
      </div>
    </template>

    <!-- 文本类型 -->
    <template v-else-if="isTextType">
      <el-input
        v-model="localValue"
        type="textarea"
        :rows="rows"
        :placeholder="placeholder"
      />
    </template>

    <!-- 数字类型 -->
    <template v-else-if="isNumberType">
      <el-input-number
        v-model="localValue"
        style="width: 100%"
        :placeholder="placeholder"
      />
    </template>

    <!-- 默认输入框 -->
    <template v-else>
      <el-input
        v-model="localValue"
        :type="inputType"
        :rows="rows"
        :placeholder="placeholder"
        clearable
      />
    </template>

    <!-- 操作按钮 -->
    <div v-if="showActions" class="field-actions">
      <!-- 可空字段的"设为 null"按钮 -->
      <el-button
        v-if="nullable"
        size="small"
        :type="localValue === null ? 'warning' : 'default'"
        @click="setToNull"
      >
        {{ localValue === null ? '已设为 null' : '设为 null' }}
      </el-button>

      <!-- 重置按钮 -->
      <el-button
        v-if="showReset"
        size="small"
        @click="reset"
      >
        重置为原值
      </el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { WarningFilled, Document, CircleCheck, Minus } from '@element-plus/icons-vue'
import VueJsonPretty from 'vue3-json-viewer'
import 'vue3-json-viewer/dist/vue3-json-viewer.css'

const props = defineProps({
  /**
   * 字段值
   */
  modelValue: {
    type: [String, Number, Boolean, null],
    default: null
  },

  /**
   * 字段类型
   */
  fieldType: {
    type: String,
    required: true
  },

  /**
   * 是否可空
   */
  nullable: {
    type: Boolean,
    default: false
  },

  /**
   * 原始值（用于重置）
   */
  originalValue: {
    type: [String, Number, Boolean, null],
    default: null
  },

  /**
   * 占位符
   */
  placeholder: {
    type: String,
    default: '请输入值'
  },

  /**
   * 是否为大屏幕模式（影响文本域行数）
   */
  large: {
    type: Boolean,
    default: false
  },

  /**
   * 是否显示操作按钮
   */
  showActions: {
    type: Boolean,
    default: true
  },

  /**
   * 是否显示重置按钮
   */
  showReset: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue'])

// ==================== 本地值 ====================

const localValue = ref(props.modelValue)

// 监听外部值变化
watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
})

// 监听本地值变化，通知父组件
watch(localValue, (newVal) => {
  emit('update:modelValue', newVal)
})

// ==================== 类型判断 ====================

/**
 * 是否为布尔类型
 */
const isBooleanType = computed(() => {
  return props.fieldType === 'boolean'
})

/**
 * 是否为 JSON 类型
 */
const isJsonType = computed(() => {
  return ['json', 'jsonb'].includes(props.fieldType)
})

/**
 * 是否为文本类型
 */
const isTextType = computed(() => {
  return ['text', 'longtext', 'mediumtext', 'blob', 'longblob'].includes(props.fieldType)
})

/**
 * 是否为数字类型
 */
const isNumberType = computed(() => {
  return ['int', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'].includes(props.fieldType)
})

// ==================== JSON 相关 ====================

/** JSON 错误信息 */
const jsonError = ref('')

/** JSON 编辑模式 */
const jsonEditMode = ref('text') // 'text' | 'tree'

/**
 * 格式化 JSON
 */
const formatJson = () => {
  try {
    const obj = typeof localValue.value === 'string' ? JSON.parse(localValue.value) : localValue.value
    localValue.value = JSON.stringify(obj, null, 2)
    jsonError.value = ''
    ElMessage.success('JSON 格式化成功')
  } catch (e) {
    jsonError.value = `格式化失败: ${e.message}`
    ElMessage.error('JSON 格式错误')
  }
}

/**
 * 校验 JSON
 */
const validateJson = () => {
  try {
    JSON.parse(localValue.value)
    jsonError.value = ''
    ElMessage.success('JSON 格式正确')
  } catch (e) {
    jsonError.value = `校验失败: ${e.message}`
    ElMessage.error('JSON 格式错误')
  }
}

/**
 * 压缩 JSON
 */
const minifyJson = () => {
  try {
    const obj = typeof localValue.value === 'string' ? JSON.parse(localValue.value) : localValue.value
    localValue.value = JSON.stringify(obj)
    jsonError.value = ''
    ElMessage.success('JSON 压缩成功')
  } catch (e) {
    jsonError.value = `压缩失败: ${e.message}`
    ElMessage.error('JSON 格式错误')
  }
}

// ==================== 计算属性 ====================

/**
 * 输入框类型
 */
const inputType = computed(() => {
  return props.large ? 'textarea' : 'text'
})

/**
 * 文本域行数
 */
const rows = computed(() => {
  if (props.large) {
    // 大屏幕模式
    if (isTextType.value) return 20
    return 10
  } else {
    // 普通模式
    if (isTextType.value) return 5
    return 1
  }
})

// ==================== 操作方法 ====================

/**
 * 设置为 null
 */
const setToNull = () => {
  localValue.value = null
}

/**
 * 重置为原始值
 */
const reset = () => {
  localValue.value = props.originalValue
}

// ==================== 暴露方法 ====================

defineExpose({
  setToNull,
  reset
})
</script>

<style scoped>
.field-editor {
  width: 100%;
}

.field-actions {
  margin-top: 8px;
  display: flex;
  gap: 8px;
}

/* ==================== JSON 编辑器 ==================== */
.json-editor {
  width: 100%;
}

.json-error :deep(textarea) {
  border-color: #f56c6c;
}

.json-error-tip {
  margin-top: 4px;
  padding: 4px 8px;
  background: #fef0f0;
  border-radius: 4px;
  color: #f56c6c;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.json-actions {
  margin-top: 8px;
  display: flex;
  gap: 8px;
}
</style>