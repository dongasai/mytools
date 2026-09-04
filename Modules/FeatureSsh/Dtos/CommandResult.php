<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Dtos;

use Modules\FeatureSsh\Enums\CommandStatus;

/**
 * SSH命令执行结果DTO
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class CommandResult
{
    /**
     * 执行的命令
     */
    public readonly string $command;

    /**
     * 退出码
     */
    public readonly ?int $exitCode;

    /**
     * 命令输出
     */
    public readonly string $output;

    /**
     * 错误输出
     */
    public readonly string $error;

    /**
     * 执行状态
     */
    public readonly CommandStatus $status;

    /**
     * 执行时间（毫秒）
     */
    public readonly int $executionTime;

    /**
     * 后台任务ID
     */
    public readonly ?string $taskId;

    /**
     * 构造函数
     *
     * @param string $command 执行的命令
     * @param int|null $exitCode 退出码
     * @param string $output 命令输出
     * @param string $error 错误输出
     * @param CommandStatus $status 执行状态
     * @param int $executionTime 执行时间（毫秒）
     * @param string|null $taskId 后台任务ID
     */
    public function __construct(
        string $command,
        ?int $exitCode = null,
        string $output = '',
        string $error = '',
        CommandStatus $status = CommandStatus::SUCCESS,
        int $executionTime = 0,
        ?string $taskId = null,
    ) {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->output = $output;
        $this->error = $error;
        $this->status = $status;
        $this->executionTime = $executionTime;
        $this->taskId = $taskId;
    }

    /**
     * 是否成功
     */
    public function isSuccess(): bool
    {
        return $this->status === CommandStatus::SUCCESS;
    }

    /**
     * 是否失败
     */
    public function isFailed(): bool
    {
        return $this->status === CommandStatus::FAILED;
    }

    /**
     * 是否超时
     */
    public function isTimeout(): bool
    {
        return $this->status === CommandStatus::TIMEOUT;
    }

    /**
     * 获取完整输出（stdout + stderr）
     */
    public function getFullOutput(): string
    {
        $parts = [];

        if ($this->output) {
            $parts[] = $this->output;
        }

        if ($this->error) {
            $parts[] = 'ERROR: ' . $this->error;
        }

        return implode("\n", $parts);
    }

    /**
     * 获取截断的输出
     */
    public function getTruncatedOutput(int $length = 1000): string
    {
        $fullOutput = $this->getFullOutput();

        if (strlen($fullOutput) <= $length) {
            return $fullOutput;
        }

        return substr($fullOutput, 0, $length) . "\n... (truncated)";
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'command' => $this->command,
            'exit_code' => $this->exitCode,
            'output' => $this->output,
            'error' => $this->error,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'execution_time' => $this->executionTime,
            'task_id' => $this->taskId,
        ];
    }
}
