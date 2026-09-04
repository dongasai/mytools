# AI模块数据库设计文档

> 设计时间：2026-05-05
> 最后更新：2026-09-01
> 设计原则：遵循 design-database skill 规范
> 文档状态：已实现

---

## 一、数据库设计原则

### 核心规范（强制遵守）

| 规范 | 说明 |
|------|------|
| **无外键约束** | 在应用层处理关联关系，避免数据库层外键 |
| **无check约束** | 枚举验证在应用层处理 |
| **无触发器** | 业务逻辑在应用层处理 |
| **无视图** | 不使用数据库视图 |
| **命名规范** | 小写字母+下划线，避免MySQL保留字 |

---

## 二、数据表总览

### 2.1 表清单

| 表名 | 职责 | 模块 | 状态 |
|------|------|------|------|
| `ai_providers` | AI提供商配置 | 基础配置 | ✅ 已实现 |
| `ai_provider_models` | 提供商下的模型配置 | 基础配置 | ✅ 已实现 |
| `ai_conversations` | AI对话记录 | 业务数据 | ✅ 已实现 |
| `ai_images` | AI图片生成记录 | 业务数据 | ✅ 已实现 |
| `ai_tests` | AI集成测试记录 | 测试数据 | ✅ 已实现 |
| `ai_test_results` | 测试结果详情 | 测试数据 | ✅ 已实现 |
| `ai_service_mappings` | 服务映射配置（新增） | 基础配置 | ✅ 已实现 |
| `ai_llm_api_logs` | LLM API调用日志（新增） | 日志数据 | ✅ 已实现 |

### 2.2 表关系图

```
ai_providers (1) ──→ (N) ai_provider_models
        ↓                              ↓
        ├──────────→ (N) ai_conversations
        ├──────────→ (N) ai_images
        ├──────────→ (N) ai_tests (1) ──→ (N) ai_test_results
        └──────────→ (N) ai_llm_api_logs
        ↓
ai_service_mappings (服务类型+服务名字→提供商映射)
```

**关系说明**：
- 一个提供商可有多个模型
- 一个模型可被多次用于对话/图片生成/测试
- 一个测试可有多个测试结果记录
- **服务映射**：支持按服务类型+服务名字灵活匹配提供商

---

## 三、详细表结构设计

### 3.1 ai_providers（AI提供商配置表）

**表用途**：存储AI服务提供商的基础配置信息

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `provider_type` | varchar | 50 | NULL | YES | INDEX | 提供商类型(openai/claude/gemini/deepseek/zai/minimax/mistral/ollama/cohere/xai/aws/huggingface/elevenlabs/custom) |
| `provider_name` | varchar | 100 | NULL | YES | - | 提供商名称（同一类型可有多个配置） |
| `api_key` | varchar | 255 | NULL | YES | - | API密钥(加密存储) |
| `api_endpoint` | varchar | 255 | NULL | NO | - | API端点URL |
| `is_active` | tinyint | 1 | 1 | YES | INDEX | 是否启用:1启用,2禁用 |
| `priority` | tinyint | 3 | 0 | YES | - | 优先级(数字越大优先级越高) |
| `config_json` | json | - | NULL | NO | - | 其他配置参数(JSON格式) |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |
| `deleted_at` | timestamp | - | NULL | NO | INDEX | 软删除时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_provider_type (provider_type)`
- `INDEX idx_is_active (is_active)`
- `INDEX idx_deleted_at (deleted_at)`

**表注释**：`AI服务提供商配置表`

**支持的提供商类型**（14种）：
- **OpenAI** - GPT系列
- **Claude** - Anthropic
- **Gemini** - Google
- **Deepseek** - 国产大模型
- **ZAI** - 智谱AI (GLM)
- **MiniMax** - 使用Anthropic兼容API
- **Mistral** - 欧洲开源模型
- **Ollama** - 本地模型
- **Cohere** - 企业级AI
- **XAI** - Grok
- **AWS** - Bedrock Runtime
- **HuggingFace** - 开源模型社区
- **ElevenLabs** - 语音合成
- **Custom** - 自定义提供商

---

### 3.2 ai_provider_models（AI模型配置表）

**表用途**：存储提供商下的具体模型配置信息

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `provider_id` | bigint | - | NULL | YES | INDEX | 提供商ID(关联ai_providers.id) |
| `model_name` | varchar | 100 | NULL | YES | INDEX | 模型名称(gpt-4/claude-3-opus等) |
| `model_type` | varchar | 50 | NULL | YES | INDEX | 模型类型(chat/image/embedding/other) |
| `max_tokens` | int | 11 | 4096 | YES | - | 最大tokens限制 |
| `cost_per_input_token` | decimal | 10,8 | 0.00000000 | YES | - | 输入tokens单价(美元) |
| `cost_per_output_token` | decimal | 10,8 | 0.00000000 | YES | - | 输出tokens单价(美元) |
| `is_active` | tinyint | 1 | 1 | YES | INDEX | 是否启用:1启用,2禁用 |
| `config_json` | json | - | NULL | NO | - | 模型特定配置(JSON格式) |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |
| `deleted_at` | timestamp | - | NULL | NO | INDEX | 软删除时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_provider_id (provider_id)`
- `INDEX idx_model_name (model_name)`
- `INDEX idx_model_type (model_type)`
- `INDEX idx_is_active (is_active)`
- `INDEX idx_deleted_at (deleted_at)`

**表注释**：`AI模型配置表`

**关联关系**（应用层处理）：
- `provider_id` 关联 `ai_providers.id`，在Model层处理关联查询

---

### 3.3 ai_conversations（AI对话记录表）

**表用途**：存储用户与AI的对话交互记录

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `provider_id` | bigint | - | NULL | YES | INDEX | 提供商ID |
| `model_id` | bigint | - | NULL | YES | INDEX | 模型ID |
| `user_id` | bigint | - | NULL | YES | INDEX | 用户ID(可选,后台测试时可为NULL) |
| `conversation_id` | varchar | 100 | NULL | YES | INDEX | 对话会话ID(多轮对话标识) |
| `prompt_text` | text | - | NULL | YES | - | 用户输入的提示文本 |
| `response_text` | text | - | NULL | NO | - | AI返回的响应文本 |
| `input_tokens` | int | 11 | 0 | YES | - | 输入tokens数量 |
| `output_tokens` | int | 11 | 0 | YES | - | 输出tokens数量 |
| `total_cost` | decimal | 10,6 | 0.000000 | YES | - | 总成本(美元) |
| `status` | tinyint | 1 | 1 | YES | INDEX | 状态:1进行中,2已完成,3失败 |
| `error_message` | text | - | NULL | NO | - | 错误信息(失败时记录) |
| `response_time_ms` | int | 11 | 0 | YES | - | 响应时间(毫秒) |
| `context_json` | json | - | NULL | NO | - | 对话上下文(JSON格式) |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_provider_id (provider_id)`
- `INDEX idx_model_id (model_id)`
- `INDEX idx_user_id (user_id)`
- `INDEX idx_conversation_id (conversation_id)`
- `INDEX idx_status (status)`
- `INDEX idx_created_at (created_at)`

**表注释**：`AI对话记录表`

**关联关系**（应用层处理）：
- `provider_id` 关联 `ai_providers.id`
- `model_id` 关联 `ai_provider_models.id`
- `user_id` 关联 `user_users.id`（跨模块）

---

### 3.4 ai_images（AI图片生成记录表）

**表用途**：存储AI生成图片的记录信息

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `provider_id` | bigint | - | NULL | YES | INDEX | 提供商ID |
| `model_id` | bigint | - | NULL | YES | INDEX | 模型ID |
| `user_id` | bigint | - | NULL | YES | INDEX | 用户ID |
| `prompt_text` | text | - | NULL | YES | - | 图片生成提示文本 |
| `image_url` | varchar | 500 | NULL | NO | - | 生成的图片URL |
| `image_path` | varchar | 255 | NULL | NO | - | 图片存储路径(本地或云端) |
| `image_size` | varchar | 50 | NULL | NO | - | 图片尺寸(如1024x1024) |
| `cost` | decimal | 10,6 | 0.000000 | YES | - | 生成成本(美元) |
| `status` | tinyint | 1 | 1 | YES | INDEX | 状态:1待处理,2生成中,3成功,4失败 |
| `error_message` | text | - | NULL | NO | - | 错误信息 |
| `retry_count` | tinyint | 3 | 0 | YES | - | 重试次数 |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |
| `deleted_at` | timestamp | - | NULL | NO | INDEX | 软删除时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_provider_id (provider_id)`
- `INDEX idx_model_id (model_id)`
- `INDEX idx_user_id (user_id)`
- `INDEX idx_status (status)`
- `INDEX idx_deleted_at (deleted_at)`

**表注释**：`AI图片生成记录表`

**关联关系**（应用层处理）：
- `provider_id` 关联 `ai_providers.id`
- `model_id` 关联 `ai_provider_models.id`
- `user_id` 关联 `user_users.id`

---

### 3.5 ai_tests（AI集成测试记录表）

**表用途**：存储AI服务集成测试的主记录

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `provider_id` | bigint | - | NULL | YES | INDEX | 提供商ID |
| `model_id` | bigint | - | NULL | YES | INDEX | 模型ID |
| `test_type` | varchar | 50 | NULL | YES | INDEX | 测试类型(connect/response/cost/image) |
| `test_name` | varchar | 100 | NULL | YES | - | 测试名称 |
| `test_config_json` | json | - | NULL | NO | - | 测试配置(JSON格式) |
| `status` | tinyint | 1 | 1 | YES | INDEX | 状态:1待执行,2执行中,3成功,4失败 |
| `total_tests` | int | 11 | 1 | YES | - | 总测试次数 |
| `success_count` | int | 11 | 0 | YES | - | 成功次数 |
| `fail_count` | int | 11 | 0 | YES | - | 失败次数 |
| `avg_response_time_ms` | int | 11 | 0 | YES | - | 平均响应时间(毫秒) |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_provider_id (provider_id)`
- `INDEX idx_model_id (model_id)`
- `INDEX idx_test_type (test_type)`
- `INDEX idx_status (status)`

**表注释**：`AI集成测试记录表`

**关联关系**（应用层处理）：
- `provider_id` 关联 `ai_providers.id`
- `model_id` 关联 `ai_provider_models.id`

---

### 3.6 ai_test_results（AI测试结果详情表）

**表用途**：存储每次测试的具体执行结果

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `test_id` | bigint | - | NULL | YES | INDEX | 测试记录ID |
| `test_sequence` | int | 11 | 1 | YES | - | 测试序号 |
| `is_success` | tinyint | 1 | 0 | YES | INDEX | 是否成功:1成功,2失败 |
| `response_time_ms` | int | 11 | 0 | YES | - | 响应时间(毫秒) |
| `input_tokens` | int | 11 | 0 | YES | - | 输入tokens |
| `output_tokens` | int | 11 | 0 | YES | - | 输出tokens |
| `cost` | decimal | 10,6 | 0.000000 | YES | - | 成本(美元) |
| `response_text` | text | - | NULL | NO | - | 响应文本 |
| `error_message` | text | - | NULL | NO | - | 错误信息 |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_test_id (test_id)`
- `INDEX idx_is_success (is_success)`

**表注释**：`AI测试结果详情表`

**关联关系**（应用层处理）：
- `test_id` 关联 `ai_tests.id`

---

### 3.7 ai_service_mappings（AI服务映射表）- 新增

**表用途**：服务类型+服务名字到供应商的映射关系管理

**设计背景**：支持业务系统灵活选择AI服务商，同一服务类型可配置多个供应商

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `service_type` | varchar | 50 | NULL | YES | INDEX | 服务类型，如a/b等 |
| `service_name` | varchar | 100 | '' | YES | UNIQUE | 服务名字，如name1/name2等，空字符串表示默认 |
| `provider_id` | bigint | - | NULL | YES | INDEX | 关联ai_providers.id |
| `is_active` | tinyint | 1 | 1 | YES | INDEX | 是否启用:1启用,2禁用 |
| `description` | varchar | 255 | NULL | NO | - | 映射说明 |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |
| `deleted_at` | timestamp | - | NULL | NO | INDEX | 软删除时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `INDEX idx_service_type (service_type)`
- `INDEX idx_provider_id (provider_id)`
- `INDEX idx_is_active (is_active)`
- `INDEX idx_deleted_at (deleted_at)`
- `UNIQUE INDEX uk_service_type_name (service_type, service_name)` - 组合唯一索引

**表注释**：`AI服务映射表：服务类型+服务名字→供应商映射关系`

**映射规则**：
1. **精确匹配优先**：优先匹配 `service_type + service_name`
2. **默认回退**：无精确匹配时，回退到 `service_type + ''` (默认供应商)
3. **唯一性保证**：`service_type + service_name` 组合唯一

**关联关系**（应用层处理）：
- `provider_id` 关联 `ai_providers.id`

---

### 3.8 ai_llm_api_logs（LLM API调用日志表）- 新增

**表用途**：记录所有LLM API调用的详细信息，用于成本分析和性能监控

**设计背景**：统一记录所有AI服务的API调用，支持成本追踪、性能分析、问题排查

| 字段名 | 类型 | 长度 | 默认值 | 必填 | 索引 | 说明 |
|--------|------|------|--------|------|------|------|
| `id` | bigint | - | AUTO_INCREMENT | YES | PRI | 主键ID |
| `provider_id` | bigint | - | NULL | YES | INDEX | 提供商ID |
| `model_id` | bigint | - | NULL | YES | INDEX | 模型ID |
| `model_name` | varchar | 100 | NULL | YES | INDEX | 模型名称(冗余,便于查询) |
| `request_id` | varchar | 100 | NULL | YES | UNIQUE | 请求ID(唯一,对应日志文件) |
| `service_type` | varchar | 50 | NULL | YES | INDEX | 服务类型(如chat,image等) |
| `service_name` | varchar | 100 | NULL | YES | INDEX | 服务名称(具体服务标识) |
| `input_tokens` | int | 11 | 0 | YES | - | 输入tokens数量 |
| `output_tokens` | int | 11 | 0 | YES | - | 输出tokens数量 |
| `total_tokens` | int | 11 | 0 | YES | - | 总tokens数量 |
| `input_cost` | decimal | 15,10 | 0.0000000000 | YES | - | 输入成本(美元,高精度) |
| `output_cost` | decimal | 15,10 | 0.0000000000 | YES | - | 输出成本(美元,高精度) |
| `total_cost` | decimal | 15,10 | 0.0000000000 | YES | - | 总成本(美元,高精度) |
| `response_time_ms` | int | 11 | 0 | YES | - | 响应时间(毫秒) |
| `success` | tinyint | 1 | 1 | YES | INDEX | 是否成功:1成功,0失败 |
| `error_type` | varchar | 50 | NULL | NO | INDEX | 错误类型(失败时记录) |
| `error_message` | text | - | NULL | NO | - | 错误详细信息(失败时记录) |
| `created_at` | timestamp | - | CURRENT_TIMESTAMP | YES | INDEX | 创建时间 |
| `updated_at` | timestamp | - | CURRENT_TIMESTAMP | YES | - | 更新时间 |

**索引设计**：
- `PRIMARY KEY (id)`
- `UNIQUE INDEX request_id (request_id)` - 请求唯一标识
- `INDEX idx_provider_id (provider_id)`
- `INDEX idx_model_id (model_id)`
- `INDEX idx_model_name (model_name)`
- `INDEX idx_service_type (service_type)`
- `INDEX idx_service_name (service_name)`
- `INDEX idx_success (success)`
- `INDEX idx_error_type (error_type)`
- `INDEX idx_created_at (created_at)`
- `INDEX idx_service (service_type, service_name)` - 复合索引(统计分析)
- `INDEX idx_provider_time (provider_id, created_at)` - 复合索引(按时间统计)

**表注释**：`LLM API调用日志表`

**表名变更记录**：
- 原表名：`feature_ai_llm_api_logs`
- 新表名：`ai_llm_api_logs`（统一前缀为 `ai_`）
- 迁移文件：`2026_09_01_000001_rename_llm_api_logs_table.php`

**关联关系**（应用层处理）：
- `provider_id` 关联 `ai_providers.id`
- `model_id` 关联 `ai_provider_models.id`

**成本精度说明**：
- `decimal(15,10)` - 支持10位小数精度
- 原因：AI tokens成本极低（如GPT-4: $0.03/1K tokens），需要高精度记录

---

## 四、字段设计要点

### 4.1 枚举字段设计

**遵循原则**：使用 tinyint + 应用层枚举类验证

**状态字段对照表**：

| 字段 | 枚举类 | 枚举值 | 说明 |
|------|--------|--------|------|
| `provider_type` | AiProviderType | 14种提供商类型 | OpenAI/Claude/Gemini/Deepseek等 |
| `model_type` | AiModelType | chat/image/embedding/other | 模型类型 |
| `is_active` | - | 1=启用, 2=禁用 | 启用状态 |
| `status(ai_conversations)` | AiConversationStatus | 1=进行中, 2=已完成, 3=失败 | 对话状态 |
| `status(ai_images)` | AiImageStatus | 1=待处理, 2=生成中, 3=成功, 4=失败 | 图片状态 |
| `status(ai_tests)` | AiTestStatus | 1=待执行, 2=执行中, 3=成功, 4=失败 | 测试状态 |
| `is_success` | - | 1=成功, 2=失败 | 测试结果 |
| `success` | - | 1=成功, 0=失败 | API调用结果 |

### 4.2 JSON字段使用场景

**使用JSON存储灵活配置**：

| 字段 | 用途 | 示例数据 |
|------|------|---------|
| `config_json(ai_providers)` | 提供商特定配置 | `{"timeout": 30, "max_retries": 3, "default_chat_model": "gpt-4"}` |
| `config_json(ai_provider_models)` | 模型特定配置 | `{"temperature": 0.7, "top_p": 1, "max_output_tokens": 2048}` |
| `context_json(ai_conversations)` | 对话上下文历史 | `{"history": [{"role": "user", "content": "..."}]}` |
| `test_config_json(ai_tests)` | 测试配置参数 | `{"test_prompts": ["hello", "test"], "max_tokens": 100}` |

### 4.3 成本字段设计

**精确成本记录**：

| 字段 | 类型 | 精度 | 说明 |
|------|------|------|------|
| `cost_per_input_token` | decimal(10,8) | 8位小数 | 输入token单价(美元) |
| `cost_per_output_token` | decimal(10,8) | 8位小数 | 输出token单价(美元) |
| `total_cost(ai_conversations)` | decimal(10,6) | 6位小数 | 总成本(美元) |
| `cost` | decimal(10,6) | 6位小数 | 成本(美元) |
| `input_cost/output_cost/total_cost(llm_api_logs)` | decimal(15,10) | 10位小数 | API调用成本(美元) |

**设计原因**：AI tokens成本极低，需要高精度记录（如GPT-4: $0.03/1K tokens）

### 4.4 时间字段设计

**时间戳字段规范**：

| 字段 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `created_at` | timestamp | CURRENT_TIMESTAMP | 创建时间 |
| `updated_at` | timestamp | CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新时间 |
| `deleted_at` | timestamp | NULL | 软删除时间 |
| `response_time_ms` | int | - | 响应时间(毫秒，性能监控) |

---

## 五、索引策略

### 5.1 索引设计原则

| 原则 | 说明 |
|------|------|
| **主键索引** | 所有表必须有主键(id) |
| **外键字段索引** | 关联字段必须加索引(provider_id/model_id/user_id) |
| **状态字段索引** | 查询频繁的状态字段加索引(status/is_active) |
| **时间字段索引** | 查询范围的时间字段加索引(created_at/deleted_at) |
| **唯一标识索引** | 业务唯一标识加索引(conversation_id/request_id) |
| **复合索引** | 统计分析场景使用复合索引(service_type+service_name, provider_id+created_at) |

### 5.2 索引命名规范

| 索引类型 | 命名格式 | 示例 |
|---------|---------|------|
| 主键 | `PRIMARY KEY` | `PRIMARY KEY (id)` |
| 普通索引 | `idx_{字段名}` | `INDEX idx_provider_id (provider_id)` |
| 组合索引 | `idx_{字段1}_{字段2}` | `INDEX idx_provider_model (provider_id, model_id)` |
| 唯一索引 | `uk_{字段名}` | `UNIQUE INDEX uk_service_type_name (service_type, service_name)` |

### 5.3 索引清单

**ai_providers**：
- `idx_provider_type` - 按提供商类型查询
- `idx_is_active` - 查询启用的提供商
- `idx_deleted_at` - 软删除查询

**ai_provider_models**：
- `idx_provider_id` - 查询提供商下的模型
- `idx_model_name` - 按模型名称查询
- `idx_model_type` - 按模型类型查询
- `idx_is_active` - 查询启用的模型

**ai_conversations**：
- `idx_provider_id` - 按提供商统计
- `idx_model_id` - 按模型统计
- `idx_user_id` - 查询用户对话
- `idx_conversation_id` - 多轮对话查询
- `idx_status` - 查询进行中/完成的对话
- `idx_created_at` - 按时间范围查询

**ai_images**：
- `idx_provider_id` - 按提供商统计
- `idx_model_id` - 按模型统计
- `idx_user_id` - 查询用户图片
- `idx_status` - 查询待处理/成功的图片

**ai_tests**：
- `idx_provider_id` - 查询提供商测试
- `idx_model_id` - 查询模型测试
- `idx_test_type` - 按测试类型查询
- `idx_status` - 查询测试状态

**ai_test_results**：
- `idx_test_id` - 查询测试详情
- `idx_is_success` - 查询成功/失败结果

**ai_service_mappings**：
- `idx_service_type` - 按服务类型查询
- `idx_provider_id` - 按提供商查询
- `idx_is_active` - 查询启用的映射
- `uk_service_type_name` - 服务类型+服务名字唯一约束

**ai_llm_api_logs**：
- `request_id` - 请求唯一标识
- `idx_provider_id` - 按提供商统计
- `idx_model_id` - 按模型统计
- `idx_service` - 服务复合索引
- `idx_provider_time` - 按提供商时间统计
- `idx_success` - 查询成功/失败记录
- `idx_error_type` - 按错误类型排查

---

## 六、表注释规范

**所有表必须添加注释**：

```sql
ALTER TABLE ai_providers COMMENT 'AI服务提供商配置表';
ALTER TABLE ai_provider_models COMMENT 'AI模型配置表';
ALTER TABLE ai_conversations COMMENT 'AI对话记录表';
ALTER TABLE ai_images COMMENT 'AI图片生成记录表';
ALTER TABLE ai_tests COMMENT 'AI集成测试记录表';
ALTER TABLE ai_test_results COMMENT 'AI测试结果详情表';
ALTER TABLE ai_service_mappings COMMENT 'AI服务映射表：服务类型+服务名字→供应商映射关系';
ALTER TABLE ai_llm_api_logs COMMENT 'LLM API调用日志表';
```

---

## 七、迁移文件创建计划

### 7.1 创建顺序（按依赖关系）

| 序号 | 迁移文件名 | 时间戳 | 说明 |
|------|-----------|--------|------|
| 1 | `create_ai_providers_table.php` | `2026_05_05_143419` | 基础表，无依赖 |
| 2 | `create_ai_provider_models_table.php` | `2026_05_05_143441` | 依赖ai_providers |
| 3 | `create_ai_conversations_table.php` | `2026_05_05_143500` | 依赖ai_providers+ai_provider_models |
| 4 | `create_ai_images_table.php` | `2026_05_05_143500` | 依赖ai_providers+ai_provider_models |
| 5 | `create_ai_tests_table.php` | `2026_05_05_143500` | 依赖ai_providers+ai_provider_models |
| 6 | `create_ai_test_results_table.php` | `2026_05_05_143501` | 依赖ai_tests |
| 7 | `create_ai_service_mappings_table.php` | `2026_08_11_210001` | 依赖ai_providers |
| 8 | `create_llm_api_logs_table.php` | `2026_08_20_000001` | 依赖ai_providers+ai_provider_models |
| 9 | `rename_llm_api_logs_table.php` | `2026_09_01_000001` | 重命名表（统一前缀） |

**迁移文件已创建**：所有迁移文件已按计划创建并执行

### 7.2 迁移文件编写要点

**必须包含**：
- 表结构定义
- 所有索引定义
- 表注释
- 字段注释
- down方法（dropIfExists）

**禁止包含**：
- 外键约束（foreign key）
- check约束
- 触发器

---

## 八、Model层关联处理

### 8.1 关联关系定义

**AiProvider Model**：
```php
public function models(): HasMany {
    return $this->hasMany(AiProviderModel::class, 'provider_id', 'id');
}

public function conversations(): HasMany {
    return $this->hasMany(AiConversation::class, 'provider_id', 'id');
}

public function images(): HasMany {
    return $this->hasMany(AiImage::class, 'provider_id', 'id');
}

public function tests(): HasMany {
    return $this->hasMany(AiTest::class, 'provider_id', 'id');
}
```

**AiProviderModel Model**：
```php
public function provider(): BelongsTo {
    return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
}

public function conversations(): HasMany {
    return $this->hasMany(AiConversation::class, 'model_id', 'id');
}
```

**AiServiceMapping Model**：
```php
public function provider(): BelongsTo {
    return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
}
```

**LlmApiLog Model**：
```php
public function provider(): BelongsTo {
    return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
}

public function model(): BelongsTo {
    return $this->belongsTo(AiProviderModel::class, 'model_id', 'id');
}
```

### 8.2 软删除配置

**需要软删除的Model**：
- `AiProvider` - use SoftDeletes
- `AiProviderModel` - use SoftDeletes
- `AiImage` - use SoftDeletes
- `AiServiceMapping` - use SoftDeletes

**不需要软删除的Model**：
- `AiConversation` - 对话记录不删除
- `AiTest` - 测试记录不删除
- `AiTestResult` - 测试结果不删除
- `LlmApiLog` - 日志记录不删除

---

## 九、核心功能实现

### 9.1 提供商管理服务（AiProviderService）

**核心功能**：
- **数据库优先策略**：优先从 `ai_providers` 表读取，无记录时回退到文件配置
- **服务映射查询**：支持按 `service_type + service_name` 查找提供商
- **优先级管理**：按 `priority` 字段选择最高优先级的提供商
- **成本计算**：集成模型单价配置，自动计算成本

**主要方法**：
```php
// 按服务类型+服务名字获取提供商
AiProviderService::getProviderByService(string $serviceType, ?string $serviceName = null): array

// 按提供商ID获取配置
AiProviderService::getProviderById(int $providerId): array

// 按提供商类型和名称获取配置
AiProviderService::getProviderByName(string $providerType, string $providerName): array

// 获取默认提供商
AiProviderService::getDefaultProviderType(): AiProviderType

// 获取所有已配置的提供商
AiProviderService::getConfiguredProviders(): array
```

### 9.2 API调用日志服务（LlmApiLogService）

**核心功能**：
- **统一日志记录**：所有LLM API调用统一记录到数据库
- **成本精确追踪**：自动计算输入/输出成本（decimal(15,10)精度）
- **性能监控**：记录响应时间、tokens使用量
- **错误追踪**：记录错误类型和详细信息

**主要方法**：
```php
// 记录API调用
LlmApiLogService::logApiCall(
    string $requestId,
    array $providerConfig,
    string $prompt,
    ?Message $response,
    int $durationMs,
    array $parameters = [],
    bool $success = true,
    ?string $errorType = null,
    ?string $errorMessage = null
): void

// 生成请求唯一ID
LlmApiLogService::generateRequestId(): string
```

### 9.3 自定义Provider实现

**MiniMax Provider**：
- **路径**：`Modules/FeatureAi/AiProviders/Chat/MiniMaxChatProvider.php`
- **原因**：MiniMax使用Anthropic兼容API，需要独立驱动
- **特点**：
  - API Endpoint: `https://api.minimaxi.com/anthropic`
  - Driver: Anthropic
  - 支持流式响应

**MiniMax Image Provider**：
- **路径**：`Modules/FeatureAi/AiProviders/Image/MiniMaxImageProvider.php`
- **用途**：MiniMax图片生成服务

### 9.4 枚举类设计

**AiProviderType**：
- 14种提供商类型
- 每个类型映射到对应的NeuronAI Provider类
- 支持获取提供商名称和Provider类名

**AiModelType**：
- chat - 聊天模型
- image - 图片生成模型
- embedding - 嵌入模型
- other - 其他模型

**状态枚举**：
- AiConversationStatus - 对话状态
- AiImageStatus - 图片生成状态
- AiTestStatus - 测试状态

---

## 十、数据库设计总结

### 10.1 设计亮点

| 亮点 | 说明 |
|------|------|
| **无外键约束** | 应用层处理关联，数据库性能更好 |
| **成本精确追踪** | decimal(15,10)记录token成本，精度极高 |
| **JSON灵活配置** | 提供商/模型配置可灵活扩展 |
| **状态枚举** | tinyint+应用层枚举，查询高效 |
| **软删除** | 重要数据使用软删除保护 |
| **索引完善** | 关键查询字段全部加索引，支持复合索引优化统计查询 |
| **服务映射** | 支持业务系统灵活选择AI服务商 |
| **API日志** | 统一记录所有API调用，支持成本分析和性能监控 |
| **提供商多样化** | 支持14种AI提供商，覆盖主流AI服务 |

### 10.2 实际应用场景

**场景1：业务系统调用AI服务**
```php
// 1. 根据服务类型获取提供商
$providerConfig = AiProviderService::getProviderByService('chat', 'customer-service');

// 2. 调用AI服务
$agent = new Agent([], $providerConfig['class'], $providerConfig);
$response = $agent->chat($prompt);

// 3. 自动记录日志（已在服务层集成）
// request_id, tokens, cost, response_time 自动记录
```

**场景2：成本分析**
```sql
-- 按服务类型统计成本
SELECT service_type, service_name,
       COUNT(*) as total_calls,
       SUM(total_cost) as total_cost,
       AVG(response_time_ms) as avg_response_time
FROM ai_llm_api_logs
WHERE created_at >= '2026-09-01'
GROUP BY service_type, service_name;
```

**场景3：性能监控**
```sql
-- 查询慢请求（响应时间>5秒）
SELECT request_id, model_name, response_time_ms, total_cost, success
FROM ai_llm_api_logs
WHERE response_time_ms > 5000
  AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY response_time_ms DESC;
```

---

## 十一、迁移执行记录

**已执行的迁移**：
- ✅ 2026_05_05_143419_create_ai_providers_table
- ✅ 2026_05_05_143441_create_ai_provider_models_table
- ✅ 2026_05_05_143500_create_ai_conversations_table
- ✅ 2026_05_05_143500_create_ai_images_table
- ✅ 2026_05_05_143500_create_ai_tests_table
- ✅ 2026_05_05_143501_create_ai_test_results_table
- ✅ 2026_08_11_210001_create_ai_service_mappings_table
- ✅ 2026_08_19_204521_add_indexes_to_ai_tables
- ✅ 2026_08_20_000001_create_llm_api_logs_table
- ✅ 2026_08_21_234827_add_error_message_to_ai_llm_api_logs_table
- ✅ 2026_09_01_000001_rename_llm_api_logs_table

---

**设计完成时间**：2026-05-05
**最后更新时间**：2026-09-01
**实现状态**：✅ 已全部实现
**文档状态**：✅ 已同步实际代码