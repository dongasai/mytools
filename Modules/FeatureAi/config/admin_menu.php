<?php

declare(strict_types=1);

/**
 * FeatureAi 模块后台菜单配置
 *
 * 菜单ID范围: 102000 系列
 *
 * @author AI Development Team
 * @since 2026-08-19
 */
return [
    // ==================== 顶级父菜单 ====================
    [
        'id' => 102001,
        'parent_id' => 0,
        'order' => 102001,
        'title' => 'AI服务',
        'icon' => 'feather icon-cpu',
        'uri' => '',
    ],

    // ==================== 子菜单 ====================
    // 供应商管理
    [
        'id' => 102002,
        'parent_id' => 102001,
        'order' => 102002,
        'title' => '供应商管理',
        'icon' => '',
        'uri' => 'module_featureai/ai-providers',
    ],
    // 服务映射
    [
        'id' => 102003,
        'parent_id' => 102001,
        'order' => 102003,
        'title' => '服务映射',
        'icon' => '',
        'uri' => 'module_featureai/ai-service-mappings',
    ],
    // 对话日志
    [
        'id' => 102004,
        'parent_id' => 102001,
        'order' => 102004,
        'title' => '对话日志',
        'icon' => '',
        'uri' => 'module_featureai/ai-conversations',
    ],
    // AI模型管理
    [
        'id' => 102005,
        'parent_id' => 102001,
        'order' => 102005,
        'title' => 'AI模型管理',
        'icon' => 'feather icon-cpu',
        'uri' => 'module_featureai/ai-provider-models',
    ],
    // 图片生成记录
    [
        'id' => 102006,
        'parent_id' => 102001,
        'order' => 102006,
        'title' => '图片生成记录',
        'icon' => 'feather icon-image',
        'uri' => 'module_featureai/ai-images',
    ],
    // 测试记录
    [
        'id' => 102007,
        'parent_id' => 102001,
        'order' => 102007,
        'title' => '测试记录',
        'icon' => 'feather icon-activity',
        'uri' => 'module_featureai/ai-tests',
    ],
    // LLM API日志
    [
        'id' => 102008,
        'parent_id' => 102001,
        'order' => 102008,
        'title' => 'LLM API日志',
        'icon' => 'feather icon-file-text',
        'uri' => 'module_featureai/llm-api-logs',
    ],
];
