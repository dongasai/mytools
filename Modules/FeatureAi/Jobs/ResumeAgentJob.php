<?php

namespace Modules\FeatureAi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\FeatureAi\Models\AiAsk;
use Modules\FeatureAi\NeuronAI\Tools\AskRequiredException;

/**
 * 恢复 Agent 执行 Job.
 *
 * 用户回答问题后，恢复 Agent 执行。
 */
class ResumeAgentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;
    public $tries = 1;

    /**
     * @param string $askId 问题ID
     */
    public function __construct(
        protected string $askId
    ) {
    }

    /**
     * 执行任务.
     *
     * @return void
     */
    public function handle(): void
    {
        // 获取问题
        $ask = AiAsk::where('ask_id', $this->askId)->first();

        if (!$ask || !$ask->isAnswered()) {
            \Log::error('问题不存在或未回答', ['ask_id' => $this->askId]);
            return;
        }

        // 恢复 Agent 执行
        try {
            // 从状态快照恢复 Agent
            $agentClass = $ask->agent_class;
            $agentState = $ask->agent_state;

            // 创建 Agent 实例
            $agent = app($agentClass);

            // 恢复状态（如果有）
            if ($agentState) {
                // 根据 NeuronAI 的恢复机制恢复状态
                // 这部分需要根据 NeuronAI 的具体 API 实现
            }

            // 继续对话，传入用户答案
            $response = $agent->chat(
                "用户回答：{$ask->answer}"
            );

            // 成功
            $this->handleSuccess($ask, $response);

        } catch (AskRequiredException $e) {
            // 又遇到新的问题
            $this->handleNewAsk($e);

        } catch (\Exception $e) {
            $this->handleError($ask, $e);
            throw $e;
        }
    }

    /**
     * 处理成功.
     *
     * @param AiAsk $ask
     * @param mixed $response
     *
     * @return void
     */
    protected function handleSuccess(AiAsk $ask, $response): void
    {
        // 更新会话状态
        \Modules\FeatureAi\Models\AiConversation::where('workflow_id', $ask->workflow_id)
            ->update([
                'status' => 'completed',
                'response' => $response->getContent(),
                'completed_at' => now(),
            ]);

        // 通知前端
        event(new \Modules\FeatureAi\Events\AgentCompleted(
            $ask->workflow_id,
            $response->getContent()
        ));

        \Log::info('Agent 恢复执行完成', [
            'workflow_id' => $ask->workflow_id,
            'ask_id' => $ask->ask_id,
        ]);
    }

    /**
     * 处理新问题.
     *
     * @param AskRequiredException $e
     *
     * @return void
     */
    protected function handleNewAsk(AskRequiredException $e): void
    {
        // 新问题已经被 AskTool 存入数据库
        // 通知前端
        event(new \Modules\FeatureAi\Events\AskCreated(
            $e->getAskId(),
            $e->getWorkflowId(),
            $e->toArray()
        ));

        \Log::info('Agent 恢复后遇到新问题', [
            'workflow_id' => $e->getWorkflowId(),
            'ask_id' => $e->getAskId(),
        ]);
    }

    /**
     * 处理错误.
     *
     * @param AiAsk $ask
     * @param \Exception $e
     *
     * @return void
     */
    protected function handleError(AiAsk $ask, \Exception $e): void
    {
        \Log::error('Agent 恢复执行失败', [
            'workflow_id' => $ask->workflow_id,
            'ask_id' => $ask->ask_id,
            'error' => $e->getMessage(),
        ]);

        // 更新会话状态
        \Modules\FeatureAi\Models\AiConversation::where('workflow_id', $ask->workflow_id)
            ->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

        // 通知前端
        event(new \Modules\FeatureAi\Events\AgentFailed(
            $ask->workflow_id,
            $e->getMessage()
        ));
    }
}