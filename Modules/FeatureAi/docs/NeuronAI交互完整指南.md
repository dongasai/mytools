# NeuronAI Agent 用户交互完整指南

## 概述

NeuronAI Agent 提供三种用户交互方式，满足不同场景需求：

| 方式 | 适用场景 | 触发时机 | 复杂度 |
|------|---------|---------|--------|
| **ToolApproval 中间件** | 工具调用确认 | 工具执行前 | ⭐ 简单 |
| **AskTool 工具** | Agent 主动提问 | Agent 自主决定 | ⭐⭐ 中等 |
| **Workflow Interrupt** | 复杂交互流程 | 任意节点 | ⭐⭐⭐ 高级 |

---

## 方式一：ToolApproval 中间件（推荐）

### 核心概念

在 Agent 执行工具前，自动请求用户确认，适用于敏感操作审批。

### 工作流程

```
用户请求 → Agent 分析 → 决定调用工具
    ↓
ToolApproval 拦截 → 创建 ApprovalRequest
    ↓
抛出 WorkflowInterrupt → 展示给用户
    ↓
用户决策（批准/拒绝） → 恢复 Workflow
    ↓
工具执行或跳过 → 返回结果
```

### 快速开始

#### 1. 所有工具都需要确认

```php
use NeuronAI\Agent;
use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Providers\OpenAI\OpenAI;

class SafeAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAI(env('OPENAI_API_KEY'), 'gpt-4');
    }

    protected function tools(): array
    {
        return [
            new DeleteFileTool(),
            new SendEmailTool(),
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

#### 2. 指定工具需要确认

```php
new ToolApproval([
    'delete_file',           // 删除文件总是需要确认
    'send_email',            // 发送邮件总是需要确认
    'transfer_money' => fn($args) => $args['amount'] > 100, // 金额>100才确认
]);
```

#### 3. 完整使用流程

```php
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;

try {
    $response = SafeAgent::make()->chat('删除 /tmp/test.txt');
    echo $response->getContent();
} catch (WorkflowInterrupt $interrupt) {
    $request = $interrupt->getRequest(); // ApprovalRequest 实例

    echo "需要确认以下操作：\n";
    echo $request->message . "\n\n";

    // 展示所有待确认操作
    foreach ($request->getActions() as $action) {
        echo "工具：{$action->name}\n";
        echo "参数：{$action->description}\n\n";

        // 用户决策
        $decision = readline("批准？(y/n): ");

        if ($decision === 'y') {
            $action->approve('用户批准');
        } else {
            $action->reject('用户拒绝');
        }
    }

    // 恢复执行
    $handler = SafeAgent::make()->chat('删除 /tmp/test.txt', $request);
    $response = $handler->run();
    echo $response->getContent();
}
```

### 配置选项

```php
new ToolApproval([
    // 1. 空数组：所有工具都需要确认
    [],

    // 2. 字符串数组：指定工具总是需要确认
    ['delete_file', 'send_email'],

    // 3. 键值对：条件性确认
    [
        'delete_file',  // 无条件确认
        'transfer_money' => fn(array $args) => ($args['amount'] ?? 0) > 100, // 条件确认
        'send_email' => fn(array $args) => !str_ends_with($args['to'], '@company.com'), // 外部邮箱确认
    ],
]);
```

### 应用场景

| 场景 | 配置 | 说明 |
|------|------|------|
| 文件操作 | `['delete_file', 'write_file']` | 删除和写入文件前确认 |
| 支付确认 | `['pay' => fn($args) => $args['amount'] > 100]` | 大额支付确认 |
| 邮件发送 | `['send_email' => fn($args) => !is_internal($args['to'])]` | 外部邮件确认 |
| 数据库操作 | `['delete_record', 'update_record']` | 数据修改确认 |

### 核心类

#### ApprovalRequest

```php
class ApprovalRequest extends InterruptRequest
{
    public function __construct(string $message, array $actions = []);

    // 方法
    public function getAction(string $id): ?Action;
    public function getActions(): array;
    public function getPendingActions(): array;
    public function getApprovedActions(): array;
    public function getRejectedActions(): array;
}
```

#### Action

```php
class Action
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public ActionDecision $decision = ActionDecision::Pending,
        public ?string $feedback = null
    );

    // 用户决策
    public function approve(?string $feedback = null): void;
    public function reject(?string $feedback = null): void;
    public function edit(array $newInputs, ?string $feedback = null): void;

    // 状态检查
    public function isPending(): bool;
    public function isApproved(): bool;
    public function isRejected(): bool;
}
```

---

## 方式二：AskTool 工具

### 核心概念

让 Agent 主动向用户提问，等待回答后继续执行，适用于信息收集场景。

### 工作流程

```
Agent 执行 → 需要信息 → 调用 ask 工具
    ↓
抛出 AskRequiredException → 外部系统捕获
    ↓
展示问题给用户 → 用户回答
    ↓
提交答案到缓存 → Agent 继续执行
    ↓
获取答案 → 完成任务
```

### 快速开始

#### 1. 注册 AskTool

```php
use NeuronAI\Agent;
use Modules\FeatureAi\Tools\AskTool;

class RegistrationAgent extends Agent
{
    protected function tools(): array
    {
        return [
            new AskTool(),
        ];
    }

    public function instructions(): string
    {
        return '你是注册助手，需要用户信息时使用 ask 工具提问。';
    }
}
```

#### 2. 外部系统处理

```php
use Modules\FeatureAi\Tools\AskTool;
use Modules\FeatureAi\Tools\AskRequiredException;

try {
    $response = RegistrationAgent::make()->chat('帮我注册');
} catch (AskRequiredException $e) {
    // 显示问题
    echo "问题：{$e->getQuestion()}\n";
    if ($e->getContext()) {
        echo "上下文：{$e->getContext()}\n";
    }
    if ($e->getDefault()) {
        echo "默认值：{$e->getDefault()}\n";
    }

    // 获取用户输入
    $answer = readline("请输入：");
    if (empty($answer) && $e->getDefault()) {
        $answer = $e->getDefault();
    }

    // 提交答案
    AskTool::answer($e->getQuestionId(), $answer);

    // 继续执行
    $response = RegistrationAgent::make()->chat("用户回答：{$answer}");
}
```

### Web 应用集成

#### Laravel Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\FeatureAi\Agents\RegistrationAgent;
use Modules\FeatureAi\Tools\AskTool;
use Modules\FeatureAi\Tools\AskRequiredException;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $response = RegistrationAgent::make()->chat($request->input('message'));

            return response()->json([
                'status' => 'success',
                'message' => $response->getContent(),
            ]);
        } catch (AskRequiredException $e) {
            return response()->json([
                'status' => 'ask_required',
                'question_id' => $e->getQuestionId(),
                'question' => $e->getQuestion(),
                'context' => $e->getContext(),
                'default' => $e->getDefault(),
            ]);
        }
    }

    public function answer(Request $request)
    {
        AskTool::answer(
            $request->input('question_id'),
            $request->input('answer')
        );

        $response = RegistrationAgent::make()->chat(
            "用户已回答问题"
        );

        return response()->json([
            'status' => 'success',
            'message' => $response->getContent(),
        ]);
    }
}
```

#### 前端 JavaScript

```javascript
async function chat(message) {
    const response = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
    });

    const data = await response.json();

    if (data.status === 'ask_required') {
        // 弹窗提示用户
        const answer = prompt(
            data.question + 
            (data.default ? `\n默认：${data.default}` : '')
        );

        if (answer !== null) {
            // 提交答案
            return await fetch('/api/chat/answer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    question_id: data.question_id,
                    answer: answer || data.default
                })
            }).then(r => r.json());
        }
    }

    return data;
}
```

### 核心类

#### AskTool

```php
class AskTool extends Tool
{
    // 工具定义
    public function __construct();
    public function properties(): array;
    public function __invoke(): string;

    // 静态方法
    public static function storeQuestion(string $questionId, array $data): void;
    public static function getQuestion(string $questionId): ?array;
    public static function answer(string $questionId, string $answer): void;
    public static function getAnswer(string $questionId): ?string;
}
```

#### AskRequiredException

```php
class AskRequiredException extends Exception
{
    public function __construct(
        string $question,
        string $questionId,
        string $context = '',
        string $default = ''
    );

    // 属性获取
    public function getQuestion(): string;
    public function getQuestionId(): string;
    public function getContext(): string;
    public function getDefault(): string;

    // 转换
    public function toArray(): array;
}
```

### 应用场景

| 场景 | 使用方式 |
|------|---------|
| 用户注册 | 收集姓名、邮箱、密码 |
| 表单填写 | 引导用户填写多步骤表单 |
| 数据收集 | 调研问卷、信息录入 |
| 配置向导 | 引导用户完成配置流程 |
| 问题诊断 | 逐步排查问题原因 |

---

## 方式三：Workflow Interrupt（高级）

### 核心概念

在 Workflow 的任何节点主动中断执行，等待外部输入后恢复，适用于复杂交互流程。

### 工作流程

```
StartEvent → NodeA → 需要用户输入
    ↓
调用 $this->interrupt() → 抛出 WorkflowInterrupt
    ↓
外部系统捕获 → 展示请求给用户
    ↓
用户操作 → 构造 ResumeRequest
    ↓
恢复 Workflow → NodeA 继续 → StopEvent
```

### 使用示例

#### 自定义交互节点

```php
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\WorkflowState;
use NeuronAI\Workflow\Interrupt\ApprovalRequest;
use NeuronAI\Workflow\Interrupt\Action;

class UserInputNode extends Node
{
    public function __invoke(ProcessEvent $event, WorkflowState $state): ResultEvent
    {
        // 检查是否在恢复执行
        if ($this->isResuming()) {
            $resumeRequest = $this->getResumeRequest();
            $userChoice = $resumeRequest->getAction('choice');

            if ($userChoice->isApproved()) {
                // 用户选择了继续
                $state->set('user_choice', $userChoice->feedback);
                return new ResultEvent('继续执行');
            }

            // 用户取消
            return new CancelEvent();
        }

        // 首次执行，请求用户输入
        $this->interrupt(
            new ApprovalRequest(
                message: '请选择下一步操作',
                actions: [
                    new Action('choice', '继续', '继续执行'),
                    new Action('cancel', '取消', '取消操作'),
                ]
            )
        );
    }
}
```

#### 自定义 InterruptRequest

```php
use NeuronAI\Workflow\Interrupt\InterruptRequest;

class UserInputRequest extends InterruptRequest
{
    public function __construct(
        string $message,
        public array $options = [],
        public bool $multipleChoice = false
    ) {
        parent::__construct($message);
    }

    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'options' => $this->options,
            'multiple_choice' => $this->multipleChoice,
        ];
    }
}
```

#### 使用自定义中断

```php
// 在节点中使用
$this->interrupt(new UserInputRequest(
    message: '请选择文件类型',
    options: ['pdf', 'doc', 'txt'],
    multipleChoice: false
));

// 外部处理
try {
    $handler = $workflow->init()->run();
} catch (WorkflowInterrupt $interrupt) {
    $request = $interrupt->getRequest();

    if ($request instanceof UserInputRequest) {
        // 显示选项
        echo $request->message . "\n";
        foreach ($request->options as $index => $option) {
            echo "[{$index}] {$option}\n";
        }

        // 用户选择
        $choice = (int)readline("请选择：");
        $request->setChoice($choice);

        // 恢复执行
        $handler = $workflow->init($request)->run();
    }
}
```

### 持久化配置

对于可能中断的 Workflow，必须配置持久化：

```php
use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\Persistence\FilePersistence;

$workflow = Workflow::make()
    ->setPersistence(new FilePersistence('/tmp/workflows'))
    ->addNodes([
        new UserInputNode(),
        new ProcessingNode(),
    ]);

try {
    $handler = $workflow->init()->run();
} catch (WorkflowInterrupt $interrupt) {
    $workflowId = $interrupt->getWorkflowId();

    // 保存 workflowId，供后续恢复使用
    Cache::put("workflow_{$workflowId}", [
        'request' => $interrupt->getRequest(),
        'timestamp' => time(),
    ], 3600);

    // 返回给前端
    return response()->json([
        'workflow_id' => $workflowId,
        'request' => $interrupt->getRequest()->jsonSerialize(),
    ]);
}

// 恢复执行
public function resume(string $workflowId, array $userInput)
{
    $cached = Cache::get("workflow_{$workflowId}");
    $request = $cached['request'];

    // 设置用户输入
    $request->setUserInput($userInput);

    // 恢复 Workflow
    $workflow = Workflow::make()
        ->setPersistence(new FilePersistence('/tmp/workflows'));

    $handler = $workflow->init($request)->run();
    return $handler->getState();
}
```

---

## 最佳实践

### 1. 选择合适的交互方式

| 需求 | 推荐方式 | 原因 |
|------|---------|------|
| 工具执行前确认 | ToolApproval | 自动拦截，简单可靠 |
| 收集用户信息 | AskTool | Agent 主动提问，灵活自然 |
| 复杂多步骤流程 | Workflow Interrupt | 完全控制，支持任意中断点 |
| 支付、删除等敏感操作 | ToolApproval | 失败安全（fail-safe） |
| 注册、表单等数据收集 | AskTool | 逐步引导，用户体验好 |
| 审批工作流 | Workflow Interrupt | 支持多级审批，灵活配置 |

### 2. 异常处理最佳实践

```php
try {
    $response = $agent->chat($message);
} catch (WorkflowInterrupt $interrupt) {
    // ToolApproval 中断
    if ($interrupt->getRequest() instanceof ApprovalRequest) {
        return $this->handleApproval($interrupt);
    }

    // 其他类型中断
    return $this->handleCustomInterrupt($interrupt);
} catch (AskRequiredException $e) {
    // AskTool 提问
    return $this->handleAsk($e);
} catch (Exception $e) {
    // 其他异常
    Log::error('Agent error', ['exception' => $e]);
    return response()->json(['error' => '服务异常'], 500);
}
```

### 3. 持久化配置

```php
use NeuronAI\Workflow\Persistence\FilePersistence;
use NeuronAI\Workflow\Persistence\DatabasePersistence;

// 文件持久化（适合单机）
$agent->setPersistence(new FilePermission('/tmp/agent_workflows'));

// 数据库持久化（适合分布式）
$agent->setPersistence(new DatabasePersistence($pdo, 'workflows'));
```

### 4. 用户反馈

```php
// ToolApproval
$action->approve('确认删除');
$action->reject('文件不能删除');

// AskTool
AskTool::answer($questionId, '张三');

// Workflow Interrupt
$resumeRequest->setUserInput(['choice' => 'pdf']);
```

---

## 完整示例：注册助手

### Agent 定义

```php
<?php

use NeuronAI\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Agent\Middleware\ToolApproval;
use Modules\FeatureAi\Tools\AskTool;

class RegistrationAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAI(env('OPENAI_API_KEY'), 'gpt-4');
    }

    public function instructions(): string
    {
        return '你是注册助手。
        
工作流程：
1. 使用 ask 工具收集用户姓名、邮箱
2. 使用 ask 工具确认注册信息
3. 调用 register_user 工具完成注册（需要确认）

注意：
- 收集信息时使用 ask 工具
- 注册前必须获得用户确认';
    }

    protected function tools(): array
    {
        return [
            new AskTool(),
            Tool::make('register_user', '注册用户到系统')
                ->setCallable(fn($name, $email) => User::create([...]))
                ->addProperty(
                    ToolProperty::string('name', '用户姓名')
                )
                ->addProperty(
                    ToolProperty::string('email', '用户邮箱')
                ),
        ];
    }

    protected function middleware(): array
    {
        return [
            new ToolApproval(['register_user']), // 注册需要确认
        ];
    }
}
```

### Controller 处理

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\FeatureAi\Tools\AskTool;
use Modules\FeatureAi\Tools\AskRequiredException;
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;

class RegistrationController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        $agent = RegistrationAgent::make();

        try {
            $response = $agent->chat($message);

            return response()->json([
                'status' => 'success',
                'message' => $response->getContent(),
            ]);
        } catch (AskRequiredException $e) {
            // 需要用户回答问题
            return response()->json([
                'status' => 'ask',
                'type' => 'information_collection',
                'question_id' => $e->getQuestionId(),
                'question' => $e->getQuestion(),
                'context' => $e->getContext(),
                'default' => $e->getDefault(),
            ]);
        } catch (WorkflowInterrupt $interrupt) {
            // 需要用户确认操作
            $request = $interrupt->getRequest();

            return response()->json([
                'status' => 'confirm',
                'type' => 'tool_approval',
                'workflow_id' => $interrupt->getWorkflowId(),
                'message' => $request->message,
                'actions' => $request->getActions(),
            ]);
        }
    }

    public function answer(Request $request)
    {
        AskTool::answer(
            $request->input('question_id'),
            $request->input('answer')
        );

        return $this->chat(new Request([
            'message' => '用户已回答'
        ]));
    }

    public function confirm(Request $request)
    {
        $workflowId = $request->input('workflow_id');
        $decisions = $request->input('decisions'); // ['action_id' => 'approve/reject']

        // 构造恢复请求
        // ... 根据 decisions 设置 Action 状态

        $agent = RegistrationAgent::make();
        $response = $agent->chat('继续', $resumeRequest);

        return response()->json([
            'status' => 'success',
            'message' => $response->getContent(),
        ]);
    }
}
```

### 前端完整流程

```javascript
class RegistrationChat {
    async sendMessage(message) {
        const response = await fetch('/api/chat', {
            method: 'POST',
            body: JSON.stringify({ message })
        });

        const data = await response.json();

        switch (data.status) {
            case 'success':
                this.displayMessage(data.message);
                break;

            case 'ask':
                await this.handleAsk(data);
                break;

            case 'confirm':
                await this.handleConfirm(data);
                break;
        }
    }

    async handleAsk(data) {
        const answer = prompt(
            data.question +
            (data.default ? `\n默认：${data.default}` : '')
        );

        if (answer !== null) {
            await fetch('/api/chat/answer', {
                method: 'POST',
                body: JSON.stringify({
                    question_id: data.question_id,
                    answer: answer || data.default
                })
            });

            this.sendMessage('继续');
        }
    }

    async handleConfirm(data) {
        const approved = confirm(
            data.message + '\n\n是否继续？'
        );

        await fetch('/api/chat/confirm', {
            method: 'POST',
            body: JSON.stringify({
                workflow_id: data.workflow_id,
                decisions: {
                    [data.actions[0].id]: approved ? 'approve' : 'reject'
                }
            })
        });
    }

    displayMessage(message) {
        document.getElementById('chat').innerHTML += 
            `<div class="message">${message}</div>`;
    }
}
```

---

## 常见问题

### Q1: ToolApproval 和 AskTool 有什么区别？

**ToolApproval**: 被动确认，Agent 决定调用工具，系统拦截要求确认
**AskTool**: 主动提问，Agent 主动向用户询问信息

### Q2: 如何选择持久化方式？

- **单机应用**: FilePersistence
- **分布式应用**: DatabasePersistence
- **生产环境**: 建议使用 Redis 或数据库持久化

### Q3: 如何处理多轮对话？

```php
// 使用 Chat History
use NeuronAI\Chat\History\ChatHistory;

$agent = RegistrationAgent::make()
    ->withChatHistory(new ChatHistory());

// 每次对话自动包含历史记录
$response = $agent->chat($message);
```

### Q4: 如何在中断后恢复执行？

保存 Workflow ID 和 Interrupt Request，用户操作后传入恢复：

```php
// 捕获中断
catch (WorkflowInterrupt $interrupt) {
    $workflowId = $interrupt->getWorkflowId();
    $request = $interrupt->getRequest();
    Cache::put($workflowId, $request);
}

// 恢复执行
$request = Cache::get($workflowId);
// ... 设置用户决策
$response = $agent->chat('继续', $request);
```

### Q5: AskTool 的答案能保存多久？

默认 1 小时（3600秒），可以在代码中修改：

```php
Cache::put(self::ANSWER_PREFIX . $questionId, $answer, 7200); // 2小时
```

---

## 参考文档

- [ToolApproval 源码](vendor/neuron-core/neuron-ai/src/Agent/Middleware/ToolApproval.php)
- [Workflow Interrupt 源码](vendor/neuron-core/neuron-ai/src/Workflow/Interrupt/)
- [NeuronAI 官方文档](https://github.com/neuron-ai/neuron-ai)

---

**更新时间**: 2026-09-02  
**版本**: 1.0.0