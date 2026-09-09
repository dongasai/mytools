<template>
  <div ref="editorContainer" class="monaco-editor-container"></div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import * as monaco from 'monaco-editor'
import { format as formatSQL } from 'sql-formatter'

const props = defineProps({
  /**
   * 编辑器内容
   */
  modelValue: {
    type: String,
    default: ''
  },

  /**
   * 语言类型
   */
  language: {
    type: String,
    default: 'sql'
  },

  /**
   * 主题
   */
  theme: {
    type: String,
    default: 'vs'
  },

  /**
   * 是否只读
   */
  readOnly: {
    type: Boolean,
    default: false
  },

  /**
   * 字体大小
   */
  fontSize: {
    type: Number,
    default: 14
  },

  /**
   * 是否显示行号
   */
  lineNumbers: {
    type: Boolean,
    default: true
  },

  /**
   * 是否显示小地图
   */
  minimap: {
    type: Boolean,
    default: true
  },

  /**
   * 是否自动换行
   */
  wordWrap: {
    type: Boolean,
    default: true
  },

  /**
   * 是否显示折叠
   */
  folding: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'save'])

const editorContainer = ref(null)
let editorInstance = null

// ==================== 生命周期 ====================

onMounted(() => {
  initEditor()
})

onBeforeUnmount(() => {
  destroyEditor()
})

// ==================== 初始化编辑器 ====================

/**
 * 初始化 Monaco Editor
 */
const initEditor = () => {
  if (!editorContainer.value) return

  // 创建编辑器实例
  editorInstance = monaco.editor.create(editorContainer.value, {
    value: props.modelValue,
    language: props.language,
    theme: props.theme,
    readOnly: props.readOnly,
    fontSize: props.fontSize,
    lineNumbers: props.lineNumbers ? 'on' : 'off',
    minimap: { enabled: props.minimap },
    wordWrap: props.wordWrap ? 'on' : 'off',
    folding: props.folding,
    automaticLayout: true, // 自动调整布局
    scrollBeyondLastLine: false, // 不滚动超过最后一行
    renderWhitespace: 'selection', // 渲染选中的空白字符
    cursorBlinking: 'smooth', // 光标闪烁
    smoothScrolling: true, // 平滑滚动
    tabSize: 2, // Tab 大小
    insertSpaces: true, // 使用空格代替 Tab
    formatOnPaste: true, // 粘贴时自动格式化
    formatOnType: true, // 输入时自动格式化
    suggestOnTriggerCharacters: true, // 自动触发建议
    acceptSuggestionOnEnter: 'on', // Enter 接受建议
    quickSuggestions: true, // 快速建议
    parameterHints: { enabled: true }, // 参数提示
    // SQL 特定配置
    brackets: [
      ['(', ')'],
      ['[', ']'],
      ['{', '}']
    ],
    autoClosingBrackets: 'always',
    autoClosingQuotes: 'always',
    matchBrackets: 'always'
  })

  // 监听内容变化
  editorInstance.onDidChangeModelContent(() => {
    const value = editorInstance.getValue()
    emit('update:modelValue', value)
    emit('change', value)
  })

  // 注册 SQL 自动补全
  registerSQLCompletion()

  // 注册 SQL 格式化
  registerSQLFormatter()

  // 添加快捷键
  addKeyBindings()
}

/**
 * 销毁编辑器
 */
const destroyEditor = () => {
  if (editorInstance) {
    editorInstance.dispose()
    editorInstance = null
  }
}

// ==================== SQL 自动补全 ====================

/**
 * 注册 SQL 关键字自动补全
 */
const registerSQLCompletion = () => {
  // SQL 关键字
  const sqlKeywords = [
    'SELECT', 'FROM', 'WHERE', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER',
    'TABLE', 'INDEX', 'VIEW', 'DATABASE', 'AND', 'OR', 'NOT', 'IN', 'EXISTS', 'BETWEEN',
    'LIKE', 'IS', 'NULL', 'AS', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'ON',
    'GROUP', 'BY', 'ORDER', 'HAVING', 'LIMIT', 'OFFSET', 'UNION', 'ALL', 'DISTINCT',
    'COUNT', 'SUM', 'AVG', 'MAX', 'MIN', 'CASE', 'WHEN', 'THEN', 'ELSE', 'END',
    'PRIMARY', 'KEY', 'FOREIGN', 'REFERENCES', 'CONSTRAINT', 'DEFAULT', 'AUTO_INCREMENT',
    'INT', 'VARCHAR', 'TEXT', 'LONGTEXT', 'BLOB', 'DATETIME', 'TIMESTAMP', 'DATE',
    'BOOLEAN', 'DECIMAL', 'FLOAT', 'DOUBLE', 'BIGINT', 'SMALLINT', 'TINYINT'
  ]

  // SQL 函数
  const sqlFunctions = [
    'NOW()', 'DATE()', 'TIME()', 'YEAR()', 'MONTH()', 'DAY()', 'HOUR()', 'MINUTE()', 'SECOND()',
    'CONCAT()', 'SUBSTRING()', 'LENGTH()', 'CHAR_LENGTH()', 'TRIM()', 'LTRIM()', 'RTRIM()',
    'UPPER()', 'LOWER()', 'REPLACE()', 'REVERSE()', 'LEFT()', 'RIGHT()',
    'ABS()', 'CEIL()', 'FLOOR()', 'ROUND()', 'RAND()', 'SQRT()', 'POWER()',
    'IFNULL()', 'COALESCE()', 'NULLIF()', 'IF()', 'CASE()',
    'MD5()', 'SHA1()', 'SHA2()',
    'LAST_INSERT_ID()', 'UUID()'
  ]

  monaco.languages.registerCompletionItemProvider('sql', {
    provideCompletionItems: (model, position) => {
      const word = model.getWordUntilPosition(position)
      const range = {
        startLineNumber: position.lineNumber,
        endLineNumber: position.lineNumber,
        startColumn: word.startColumn,
        endColumn: word.endColumn
      }

      const suggestions = []

      // 添加关键字
      sqlKeywords.forEach(keyword => {
        suggestions.push({
          label: keyword,
          kind: monaco.languages.CompletionItemKind.Keyword,
          insertText: keyword,
          range: range,
          documentation: `SQL 关键字: ${keyword}`
        })
      })

      // 添加函数
      sqlFunctions.forEach(func => {
        suggestions.push({
          label: func,
          kind: monaco.languages.CompletionItemKind.Function,
          insertText: func,
          range: range,
          documentation: `SQL 函数: ${func}`
        })
      })

      return { suggestions }
    }
  })
}

/**
 * 注册 SQL 格式化提供器
 */
const registerSQLFormatter = () => {
  monaco.languages.registerDocumentFormattingEditProvider('sql', {
    provideDocumentFormattingEdits: (model) => {
      const text = model.getValue()
      try {
        const formatted = formatSQL(text, {
          language: 'sql',
          tabWidth: 2,
          keywordCase: 'upper',
          linesBetweenQueries: 2
        })

        return [{
          range: model.getFullModelRange(),
          text: formatted
        }]
      } catch (error) {
        console.error('SQL 格式化错误:', error)
        return []
      }
    }
  })
}

// ==================== 快捷键 ====================

/**
 * 添加快捷键绑定
 */
const addKeyBindings = () => {
  // Ctrl+Enter 执行查询
  editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Enter, () => {
    emit('save')
  })

  // Ctrl+S 保存
  editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, () => {
    emit('save')
  })

  // Ctrl+F 格式化
  editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyF, () => {
    formatDocument()
  })
}

// ==================== 公共方法 ====================

/**
 * 获取编辑器内容
 */
const getValue = () => {
  return editorInstance?.getValue() || ''
}

/**
 * 设置编辑器内容
 */
const setValue = (value) => {
  if (editorInstance) {
    editorInstance.setValue(value)
  }
}

/**
 * 清空编辑器
 */
const clear = () => {
  setValue('')
}

/**
 * 格式化文档
 */
const formatDocument = () => {
  if (editorInstance) {
    editorInstance.getAction('editor.action.formatDocument').run()
  }
}

/**
 * 插入文本
 */
const insertText = (text) => {
  if (editorInstance) {
    const position = editorInstance.getPosition()
    editorInstance.executeEdits('', [{
      range: new monaco.Range(
        position.lineNumber,
        position.column,
        position.lineNumber,
        position.column
      ),
      text: text
    }])
    editorInstance.focus()
  }
}

/**
 * 聚焦编辑器
 */
const focus = () => {
  editorInstance?.focus()
}

/**
 * 设置主题
 */
const setTheme = (theme) => {
  if (editorInstance) {
    monaco.editor.setTheme(theme)
  }
}

// ==================== 监听属性变化 ====================

watch(() => props.modelValue, (newVal) => {
  if (editorInstance && editorInstance.getValue() !== newVal) {
    editorInstance.setValue(newVal)
  }
})

watch(() => props.theme, (newTheme) => {
  if (editorInstance) {
    monaco.editor.setTheme(newTheme)
  }
})

// ==================== 暴露方法 ====================

defineExpose({
  getValue,
  setValue,
  clear,
  formatDocument,
  insertText,
  focus,
  setTheme
})
</script>

<style scoped>
.monaco-editor-container {
  width: 100%;
  height: 100%;
  min-height: 440px;
}
</style>