<?php

declare(strict_types=1);

namespace Modules\FeatureAi\NeuronAI\Tools;

use NeuronAI\Tools\Tool;
use Modules\FeatureAi\Models\AiAsk;
use Illuminate\Support\Str;

/**
 * Ask 工具 - Agent 主动向用户提问（数据库持久化版本）.
 *
 * 适用于 Job 异步执行场景：
 * 1. Agent 在 Job 中执行，遇到 ask 调用
 * 2. Job 停止，问题存入数据库
 * 3. 用户回答后，触发新 Job 继续 Agent 执行
 *
 * 使用示例：
 * ```php
 * // 在 Agent 中注册
 * protected function tools(): array {
 *     return [new AskTool()];
 * }
 *
 * // Job 中捕获异常
 * try {
 *     $response = $agent->chat('帮我注册');
 * } catch (AskRequiredException $e) {
 *     // 问题已存入数据库
 *     // Job 应该在这里停止，等待用户回答
 *     return;
 * }
 *
 * // 用户回答后，恢复执行
 * $ask = AiAsk::getPendingAsk($askId);
 * $ask->markAsAnswered('用户答案');
 * ResumeAgentJob::dispatch($ask->workflow_id);
 * ```
 */
class AskTool extends Tool
{
    /**
     * 构造函数.
     */
    public function __construct()
    {
        parent::__construct(
            name: 'ask',
            description: '向用户提问并等待回答。当需要用户提供信息、确认选择或给出反馈时使用此工具。' .
                         '问题会被持久化到数据库，等待用户回答后继续执行。'
        );
    }

    /**
     * 定义工具输入参数.
     *
     * @return array
     */
    public function properties(): array
    {
        return [
            $this->stringProperty(
                name: 'question',
                description: '向用户提出的问题',
                required: true
            ),
            $this->stringProperty(
                name: 'context',
                description: '问题的上下文说明（可选）',
                required: false
            ),
            $this->stringProperty(
                name: 'default',
                description: '默认值（可选）',
                required: false
            ),
            $this->stringProperty(
                name: 'workflow_id',
                description: '当前 Workflow ID（从 Agent 状态获取，自动注入）',
                required: false
            ),
        ];
    }

    /**
     * 执行工具逻辑.
     *
     * 工作流程：
     * 1. 生成唯一 ask_id
     * 2. 将问题存入数据库
     * 3. 抛出 AskRequiredException，停止 Job
     * 4. 外部系统展示问题给用户
     * 5. 用户回答后，更新数据库
     * 6. 触发新 Job 恢复执行
     *
     * @return string 提示信息
     *
     * @throws AskRequiredException
     */
    public function __invoke(): string
    {
        $question = $this->getInput('question');
        $context = $this->getInput('context', '');
        $default = $this->getInput('default', '');

        if (empty($question)) {
            throw new \InvalidArgumentException('question 参数不能为空');
        }

        // 生成唯一 ID
        $askId = 'ask_' . Str::uuid()->toString();
        $workflowId = $this->getInput('workflow_id') ?? Str::uuid()->toString();

        // 获取 Agent 信息（从工具上下文或外部注入）
        $agentClass = $this->getAgentClass();
        $agentState = $this->getAgentState();

        // 存入数据库
        $ask = AiAsk::create([
            'ask_id' => $askId,
            'workflow_id' => $workflowId,
            'question' => $question,
            'context' => $context,
            'default' => $default,
            'status' => AiAsk::STATUS_PENDING,
            'agent_class' => $agentClass,
            'agent_state' => $agentState,
            'expires_at' => now()->addHours(24), // 24小时后过期
        ]);

        // 抛出异常，停止 Job
        throw new AskRequiredException(
            question: $question,
            askId: $ask->ask_id,
            workflowId: $workflowId,
            context: $context,
            default: $default
        );
    }

    /**
     * 获取 Agent 类名.
     *
     * 从调用栈或上下文获取。
     *
     * @return string
     */
    protected function getAgentClass(): string
    {
        // 尝试从工具元数据获取
        if (isset($this->metadata['agent_class'])) {
            return $this->metadata['agent_class'];
        }

        // 从调用栈推断
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($trace as $frame) {
            if (isset($frame['class']) && is_subclass_of($frame['class'], \NeuronAI\Agent::class)) {
                return $frame['class'];
            }
        }

        return 'Unknown';
    }

    /**
     * 获取 Agent 状态快照.
     *
     * 用于恢复执行。
     *
     * @return array|null
     */
    protected function getAgentState(): ?array
    {
        // 从工具元数据获取
        return $this->metadata['agent_state'] ?? null;
    }

    /**
     * 提交答案.
     *
     * @param string $askId 问题ID
     * @param string $answer 答案
     * @param int|null $userId 用户ID
     *
     * @return AiAsk
     *
     * @throws \InvalidArgumentException
     */
    public static function answer(string $askId, string $answer, ?int $userId = null): AiAsk
    {
        $ask = AiAsk::getPendingAsk($askId);

        if (!$ask) {
            throw new \InvalidArgumentException("问题不存在或已处理: {$askId}");
        }

        $ask->markAsAnswered($answer, $userId);

        return $ask;
    }

    /**
     * 取消问题.
     *
     * @param string $askId
     *
     * @return void
     */
    public static function cancel(string $askId): void
    {
        $ask = AiAsk::find($askId);
        if ($ask && $ask->isPending()) {
            $ask->markAsCancelled();
        }
    }
}