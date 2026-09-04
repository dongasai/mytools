<?php

namespace Modules\FeatureAi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\FeatureAi\Jobs\ExecuteAgentJob;
use Modules\FeatureAi\Jobs\ResumeAgentJob;
use Modules\FeatureAi\Models\AiAsk;
use Modules\FeatureAi\Models\AiConversation;
use Modules\FeatureAi\NeuronAI\Tools\AskTool;

/**
 * AI Agent API 控制器.
 *
 * 处理 Agent 的异步执行和交互。
 */
class AgentApiController extends Controller
{
    /**
     * 启动 Agent 对话.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'agent' => 'required|string', // Agent 类名或别名
            'context' => 'array',
        ]);

        $message = $request->input('message');
        $agentClass = $this->resolveAgentClass($request->input('agent'));
        $context = $request->input('context', []);

        // 生成 Workflow ID
        $workflowId = 'wf_' . Str::uuid()->toString();

        // 创建会话记录
        $conversation = AiConversation::create([
            'workflow_id' => $workflowId,
            'agent_class' => $agentClass,
            'status' => 'processing',
            'user_id' => auth()->id(),
            'context' => $context,
        ]);

        // 分发 Job
        ExecuteAgentJob::dispatch(
            $agentClass,
            $message,
            $workflowId,
            $context
        );

        return response()->json([
            'status' => 'processing',
            'workflow_id' => $workflowId,
            'message' => 'Agent 开始执行',
        ]);
    }

    /**
     * 获取对话状态.
     *
     * @param string $workflowId
     *
     * @return JsonResponse
     */
    public function status(string $workflowId): JsonResponse
    {
        $conversation = AiConversation::where('workflow_id', $workflowId)->first();

        if (!$conversation) {
            return response()->json(['error' => '对话不存在'], 404);
        }

        $response = [
            'workflow_id' => $workflowId,
            'status' => $conversation->status,
            'response' => $conversation->response,
        ];

        // 如果有待回答问题，返回问题信息
        if ($conversation->status === 'waiting_user_input' && $conversation->last_ask_id) {
            $ask = AiAsk::where('ask_id', $conversation->last_ask_id)->first();
            if ($ask && $ask->isPending()) {
                $response['ask'] = $ask->toArray();
            }
        }

        return response()->json($response);
    }

    /**
     * 获取待回答的问题列表.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getPendingAsks(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $asks = AiAsk::whereHas('conversation', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', AiAsk::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'asks' => $asks->map(fn($ask) => $ask->toArray()),
        ]);
    }

    /**
     * 回答问题.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function answerAsk(Request $request): JsonResponse
    {
        $request->validate([
            'ask_id' => 'required|string',
            'answer' => 'required|string',
        ]);

        $askId = $request->input('ask_id');
        $answer = $request->input('answer');
        $userId = auth()->id();

        try {
            // 提交答案
            $ask = AskTool::answer($askId, $answer, $userId);

            // 触发恢复 Job
            ResumeAgentJob::dispatch($askId);

            return response()->json([
                'status' => 'answered',
                'ask_id' => $askId,
                'workflow_id' => $ask->workflow_id,
                'message' => '答案已提交，Agent 继续执行',
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 取消问题.
     *
     * @param string $askId
     *
     * @return JsonResponse
     */
    public function cancelAsk(string $askId): JsonResponse
    {
        AskTool::cancel($askId);

        return response()->json([
            'status' => 'cancelled',
            'ask_id' => $askId,
        ]);
    }

    /**
     * 解析 Agent 类名.
     *
     * @param string $agent
     *
     * @return string
     */
    protected function resolveAgentClass(string $agent): string
    {
        // 支持别名映射
        $aliases = config('ai.agents', []);

        if (isset($aliases[$agent])) {
            return $aliases[$agent];
        }

        // 如果是完整类名，直接返回
        if (class_exists($agent)) {
            return $agent;
        }

        throw new \InvalidArgumentException("Agent 不存在: {$agent}");
    }
}