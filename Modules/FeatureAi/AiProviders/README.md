# FeatureAi 模块目录说明

## 目录结构

```
FeatureAi/
├── AiProviders/              # AI Provider 封装类（neuron-ai）
│   ├── Chat/                 # Chat Provider 封装
│   │   └── MiniMaxChatProvider.php
│   └── Image/                # Image Provider 封装
│       └── MiniMaxImageProvider.php
├── Providers/                # Laravel ServiceProviders
│   ├── FeatureAiServiceProvider.php
│   ├── DcatAdminRouteServiceProvider.php
│   └── EventServiceProvider.php
├── Services/                 # 业务服务层
├── Models/                   # 数据模型
├── Enums/                    # 枚举定义
└── config/                   # 配置文件
```

## 职责划分

### AiProviders（AI Provider 封装类）

**用途**：封装 neuron-ai 包的 Provider 类，提供定制化配置

**特点**：
- 继承或实现 neuron-ai 的 Provider 接口
- 提供特定提供商的默认配置（如 endpoint、timeout）
- 命名空间：`Modules\FeatureAi\AiProviders\{Type}`

**示例**：
- `MiniMaxChatProvider` - MiniMax Chat Provider，使用 Anthropic 兼容 API
- `MiniMaxImageProvider` - MiniMax Image Provider，实现图片生成

### Providers（Laravel ServiceProviders）

**用途**：Laravel 模块服务提供者

**特点**：
- 继承 `Illuminate\Support\ServiceProvider`
- 负责模块注册、路由加载、事件监听等
- 命名空间：`Modules\FeatureAi\Providers`

**包含文件**：
- `FeatureAiServiceProvider` - 模块主 ServiceProvider
- `DcatAdminRouteServiceProvider` - 后台路由 ServiceProvider
- `EventServiceProvider` - 事件 ServiceProvider

## 使用示例

### AiProviders 使用

```php
use Modules\FeatureAi\AiProviders\Chat\MiniMaxChatProvider;

$provider = new MiniMaxChatProvider(
    key: $apiKey,
    model: 'MiniMax-M3',
    parameters: ['max_tokens' => 4096]
);

$response = $provider->chat(new UserMessage('你好'));
```

### Providers 使用

Providers 由 Laravel 自动加载，无需手动实例化。

---

**创建时间**：2026-08-20
**维护者**：AI 开发团队