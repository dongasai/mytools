<?php

namespace Modules\FeatureAi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\FeatureAi\NeuronAI\Tools\AskRequiredException;
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;

/**
 * Agent 执行 Job.
 *
 * 在队列中异步执行 Agent，支持 Ask 工具中断和恢复。
 */
class ExecuteAgentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * 任务超时时间（秒）.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * 任务最大尝试次数.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param string $agentClass Agent 类名
     * @param string $message 用户消息
     * @param string $workflowId Workflow ID
     * @param array $context 上下文数据
     */
    public function __construct(
        protected string $agentClass,
        protected string $message,
        protected string $workflowId,
        protected array $context = []
    ) {
    }

    /**
     * 执行任务.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // 创建 Agent 实例
            $agent = app($this->agentClass);

            // 执行
            $response = $agent->chat($this->message);

            // 成功：处理结果
            $this->handleSuccess($response);

        } catch (AskRequiredException $e) {
            // Agent 需要用户输入
            $this->handleAskRequired($e);

            // Job 停止，等待用户回答
            // 不抛出异常，Job 视为完成
            return;

        } catch (WorkflowInterrupt $interrupt) {
            // ToolApproval 等中断
            $this->handleInterrupt($interrupt);

            // Job 停止
            return;

        } catch (\Exception $e) {
            // 其他异常
            $this->handleError($e);
            throw $e;
        }
    }

    /**
     * 处理成功结果.
     *
     * @param mixed $response
     *
     * @return void
     */
    protected function handleSuccess($response): void
    {
        // 存储结果到数据库或通知前端
        \Modules\FeatureAi\Models\AiConversation::updateOrCreate(
            ['workflow_id' => $this->workflowId],
            [
                'status' => 'completed',
                'response' => $response->getContent(),
                'completed_at' => now(),
            ]
        );

        // 通知前端（通过事件、WebSocket等）
        event(new \Modules\FeatureAi\Events\AgentCompleted(
            $this->workflowId,
            $response->getContent()
        ));
    }

    /**
     * 处理 Ask 需求.
     *
     * @param AskRequiredException $e
     *
     * @return void
     */
    protected function handleAskRequired(AskRequiredException $e): void
    {
        // Ask 已经被 AskTool 存入数据库
        // 这里只需要通知前端

        // 通知前端有新问题
        event(new \Modules\FeatureAi\Events\AskCreated(
            $e->getAskId(),
            $e->getWorkflowId(),
            $e->toArray()
        ));

        // 更新会话状态
        \Modules\FeatureAi\Models\AiConversation::where('workflow_id', $this->workflowId)
            ->update([
                'status' => 'waiting_user_input',
                'last_ask_id' => $e->getAskId(),
            ]);

        \Log::info('Agent 执行暂停，等待用户输入', [
            'workflow_id' => $this->workflowId,
            'ask_id' => $e->getAskId(),
            'question' => $e->getQuestion(),
        ]);
    }

    /**
     * 处理 Workflow 中断.
     *
     * @param WorkflowInterrupt $interrupt
     *
     * @return void
     */
    protected function handleInterrupt(WorkflowInterrupt $interrupt): void
    {
        // 处理 ToolApproval 等中断
        \Log::info('Agent 执行中断', [
            'workflow_id' => $this->workflowId,
            'request' => $interrupt->getRequest()->jsonSerialize(),
        ]);
    }

    /**
     * 处理错误.
     *
     * @param \Exception $e
     *
     * @return void
     */
    protected function handleError(\Exception $e): void
    {
        \Log::error('Agent 执行失败', [
            'workflow_id' => $this->workflowId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        // 更新会话状态
        \Modules\FeatureAi\Models\AiConversation::where('workflow_id', $this->workflowId)
            ->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
    }

    /**
     * 任务失败处理.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public function failed(\Exception $exception): void
    {
        \Log::error('Agent Job 失败', [
            'workflow_id' => $this->workflowId,
            'error' => $exception->getMessage(),
        ]);

        // 通知前端
        event(new \Modules\FeatureAi\Events\AgentFailed(
            $this->workflowId,
            $exception->getMessage()
        ));
    }
}