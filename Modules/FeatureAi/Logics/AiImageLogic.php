<?php

namespace Modules\FeatureAi\Logics;

/**
 * AI图片处理逻辑.
 *
 * 提供图片处理相关的静态方法.
 */
class AiImageLogic
{
    /**
     * 支持的图片尺寸列表.
     */
    public const SUPPORTED_SIZES = [
        '256x256',
        '512x512',
        '1024x1024',
        '1792x1024',
        '1024x1792',
    ];

    /**
     * 验证图片尺寸.
     *
     * @param string $size 图片尺寸
     *
     * @return bool 是否有效
     */
    public static function validateSize(string $size): bool
    {
        return in_array($size, self::SUPPORTED_SIZES);
    }

    /**
     * 验证提示文本.
     *
     * @param string $prompt 提示文本
     * @param int $maxLength 最大长度
     *
     * @return bool 是否有效
     */
    public static function validatePrompt(string $prompt, int $maxLength = 4000): bool
    {
        $length = mb_strlen($prompt);

        return $length > 0 && $length <= $maxLength;
    }

    /**
     * 生成图片文件名.
     *
     * @param string|null $outputPath 输出路径
     * @param string $prefix 文件名前缀
     * @param string $extension 文件扩展名
     *
     * @return string 文件名
     */
    public static function generateFilename(?string $outputPath = null, string $prefix = 'ai_img_', string $extension = 'png'): string
    {
        if ($outputPath) {
            return $outputPath;
        }

        $basePath = config('ai_providers.image_storage.path', 'ai/images');

        return $basePath . '/' . uniqid($prefix, true) . '.' . $extension;
    }

    /**
     * 获取默认图片尺寸.
     *
     * @param string $model 模型名称
     *
     * @return string 默认尺寸
     */
    public static function getDefaultSize(string $model = 'dall-e-3'): string
    {
        // DALL-E 3默认1024x1024
        // DALL-E 2默认1024x1024
        return '1024x1024';
    }

    /**
     * 解析尺寸为宽高.
     *
     * @param string $size 尺寸字符串(如'1024x1024')
     *
     * @return array ['width', 'height']
     */
    public static function parseSize(string $size): array
    {
        $parts = explode('x', $size);

        if (count($parts) !== 2) {
            return ['width' => 1024, 'height' => 1024];
        }

        return [
            'width' => (int) $parts[0],
            'height' => (int) $parts[1],
        ];
    }

    /**
     * 计算图片成本.
     *
     * @param string $model 模型名称
     * @param string $size 图片尺寸
     *
     * @return string 成本(美元)
     */
    public static function calculateImageCost(string $model, string $size): string
    {
        // DALL-E 3成本表
        // - 1024x1024: $0.04
        // - 1792x1024 或 1024x1792: $0.08
        if ($model === 'dall-e-3') {
            if ($size === '1024x1024') {
                return '0.04';
            }
            if ($size === '1792x1024' || $size === '1024x1792') {
                return '0.08';
            }
        }

        // DALL-E 2成本表
        // - 1024x1024: $0.02
        // - 512x512: $0.018
        // - 256x256: $0.016
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

        // 默认成本
        return '0.04';
    }

    /**
     * 检查模型是否支持图片生成.
     *
     * @param string $model 模型名称
     *
     * @return bool 是否支持
     */
    public static function isImageModel(string $model): bool
    {
        $imageModels = ['dall-e-3', 'dall-e-2', 'gpt-image-1', 'image-01', 'image-01-live'];

        return in_array($model, $imageModels);
    }

    /**
     * 格式化提示文本(去除多余空格和换行).
     *
     * @param string $prompt 提示文本
     *
     * @return string 格式化后的提示文本
     */
    public static function formatPrompt(string $prompt): string
    {
        // 去除多余空格
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        // 去除首尾空格
        $prompt = trim($prompt);

        return $prompt;
    }
}