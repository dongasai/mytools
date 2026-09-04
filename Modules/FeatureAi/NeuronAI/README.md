# NeuronAI 目录结构说明

本目录专门用于实现和扩展 NeuronAI 功能，为 FeatureAi 模块提供 AI Agent 能力。

## 目录结构

```
NeuronAI/
├── Agents/          # Agent 实现
│   └── (待添加)
├── Tools/           # 自定义工具
│   ├── AskTool.php
│   └── AskRequiredException.php
├── Workflows/       # 自定义 Workflow
│   └── (待添加)
├── Nodes/           # 自定义节点
│   └── (待添加)
├── Events/          # 自定义事件
│   └── (待添加)
└── Middleware/      # 自定义中间件
    └── (待添加)
```

## 已实现的工具

### AskTool - 用户交互工具

**位置**: `Tools/AskTool.php`

**功能**: 让 Agent 主动向用户提问，等待回答后继续执行。

**特性**:
- ✅ 数据库持久化（ai_asks 表）
- ✅ 异步 Job 支持
- ✅ 24 小时过期时间
- ✅ 支持默认值和上下文

**使用示例**:

```php
use Modules\FeatureAi\NeuronAI\Tools\AskTool;
use Modules\FeatureAi\NeuronAI\Tools\AskRequiredException;

// 在 Agent 中注册
protected function tools(): array
{
    return [new AskTool()];
}

// Job 中捕获异常
try {
    $response = $agent->chat('帮我注册');
} catch (AskRequiredException $e) {
    // 问题已存入数据库
    // Job 停止，等待用户回答
    return;
}

// 用户回答后，恢复执行
AskTool::answer($askId, '用户答案');
ResumeAgentJob::dispatch($askId);
```

**数据库表**: `ai_asks`
- ask_id: 问题唯一 ID
- workflow_id: Workflow ID
- question: 问题内容
- status: pending/answered/timeout/cancelled
- answer: 用户答案

---

## 目录用途详解

### Agents/
存放 Agent 类，继承 `NeuronAI\Agent`。

**用途**: 定义业务 Agent，配置 Provider、Tools、Instructions。

**示例**:
```php
class RegistrationAgent extends Agent
{
    protected function provider(): AIProviderInterface {
        // 配置 AI 提供商
    }

    public function instructions(): string {
        // 系统指令
    }

    protected function tools(): array {
        // 注册工具
    }
}
```

### Tools/
存放自定义工具，继承 `NeuronAI\Tools\Tool`。

**用途**: 为 Agent 提供具体功能，如数据库操作、API 调用等。

**现有工具**:
- ✅ **AskTool**: 用户交互工具
- ✅ **AskRequiredException**: Ask 异常类

**命名规范**: `{功能}Tool`

### Workflows/
存放自定义 Workflow，继承 `NeuronAI\Workflow\Workflow`。

**用途**: 定义复杂的多步骤流程，支持中断和恢复。

**示例**:
```php
class RegistrationWorkflow extends Workflow
{
    protected function nodes(): array {
        return [
            new CollectInfoNode(),
            new ValidateNode(),
            new RegisterNode(),
        ];
    }
}
```

### Nodes/
存放自定义节点，继承 `NeuronAI\Workflow\Node`。

**用途**: Workflow 中的执行单元，处理具体业务逻辑。

**命名规范**: `{动作}Node`（如 `ProcessNode`, `ValidationNode`）

### Events/
存放自定义事件，实现 `NeuronAI\Workflow\Event` 接口。

**用途**: 节点间通信，触发监听器。

### Middleware/
存放自定义中间件，实现 `NeuronAI\Workflow\Middleware\WorkflowMiddleware` 接口。

**用途**: 在节点执行前后添加横切逻辑（日志、性能监控等）。

---

## 与 FeatureAi 模块的集成

### Job 异步执行

FeatureAi 模块提供了完整的 Job 异步执行方案：

| Job | 用途 | 文件位置 |
|-----|------|---------|
| ExecuteAgentJob | 执行 Agent | `Jobs/ExecuteAgentJob.php` |
| ResumeAgentJob | 恢复 Agent | `Jobs/ResumeAgentJob.php` |

### 数据库支持

| 表 | 用途 | Model |
|----|------|-------|
| ai_asks | 存储问题 | `Models/AiAsk.php` |
| ai_conversations | 存储对话 | `Models/AiConversation.php` |

### API 端点

| 端点 | 用途 |
|------|------|
| POST /api/agent/chat | 启动对话 |
| GET /api/agent/status/{id} | 获取状态 |
| POST /api/agent/answer | 提交答案 |

---

## 开发指南

### 添加新 Agent

1. 在 `Agents/` 创建文件
2. 继承 `NeuronAI\Agent`
3. 实现 `provider()`, `instructions()`, `tools()` 方法
4. 在 Service 或 Job 中使用

### 添加新 Tool

1. 在 `Tools/` 创建文件
2. 继承 `NeuronAI\Tools\Tool`
3. 实现 `properties()` 和 `__invoke()` 方法
4. 在 Agent 中注册

### 使用 AskTool

参考完整文档：
- [Ask 工具使用指南](../docs/ask工具使用指南.md)
- [Agent 异步执行方案](../docs/Agent异步执行方案.md)

---

## 参考文档

- [NeuronAI 官方文档](../../../vendor/neuron-core/neuron-ai/README.md)
- [FeatureAi 交互完整指南](../docs/NeuronAI交互完整指南.md)
- [FeatureAi 开发文档](../docs/)

---

**创建时间**: 2026-09-02
**维护者**: AI 开发团队