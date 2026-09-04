# NeuronAI Ask 工具使用指南

## 概述

`AskTool` 让 Agent 可以主动向用户提问，等待用户输入回答后继续执行。

## 工作原理

```
Agent 执行 → 遇到 ask 工具 → 抛出 AskRequiredException
    ↓
外部系统捕获异常 → 展示问题给用户 → 用户回答
    ↓
提交答案到缓存 → Agent 继续执行 → 通过 question_id 获取答案
```

## 使用示例

### 1. 在 Agent 中注册 AskTool

```php
<?php

use NeuronAI\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;
use Modules\FeatureAi\Tools\AskTool;

class RegistrationAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAI(env('OPENAI_API_KEY'), 'gpt-4');
    }

    public function instructions(): string
    {
        return '你是注册助手，帮助用户完成注册流程。需要用户信息时使用 ask 工具提问。';
    }

    protected function tools(): array
    {
        return [
            new AskTool(),
        ];
    }
}
```

### 2. 外部系统处理用户输入

#### 基本用法

```php
use Modules\FeatureAi\Tools\AskTool;
use Modules\FeatureAi\Tools\AskRequiredException;

try {
    $response = RegistrationAgent::make()->chat('帮我注册账号');
} catch (AskRequiredException $e) {
    // 获取问题信息
    $questionId = $e->getQuestionId();
    $question = $e->getQuestion();
    $context = $e->getContext();
    $default = $e->getDefault();

    // 展示给用户
    echo "问题：{$question}\n";
    if ($context) {
        echo "上下文：{$context}\n";
    }
    if ($default) {
        echo "默认值：{$default}\n";
    }

    // 获取用户输入
    $answer = readline("请输入：");
    if (empty($answer) && $default) {
        $answer = $default;
    }

    // 提交答案
    AskTool::answer($questionId, $answer);

    // 继续执行（Agent 会通过 question_id 获取答案）
    $response = RegistrationAgent::make()->chat(
        "用户已回答问题 {$questionId}，答案：{$answer}"
    );
}
```

#### Web 应用示例（Laravel）

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
        $message = $request->input('message');
        $conversationId = $request->input('conversation_id');

        try {
            $response = RegistrationAgent::make()->chat($message);

            return response()->json([
                'status' => 'success',
                'message' => $response->getContent(),
            ]);
        } catch (AskRequiredException $e) {
            // 需要用户输入，返回问题信息
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
        $questionId = $request->input('question_id');
        $answer = $request->input('answer');

        // 提交答案
        AskTool::answer($questionId, $answer);

        // 继续对话
        $response = RegistrationAgent::make()->chat(
            "用户已回答问题 {$questionId}，答案：{$answer}"
        );

        return response()->json([
            'status' => 'success',
            'message' => $response->getContent(),
        ]);
    }
}
```

### 3. 前端处理示例（JavaScript）

```javascript
async function chat(message) {
    const response = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
    });

    const data = await response.json();

    if (data.status === 'ask_required') {
        // 显示问题给用户
        const answer = prompt(data.question + (data.default ? `\n默认：${data.default}` : ''));

        if (answer) {
            // 提交答案
            return await fetch('/api/chat/answer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    question_id: data.question_id,
                    answer: answer || data.default
                })
            });
        }
    }

    return data;
}
```

## 高级用法

### 1. 多轮对话

```php
$conversation = [];

try {
    $response = RegistrationAgent::make()->chat('帮我注册');
    $conversation[] = $response;
} catch (AskRequiredException $e) {
    AskTool::answer($e->getQuestionId(), '张三');

    // 继续下一轮
    $response = RegistrationAgent::make()->chat(
        "用户回答：张三"
    );
}
```

### 2. 条件性询问

Agent 的 System Prompt 中指导：

```
你是注册助手。收集信息时：
1. 先询问姓名
2. 再询问邮箱
3. 最后确认是否注册

每个信息使用 ask 工具单独提问。
```

### 3. 使用默认值

```php
try {
    $response = $agent->chat('设置通知偏好');
} catch (AskRequiredException $e) {
    // 用户可以直接回车使用默认值
    if ($e->getDefault()) {
        echo "问题：{$e->getQuestion()}\n";
        echo "默认：{$e->getDefault()}\n";

        $answer = readline("请输入（回车使用默认）：");
        AskTool::answer(
            $e->getQuestionId(),
            $answer ?: $e->getDefault()
        );
    }
}
```

## API 说明

### AskTool 静态方法

```php
// 存储问题
AskTool::storeQuestion(string $questionId, array $data): void

// 获取问题
AskTool::getQuestion(string $questionId): ?array

// 提交答案
AskTool::answer(string $questionId, string $answer): void

// 获取答案
AskTool::getAnswer(string $questionId): ?string
```

### AskRequiredException 属性

```php
$e->getQuestion(): string        // 问题内容
$e->getQuestionId(): string      // 问题ID
$e->getContext(): string         // 上下文
$e->getDefault(): string         // 默认值
$e->toArray(): array             // 转换为数组
```

## 注意事项

1. **缓存时间**：问题和答案默认缓存 1 小时（3600秒）
2. **唯一性**：每个问题都有唯一的 `questionId`
3. **异常处理**：必须捕获 `AskRequiredException` 才能正常工作
4. **并发**：使用 Cache 存储，支持多进程访问

## 与其他工具结合

```php
protected function tools(): array
{
    return [
        new AskTool(),
        new SendEmailTool(),
        new DatabaseTool(),
    ];
}
```

Agent 可以根据上下文决定是否需要询问用户：

```text
系统提示：
执行敏感操作前使用 ask 工具确认。
示例：
- 发送邮件：先 ask 确认收件人和内容
- 删除数据：先 ask 确认是否删除
- 查询数据：直接执行，无需确认
```

---

**更新时间**: 2026-09-02