# NeuronAI Agent 用户交互指南

## 概述

NeuronAI Agent 提供了两种主要的用户交互方式：

1. **ToolApproval 中间件** - 工具调用前请求用户确认
2. **Workflow interrupt** - 在任何节点发起询问（高级用法）

---

## 方式一：ToolApproval 中间件（推荐）

### 适用场景

- Agent 执行敏感工具前需要确认（如删除文件、转账）
- 工具调用有风险，需要人工审批
- 用户需要对工具执行有控制权

### 基本用法

#### 1. 所有工具都需要确认

```php
use NeuronAI\Agent;
use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Providers\OpenAI\OpenAI;

class SafeAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAI('your-api-key', 'gpt-4');
    }

    public function instructions(): string
    {
        return 'You are a helpful assistant with file access.';
    }

    protected function tools(): array
    {
        return [
            new DeleteFileTool(),
            new SendEmailTool(),
        ];
    }

    // 注册中间件
    protected function middleware(): array
    {
        return [
            // 所有工具调用前都会请求用户确认
            new ToolApproval(),
        ];
    }
}
```

#### 2. 指定工具需要确认

```php
use NeuronAI\Agent\Middleware\ToolApproval;

// 只有 delete_file 和 send_email 工具需要确认
$this->middleware([
    ChatNode::class,
    StreamNode::class,
    StructuredOutputNode::class
], new ToolApproval([
    'delete_file',
    'send_email',
]));
```

#### 3. 条件性确认

```php
use NeuronAI\Agent\Middleware\ToolApproval;

// 转账超过 100 元才需要确认
new ToolApproval([
    'delete_file',  // 删除文件总是需要确认
    'transfer_money' => fn(array $args) => ($args['amount'] ?? 0) > 100,
]);
```

### 完整示例

```php
<?php

use NeuronAI\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Tools\Tool;

class FileAssistantAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAI(env('OPENAI_API_KEY'), 'gpt-4');
    }

    public function instructions(): string
    {
        return 'You are a file management assistant. You can read and delete files.';
    }

    protected function tools(): array
    {
        return [
            Tool::make('read_file', 'Read file content')
                ->setCallable(fn(string $path) => file_get_contents($path)),

            Tool::make('delete_file', 'Delete a file')
                ->setCallable(fn(string $path) => unlink($path)),
        ];
    }

    protected function middleware(): array
    {
        return [
            // 只有 delete_file 需要确认
            new ToolApproval(['delete_file']),
        ];
    }
}

// 使用
try {
    $response = FileAssistantAgent::make()->chat('删除 /tmp/test.txt');
} catch (\NeuronAI\Workflow\Interrupt\WorkflowInterrupt $interrupt) {
    $request = $interrupt->getRequest(); // ApprovalRequest 实例

    echo $request->message; // "1 tool call requires approval before execution"

    foreach ($request->getActions() as $action) {
        echo "工具: {$action->name}\n";
        echo "参数: {$action->description}\n";

        // 用户决定
        $action->approve();  // 批准
        // $action->reject('不想删除这个文件');  // 拒绝
    }

    // 恢复执行
    $response = FileAssistantAgent::make()
        ->chat('删除 /tmp/test.txt', $request);
}
```

---

## 方式二：Workflow Interrupt（高级）

### 适用场景

- 在 Agent 执行过程中主动询问用户
- 需要用户提供额外信息
- 复杂的交互流程

### 原理

Agent 本质上是一个 Workflow，可以在任何节点使用 `$this->interrupt()` 发起中断。

### 示例：Agent 主动询问

```php
<?php

use NeuronAI\Agent;
use NeuronAI\Workflow\Interrupt\ApprovalRequest;
use NeuronAI\Workflow\Interrupt\Action;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Chat\Messages\UserMessage;

class InteractiveAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAI(env('OPENAI_API_KEY'), 'gpt-4');
    }

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    // 重写 chat 方法，添加交互逻辑
    public function chat(Message|array $messages = [], ?InterruptRequest $interrupt = null): AgentHandler
    {
        // 检查是否需要询问用户
        $message = is_array($messages) ? $messages[0] : $messages;
        $content = $message->getContent();

        if (str_contains($content, '敏感操作')) {
            // 发起询问
            $request = new ApprovalRequest(
                message: '检测到敏感操作，需要确认',
                actions: [
                    new Action(
                        id: 'proceed',
                        name: '继续执行',
                        description: '执行敏感操作'
                    ),
                    new Action(
                        id: 'cancel',
                        name: '取消',
                        description: '取消操作'
                    ),
                ]
            );

            // 这里会抛出 WorkflowInterrupt
            throw new \NeuronAI\Workflow\Interrupt\WorkflowInterrupt(
                $request,
                $this,
                new \NeuronAI\Workflow\WorkflowState()
            );
        }

        return parent::chat($messages, $interrupt);
    }
}

// 使用
try {
    $response = InteractiveAgent::make()->chat('执行敏感操作');
} catch (\NeuronAI\Workflow\Interrupt\WorkflowInterrupt $interrupt) {
    $request = $interrupt->getRequest();

    // 展示给用户
    echo $request->message . "\n";
    foreach ($request->getActions() as $action) {
        echo "[{$action->id}] {$action->name}: {$action->description}\n";
    }

    // 用户选择
    $selectedAction = $request->getAction('proceed');
    $selectedAction->approve();

    // 恢复执行
    $handler = InteractiveAgent::make()->chat('执行敏感操作', $request);
    $response = $handler->run();
}
```

---

## 实际应用场景

### 场景 1：文件操作确认

```php
use NeuronAI\Agent\Middleware\ToolApproval;

// 所有文件操作都需要确认
new ToolApproval(['read_file', 'write_file', 'delete_file']);
```

### 场景 2：支付确认

```php
use NeuronAI\Agent\Middleware\ToolApproval;

// 超过 100 元的支付需要确认
new ToolApproval([
    'process_payment' => fn(array $args) => ($args['amount'] ?? 0) > 100,
]);
```

### 场景 3：邮件发送确认

```php
use NeuronAI\Agent\Middleware\ToolApproval;

// 发送给外部邮箱需要确认
new ToolApproval([
    'send_email' => fn(array $args) => !str_ends_with($args['to'] ?? '', '@company.com'),
]);
```

---

## 核心类说明

### ApprovalRequest

```php
class ApprovalRequest extends InterruptRequest
{
    public function __construct(
        string $message,       // 询问消息
        array $actions = []    // Action 数组
    ) {}

    public function getAction(string $id): ?Action;
    public function getActions(): array;
    public function getPendingActions(): array;
    public function getApprovedActions(): array;
    public function getRejectedActions(): array;
}
```

### Action

```php
class Action
{
    public function __construct(
        public string $id,              // 唯一标识
        public string $name,            // 名称
        public string $description,     // 描述
        public ActionDecision $decision = ActionDecision::Pending,
        public ?string $feedback = null
    ) {}

    public function approve(?string $feedback = null): void;
    public function reject(?string $feedback = null): void;
    public function edit(array $newInputs, ?string $feedback = null): void;

    public function isPending(): bool;
    public function isApproved(): bool;
    public function isRejected(): bool;
}
```

### WorkflowInterrupt

```php
class WorkflowInterrupt extends \Exception
{
    public function __construct(
        InterruptRequest $request,
        ?NodeInterface $node = null,
        ?WorkflowState $state = null,
        ?Event $event = null
    ) {}

    public function getRequest(): InterruptRequest;
    public function getWorkflowId(): string;
}
```

---

## 最佳实践

### 1. 使用 Persistence 持久化

对于可能中断的 Agent，必须配置持久化：

```php
use NeuronAI\Workflow\Persistence\FilePersistence;

$agent = new InteractiveAgent();
$agent->setPersistence(new FilePersistence('/tmp/workflows'));

try {
    $response = $agent->chat('执行操作');
} catch (WorkflowInterrupt $interrupt) {
    $workflowId = $interrupt->getWorkflowId();
    $request = $interrupt->getRequest();

    // 保存 workflowId 和 request 供后续恢复使用
    Cache::put("workflow_{$workflowId}", $request, 3600);
}
```

### 2. 用户反馈

```php
// 用户批准并添加反馈
$action->approve('确认删除测试文件');

// 用户拒绝并提供原因
$action->reject('这个文件不能删除');
```

### 3. 工具输入展示

```php
protected function createAction(ToolInterface $tool): Action
{
    $inputs = $tool->getInputs();

    return new Action(
        id: $tool->getCallId(),
        name: $tool->getName(),
        description: json_encode($inputs, JSON_PRETTY_PRINT), // 格式化显示参数
    );
}
```

---

## 集成到 FeatureAi

### 创建交互式 Agent

```php
<?php

namespace Modules\FeatureAi\Agents;

use NeuronAI\Agent;
use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Providers\OpenAI\OpenAI;
use Modules\FeatureAi\Services\AiProviderService;

class SafeChatAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        $config = AiProviderService::getProviderConfig('openai');
        return new OpenAI(
            key: $config['api_key'],
            model: $config['default_chat_model'] ?? 'gpt-4',
            httpClient: AiProviderService::createHttpClient()
        );
    }

    public function instructions(): string
    {
        return '你是一个安全的 AI 助手，执行敏感操作前会请求用户确认。';
    }

    protected function tools(): array
    {
        return [
            // 你的工具
        ];
    }

    protected function middleware(): array
    {
        return [
            new ToolApproval(), // 所有工具都需要确认
        ];
    }
}
```

---

## 参考文档

- [NeuronAI Workflow 文档](vendor/neuron-core/neuron-ai/src/Workflow/AGENTS.md)
- [NeuronAI Agent 文档](vendor/neuron-core/neuron-ai/src/Agent/AGENTS.md)
- [ToolApproval 源码](vendor/neuron-core/neuron-ai/src/Agent/Middleware/ToolApproval.php)

---

**更新时间**: 2026-09-02