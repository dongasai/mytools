<?php

namespace Modules\FeatureAi\Services;

use Illuminate\Support\Facades\Log;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureAi\Models\LlmApiLog;
use Modules\FeatureAi\Models\AiProviderModel;
use NeuronAI\Chat\Messages\Message;

/**
 * LLM API 调用日志服务
 *
 * 记录 LLM API 调用到数据库，包含 tokens、成本、响应时间等关键指标
 *
 * @package Modules\FeatureAi\Services
 */
class LlmApiLogService
{
    /**
     * 记录 LLM API 调用
     *
     * @param string $requestId 请求唯一ID
     * @param array $providerConfig 提供商配置
     * @param string $prompt 用户提示文本
     * @param Message|null $response neuron-ai 响应对象
     * @param int $durationMs 响应时间（毫秒）
     * @param array $parameters 额外参数
     * @param bool $success 是否成功
     * @param string|null $errorType 错误类型
     * @param string|null $errorMessage 错误信息
     * @return void
     */
    public static function logApiCall(
        string $requestId,
        array $providerConfig,
        string $prompt,
        ?Message $response,
        int $durationMs,
        array $parameters = [],
        bool $success = true,
        ?string $errorType = null,
        ?string $errorMessage = null
    ): void {
        // 1. 提取 tokens 信息
        $usage = $response ? $response->getUsage() : null;
        $inputTokens = $usage ? $usage->inputTokens : 0;
        $outputTokens = $usage ? $usage->outputTokens : 0;
        $totalTokens = $inputTokens + $outputTokens;

        // 2. 查找模型配置
        $model = null;
        $modelId = null;
        if (!empty($providerConfig['id']) && !empty($providerConfig['model'])) {
            $model = self::findModel($providerConfig['id'], $providerConfig['model']);
            $modelId = $model ? $model->id : null;
        }

        // 3. 计算成本
        $inputCost = self::calculateInputCost($model, $inputTokens);
        $outputCost = self::calculateOutputCost($model, $outputTokens);
        $totalCost = number_format($inputCost + $outputCost, 10, '.', '');

        // 4. 写数据库日志（失败不影响主流程）
        self::logToDatabase([
            'request_id' => $requestId,
            'provider_id' => $providerConfig['id'] ?? null,
            'model_id' => $modelId,
            'model_name' => $providerConfig['model'] ?? '',
            'service_type' => $parameters['service_type'] ?? null,
            'service_name' => $parameters['service_name'] ?? null,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost,
            'response_time_ms' => $durationMs,
            'success' => $success,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * 生成请求唯一ID
     *
     * @return string
     */
    public static function generateRequestId(): string
    {
        return date('YmdHis') . bin2hex(random_bytes(8));
    }

    /**
     * 查找模型配置
     *
     * @param int $providerId 提供商ID
     * @param string $modelName 模型名称
     * @return AiProviderModel|null
     */
    private static function findModel(int $providerId, string $modelName): ?AiProviderModel
    {
        return AiProviderModel::where('provider_id', $providerId)
            ->where('model_name', $modelName)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * 计算输入成本
     *
     * @param AiProviderModel|null $model 模型配置
     * @param int $tokens 输入tokens数量
     * @return string 成本（美元，保留10位小数）
     */
    private static function calculateInputCost(?AiProviderModel $model, int $tokens): string
    {
        if (!$model || $tokens === 0) {
            return '0.0000000000';
        }

        $costPerToken = (float) $model->cost_per_input_token;
        $totalCost = $tokens * $costPerToken;

        return number_format($totalCost, 10, '.', '');
    }

    /**
     * 计算输出成本
     *
     * @param AiProviderModel|null $model 模型配置
     * @param int $tokens 输出tokens数量
     * @return string 成本（美元，保留10位小数）
     */
    private static function calculateOutputCost(?AiProviderModel $model, int $tokens): string
    {
        if (!$model || $tokens === 0) {
            return '0.0000000000';
        }

        $costPerToken = (float) $model->cost_per_output_token;
        $totalCost = $tokens * $costPerToken;

        return number_format($totalCost, 10, '.', '');
    }

    /**
     * 写数据库日志（失败不影响主流程）
     *
     * 注意：此处使用 try-catch 是必要场景
     * 原因：数据库日志失败不应影响主业务流程，必须静默处理
     *
     * @param array $data 日志数据
     * @return void
     */
    private static function logToDatabase(array $data): void
    {
        try {
            LlmApiLog::create($data);
        } catch (\Throwable $e) {
            Log::warning('[LLM-API] 数据库日志记录失败', [
                'request_id' => $data['request_id'] ?? '',
                'error' => $e->getMessage(),
            ]);

            // 记录系统日志：数据库日志记录失败
            SystemLogService::exception('feature_ai', $e, [
                'service' => 'LlmApiLogService::logToDatabase',
                'request_id' => $data['request_id'] ?? '',
                'provider_id' => $data['provider_id'] ?? null,
                'model_name' => $data['model_name'] ?? '',
            ]);
        }
    }
}