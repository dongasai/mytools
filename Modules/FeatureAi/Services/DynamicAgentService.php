<?php

namespace Modules\FeatureAi\Services;

use Illuminate\Support\Facades\DB;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureAi\Enums\AiProviderType;
use Modules\FeatureAi\Models\DynamicAgent;
use Modules\FeatureAi\Models\DynamicAgentExecution;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\OpenAILike;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Workflow\Persistence\DatabasePersistence;
use NeuronAI\Workflow\Persistence\FilePersistence;
use NeuronAI\Workflow\Persistence\InMemoryPersistence;
use NeuronAI\Workflow\WorkflowState;

/**
 * 动态Agent构建与执行服务.
 *
 * 提供从数据库动态构建Agent实例、执行对话、记录执行日志等功能。
 * 所有方法均为静态方法，便于调用。
 */
class DynamicAgentService
{
    /**
     * 从数据库构建Agent实例.
     *
     * 根据Agent ID从数据库加载配置，动态构建NeuronAI Agent实例。
     *
     * @param int $agentId Agent ID
     * @return Agent 构建好的Agent实例
     *
     * @throws \RuntimeException Agent不存在或配置错误
     * @throws \InvalidArgumentException Provider类不支持
     */
    public static function buildFromDatabase(int $agentId): Agent
    {
        // 1. 加载Agent定义
        $agentDef = DynamicAgent::where('id', $agentId)
            ->where('is_active', true)
            ->first();

        if (!$agentDef) {
            throw new \RuntimeException("Agent with ID {$agentId} not found or inactive");
        }

        // 2. 加载启用的工具
        $tools = $agentDef->enabledTools();

        // 3. 构建Provider
        $provider = self::buildProvider(
            $agentDef->provider_class,
            $agentDef->provider_config ?? []
        );

        // 4. 构建Agent
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
                    $toolConfig->tool_config ?? []
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
     * 构建Provider实例.
     *
     * 根据Provider类名和配置创建对应的AI Provider实例。
     *
     * @param string $providerClass Provider类名
     * @param array $config Provider配置
     * @return AIProviderInterface Provider实例
     *
     * @throws \RuntimeException Provider类不存在
     * @throws \InvalidArgumentException Provider类必须实现AIProviderInterface
     */
    protected static function buildProvider(string $providerClass, array $config): AIProviderInterface
    {
        if (!class_exists($providerClass)) {
            throw new \RuntimeException("Provider class {$providerClass} not found");
        }

        if (!is_a($providerClass, AIProviderInterface::class, true)) {
            throw new \InvalidArgumentException(
                "Class {$providerClass} must implement AIProviderInterface"
            );
        }

        // 创建带全局配置的HttpClient
        $httpClient = AiProviderService::createHttpClient();

        // 根据Provider类型处理配置
        // Anthropic
        if (is_a($providerClass, Anthropic::class, true)) {
            return new $providerClass(
                key: $config['api_key'] ?? env('ANTHROPIC_API_KEY'),
                model: $config['model'] ?? 'claude-sonnet-4-6',
                max_tokens: $config['max_tokens'] ?? 8192,
                parameters: $config['parameters'] ?? [],
                httpClient: $httpClient,
            );
        }

        // Gemini
        if (is_a($providerClass, Gemini::class, true)) {
            return new $providerClass(
                key: $config['api_key'] ?? env('GEMINI_API_KEY'),
                model: $config['model'] ?? 'gemini-pro',
                parameters: $config['parameters'] ?? [],
                httpClient: $httpClient,
            );
        }

        // Ollama
        if (is_a($providerClass, Ollama::class, true)) {
            return new $providerClass(
                url: $config['url'] ?? 'http://localhost:11434/api',
                model: $config['model'] ?? 'llama2',
                parameters: $config['parameters'] ?? [],
                httpClient: $httpClient,
            );
        }

        // OpenAILike
        if (is_a($providerClass, OpenAILike::class, true)) {
            return new $providerClass(
                baseUri: $config['base_url'] ?? '',
                key: $config['api_key'] ?? '',
                model: $config['model'] ?? '',
                parameters: $config['parameters'] ?? [],
                httpClient: $httpClient,
            );
        }

        // OpenAI家族（OpenAI, Deepseek, Grok, ZAI, Mistral等）
        if (is_a($providerClass, OpenAI::class, true)) {
            return new $providerClass(
                key: $config['api_key'] ?? env('OPENAI_API_KEY'),
                model: $config['model'] ?? 'gpt-4o',
                parameters: $config['parameters'] ?? [],
                httpClient: $httpClient,
            );
        }

        // 其他Provider，直接传入配置
        return new $providerClass(...$config);
    }

    /**
     * 构建工具实例.
     *
     * 根据工具类名和配置创建对应的Tool实例。
     *
     * @param string $toolClass 工具类名
     * @param array|null $config 工具配置
     * @return ToolInterface 工具实例
     *
     * @throws \RuntimeException 工具类不存在
     * @throws \InvalidArgumentException 工具类必须实现ToolInterface
     */
    protected static function buildTool(string $toolClass, ?array $config = []): ToolInterface
    {
        if (!class_exists($toolClass)) {
            throw new \RuntimeException("Tool class {$toolClass} not found");
        }

        if (!is_a($toolClass, ToolInterface::class, true)) {
            throw new \InvalidArgumentException(
                "Class {$toolClass} must implement ToolInterface"
            );
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
     * 构建持久化实例.
     *
     * @param string $driver 持久化驱动:database/file/memory
     * @param int $agentId Agent ID
     * @return object|null 持久化实例
     */
    protected static function buildPersistence(string $driver, int $agentId): ?object
    {
        return match ($driver) {
            'database' => new DatabasePersistence(
                DB::connection()->getPdo(),
                'featureai_dynamic_agent_workflow_states'
            ),
            'file' => new FilePersistence(
                storage_path("app/agent_workflows/{$agentId}")
            ),
            'memory' => new InMemoryPersistence(),
            default => null,
        };
    }

    /**
     * 执行Agent对话.
     *
     * 根据Agent ID构建Agent并执行对话，自动记录执行日志。
     *
     * @param int $agentId Agent ID
     * @param string $message 用户消息
     * @param array $context 上下文数据
     * @param int|null $userId 执行用户ID（用于记录日志）
     * @return array 执行结果 ['status' => 'success/failed/interrupted', 'message' => '...', ...]
     */
    public static function chat(
        int $agentId,
        string $message,
        array $context = [],
        ?int $userId = null
    ): array {
        $startTime = microtime(true);
        $workflowId = null;
        $response = null;
        $status = 'failed';
        $outputMessage = null;
        $toolsCalled = [];

        try {
            // 构建Agent
            $agent = self::buildFromDatabase($agentId);

            // 添加上下文到消息
            $userMessage = UserMessage::make($message);
            if (!empty($context)) {
                $state = new WorkflowState($context);
                $agent->resolveState()->merge($state);
            }

            // 执行对话
            $response = $agent->chat($userMessage);
            $workflowId = $agent->getWorkflowId();

            // 获取响应内容
            $outputMessage = $response->getContent();
            $status = 'success';

            return [
                'status' => 'success',
                'message' => $outputMessage,
                'workflow_id' => $workflowId,
            ];
        } catch (\NeuronAI\Workflow\Interrupt\WorkflowInterrupt $interrupt) {
            $status = 'interrupted';
            $workflowId = $interrupt->getWorkflowId();

            // 记录中断信息
            $toolsCalled = [
                'interrupt_type' => get_class($interrupt),
                'request' => $interrupt->getRequest()->jsonSerialize(),
            ];

            return [
                'status' => 'interrupted',
                'workflow_id' => $workflowId,
                'request' => $interrupt->getRequest()->jsonSerialize(),
            ];
        } catch (\Exception $e) {
            $status = 'failed';
            $outputMessage = $e->getMessage();

            // 记录系统日志
            SystemLogService::exception('feature_ai', $e, [
                'service' => 'DynamicAgentService::chat',
                'agent_id' => $agentId,
                'user_id' => $userId,
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        } finally {
            // 计算执行时长
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // 记录执行日志
            self::logExecution(
                agentId: $agentId,
                workflowId: $workflowId,
                userId: $userId,
                inputMessage: $message,
                outputMessage: $outputMessage,
                status: $status,
                toolsCalled: $toolsCalled,
                durationMs: $durationMs
            );
        }
    }

    /**
     * 恢复中断的Agent执行.
     *
     * @param int $agentId Agent ID
     * @param string $workflowId Workflow ID
     * @param array $userDecisions 用户决策
     * @return array 执行结果
     */
    public static function resume(int $agentId, string $workflowId, array $userDecisions): array
    {
        // 加载Agent定义
        $agentDef = DynamicAgent::find($agentId);
        if (!$agentDef) {
            return ['status' => 'failed', 'error' => 'Agent not found'];
        }

        // 构建持久化
        $persistence = self::buildPersistence($agentDef->persistence_driver, $agentId);

        // 构建Agent（带恢复token）
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
     * 构造恢复请求.
     *
     * @param array $userDecisions 用户决策
     * @return \NeuronAI\Workflow\Interrupt\InterruptRequest 恢复请求
     */
    protected static function buildResumeRequest(array $userDecisions): \NeuronAI\Workflow\Interrupt\InterruptRequest
    {
        // 根据userDecisions构造具体的InterruptRequest
        // 这里需要根据具体的中断类型处理
        // 简化示例：
        return new \NeuronAI\Workflow\Interrupt\ApprovalRequest(
            message: 'Resuming execution',
            actions: []
        );
    }

    /**
     * 记录Agent执行日志.
     *
     * @param int $agentId Agent ID
     * @param string|null $workflowId Workflow ID
     * @param int|null $userId 用户ID
     * @param string $inputMessage 输入消息
     * @param string|null $outputMessage 输出消息
     * @param string $status 执行状态
     * @param array $toolsCalled 调用的工具列表
     * @param int $durationMs 执行时长（毫秒）
     * @return void
     */
    protected static function logExecution(
        int $agentId,
        ?string $workflowId,
        ?int $userId,
        string $inputMessage,
        ?string $outputMessage,
        string $status,
        array $toolsCalled,
        int $durationMs
    ): void {
        try {
            DynamicAgentExecution::create([
                'agent_id' => $agentId,
                'workflow_id' => $workflowId,
                'user_id' => $userId,
                'input_message' => $inputMessage,
                'output_message' => $outputMessage,
                'status' => $status,
                'tools_called' => $toolsCalled,
                'duration_ms' => $durationMs,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不应影响主流程
            SystemLogService::exception('feature_ai', $e, [
                'service' => 'DynamicAgentService::logExecution',
                'agent_id' => $agentId,
            ]);
        }
    }

    /**
     * 获取Agent的执行统计.
     *
     * @param int $agentId Agent ID
     * @param int $days 统计天数（默认30天）
     * @return array 统计信息
     */
    public static function getExecutionStats(int $agentId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = DynamicAgentExecution::where('agent_id', $agentId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count, AVG(duration_ms) as avg_duration')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'total' => $stats->sum('count'),
            'success' => $stats->get('success')?->count ?? 0,
            'failed' => $stats->get('failed')?->count ?? 0,
            'interrupted' => $stats->get('interrupted')?->count ?? 0,
            'avg_duration_ms' => (int) ($stats->avg('avg_duration') ?? 0),
            'period_days' => $days,
        ];
    }
}
