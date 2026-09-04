<?php

declare(strict_types=1);

namespace Modules\FeatureAi\NeuronAI\Tools;

/**
 * Ask 工具异常 - 表示需要用户输入（数据库持久化版本）.
 *
 * 当 Agent 在 Job 中执行并需要用户回答问题时抛出此异常。
 * Job 应该捕获此异常并停止，等待用户回答后重新启动。
 */
class AskRequiredException extends \Exception
{
    /**
     * @param string $question 问题内容
     * @param string $askId 问题ID（数据库主键）
     * @param string $workflowId Workflow ID（用于恢复执行）
     * @param string $context 上下文
     * @param string $default 默认值
     */
    public function __construct(
        protected string $question,
        protected string $askId,
        protected string $workflowId,
        protected string $context = '',
        protected string $default = ''
    ) {
        $message = "需要用户回答问题：{$question}";
        if ($context) {
            $message = "{$context}\n{$message}";
        }
        if ($default) {
            $message .= "\n默认值：{$default}";
        }

        $message .= "\n\nAsk ID: {$askId}";
        $message .= "\nWorkflow ID: {$workflowId}";

        parent::__construct($message);
    }

    /**
     * 获取问题内容.
     *
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * 获取问题ID.
     *
     * @return string
     */
    public function getAskId(): string
    {
        return $this->askId;
    }

    /**
     * 获取 Workflow ID.
     *
     * @return string
     */
    public function getWorkflowId(): string
    {
        return $this->workflowId;
    }

    /**
     * 获取上下文.
     *
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * 获取默认值.
     *
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * 转换为数组（用于 API 响应）.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ask_id' => $this->askId,
            'workflow_id' => $this->workflowId,
            'question' => $this->question,
            'context' => $this->context,
            'default' => $this->default,
        ];
    }

    /**
     * 转换为 JSON（用于日志或队列）.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}