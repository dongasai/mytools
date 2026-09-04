# NeuronAI Agent 动态定义方案

## 核心回答

**✅ NeuronAI Agent 完全支持动态定义！**

与 Claude Code 的配置文件驱动不同，NeuronAI 采用 **PHP Fluent API** 方式，提供更灵活的动态构建能力。

---

## 一、动态构建能力验证

### Agent 提供的 Fluent API

| 方法 | 功能 | 示例 |
|------|------|------|
| `setAiProvider()` | 动态设置 AI 提供者 | `->setAiProvider(new OpenAI(...))` |
| `setInstructions()` | 动态设置系统提示词 | `->setInstructions('你是助手...')` |
| `addTool()` | 动态添加工具 | `->addTool([new Tool1(), new Tool2()])` |
| `toolMaxRuns()` | 工具最大调用次数 | `->toolMaxRuns(10)` |
| `parallelToolCalls()` | 并行执行工具 | `->parallelToolCalls(true)` |

### 动态构建示例

```php
use NeuronAI\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Chat\Messages\UserMessage;

// ✅ 完全动态构建，无需继承 Agent 类
$agent = Agent::make()
    ->setAiProvider(new OpenAI(
        key: env('OPENAI_API_KEY'),
        model: 'gpt-4o'
    ))
    ->setInstructions('你是一个智能助手，专门帮助用户解决问题。')
    ->addTool([
        new SearchTool(),
        new CalculatorTool(),
        new WeatherTool(),
    ])
    ->toolMaxRuns(10)
    ->parallelToolCalls(false);

// 执行对话
$response = $agent->chat(new UserMessage('今天天气怎么样？'));
echo $response->getContent();
```

---

## 二、与 Claude Code 自定义 Agent 对比

| 特性 | Claude Code | NeuronAI |
|------|-------------|----------|
| **定义方式** | 配置文件（JSON/YAML） | PHP Fluent API |
| **动态性** | 文件驱动 | 代码驱动 |
| **灵活性** | 受配置结构限制 | 完全灵活（PHP代码能力） |
| **学习曲线** | 低（声明式） | 中等（需了解 API） |
| **类型安全** | 无（运行时检查） | 有（PHP 类型系统） |
| **扩展性** | 需要新配置字段 | 直接调用方法 |
| **适用场景** | 快速原型、非程序员 | 复杂业务、程序员友好 |

### Claude Code 方式（配置文件）

```json
{
  "name": "weather-agent",
  "provider": "openai",
  "model": "gpt-4",
  "instructions": "You are a weather assistant.",
  "tools": ["weather_tool", "search_tool"]
}
```

### NeuronAI 方式（PHP Fluent API）

```php
$agent = Agent::make()
    ->setAiProvider(new OpenAI(key: '...', model: 'gpt-4'))
    ->setInstructions('You are a weather assistant.')
    ->addTool([new WeatherTool(), new SearchTool()]);
```

---

## 三、数据库驱动的完整方案

### 设计思路

```
数据库配置 → DynamicAgentService → 动态构建 Agent → 执行
```

### 1. 数据库表设计

```sql
-- Agent 定义表
CREATE TABLE dynamic_agents (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Agent名称',
    slug VARCHAR(100) UNIQUE COMMENT 'URL友好标识',
    description TEXT COMMENT '描述',
    is_active BOOLEAN DEFAULT TRUE COMMENT '是否启用',

    -- Provider 配置
    provider_class VARCHAR(255) NOT NULL COMMENT 'Provider类名',
    provider_config JSON COMMENT 'Provider配置（api_key, model等）',

    -- Instructions
    instructions TEXT NOT NULL COMMENT '系统提示词',

    -- 行为配置
    tool_max_runs INT DEFAULT 10 COMMENT '工具最大调用次数',
    parallel_tool_calls BOOLEAN DEFAULT FALSE COMMENT '是否并行执行工具',

    -- 持久化
    persistence_driver ENUM('database','file','memory') DEFAULT 'database',

    created_at TIMESTAMP,
    updated_at TIMESTAMP
) COMMENT '动态Agent定义表';

-- Agent 工具关联表
CREATE TABLE dynamic_agent_tools (
    id BIGINT PRIMARY KEY,
    agent_id BIGINT NOT NULL,
    tool_class VARCHAR(255) NOT NULL COMMENT '工具类名',
    tool_name VARCHAR(100) COMMENT '工具名称',
    tool_config JSON COMMENT '工具配置参数',
    order_index INT DEFAULT 0 COMMENT '排序',
    is_enabled BOOLEAN DEFAULT TRUE COMMENT '是否启用',

    FOREIGN KEY (agent_id) REFERENCES dynamic_agents(id) ON DELETE CASCADE,
    INDEX idx_agent_id (agent_id)
) COMMENT 'Agent工具关联表';

-- Agent 执行记录表（可选，用于审计）
CREATE TABLE dynamic_agent_executions (
    id BIGINT PRIMARY KEY,
    agent_id BIGINT NOT NULL,
    workflow_id VARCHAR(100) COMMENT 'NeuronAI Workflow ID',
    user_id BIGINT COMMENT '执行用户ID',
    input_message TEXT COMMENT '输入消息',
    output_message TEXT COMMENT '输出消息',
    status ENUM('success','failed','interrupted') COMMENT '执行状态',
    tools_called JSON COMMENT '调用的工具列表',
    duration_ms INT COMMENT '执行时长（毫秒）',
    created_at TIMESTAMP,

    FOREIGN KEY (agent_id) REFERENCES dynamic_agents(id),
    INDEX idx_agent_id (agent_id),
    INDEX idx_workflow_id (workflow_id)
) COMMENT 'Agent执行记录表';
```

### 2. 核心服务类实现

```php
<?php

namespace Modules\FeatureAi\Services;

use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Workflow\Persistence\DatabasePersistence;
use NeuronAI\Workflow\Persistence\FilePersistence;
use NeuronAI\Workflow\Persistence\InMemoryPersistence;
use Illuminate\Support\Facades\DB;

/**
 * 动态 Agent 构建服务
 */
class DynamicAgentService
{
    /**
     * 从数据库构建 Agent
     *
     * @param int $agentId Agent ID
     * @return Agent 构建好的 Agent 实例
     * @throws \RuntimeException Agent 不存在或配置错误
     */
    public static function buildFromDatabase(int $agentId): Agent
    {
        // 1. 加载 Agent 定义
        $agentDef = DB::table('dynamic_agents')
            ->where('id', $agentId)
            ->where('is_active', true)
            ->first();

        if (!$agentDef) {
            throw new \RuntimeException("Agent with ID {$agentId} not found or inactive");
        }

        // 2. 加载工具配置
        $tools = DB::table('dynamic_agent_tools')
            ->where('agent_id', $agentId)
            ->where('is_enabled', true)
            ->orderBy('order_index')
            ->get();

        // 3. 构建 Provider
        $provider = self::buildProvider(
            $agentDef->provider_class,
            json_decode($agentDef->provider_config, true)
        );

        // 4. 构建 Agent
        $agent = Agent::make()
            ->setAiProvider($provider)
            ->setInstructions($agentDef->instructions)
            ->toolMaxRuns($agentDef->tool_max_runs)
            ->parallelToolCalls($agentDef->parallel_tool_calls);

        // 5. 添加工具
        if ($tools->isNotEmpty()) {
            $toolInstances = $tools->map(function ($toolConfig) {
                return self::buildTool(
                    $toolConfig->tool_class,
                    json_decode($toolConfig->tool_config, true)
                );
            })->toArray();

            $agent->addTool($toolInstances);
        }

        // 6. 配置持久化
        $persistence = self::buildPersistence(
            $agentDef->persistence_driver,
            $agentId
        );

        if ($persistence) {
            $agent->setPersistence($persistence);
        }

        return $agent;
    }

    /**
     * 构建 Provider 实例
     */
    protected static function buildProvider(string $providerClass, array $config): AIProviderInterface
    {
        if (!class_exists($providerClass)) {
            throw new \RuntimeException("Provider class {$providerClass} not found");
        }

        if (!is_a($providerClass, AIProviderInterface::class, true)) {
            throw new \RuntimeException("Class {$providerClass} must implement AIProviderInterface");
        }

        // 根据 Provider 类型处理配置
        return match ($providerClass) {
            \NeuronAI\Providers\OpenAI\OpenAI::class => new $providerClass(
                key: $config['api_key'] ?? env('OPENAI_API_KEY'),
                model: $config['model'] ?? 'gpt-4o'
            ),
            \NeuronAI\Providers\Anthropic\Anthropic::class => new $providerClass(
                key: $config['api_key'] ?? env('ANTHROPIC_API_KEY'),
                model: $config['model'] ?? 'claude-sonnet-4-6'
            ),
            default => new $providerClass(...$config),
        };
    }

    /**
     * 构建工具实例
     */
    protected static function buildTool(string $toolClass, ?array $config = []): ToolInterface
    {
        if (!class_exists($toolClass)) {
            throw new \RuntimeException("Tool class {$toolClass} not found");
        }

        if (!is_a($toolClass, ToolInterface::class, true)) {
            throw new \RuntimeException("Class {$toolClass} must implement ToolInterface");
        }

        // 通过反射检查构造函数是否接受配置
        $reflection = new \ReflectionClass($toolClass);
        $constructor = $reflection->getConstructor();

        if ($constructor && $constructor->getNumberOfParameters() > 0) {
            // 有构造函数参数，传入配置
            return new $toolClass($config ?? []);
        }

        // 无构造函数参数，直接实例化
        return new $toolClass();
    }

    /**
     * 构建持久化实例
     */
    protected static function buildPersistence(string $driver, int $agentId): ?object
    {
        return match ($driver) {
            'database' => new DatabasePersistence(
                DB::connection()->getPdo(),
                'dynamic_agent_workflow_states'
            ),
            'file' => new FilePersistence(
                storage_path("app/agent_workflows/{$agentId}")
            ),
            'memory' => new InMemoryPersistence(),
            default => null,
        };
    }

    /**
     * 执行 Agent 对话
     *
     * @param int $agentId Agent ID
     * @param string $message 用户消息
     * @param array $context 上下文数据
     * @return array ['status' => 'success', 'message' => '...']
     */
    public static function chat(int $agentId, string $message, array $context = []): array
    {
        try {
            $agent = self::buildFromDatabase($agentId);

            // 添加上下文到消息
            $userMessage = \NeuronAI\Chat\Messages\UserMessage::make($message);
            if (!empty($context)) {
                // 可以通过 state 传递上下文
                $state = new \NeuronAI\Workflow\WorkflowState($context);
                $agent->resolveState()->merge($state);
            }

            $response = $agent->chat($userMessage);

            return [
                'status' => 'success',
                'message' => $response->getContent(),
                'workflow_id' => $agent->getWorkflowId(),
            ];
        } catch (\NeuronAI\Workflow\Interrupt\WorkflowInterrupt $interrupt) {
            return [
                'status' => 'interrupted',
                'workflow_id' => $interrupt->getWorkflowId(),
                'request' => $interrupt->getRequest()->jsonSerialize(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 恢复中断的 Agent 执行
     */
    public static function resume(int $agentId, string $workflowId, array $userDecisions): array
    {
        // 加载 Agent 定义
        $agentDef = DB::table('dynamic_agents')->find($agentId);
        if (!$agentDef) {
            return ['status' => 'failed', 'error' => 'Agent not found'];
        }

        // 构建持久化
        $persistence = self::buildPersistence($agentDef->persistence_driver, $agentId);

        // 构建 Agent（带恢复 token）
        $agent = Agent::make($persistence, $workflowId);
        $agent = self::buildFromDatabase($agentId);

        // 构造恢复请求
        $resumeRequest = self::buildResumeRequest($userDecisions);

        try {
            $response = $agent->chat('', $resumeRequest);

            return [
                'status' => 'success',
                'message' => $response->getContent(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 构造恢复请求
     */
    protected static function buildResumeRequest(array $userDecisions): \NeuronAI\Workflow\Interrupt\InterruptRequest
    {
        // 根据 userDecisions 构造具体的 InterruptRequest
        // 这里需要根据具体的中断类型处理
        // 简化示例：
        return new \NeuronAI\Workflow\Interrupt\ApprovalRequest(
            message: 'Resuming execution',
            actions: []
        );
    }
}
```

### 3. 使用示例

#### 创建 Agent 定义

```php
use Modules\FeatureAi\Services\DynamicAgentService;
use Illuminate\Support\Facades\DB;

// 插入 Agent 定义
$agentId = DB::table('dynamic_agents')->insertGetId([
    'name' => '智能客服助手',
    'slug' => 'customer-service',
    'description' => '处理客户咨询的智能助手',
    'is_active' => true,
    'provider_class' => \NeuronAI\Providers\OpenAI\OpenAI::class,
    'provider_config' => json_encode([
        'api_key' => env('OPENAI_API_KEY'),
        'model' => 'gpt-4o',
    ]),
    'instructions' => '你是一个专业的客服助手，耐心解答客户问题。',
    'tool_max_runs' => 10,
    'parallel_tool_calls' => false,
    'persistence_driver' => 'database',
    'created_at' => now(),
    'updated_at' => now(),
]);

// 添加工具
DB::table('dynamic_agent_tools')->insert([
    [
        'agent_id' => $agentId,
        'tool_class' => \Modules\FeatureAi\Tools\KnowledgeBaseTool::class,
        'tool_name' => 'knowledge_base',
        'tool_config' => json_encode(['source' => 'faq']),
        'order_index' => 1,
        'is_enabled' => true,
    ],
    [
        'agent_id' => $agentId,
        'tool_class' => \Modules\FeatureAi\Tools\TicketTool::class,
        'tool_name' => 'create_ticket',
        'tool_config' => json_encode([]),
        'order_index' => 2,
        'is_enabled' => true,
    ],
]);
```

#### 执行 Agent

```php
use Modules\FeatureAi\Services\DynamicAgentService;

// 执行对话
$result = DynamicAgentService::chat(
    agentId: 1,
    message: '我想了解退款政策',
    context: ['user_id' => 123, 'merchant_id' => 1]
);

if ($result['status'] === 'success') {
    echo $result['message'];
} elseif ($result['status'] === 'interrupted') {
    // 需要用户确认
    $workflowId = $result['workflow_id'];
    $request = $result['request'];
    // ... 展示给用户，收集决策
}
```

---

## 四、高级特性

### 1. 条件性工具加载

```php
// 根据用户角色动态加载工具
$userId = auth()->id();
$userRole = getUserRole($userId);

$tools = DB::table('dynamic_agent_tools')
    ->where('agent_id', $agentId)
    ->where('is_enabled', true)
    ->when($userRole === 'admin', function ($query) {
        return $query->orWhere('tool_name', 'admin_panel');
    })
    ->get();
```

### 2. 动态 Instructions

```php
// Instructions 支持模板变量
$instructions = '你是 {merchant_name} 的客服助手。当前用户：{user_name}';

// 替换变量
$instructions = str_replace(
    ['{merchant_name}', '{user_name}'],
    ['三牛科技', '张三'],
    $instructions
);

$agent->setInstructions($instructions);
```

### 3. 多轮对话历史

```php
use NeuronAI\Chat\History\ChatHistory;

$agent = Agent::make()
    ->setAiProvider($provider)
    ->setInstructions($instructions);

// 添加对话历史
$history = new ChatHistory();
$history->addMessage(new UserMessage('之前的消息'));
$history->addMessage(new AssistantMessage('之前的回复'));

$agent->withChatHistory($history);

$response = $agent->chat(new UserMessage('新消息'));
```

---

## 五、限制与注意事项

| 方面 | 限制 | 解决方案 |
|------|------|---------|
| **工具类定义** | 必须预先存在 | 建立工具类库，数据库配置参数 |
| **Provider 类型** | 必须实现 AIProviderInterface | 支持主流 Provider（OpenAI、Anthropic） |
| **配置复杂度** | 复杂配置需要编码 | 建立可视化配置界面 |
| **工具依赖注入** | 工具可能需要服务注入 | 工具构造函数支持依赖注入 |

---

## 六、可视化配置界面（推荐）

为了降低使用门槛，建议开发可视化配置界面：

### 功能清单

1. **Agent 管理界面**
   - 创建/编辑/删除 Agent
   - 配置 Provider、Instructions
   - 启用/禁用 Agent

2. **工具管理界面**
   - 从工具库选择工具
   - 配置工具参数（JSON Editor）
   - 调整工具顺序

3. **测试控制台**
   - 实时对话测试
   - 查看工具调用日志
   - 调试 Instructions

4. **监控仪表盘**
   - 执行统计
   - 成功率、平均时长
   - 错误日志

---

## 七、最佳实践

### 1. 工具类库设计

```
Modules/FeatureAi/Tools/
├── BaseTool.php           # 基础工具类
├── KnowledgeBaseTool.php  # 知识库查询
├── TicketTool.php         # 工单管理
├── SearchTool.php         # 搜索工具
└── WeatherTool.php        # 天气查询
```

### 2. 配置验证

```php
// 创建 Agent 前验证配置
$validator = validator($config, [
    'provider_class' => 'required|class_exists',
    'provider_config.api_key' => 'required|string',
    'instructions' => 'required|string|min:10',
    'tool_max_runs' => 'integer|min:1|max:100',
]);
```

### 3. 错误处理

```php
try {
    $result = DynamicAgentService::chat($agentId, $message);
} catch (\RuntimeException $e) {
    Log::error('Agent build failed', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
    return response()->json(['error' => 'Agent 配置错误'], 500);
}
```

---

## 八、总结

### ✅ 支持动态定义

**NeuronAI Agent 完全支持通过数据库动态定义**，提供了灵活的 Fluent API：

- ✅ Provider 可动态配置
- ✅ Instructions 可动态设置
- ✅ Tools 可动态添加
- ✅ 参数可动态调整
- ✅ 支持持久化和中断恢复

### 与 Claude Code 的区别

| Claude Code | NeuronAI |
|-------------|----------|
| 配置文件驱动 | 代码驱动 |
| 声明式定义 | 流式 API 构建 |
| 适合非程序员 | 程序员友好 |
| 灵活性受限 | 完全灵活 |

### 推荐方案

1. **建立工具类库** - 预定义常用工具
2. **数据库存储配置** - Agent 定义 + 工具配置
3. **服务类动态构建** - `DynamicAgentService::buildFromDatabase()`
4. **可视化配置界面** - 降低使用门槛

---

**更新时间**: 2026-09-02
**版本**: 1.0.0