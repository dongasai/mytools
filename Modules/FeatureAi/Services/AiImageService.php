<?php

namespace Modules\FeatureAi\Services;

use Modules\Application\Services\SystemLogService;
use Modules\FeatureAi\Enums\AiImageStatus;
use Modules\FeatureAi\Enums\AiProviderType;
use Modules\FeatureAi\Models\AiImage;
use Modules\FeatureAi\AiProviders\Image\MiniMaxImageProvider;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\OpenAI\Image\OpenAIImage;
use NeuronAI\Providers\AIProviderInterface;
use Illuminate\Support\Facades\Storage;

/**
 * AI图片生成服务.
 *
 * 使用neuron-ai包生成图片.
 */
class AiImageService
{
    /**
     * 根据服务映射批量生成图片.
     *
     * 图片保存到临时目录，调用方需通过 ModuleFileService::uploadImageForPath() 上传到 AFile 模块统一管理.
     * 临时文件24小时后自动清理.
     *
     * 注意：批量生成仅支持 MiniMax 提供商，默认尺寸 1024x1792 为竖图（适合 MiniMax）。
     * 如需其他尺寸，请通过 $parameters['size'] 显式指定。
     *
     * @param string $prompt 图片生成提示文本
     * @param string $serviceType 服务类型
     * @param string|null $serviceName 服务名字（可选，null表示默认）
     * @param int $count 生成数量(1-9张)
     * @param array $parameters 额外参数（model, size, outputPathPrefix等）
     *                           - size: 图片尺寸，默认 '1024x1792'（MiniMax 竖图）
     *
     * @return array 生成结果数组[['path', 'relative_path', 'model_id', 'cost'], ...]
     *
     * @throws \InvalidArgumentException 服务映射不存在或参数无效时抛出
     * @throws \RuntimeException 图片生成失败时抛出
     */
    public static function generateImagesByService(
        string $prompt,
        string $serviceType,
        ?string $serviceName = null,
        int $count = 1,
        array $parameters = []
    ): array {
        // 验证数量
        if ($count < 1 || $count > 9) {
            throw new \InvalidArgumentException('图片数量必须在1-9之间');
        }

        // 获取供应商配置
        $providerConfig = AiProviderService::getProviderByService($serviceType, $serviceName);

        // 验证提供商类型是否支持图片生成
        $providerType = AiProviderType::from($providerConfig['provider_type']);
        if (!in_array($providerType, [AiProviderType::OPENAI, AiProviderType::MINIMAX])) {
            throw new \InvalidArgumentException(
                "提供商类型不支持图片生成: {$providerType->value}，仅支持 OpenAI 和 MiniMax"
            );
        }

        // 批量生成仅支持 MiniMax
        if ($providerType !== AiProviderType::MINIMAX) {
            throw new \InvalidArgumentException(
                '批量生成仅支持MiniMax提供商（OpenAI不支持批量生成）'
            );
        }

        // 提取模型名称：优先使用 parameters['model']，否则从配置中查找默认 image 模型
        $model = $parameters['model'] ?? null;
        if (!$model && !empty($providerConfig['models'])) {
            foreach ($providerConfig['models'] as $modelItem) {
                if (($modelItem['model_type'] ?? '') === 'image' && ($modelItem['is_active'] ?? 1) === 1) {
                    $model = $modelItem['model_name'];
                    break;
                }
            }
        }

        if (!$model) {
            throw new \InvalidArgumentException(
                "未指定模型且供应商无可用image模型: service_type={$serviceType}, service_name={$serviceName}"
            );
        }

        // 提取其他参数
        $size = $parameters['size'] ?? '1024x1792';
        $outputPathPrefix = $parameters['outputPathPrefix'] ?? null;

        // 获取 API 密钥
        $apiKey = $providerConfig['api_key'];

        // 创建图片生成提供商实例
        $imageProvider = self::createImageProvider($providerType, $apiKey, $model, $size, $count);

        // 调用AI批量生成图片
        $response = $imageProvider->chat(new UserMessage($prompt));

        // 获取批量图片数据
        $imageBase64Array = $response->getMetadata('images') ?? [];

        // 如果metadata中没有批量数据，尝试获取单张图片
        if (empty($imageBase64Array)) {
            $imageContentBlock = $response->getImage();
            if ($imageContentBlock) {
                $imageBase64Array = [$imageContentBlock->content];
                \Illuminate\Support\Facades\Log::info('MiniMax返回单张图片（未使用批量模式）', [
                    'requested_count' => $count,
                    'actual_count' => 1,
                ]);
            }
        }

        $actualCount = count($imageBase64Array);

        // 详细日志：记录实际返回情况
        \Illuminate\Support\Facades\Log::debug('MiniMax批量图片生成详情', [
            'requested_count' => $count,
            'actual_count' => $actualCount,
            'images_array_length' => count($imageBase64Array),
            'has_metadata' => $response->getMetadata('images') !== null,
        ]);

        if ($actualCount !== $count) {
            $errorMsg = "MiniMax返回的图片数量({$actualCount})与请求数量({$count})不符";
            \Illuminate\Support\Facades\Log::error($errorMsg, [
                'requested' => $count,
                'actual' => $actualCount,
                'prompt_length' => strlen($prompt),
                'size' => $size,
            ]);

            // 记录系统日志：API响应异常
            SystemLogService::apiError('feature_ai', 'AI图片生成API响应异常', [
                'requested_count' => $count,
                'actual_count' => $actualCount,
                'prompt_length' => strlen($prompt),
                'size' => $size,
                'provider_type' => $type->value ?? null,
                'model' => $model ?? null,
            ]);

            throw new \RuntimeException($errorMsg);
        }

        // 保存所有图片到临时目录
        $results = [];
        $cost = self::calculateCost($providerType, $model, $size);

        $now = now();
        $dateDir = $now->format('Ym/d');
        $tempBasePath = 'ai/temp/feature_ai/' . $dateDir;
        $disk = 'local';

        // 确保临时目录存在
        if (!Storage::disk($disk)->exists($tempBasePath)) {
            Storage::disk($disk)->makeDirectory($tempBasePath);
        }

        foreach ($imageBase64Array as $index => $imageBase64) {
            $imageData = base64_decode($imageBase64);

            // 生成临时文件名
            $timestamp = $now->format('His');
            $randomSuffix = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $tempFilename = $tempBasePath . '/' . $timestamp . '_' . $randomSuffix . '_' . $index . '.png';

            // 如果提供了输出路径前缀，使用自定义临时路径
            if ($outputPathPrefix) {
                $tempFilename = 'ai/temp/' . $outputPathPrefix . "_{$index}.png";
            }

            // 保存到临时目录
            Storage::disk($disk)->put($tempFilename, $imageData);

            // 获取绝对路径
            $absolutePath = Storage::disk($disk)->path($tempFilename);

            $results[] = [
                'path' => $absolutePath,
                'relative_path' => $tempFilename,
                'model_id' => null,
                'cost' => $cost,
            ];
        }

        return $results;
    }

    /**
     * 根据服务映射生成单张图片.
     *
     * 注意：单张生成支持 OpenAI 和 MiniMax 提供商，默认尺寸 1024x1024 为正方形（适合 OpenAI DALL-E）。
     * 如需其他尺寸，请通过 $parameters['size'] 显式指定。
     *
     * @param string $prompt 图片生成提示文本
     * @param string $serviceType 服务类型
     * @param string|null $serviceName 服务名字（可选，null表示默认）
     * @param array $parameters 额外参数（model, size, saveToDatabase, outputPath, userId等）
     *                           - size: 图片尺寸，默认 '1024x1024'（OpenAI 正方形）
     *
     * @return array 生成结果['url', 'path', 'model_id', 'cost', 'image_id']
     *
     * @throws \InvalidArgumentException 服务映射不存在或参数无效时抛出
     * @throws \RuntimeException 图片生成失败时抛出
     */
    public static function generateImageByService(
        string $prompt,
        string $serviceType,
        ?string $serviceName = null,
        array $parameters = []
    ): array {
        // 获取供应商配置
        $providerConfig = AiProviderService::getProviderByService($serviceType, $serviceName);

        // 验证提供商类型是否支持图片生成
        $providerType = AiProviderType::from($providerConfig['provider_type']);
        if (!in_array($providerType, [AiProviderType::OPENAI, AiProviderType::MINIMAX])) {
            throw new \InvalidArgumentException(
                "提供商类型不支持图片生成: {$providerType->value}，仅支持 OpenAI 和 MiniMax"
            );
        }

        // 提取模型名称：优先使用 parameters['model']，否则从配置中查找默认 image 模型
        $model = $parameters['model'] ?? null;
        if (!$model && !empty($providerConfig['models'])) {
            foreach ($providerConfig['models'] as $modelItem) {
                if (($modelItem['model_type'] ?? '') === 'image' && ($modelItem['is_active'] ?? 1) === 1) {
                    $model = $modelItem['model_name'];
                    break;
                }
            }
        }

        if (!$model) {
            throw new \InvalidArgumentException(
                "未指定模型且供应商无可用image模型: service_type={$serviceType}, service_name={$serviceName}"
            );
        }

        // 提取其他参数
        $size = $parameters['size'] ?? '1024x1024';
        $saveToDatabase = $parameters['saveToDatabase'] ?? false;
        $outputPath = $parameters['outputPath'] ?? null;
        $userId = $parameters['userId'] ?? null;

        // 获取 API 密钥
        $apiKey = $providerConfig['api_key'];

        // 获取提供商模型配置（如果有数据库记录）
        $providerModelId = null;
        $providerId = null;
        if ($saveToDatabase) {
            $providerModel = self::getOrCreateProviderModel($providerType, $model);
            if ($providerModel) {
                $providerModelId = $providerModel->id;
                $providerId = $providerModel->provider_id;
            }
        }

        // 创建图片生成客户端
        $imageProvider = self::createImageProvider($providerType, $apiKey, $model, $size);

        // 创建图片记录（如果需要保存）
        $aiImage = null;
        if ($saveToDatabase) {
            $aiImage = AiImage::create([
                'provider_id' => $providerId,
                'model_id' => $providerModelId,
                'user_id' => $userId,
                'prompt_text' => $prompt,
                'status' => AiImageStatus::PROCESSING->value,
                'retry_count' => 0,
                'image_size' => $size,
                'model_params' => ['model' => $model],
            ]);
        }

        // 调用AI生成图片
        $response = $imageProvider->chat(new UserMessage($prompt));

        // 获取图片内容
        $imageContentBlock = $response->getImage();
        if (!$imageContentBlock) {
            throw new \RuntimeException('AI返回的消息中未找到图片内容');
        }

        // 获取base64图片数据
        $imageBase64 = $imageContentBlock->content;
        $imageData = base64_decode($imageBase64);

        // 保存图片到文件
        $filename = self::saveImageFile($imageData, $outputPath);

        // 更新图片记录状态
        if ($aiImage) {
            $aiImage->update([
                'image_url' => $filename,
                'status' => AiImageStatus::SUCCESS->value,
                'cost' => self::calculateCost($providerType, $model, $size),
            ]);
        }

        return [
            'url' => Storage::url($filename),
            'path' => $filename,
            'model_id' => $providerModelId,
            'cost' => self::calculateCost($providerType, $model, $size),
            'image_id' => $aiImage?->id,
        ];
    }

    /**
     * 批量生成图片.
     *
     * 图片保存到临时目录，调用方需通过 ModuleFileService::uploadImageForPath() 上传到 AFile 模块统一管理.
     * 临时文件24小时后自动清理.
     *
     * @param string $prompt 图片生成提示文本
     * @param int $count 生成数量(1-9张)
     * @param AiProviderType|string|null $providerType 提供商类型(默认MiniMax)
     * @param string|null $model 模型名称(默认image-01)
     * @param string $size 图片尺寸(默认1024x1792)
     * @param string|null $outputPathPrefix 输出路径前缀(可选)
     *
     * @return array 生成结果数组[['path', 'relative_path', 'model_id', 'cost'], ...]
     *               - path: 绝对路径（可直接读取文件）
     *               - relative_path: Storage相对路径
     *               注意：临时文件24小时后自动清理
     *
     * @throws \Exception 图片生成失败时抛出
     */
    public static function generateImages(
        string $prompt,
        int $count = 1,
        AiProviderType|string|null $providerType = null,
        ?string $model = null,
        string $size = '1024x1792',
        ?string $outputPathPrefix = null
    ): array {
        // 验证数量
        if ($count < 1 || $count > 9) {
            throw new \InvalidArgumentException('图片数量必须在1-9之间');
        }

        // 获取默认提供商
        $providerType = $providerType ?? AiProviderService::getDefaultProviderType();
        $type = is_string($providerType) ? AiProviderType::from($providerType) : $providerType;

        // MiniMax支持批量生成
        if ($type !== AiProviderType::MINIMAX) {
            throw new \InvalidArgumentException('批量生成仅支持MiniMax提供商（OpenAI不支持批量生成）');
        }

        // 获取提供商配置
        $apiKey = AiProviderService::getApiKey($type);
        $model = $model ?? AiProviderService::getDefaultModel($type, 'image');

        // 创建图片生成客户端（支持批量）
        $imageProvider = self::createImageProvider($type, $apiKey, $model, $size, $count);

        // 调用AI批量生成图片
        $response = $imageProvider->chat(new UserMessage($prompt));

        // 获取批量图片数据
        // 尝试从metadata获取批量图片数组
        $imageBase64Array = $response->getMetadata('images') ?? [];

        // 如果metadata中没有批量数据，尝试获取单张图片
        if (empty($imageBase64Array)) {
            // 可能MiniMax只返回了单张图片（未设置metadata）
            $imageContentBlock = $response->getImage();
            if ($imageContentBlock) {
                // 将单张图片放入数组，统一处理
                $imageBase64Array = [$imageContentBlock->content];
                \Illuminate\Support\Facades\Log::info('MiniMax返回单张图片（未使用批量模式）', [
                    'requested_count' => $count,
                    'actual_count' => 1,
                ]);
            }
        }

        $actualCount = count($imageBase64Array);

        // 详细日志：记录实际返回情况
        \Illuminate\Support\Facades\Log::debug('MiniMax批量图片生成详情', [
            'requested_count' => $count,
            'actual_count' => $actualCount,
            'images_array_length' => count($imageBase64Array),
            'has_metadata' => $response->getMetadata('images') !== null,
        ]);

        if ($actualCount !== $count) {
            // 提供更详细的错误信息
            $errorMsg = "MiniMax返回的图片数量({$actualCount})与请求数量({$count})不符";
            \Illuminate\Support\Facades\Log::error($errorMsg, [
                'requested' => $count,
                'actual' => $actualCount,
                'prompt_length' => strlen($prompt),
                'size' => $size,
            ]);

            // 记录系统日志：API响应异常
            SystemLogService::apiError('feature_ai', 'AI图片生成API响应异常', [
                'requested_count' => $count,
                'actual_count' => $actualCount,
                'prompt_length' => strlen($prompt),
                'size' => $size,
                'provider_type' => $providerType->value ?? null,
                'model' => $model ?? null,
            ]);

            throw new \RuntimeException($errorMsg);
        }

        // 保存所有图片到临时目录（供调用方通过AFile管理）
        $results = [];
        $cost = self::calculateCost($type, $model, $size);

        // 临时目录：ai/temp/feature_ai/{date}
        $now = now();
        $dateDir = $now->format('Ym/d'); // 202605/28
        $tempBasePath = 'ai/temp/feature_ai/' . $dateDir;

        $disk = 'local';

        // 确保临时目录存在
        if (!Storage::disk($disk)->exists($tempBasePath)) {
            Storage::disk($disk)->makeDirectory($tempBasePath);
        }
        // \dd($imageBase64Array);
        foreach ($imageBase64Array as $index => $imageBase64) {
            $imageData = base64_decode($imageBase64);

            // 生成临时文件名（时间戳+随机+序号）
            $timestamp = $now->format('His');
            $randomSuffix = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $tempFilename = $tempBasePath . '/' . $timestamp . '_' . $randomSuffix . '_' . $index . '.png';

            // 如果提供了输出路径前缀，使用自定义临时路径
            if ($outputPathPrefix) {
                $tempFilename = 'ai/temp/' . $outputPathPrefix . "_{$index}.png";
            }

            // 保存到临时目录
            Storage::disk($disk)->put($tempFilename, $imageData);

            // 获取绝对路径（供调用方直接读取文件）
            $absolutePath = Storage::disk($disk)->path($tempFilename);

            $results[] = [
                'path' => $absolutePath,               // 绝对路径（可直接读取）
                'relative_path' => $tempFilename,      // 相对路径（供Storage使用）
                'model_id' => null,                    // 已废弃，由调用方自行管理
                'cost' => $cost,
            ];
        }

        return $results;
    }

    /**
     * 生成图片.
     *
     * @param string $prompt 图片生成提示文本
     * @param AiProviderType|string|null $providerType 提供商类型(默认OpenAI)
     * @param string|null $model 模型名称(默认dall-e-3)
     * @param string $size 图片尺寸(默认1024x1024)
     * @param bool $saveToDatabase 是否保存到数据库
     * @param string|null $outputPath 输出路径(可选)
     * @param int|null $userId 用户ID(可选)
     *
     * @return array 生成结果['url', 'path', 'model_id', 'cost', 'image_id']
     *
     * @throws \Exception 图片生成失败时抛出
     */
    public static function generateImage(
        string $prompt,
        AiProviderType|string|null $providerType = null,
        ?string $model = null,
        string $size = '1024x1024',
        bool $saveToDatabase = false,
        ?string $outputPath = null,
        ?int $userId = null
    ): array {
        // 获取默认提供商
        $providerType = $providerType ?? AiProviderService::getDefaultProviderType();
        $type = is_string($providerType) ? AiProviderType::from($providerType) : $providerType;

        // 获取提供商配置
        $apiKey = AiProviderService::getApiKey($type);
        $model = $model ?? AiProviderService::getDefaultModel($type, 'image');

        // 获取提供商模型配置（如果有数据库记录）
        $providerModelId = null;
        $providerId = null;
        if ($saveToDatabase) {
            $providerModel = self::getOrCreateProviderModel($type, $model);
            if ($providerModel) {
                $providerModelId = $providerModel->id;
                $providerId = $providerModel->provider_id;
            }
        }

        // 创建图片生成客户端（根据提供商类型）
        $imageProvider = self::createImageProvider($type, $apiKey, $model, $size);

        // 创建图片记录（如果需要保存）
        $aiImage = null;
        if ($saveToDatabase) {
            $aiImage = AiImage::create([
                'provider_id' => $providerId,
                'model_id' => $providerModelId,
                'user_id' => $userId,
                'prompt_text' => $prompt,
                'status' => AiImageStatus::PROCESSING->value,
                'retry_count' => 0,
                'image_size' => $size,
                'model_params' => ['model' => $model],
            ]);
        }

        // 调用AI生成图片
        $response = $imageProvider->chat(new UserMessage($prompt));

            // 获取图片内容
            $imageContentBlock = $response->getImage();
            if (!$imageContentBlock) {
                throw new \RuntimeException('AI返回的消息中未找到图片内容');
            }

            // 获取base64图片数据
            $imageBase64 = $imageContentBlock->content;
            $imageData = base64_decode($imageBase64);

            // 保存图片到文件
            $filename = self::saveImageFile($imageData, $outputPath);

            // 更新图片记录状态
            if ($aiImage) {
                $aiImage->update([
                    'image_url' => $filename,
                    'status' => AiImageStatus::SUCCESS->value,
                    'cost' => self::calculateCost($type, $model, $size),
                ]);
            }

            return [
                'url' => Storage::url($filename),
                'path' => $filename,
                'model_id' => $providerModelId,
                'cost' => self::calculateCost($type, $model, $size),
                'image_id' => $aiImage?->id,
            ];
    }

    /**
     * 保存图片文件.
     *
     * @param string $imageData 图片二进制数据
     * @param string|null $outputPath 输出路径（可选，包含文件名）
     *
     * @return string 存储路径
     */
    protected static function saveImageFile(string $imageData, ?string $outputPath = null): string
    {
        $storageConfig = config('ai_providers.image_storage');
        $disk = $storageConfig['disk'] ?? 'local';

        // 生成文件名
        if ($outputPath) {
            // 如果提供了完整路径，直接使用
            $filename = $outputPath;
        } else {
            // 自动生成文件名：年月/日/时分秒+随机4位.png
            $now = now();
            $yearMonth = $now->format('Ym'); // 202605
            $day = $now->format('d'); // 05
            $timeRandom = $now->format('His') . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT); // 16001234

            $basePath = $storageConfig['path'] ?? 'ai/images';
            $filename = $basePath . '/' . $yearMonth . '/' . $day . '/' . $timeRandom . '.png';

            // 确保目录存在
            $directory = $basePath . '/' . $yearMonth . '/' . $day;
            if (!Storage::disk($disk)->exists($directory)) {
                Storage::disk($disk)->makeDirectory($directory);
            }
        }

        // 保存文件
        Storage::disk($disk)->put($filename, $imageData);

        return $filename;
    }

    /**
     * 创建图片生成提供商实例.
     *
     * @param AiProviderType $type 提供商类型
     * @param string $apiKey API密钥
     * @param string $model 模型名称
     * @param string $size 图片尺寸
     * @param int $count 生成数量(默认1)
     *
     * @return AIProviderInterface 图片生成提供商
     *
     * @throws \InvalidArgumentException 不支持的提供商类型
     */
    protected static function createImageProvider(AiProviderType $type, string $apiKey, string $model, string $size, int $count = 1): AIProviderInterface
    {
        // 创建带全局配置的 HttpClient
        $httpClient = AiProviderService::createHttpClient();

        if ($type === AiProviderType::OPENAI) {
            return new OpenAIImage(
                key: $apiKey,
                model: $model,
                output_format: 'png',
                parameters: [
                    'size' => $size,
                    'n' => 1, // OpenAI仅支持n=1
                ],
                httpClient: $httpClient,
            );
        }

        if ($type === AiProviderType::MINIMAX) {
            // MiniMax使用aspect_ratio而不是size
            $aspectRatio = MiniMaxImageProvider::convertSizeToAspectRatio($size);

            return new MiniMaxImageProvider(
                key: $apiKey,
                model: $model,
                aspectRatio: $aspectRatio,
                parameters: [
                    'n' => $count, // MiniMax支持n=1-9
                    'prompt_optimizer' => config('ai_providers.minimax.config.prompt_optimizer', false),
                ],
                httpClient: $httpClient,
            );
        }

        throw new \InvalidArgumentException("不支持的图片生成提供商: {$type->value}");
    }

    /**
     * 计算成本.
     *
     * @param AiProviderType $type 提供商类型
     * @param string $model 模型名称
     * @param string $size 图片尺寸
     *
     * @return string 成本(美元)
     */
    protected static function calculateCost(AiProviderType $type, string $model, string $size): string
    {
        // MiniMax成本计算
        if ($type === AiProviderType::MINIMAX) {
            // Image-01: $0.0035/张（0.35美分）
            return '0.0035';
        }

        // OpenAI成本计算
        if ($type === AiProviderType::OPENAI) {
            // DALL-E 3成本表
            if ($model === 'dall-e-3') {
                if ($size === '1024x1024') {
                    return '0.04';
                }
                if ($size === '1792x1024' || $size === '1024x1792') {
                    return '0.08';
                }
            }

            // DALL-E 2成本表
            if ($model === 'dall-e-2') {
                if ($size === '1024x1024') {
                    return '0.02';
                }
                if ($size === '512x512') {
                    return '0.018';
                }
                if ($size === '256x256') {
                    return '0.016';
                }
            }
        }

        // 默认成本（未知模型）
        return '0.04';
    }

    /**
     * 获取或创建提供商模型记录.
     *
     * @param AiProviderType $providerType 提供商类型
     * @param string $model 模型名称
     *
     * @return \Modules\FeatureAi\Models\AiProviderModel|null 提供商模型记录
     */
    protected static function getOrCreateProviderModel(AiProviderType $providerType, string $model): ?\Modules\FeatureAi\Models\AiProviderModel
    {
        // 查找提供商
        $provider = \Modules\FeatureAi\Models\AiProvider::where('provider_type', $providerType->value)
            ->where('is_active', 1)
            ->first();

        if (!$provider) {
            return null;
        }

        // 查找模型配置
        $providerModel = \Modules\FeatureAi\Models\AiProviderModel::where('provider_id', $provider->id)
            ->where('model_name', $model)
            ->where('is_active', 1)
            ->first();

        return $providerModel;
    }
}