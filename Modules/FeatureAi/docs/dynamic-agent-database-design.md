# 动态 Agent 数据库设计

## 概述

本文档定义支持从数据库动态构建 NeuronAI Agent 的数据库架构。

**核心思路**：数据库存储配置 → DynamicAgentService 动态构建 → 执行 Agent

---

## 数据表设计

### 1. featureai_dynamic_agents - Agent 定义表

存储 Agent 的基本定义信息。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT | 主键 |
| name | VARCHAR(255) | Agent 名称 |
| slug | VARCHAR(100) | URL友好的标识符，唯一 |
| description | TEXT | 描述 |
| version | VARCHAR(20) | 版本号（如 v1.0.0） |
| is_active | BOOLEAN | 是否启用 |
| created_by | BIGINT | 创建人ID |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

**Provider 配置字段**：

| 字段 | 类型 | 说明 |
|------|------|------|
| provider_class | VARCHAR(255) | Provider类名（如 `NeuronAI\Providers\OpenAI\OpenAI`） |
| provider_config | JSON | Provider配置（api_key, model等） |

**Instructions 字段**：

| 字段 | 类型 | 说明 |
|------|------|------|
| instructions | TEXT | 系统提示词（支持模板变量） |

**行为配置字段**：

| 字段 | 类型 | 说明 |
|------|------|------|
| tool_max_runs | INT | 工具最大调用次数，默认 10 |
| parallel_tool_calls | BOOLEAN | 是否并行执行工具，默认 false |

**持久化配置**：

| 字段 | 类型 | 说明 |
|------|------|------|
| persistence_driver | ENUM | 持久化驱动：database/file/memory |

**索引**：
- UNIQUE INDEX on slug
- INDEX on is_active

**provider_config 示例**：
```json
{
    "api_key": "sk-...",
    "model": "gpt-4o",
    "temperature": 0.7,
    "max_tokens": 2000
}
```

---

### 2. featureai_dynamic_agent_tools - Agent 工具配置表

存储 Agent 可用的工具配置。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT | 主键 |
| agent_id | BIGINT | 关联 featureai_dynamic_agents.id |
| tool_class | VARCHAR(255) | 工具类名（必须实现 ToolInterface） |
| tool_name | VARCHAR(100) | 工具名称（用于日志和展示） |
| tool_description | TEXT | 工具描述 |
| tool_config | JSON | 工具配置参数（注入到构造函数） |
| order_index | INT | 排序（决定工具注册顺序） |
| is_enabled | BOOLEAN | 是否启用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

**索引**：
- INDEX on agent_id
- INDEX on tool_class
- UNIQUE INDEX on (agent_id, tool_name)

**tool_config 示例**：
```json
{
    "source": "knowledge_base",
    "max_results": 5,
    "cache_ttl": 3600
}
```

---

### 3. featureai_dynamic_agent_executions - Agent 执行记录表

记录每次 Agent 执行的历史（用于审计和统计）。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT | 主键 |
| agent_id | BIGINT | 关联 featureai_dynamic_agents.id |
| workflow_id | VARCHAR(100) | NeuronAI Workflow ID（用于恢复） |
| user_id | BIGINT | 执行用户ID |
| input_message | TEXT | 输入消息 |
| output_message | TEXT | 输出消息 |
| status | ENUM | 状态：pending/running/interrupted/completed/failed/cancelled |
| tools_called | JSON | 调用的工具列表 |
| duration_ms | INT | 执行时长（毫秒） |
| error_message | TEXT | 错误信息（failed时） |
| error_trace | TEXT | 错误堆栈（failed时） |
| created_at | TIMESTAMP | 创建时间 |
| completed_at | TIMESTAMP | 完成时间 |

**索引**：
- INDEX on agent_id
- INDEX on workflow_id
- INDEX on status
- INDEX on user_id

**tools_called 示例**：
```json
{
    "tools": [
        {
            "name": "knowledge_base",
            "called_at": "2026-09-02 10:30:15",
            "duration_ms": 150
        }
    ]
}
```

---

## 数据表关系图

```
featureai_dynamic_agents (1) ──< (N) featureai_dynamic_agent_tools
featureai_dynamic_agents (1) ──< (N) featureai_dynamic_agent_executions
```

---

## 设计要点

### 1. 节点类库（Tool Library）

**工具类必须预先定义**，数据库只存储配置：

```
Modules/FeatureAi/Tools/
├── KnowledgeBaseTool.php      # 知识库查询工具
├── TicketTool.php              # 工单管理工具
├── SearchTool.php              # 搜索工具
├── WeatherTool.php             # 天气查询工具
└── CalculatorTool.php          # 计算器工具
```

### 2. 配置注入

工具配置通过构造函数注入：

```php
class KnowledgeBaseTool extends Tool
{
    public function __construct(
        protected array $config = []
    ) {
        $source = $this->config['source'] ?? 'default';
        $maxResults = $this->config['max_results'] ?? 10;
        // ...
    }
}
```

### 3. Provider 类型支持

支持多种 AI Provider：

```php
// OpenAI
'provider_class' => 'NeuronAI\\Providers\\OpenAI\\OpenAI',
'provider_config' => '{"api_key": "sk-...", "model": "gpt-4o"}',

// Anthropic
'provider_class' => 'NeuronAI\\Providers\\Anthropic\\Anthropic',
'provider_config' => '{"api_key": "sk-...", "model": "claude-sonnet-4-6"}',
```

### 4. 持久化驱动

支持三种持久化方式：

- **database**: 使用 `DatabasePersistence`，状态存储在数据库
- **file**: 使用 `FilePersistence`，状态存储在文件系统
- **memory**: 使用 `InMemoryPersistence`，仅用于测试

---

## 示例数据

### 创建 Agent 定义

```sql
INSERT INTO featureai_dynamic_agents (
    name, slug, description, is_active,
    provider_class, provider_config,
    instructions, tool_max_runs, parallel_tool_calls,
    persistence_driver, created_by
) VALUES (
    '智能客服助手',
    'customer-service',
    '处理客户咨询的智能助手',
    true,
    'NeuronAI\\Providers\\OpenAI\\OpenAI',
    '{"api_key": "sk-...", "model": "gpt-4o"}',
    '你是一个专业的客服助手，耐心解答客户问题。',
    10,
    false,
    'database',
    1
);
```

### 添加工具配置

```sql
INSERT INTO featureai_dynamic_agent_tools (
    agent_id, tool_class, tool_name, tool_config, order_index, is_enabled
) VALUES
(1, 'Modules\\FeatureAi\\Tools\\KnowledgeBaseTool', 'knowledge_base',
 '{"source": "faq", "max_results": 5}', 1, true),
(1, 'Modules\\FeatureAi\\Tools\\TicketTool', 'create_ticket',
 '{}', 2, true);
```

---

## 迁移文件路径

```
Modules/FeatureAi/database/migrations/
├── 2026_09_02_000001_create_featureai_dynamic_agents_table.php
├── 2026_09_02_000002_create_featureai_dynamic_agent_tools_table.php
└── 2026_09_02_000003_create_featureai_dynamic_agent_executions_table.php
```

---

## 统计查询示例

### 查询 Agent 使用统计

```sql
SELECT
    da.name,
    COUNT(dae.id) as total_executions,
    AVG(dae.duration_ms) as avg_duration,
    SUM(CASE WHEN dae.status = 'success' THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN dae.status = 'failed' THEN 1 ELSE 0 END) as failed_count
FROM featureai_dynamic_agents da
LEFT JOIN featureai_dynamic_agent_executions dae ON da.id = dae.agent_id
WHERE dae.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY da.id, da.name;
```

### 查询最常用的工具

```sql
SELECT
    tool_name,
    COUNT(*) as usage_count
FROM featureai_dynamic_agent_executions
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND tools_called IS NOT NULL
GROUP BY tool_name
ORDER BY usage_count DESC;
```

---

**更新时间**: 2026-09-02
**维护者**: FeatureAi 模块