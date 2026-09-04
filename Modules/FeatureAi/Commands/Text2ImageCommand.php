<?php

namespace Modules\FeatureAi\Commands;

use Illuminate\Console\Command;
use Modules\FeatureAi\Enums\AiProviderType;
use Modules\FeatureAi\Logics\AiImageLogic;
use Modules\FeatureAi\Services\AiImageService;
use Modules\FeatureAi\Services\AiProviderService;

/**
 * AI文生图命令.
 *
 * 使用neuron-ai包生成图片.
 */
class Text2ImageCommand extends Command
{
    /**
     * 命令签名.
     */
    protected $signature = 'ai:text2image
                            {prompt : 图片生成提示文本}
                            {--provider=openai : AI提供商(openai/claude/gemini)}
                            {--model= : 模型名称(默认dall-e-3)}
                            {--size=1024x1024 : 图片尺寸}
                            {--save : 是否保存到数据库}
                            {--output= : 输出路径}';

    /**
     * 命令描述.
     */
    protected $description = '使用AI生成图片（文生图）';

    /**
     * 执行命令.
     */
    public function handle()
    {
        $prompt = $this->argument('prompt');
        $provider = $this->option('provider');
        $model = $this->option('model');
        $size = $this->option('size');
        $save = $this->option('save');
        $output = $this->option('output');

        // 验证提示文本
        $prompt = AiImageLogic::formatPrompt($prompt);
        if (!AiImageLogic::validatePrompt($prompt)) {
            $this->error('提示文本无效：长度必须在1-4000字符之间');
            return 1;
        }

        // 验证尺寸
        if (!AiImageLogic::validateSize($size)) {
            $this->error('图片尺寸无效：支持的尺寸为 ' . implode(', ', AiImageLogic::SUPPORTED_SIZES));
            return 1;
        }

        // 验证提供商
        try {
            $providerType = AiProviderType::from($provider);
        } catch (\ValueError $e) {
            $this->error('AI提供商无效：支持的提供商为 openai, claude, gemini');
            return 1;
        }

        // 获取默认模型
        if (!$model) {
            $providerService = new AiProviderService();
            $model = $providerService->getDefaultModel($providerType, 'image');
        }

        // 检查模型是否支持图片生成
        if (!AiImageLogic::isImageModel($model)) {
            $this->error("模型 {$model} 不支持图片生成");
            return 1;
        }

        $this->info('正在生成图片...');
        $this->info('提示文本: ' . $prompt);
        $this->info('提供商: ' . $providerType->getName());
        $this->info('模型: ' . $model);
        $this->info('尺寸: ' . $size);
        $this->info('保存到数据库: ' . ($save ? '是' : '否'));
        if ($output) {
            $this->info('输出路径: ' . $output);
        }

        try {
            // 生成图片（静态方法调用）
            $result = AiImageService::generateImage(
                prompt: $prompt,
                providerType: $providerType,
                model: $model,
                size: $size,
                saveToDatabase: $save,
                outputPath: $output
            );

            $this->newLine();
            $this->info('✅ 图片生成成功！');
            $this->info('图片URL: ' . $result['url']);
            $this->info('存储路径: ' . $result['path']);
            if ($result['image_id']) {
                $this->info('数据库记录ID: ' . $result['image_id']);
            }
            $this->info('成本: $' . $result['cost']);

            return 0;
        } catch (\InvalidArgumentException $e) {
            $this->error('配置错误: ' . $e->getMessage());
            $this->hint('请检查环境变量配置，确保设置了正确的API密钥（如 OPENAI_API_KEY）');
            return 1;
        } catch (\Exception $e) {
            $this->error('图片生成失败: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 提示用户配置信息.
     */
    protected function hint(string $message): void
    {
        $this->newLine();
        $this->comment($message);
        $this->comment('配置文件位置: Modules/FeatureAi/config/ai_providers.php');
        $this->comment('环境变量示例: OPENAI_API_KEY=your-api-key');
    }
}
