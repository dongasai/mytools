# Agent 异步执行 + Ask 工具完整方案

## 架构概述

```
前端请求 → API 分发 Job → 队列执行 Agent
                              ↓
                        遇到 ask 工具
                              ↓
              Job 停止，问题存入 ai_asks 表
                              ↓
                前端轮询获取问题 → 用户回答
                              ↓
                API 提交答案 → 新 Job 恢复 Agent
```

---

## 数据库设计

### ai_asks 表

```sql
CREATE TABLE ai_asks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ask_id VARCHAR(255) UNIQUE COMMENT '问题唯一ID',
    workflow_id VARCHAR(255) INDEX COMMENT 'Workflow ID',
    job_id VARCHAR(255) NULL COMMENT '队列 Job ID',
    
    question TEXT COMMENT '问题内容',
    context TEXT NULL COMMENT '上下文',
    default VARCHAR(255) NULL COMMENT '默认值',
    
    status ENUM('pending', 'answered', 'timeout', 'cancelled') DEFAULT 'pending',
    answer TEXT NULL COMMENT '答案',
    answered_at TIMESTAMP NULL,
    answered_by BIGINT UNSIGNED NULL,
    
    agent_class VARCHAR(255) COMMENT 'Agent 类名',
    agent_state JSON NULL COMMENT 'Agent 状态快照',
    metadata JSON NULL,
    expires_at TIMESTAMP NULL,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_status_expires (status, expires_at),
    INDEX idx_workflow_status (workflow_id, status)
);
```

---

## 使用流程

### 1. 前端发起对话

```javascript
// 发送消息
const response = await fetch('/api/agent/chat', {
    method: 'POST',
    body: JSON.stringify({
        message: '帮我注册账号',
        agent: 'RegistrationAgent'
    })
});

const data = await response.json();
// {
//   status: 'processing',
//   workflow_id: 'wf_xxx',
//   message: 'Agent 开始执行'
// }

// 保存 workflow_id
const workflowId = data.workflow_id;
```

### 2. 轮询获取状态

```javascript
// 轮询检查状态
const pollStatus = setInterval(async () => {
    const response = await fetch(`/api/agent/status/${workflowId}`);
    const data = await response.json();

    if (data.status === 'waiting_user_input') {
        // 有问题需要回答
        clearInterval(pollStatus);
        showAskDialog(data.ask);
    } else if (data.status === 'completed') {
        // 完成
        clearInterval(pollStatus);
        showMessage(data.response);
    } else if (data.status === 'failed') {
        // 失败
        clearInterval(pollStatus);
        showError(data.error);
    }
}, 2000); // 每 2 秒轮询一次
```

### 3. 展示问题给用户

```javascript
function showAskDialog(ask) {
    const answer = prompt(
        ask.question +
        (ask.context ? `\n\n${ask.context}` : '') +
        (ask.default ? `\n\n默认：${ask.default}` : '')
    );

    if (answer !== null) {
        submitAnswer(ask.ask_id, answer || ask.default);
    } else {
        cancelAsk(ask.ask_id);
    }
}
```

### 4. 提交答案

```javascript
async function submitAnswer(askId, answer) {
    const response = await fetch('/api/agent/answer', {
        method: 'POST',
        body: JSON.stringify({
            ask_id: askId,
            answer: answer
        })
    });

    const data = await response.json();
    // {
    //   status: 'answered',
    //   workflow_id: 'wf_xxx',
    //   message: '答案已提交，Agent 继续执行'
    // }

    // 继续轮询状态
    startPolling(data.workflow_id);
}
```

---

## 完整示例：用户注册

### Agent 定义

```php
<?php

namespace Modules\FeatureAi\Agents;

use NeuronAI\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;
use Modules\FeatureAi\Tools\AskTool;
use Modules\FeatureAi\Services\AiProviderService;

class RegistrationAgent extends Agent
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
        return '你是注册助手。
        
工作流程：
1. 使用 ask 工具询问用户姓名
2. 使用 ask 工具询问用户邮箱
3. 使用 ask 工具询问用户密码
4. 调用 register_user 工具完成注册

注意：每个信息单独提问，不要一次性问多个问题。';
    }

    protected function tools(): array
    {
        return [
            new AskTool(),
            Tool::make('register_user', '注册用户')
                ->setCallable(fn($name, $email, $password) => $this->register($name, $email, $password))
                ->addProperty(ToolProperty::string('name', '姓名'))
                ->addProperty(ToolProperty::string('email', '邮箱'))
                ->addProperty(ToolProperty::string('password', '密码')),
        ];
    }

    protected function register(string $name, string $email, string $password): string
    {
        // 实际注册逻辑
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        return "注册成功！欢迎 {$name}";
    }
}
```

### 前端完整代码

```html
<!DOCTYPE html>
<html>
<head>
    <title>AI 注册助手</title>
</head>
<body>
    <div id="chat">
        <div class="message">AI 助手：您好！我可以帮您注册账号。</div>
    </div>

    <input type="text" id="userInput" placeholder="输入消息...">
    <button onclick="sendMessage()">发送</button>

    <script>
        let workflowId = null;
        let pollingInterval = null;

        async function sendMessage() {
            const input = document.getElementById('userInput');
            const message = input.value.trim();
            if (!message) return;

            addMessage('用户：' + message);
            input.value = '';

            const response = await fetch('/api/agent/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    agent: 'RegistrationAgent'
                })
            });

            const data = await response.json();
            workflowId = data.workflow_id;

            startPolling();
        }

        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);

            pollingInterval = setInterval(async () => {
                const response = await fetch(`/api/agent/status/${workflowId}`);
                const data = await response.json();

                switch (data.status) {
                    case 'completed':
                        clearInterval(pollingInterval);
                        addMessage('AI 助手：' + data.response);
                        break;

                    case 'waiting_user_input':
                        clearInterval(pollingInterval);
                        handleAsk(data.ask);
                        break;

                    case 'failed':
                        clearInterval(pollingInterval);
                        addMessage('系统：出错了 - ' + data.error);
                        break;

                    case 'processing':
                        // 继续等待
                        break;
                }
            }, 2000);
        }

        function handleAsk(ask) {
            let prompt = ask.question;
            if (ask.context) prompt += '\n\n' + ask.context;
            if (ask.default) prompt += '\n\n默认：' + ask.default;

            const answer = window.prompt(prompt);

            if (answer !== null) {
                submitAnswer(ask.ask_id, answer || ask.default);
            } else {
                cancelAsk(ask.ask_id);
            }
        }

        async function submitAnswer(askId, answer) {
            addMessage('用户：' + answer);

            await fetch('/api/agent/answer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ask_id: askId,
                    answer: answer
                })
            });

            startPolling();
        }

        async function cancelAsk(askId) {
            await fetch(`/api/agent/ask/${askId}/cancel`, {
                method: 'POST'
            });

            addMessage('系统：已取消');
        }

        function addMessage(text) {
            const chat = document.getElementById('chat');
            const div = document.createElement('div');
            div.className = 'message';
            div.textContent = text;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }
    </script>
</body>
</html>
```

---

## Job 执行流程

### ExecuteAgentJob

```php
try {
    $response = $agent->chat($message);
    // 成功 → 通知前端
} catch (AskRequiredException $e) {
    // 问题已存入数据库
    // 通知前端 → Job 停止
    return;
} catch (Exception $e) {
    // 错误处理
}
```

### ResumeAgentJob

```php
// 获取问题和答案
$ask = AiAsk::where('ask_id', $askId)->first();

// 恢复 Agent
$response = $agent->chat("用户回答：{$ask->answer}");

// 成功 → 通知前端
```

---

## API 端点

| 端点 | 方法 | 说明 |
|------|------|------|
| `/api/agent/chat` | POST | 启动对话 |
| `/api/agent/status/{workflow_id}` | GET | 获取状态 |
| `/api/agent/asks` | GET | 获取问题列表 |
| `/api/agent/answer` | POST | 提交答案 |
| `/api/agent/ask/{ask_id}/cancel` | POST | 取消问题 |

---

## 事件通知（可选）

使用 WebSocket 实现实时推送：

```php
// AskCreated 事件
event(new AskCreated($askId, $workflowId, $askData));

// AgentCompleted 事件
event(new AgentCompleted($workflowId, $response));

// AgentFailed 事件
event(new AgentFailed($workflowId, $error));
```

前端监听：

```javascript
Echo.channel(`workflow.${workflowId}`)
    .listen('AskCreated', (e) => {
        handleAsk(e.ask);
    })
    .listen('AgentCompleted', (e) => {
        showMessage(e.response);
    });
```

---

## 优势

✅ **不阻塞 Worker**: Job 遇到 Ask 立即停止

✅ **长时间等待**: 支持 24 小时等待用户回答

✅ **可扩展**: 支持多个问题队列

✅ **容错**: 超时自动取消，失败可重试

✅ **可追踪**: 数据库记录完整执行过程

---

## 注意事项

1. **Workflow 状态**: 需要持久化 Agent 状态以恢复执行
2. **过期清理**: 定时任务清理过期问题
3. **并发控制**: 同一 workflow 避免并发执行
4. **错误处理**: Job 失败要有通知机制

---

**更新时间**: 2026-09-02