# FeatureAi 模块 UserAgent 配置指南

## 快速开始

FeatureAi 模块现已支持配置级别的 UserAgent 设置，无需关注底层实现。

### 1. 通过配置文件配置

编辑 `config/ai.php`（项目根目录）：

```php
<?php

return [
    'user_agent' => 'YourApp-Name/1.0',

    // 可选：其他全局请求头
    'headers' => [
        'X-Custom-Header' => 'value',
    ],
];
```

## 使用示例

### Chat 服务

```php
use Modules\FeatureAi\Services\AiChatService;

// 使用服务映射
$response = AiChatService::generateTextByService(
    prompt: '你好',
    serviceType: 'chat',
    serviceName: 'default'
);

// 直接使用
$response = AiChatService::generateText(
    prompt: '你好',
    providerType: 'openai',
    model: 'gpt-4'
);
```

### Image 服务

```php
use Modules\FeatureAi\Services\AiImageService;

// 生成单张图片
$result = AiImageService::generateImageByService(
    prompt: '一只可爱的猫',
    serviceType: 'image',
    serviceName: 'default'
);

// 批量生成图片
$results = AiImageService::generateImagesByService(
    prompt: '一只可爱的猫',
    serviceType: 'image',
    count: 3
);
```

## 实现原理

### AiProviderService::createHttpClient()

自动创建带全局配置的 HttpClient：

```php
// 自动注入 UserAgent 和其他全局请求头
$httpClient = AiProviderService::createHttpClient();
```

### 自动注入到 Provider

所有通过 FeatureAi 模块创建的 Provider 都会自动携带 UserAgent：

- AiChatService::createProvider()
- AiImageService::createImageProvider()

## 默认值

如果未配置，默认 UserAgent 为：`CodeNnn-AI/1.0`

## 验证

可以通过查看 API 日志确认 UserAgent 是否正确设置：

```bash
tail -f storage/logs/laravel-*.log | grep "User-Agent"
```

## 注意事项

- 所有 AI API 请求（OpenAI、Anthropic、Gemini、Deepseek 等）都会自动携带此 UserAgent
- 配置文件位置：`config/ai.php`（项目根目录，与模块配置分离）
- 修改配置后无需重启服务，立即生效

## 更新时间

2026-09-02