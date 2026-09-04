<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Services;

use Modules\FeatureSsh\Dtos\CommandResult;
use Modules\FeatureSsh\Enums\CommandStatus;
use Modules\FeatureSsh\Models\CommandLog;
use Modules\FeatureSsh\Support\SshClient;
use Illuminate\Support\Facades\Cache;

/**
 * SSH Bash执行服务
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class SshBashService
{
    /**
     * 执行命令
     *
     * @param int $serverId 服务器ID
     * @param string $command 命令
     * @param array $options 选项：timeout（超时秒数）, save_log（是否保存日志）, user_id（执行用户ID）
     * @return CommandResult 命令执行结果
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 执行失败
     */
    public static function execute(int $serverId, string $command, array $options = []): CommandResult
    {
        $timeout = $options['timeout'] ?? 30;
        $saveLog = $options['save_log'] ?? true;
        $userId = $options['user_id'] ?? null;

        $client = SshConnectionService::getConnection($serverId);
        $startTime = microtime(true);

        $result = $client->execute($command, $timeout);
        $executionTime = round((microtime(true) - $startTime) * 1000);

        $exitCode = $result['exit_code'] ?? -1;
        $status = self::determineStatus($exitCode, $executionTime, $timeout);

        $commandResult = new CommandResult(
            command: $command,
            exitCode: $exitCode,
            output: $result['output'] ?? '',
            error: $result['error'] ?? '',
            status: $status,
            executionTime: (int) $executionTime,
        );

        if ($saveLog) {
            self::saveLog($serverId, $commandResult, $userId);
        }

        SshConnectionService::releaseConnection($client);

        return $commandResult;
    }

    /**
     * 执行后台命令
     *
     * @param int $serverId 服务器ID
     * @param string $command 命令
     * @return string 任务ID
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 执行失败
     */
    public static function executeBackground(int $serverId, string $command): string
    {
        $taskId = self::generateTaskId();

        $nohupCommand = sprintf(
            'nohup %s > /tmp/fssh_task_%s.log 2>&1 & echo $!',
            $command,
            $taskId
        );

        $client = SshConnectionService::getConnection($serverId);
        $result = $client->execute($nohupCommand, 10);
        $pid = trim($result['output'] ?? '');

        SshConnectionService::releaseConnection($client);

        if (empty($pid) || !is_numeric($pid)) {
            throw new \RuntimeException('Failed to start background task');
        }

        Cache::put(
            'fssh_bg_task:' . $taskId,
            [
                'server_id' => $serverId,
                'command' => $command,
                'pid' => $pid,
                'status' => 'running',
                'started_at' => now()->toDateTimeString(),
            ],
            now()->addHours(24)
        );

        return $taskId;
    }

    /**
     * 获取后台命令输出
     *
     * @param string $taskId 任务ID
     * @return array 任务状态信息
     */
    public static function getBackgroundOutput(string $taskId): array
    {
        $task = Cache::get('fssh_bg_task:' . $taskId);

        if (!$task) {
            return [
                'exists' => false,
                'message' => 'Task not found',
            ];
        }

        $serverId = $task['server_id'];
        $pid = $task['pid'];

        $client = SshConnectionService::getConnection($serverId);

        $checkResult = $client->execute("ps -p {$pid} > /dev/null 2>&1 && echo 'running' || echo 'finished'");
        $status = trim($checkResult['output'] ?? '');

        $logPath = '/tmp/fssh_task_' . $taskId . '.log';
        $output = '';

        if ($client->getServer()->system_type->value === 'linux') {
            $outputResult = $client->execute("cat {$logPath} 2>/dev/null || echo ''");
            $output = $outputResult['output'] ?? '';
        }

        SshConnectionService::releaseConnection($client);

        if ($status === 'finished') {
            Cache::forget('fssh_bg_task:' . $taskId);

            if ($client->getServer()->system_type->value === 'linux') {
                self::cleanupBackgroundTask($serverId, $taskId, $pid);
            }
        } else {
            $task['status'] = 'running';
            $task['output'] = $output;
            Cache::put('fssh_bg_task:' . $taskId, $task, now()->addHours(24));
        }

        return [
            'exists' => true,
            'task_id' => $taskId,
            'status' => $status,
            'pid' => $pid,
            'command' => $task['command'],
            'started_at' => $task['started_at'],
            'output' => $output,
        ];
    }

    /**
     * 批量执行命令
     *
     * @param array $serverIds 服务器ID数组
     * @param string $command 命令
     * @param array $options 选项
     * @return array 执行结果数组
     */
    public static function executeBatch(array $serverIds, string $command, array $options = []): array
    {
        $results = [];

        foreach ($serverIds as $serverId) {
            $results[$serverId] = self::execute($serverId, $command, $options);
        }

        return $results;
    }

    /**
     * 测试命令（不保存日志）
     *
     * @param int $serverId 服务器ID
     * @param string $command 命令
     * @param int|null $timeout 超时时间
     * @return CommandResult 命令执行结果
     */
    public static function testCommand(int $serverId, string $command, ?int $timeout = null): CommandResult
    {
        return self::execute($serverId, $command, [
            'timeout' => $timeout ?? 10,
            'save_log' => false,
        ]);
    }

    /**
     * 确定命令状态
     *
     * @param int $exitCode 退出码
     * @param int $executionTime 执行时间（毫秒）
     * @param int $timeout 超时时间（秒）
     * @return CommandStatus 命令状态
     */
    private static function determineStatus(int $exitCode, int $executionTime, int $timeout): CommandStatus
    {
        if ($executionTime >= $timeout * 1000) {
            return CommandStatus::TIMEOUT;
        }

        if ($exitCode !== 0) {
            return CommandStatus::FAILED;
        }

        return CommandStatus::SUCCESS;
    }

    /**
     * 保存命令日志
     *
     * @param int $serverId 服务器ID
     * @param CommandResult $result 命令执行结果
     * @param int|null $userId 执行用户ID
     */
    private static function saveLog(int $serverId, CommandResult $result, ?int $userId = null): void
    {
        $output = $result->output;

        if (strlen($output) > 10000) {
            $output = substr($output, 0, 10000) . "\n... (truncated)";
        }

        CommandLog::create([
            'server_id' => $serverId,
            'user_id' => $userId,
            'command' => $result->command,
            'exit_code' => $result->exitCode,
            'output' => $output,
            'execution_time' => $result->executionTime,
            'status' => $result->status,
            'executed_at' => now(),
        ]);
    }

    /**
     * 生成任务ID
     *
     * @return string 任务ID
     */
    private static function generateTaskId(): string
    {
        return uniqid('fssh_', true);
    }

    /**
     * 清理后台任务
     *
     * @param int $serverId 服务器ID
     * @param string $taskId 任务ID
     * @param string $pid 进程ID
     */
    private static function cleanupBackgroundTask(int $serverId, string $taskId, string $pid): void
    {
        $client = SshConnectionService::getConnection($serverId);
        $client->execute("rm -f /tmp/fssh_task_{$taskId}.log");
        SshConnectionService::releaseConnection($client);
    }
}
