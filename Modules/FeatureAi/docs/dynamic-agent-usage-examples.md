# 动态 Agent 使用示例

## 快速开始

### 1. 创建动态 Agent

#### 通过数据库直接创建

```php
use Illuminate\Support\Facades\DB;

// 创建 Agent 定义
$agentId = DB::table('featureai_dynamic_agents')->insertGetId([
    'name' => '智能客服助手',
    'slug' => 'customer-service',
    'description' => '处理客户咨询的智能助手',
    'is_active' => true,

    // Provider 配置
    'provider_class' => \NeuronAI\Providers\OpenAI\OpenAI::class,
    'provider_config' => json_encode([
        'api_key' => env('OPENAI_API_KEY'),
        'model' => 'gpt-4o',
    ]),

    // Instructions
    'instructions' => '你是一个专业的客服助手，耐心解答客户问题。
注意：
1. 保持礼貌和专业
2. 如果不确定，主动承认并寻求帮助
3. 优先使用工具查询信息',

    // 行为配置
    'tool_max_runs' => 10,
    'parallel_tool_calls' => false,
    'persistence_driver' => 'database',

    'created_at' => now(),
    'updated_at' => now(),
]);

// 添加工具
DB::table('featureai_dynamic_agent_tools')->insert([
    [
        'agent_id' => $agentId,
        'tool_class' => \Modules\FeatureAi\Tools\KnowledgeBaseTool::class,
        'tool_name' => 'knowledge_base',
        'tool_config' => json_encode(['source' => 'faq', 'max_results' => 5]),
        'order_index' => 1,
        'is_enabled' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'agent_id' => $agentId,
        'tool_class' => \Modules\FeatureAi\Tools\TicketTool::class,
        'tool_name' => 'create_ticket',
        'tool_config' => json_encode([]),
        'order_index' => 2,
        'is_enabled' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
```

---

### 2. 执行 Agent 对话

#### 基础用法

```php
use Modules\FeatureAi\Services\DynamicAgentService;

// 简单对话
$result = DynamicAgentService::chat(
    agentId: 1,
    message: '我想了解退款政策'
);

if ($result['status'] === 'success') {
    echo $result['message'];
} else {
    echo "Error: " . $result['error'];
}
```

#### 带上下文的对话

```php
// 传递上下文数据
$result = DynamicAgentService::chat(
    agentId: 1,
    message: '我的订单状态是什么？',
    context: [
        'user_id' => 123,
        'merchant_id' => 1,
        'order_id' => 'ORD-2026-001',
    ]
);
```

#### 处理中断

```php
$result = DynamicAgentService::chat($agentId, '删除所有临时文件');

if ($result['status'] === 'interrupted') {
    // Agent 需要确认
    $workflowId = $result['workflow_id'];
    $request = $result['request'];

    // 展示给用户
    echo "需要确认：\n";
    foreach ($request['actions'] as $action) {
        echo "- {$action['name']}: {$action['description']}\n";
    }

    // 用户决策
    $userDecisions = [
        $request['actions'][0]['id'] => 'approve', // 批准
        $request['actions'][1]['id'] => 'reject',  // 拒绝
    ];

    // 恢复执行
    $finalResult = DynamicAgentService::resume($agentId, $workflowId, $userDecisions);
    echo $finalResult['message'];
}
```

---

### 3. 在 Controller 中使用

```php
<?php

namespace Modules\FeatureApi\ApiProto\Handlers;

use Modules\ApiProto\BaseHandler;
use Modules\FeatureAi\Services\DynamicAgentService;
use Modules\FeatureAi\Models\DynamicAgent;

class ChatHandler extends BaseHandler
{
    /**
     * 执行 Agent 对话
     */
    public function handle(): void
    {
        $agentSlug = $this->getSegment(0); // 从 URL 获取 agent slug
        $message = $this->input->getMessage();

        // 查找 Agent
        $agent = DynamicAgent::where('slug', $agentSlug)
            ->where('is_active', true)
            ->first();

        if (!$agent) {
            $this->errorResponse('Agent not found');
            return;
        }

        // 执行对话
        $result = DynamicAgentService::chat(
            agentId: $agent->id,
            message: $message,
            context: [
                'user_id' => $this->user_id,
                'merchant_id' => $this->token_merchant_id,
            ]
        );

        if ($result['status'] === 'success') {
            $this->successResponse([
                'message' => $result['message'],
                'workflow_id' => $result['workflow_id'] ?? null,
            ]);
        } elseif ($result['status'] === 'interrupted') {
            $this->successResponse([
                'status' => 'need_confirmation',
                'workflow_id' => $result['workflow_id'],
                'request' => $result['request'],
            ]);
        } else {
            $this->errorResponse($result['error']);
        }
    }
}
```

---

### 4. 在 Dcat Admin 中管理 Agent

#### 创建 Agent 控制器

```php
<?php

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\FeatureAi\Models\DynamicAgent;

class DynamicAgentController extends AdminController
{
    protected $title = '动态 Agent 管理';

    protected function grid(): Grid
    {
        return Grid::make(new DynamicAgent(), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '名称');
            $grid->column('slug', '标识');
            $grid->column('provider_class', 'Provider')->display(function ($class) {
                return class_basename($class);
            });
            $grid->column('is_active', '状态')->switch();
            $grid->column('created_at', '创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('name');
                $filter->equal('is_active')->select([0 => '禁用', 1 => '启用']);
            });

            $grid->actions([
                // 测试按钮
                new \Modules\FeatureAi\DcatAdmin\Actions\TestAgentAction(),
            ]);
        });
    }

    protected function form(): Form
    {
        return Form::make(new DynamicAgent(), function (Form $form) {
            $form->display('id', 'ID');

            $form->text('name', '名称')->required();
            $form->text('slug', '标识')->required()->rules('unique:featureai_dynamic_agents,slug,{{id}}');
            $form->textarea('description', '描述');

            // Provider 配置
            $form->select('provider_class', 'Provider')
                ->options([
                    \NeuronAI\Providers\OpenAI\OpenAI::class => 'OpenAI',
                    \NeuronAI\Providers\Anthropic\Anthropic::class => 'Anthropic',
                ])
                ->required();

            $form->textarea('provider_config', 'Provider 配置（JSON）')
                ->help('例如：{"api_key": "sk-...", "model": "gpt-4o"}')
                ->default('{}');

            // Instructions
            $form->textarea('instructions', '系统提示词')
                ->required()
                ->rows(5);

            // 行为配置
            $form->number('tool_max_runs', '工具最大调用次数')
                ->default(10)
                ->min(1)
                ->max(100);

            $form->switch('parallel_tool_calls', '并行执行工具')
                ->default(false);

            $form->select('persistence_driver', '持久化驱动')
                ->options([
                    'database' => '数据库',
                    'file' => '文件',
                    'memory' => '内存（测试用）',
                ])
                ->default('database');

            $form->switch('is_active', '启用');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 工具管理（嵌套表单）
            $form->hasMany('tools', '工具配置', function (Form\NestedForm $form) {
                $form->select('tool_class', '工具类')
                    ->options($this->getAvailableTools())
                    ->required();

                $form->text('tool_name', '工具名称')->required();
                $form->textarea('tool_config', '工具配置（JSON）')->default('{}');
                $form->number('order_index', '排序')->default(0);
                $form->switch('is_enabled', '启用')->default(true);
            });
        });
    }

    /**
     * 获取可用工具列表
     */
    protected function getAvailableTools(): array
    {
        return [
            \Modules\FeatureAi\Tools\KnowledgeBaseTool::class => '知识库查询',
            \Modules\FeatureAi\Tools\TicketTool::class => '工单管理',
            \Modules\FeatureAi\Tools\SearchTool::class => '搜索工具',
            \Modules\FeatureAi\Tools\WeatherTool::class => '天气查询',
        ];
    }
}
```

---

## 进阶用法

### 1. 动态 Instructions 模板

```php
// 在 Agent 定义中使用模板变量
'instructions' => '你是 {merchant_name} 的客服助手。
当前商户：{merchant_name}
当前用户：{user_name}
请注意保护用户隐私。',

// 在执行时替换变量
$agent = DynamicAgentService::buildFromDatabase($agentId);

// 替换 Instructions 中的变量
$instructions = str_replace(
    ['{merchant_name}', '{user_name}'],
    ['三牛科技', $userName],
    $agent->resolveInstructions()
);

$agent->setInstructions($instructions);
```

### 2. 条件性工具加载

```php
use Modules\FeatureAi\Models\DynamicAgent;

$agentDef = DynamicAgent::with(['tools' => function ($query) {
    // 根据条件过滤工具
    $query->where('is_enabled', true)
        ->when(auth()->user()->role === 'admin', function ($q) {
            $q->orWhere('tool_name', 'admin_panel');
        });
}])->find($agentId);
```

### 3. 多轮对话历史

```php
use NeuronAI\Chat\History\ChatHistory;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Chat\Messages\AssistantMessage;

// 从数据库加载对话历史
$history = new ChatHistory();
$messages = Conversation::where('session_id', $sessionId)
    ->orderBy('created_at')
    ->get();

foreach ($messages as $msg) {
    if ($msg->role === 'user') {
        $history->addMessage(new UserMessage($msg->content));
    } else {
        $history->addMessage(new AssistantMessage($msg->content));
    }
}

// 附加到 Agent
$agent = DynamicAgentService::buildFromDatabase($agentId);
$agent->withChatHistory($history);

// 继续对话
$response = $agent->chat(new UserMessage('还有其他问题吗？'));
```

### 4. 流式输出

```php
$agent = DynamicAgentService::buildFromDatabase($agentId);

foreach ($agent->stream(new UserMessage('写一篇长文章')) as $chunk) {
    echo $chunk;
    flush();
}
```

### 5. 结构化输出

```php
use Modules\FeatureAi\Dto\ReportSchema;

$agent = DynamicAgentService::buildFromDatabase($agentId);

$report = $agent->structured(
    new UserMessage('分析这个月的销售数据'),
    ReportSchema::class
);

echo "总结：" . $report->summary . "\n";
echo "建议：" . implode(', ', $report->recommendations) . "\n";
```

---

## 错误处理

### 常见错误

```php
use Modules\FeatureAi\Services\DynamicAgentService;

try {
    $result = DynamicAgentService::chat($agentId, $message);
} catch (\RuntimeException $e) {
    // Agent 配置错误
    Log::error('Agent 配置错误', [
        'agent_id' => $agentId,
        'error' => $e->getMessage(),
    ]);

    return response()->json([
        'error' => 'Agent 配置错误，请联系管理员',
    ], 500);
} catch (\NeuronAI\Exceptions\AgentException $e) {
    // Agent 执行错误
    Log::error('Agent 执行失败', [
        'agent_id' => $agentId,
        'message' => $message,
        'error' => $e->getMessage(),
    ]);

    return response()->json([
        'error' => 'Agent 执行失败，请稍后重试',
    ], 500);
}
```

---

## 监控与日志

### 查看执行日志

```php
use Modules\FeatureAi\Models\DynamicAgentExecution;

$executions = DynamicAgentExecution::where('agent_id', $agentId)
    ->orderBy('created_at', 'desc')
    ->paginate(20);

foreach ($executions as $execution) {
    echo "时间：{$execution->created_at}\n";
    echo "输入：{$execution->input_message}\n";
    echo "输出：{$execution->output_message}\n";
    echo "工具调用：" . json_encode($execution->tools_called) . "\n";
    echo "耗时：{$execution->duration_ms}ms\n";
    echo "---\n";
}
```

### 统计 Agent 使用情况

```php
use Illuminate\Support\Facades\DB;

$stats = DB::table('featureai_dynamic_agent_executions')
    ->where('agent_id', $agentId)
    ->where('created_at', '>=', now()->subDays(7))
    ->select([
        DB::raw('COUNT(*) as total_executions'),
        DB::raw('AVG(duration_ms) as avg_duration'),
        DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count'),
        DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count'),
    ])
    ->first();

echo "总执行次数：{$stats->total_executions}\n";
echo "平均耗时：" . round($stats->avg_duration) . "ms\n";
echo "成功率：" . round($stats->success_count / $stats->total_executions * 100, 2) . "%\n";
```

---

## 最佳实践

### 1. Agent 命名规范

```
客服类：customer-service, technical-support
分析类：data-analyst, report-generator
助手类：personal-assistant, writing-assistant
专业类：legal-advisor, medical-consultant
```

### 2. Instructions 编写技巧

```php
// ✅ 好的 Instructions
'instructions' => '你是一个专业的客服助手。

职责：
- 解答客户关于产品和服务的疑问
- 处理客户投诉并寻求解决方案
- 引导客户完成购买流程

注意：
1. 保持礼貌和专业
2. 不确定时，主动承认并寻求人工支持
3. 优先使用工具查询准确信息
4. 不泄露用户隐私信息

可用工具：
- knowledge_base: 查询产品信息、FAQ
- create_ticket: 创建工单转人工处理',

// ❌ 不好的 Instructions
'instructions' => '你是一个助手。', // 太简单
```

### 3. 工具配置建议

```php
// 为工具添加详细配置
'tool_config' => json_encode([
    'source' => 'knowledge_base',
    'max_results' => 5,
    'min_score' => 0.7,
    'cache_ttl' => 3600, // 缓存1小时
])
```

### 4. 性能优化

- 使用 `parallel_tool_calls` 并行执行工具
- 设置合理的 `tool_max_runs` 避免无限循环
- 使用 `database` 持久化支持分布式部署
- 缓存构建好的 Agent 实例（短时间复用）

---

## 测试 Agent

### 单元测试

```php
<?php

namespace Modules\FeatureAi\Tests\Unit;

use Tests\TestCase;
use Modules\FeatureAi\Services\DynamicAgentService;

class DynamicAgentTest extends TestCase
{
    public function test_build_agent_from_database(): void
    {
        $agent = DynamicAgentService::buildFromDatabase(1);

        $this->assertInstanceOf(\NeuronAI\Agent::class, $agent);
        $this->assertNotEmpty($agent->resolveInstructions());
        $this->assertNotEmpty($agent->getTools());
    }

    public function test_chat_with_agent(): void
    {
        $result = DynamicAgentService::chat(1, '测试消息');

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('message', $result);
    }
}
```

---

**更新时间**: 2026-09-02
**版本**: 1.0.0