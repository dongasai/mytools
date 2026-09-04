<?php

namespace Modules\FeatureAi\Enums;

/**
 * AI模型类型.
 */
enum AiModelType: string
{
    /**
     * 聊天模型.
     */
    case CHAT = 'chat';

    /**
     * 图片生成模型.
     */
    case IMAGE = 'image';

    /**
     * 嵌入模型.
     */
    case EMBEDDING = 'embedding';

    /**
     * 其他模型.
     */
    case OTHER = 'other';

    /**
     * 获取模型类型名称.
     */
    public function getName(): string
    {
        return match ($this) {
            self::CHAT => '聊天模型',
            self::IMAGE => '图片生成模型',
            self::EMBEDDING => '嵌入模型',
            self::OTHER => '其他模型',
        };
    }
}