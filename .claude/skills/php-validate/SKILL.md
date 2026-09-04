---
name: php-validate
description: inhere/php-validate 验证库开发技能。用于创建 Validation 验证类和 Validator 验证器。触发场景：用户提及验证、Validation、Validator、php-validate、数据验证、表单验证、请求验证、字段验证、输入验证等关键词，或需要编写验证规则、自定义验证逻辑时。即使只说"写个验证"、"验证数据"、"添加验证规则"等模糊表述，只要涉及数据验证，都应使用此技能。
---

# php-validate 验证库开发技能

本技能帮助你在 Laravel 模块化项目中使用 inhere/php-validate 验证库编写数据验证逻辑。

## 两种验证方式

项目中有两种验证组件：

### 1. Validation（验证类）
用于**配置验证规则**，处理整体数据验证流程,例如:LoginValidation登录验证,包括username/password的验证

**继承关系**：
- 基础：`\Inhere\Validate\Validation`
- 项目扩展：`\DLaravel\Validation\ValidationCore`（推荐）

**位置**：模块的 `Validations/` 目录

**示例**：
```php
namespace Modules\Demo5\Validations;

use DLaravel\Validation\ValidationCore;

class PostsCreateValidation extends ValidationCore
{
    public function rules(): array
    {
        return [
            ['title', 'required', 'msg' => '标题不能为空'],
            ['title', 'string', 'min' => 5, 'max' => 100],
            ['content', 'required', 'msg' => '内容不能为空'],
            ['status', 'in', [1, 2, 3]],
        ];
    }

    public function translates(): array
    {
        return [
            'title' => '文章标题',
            'content' => '文章内容',
        ];
    }

    public function beforeValidate(): bool
    {
        // 数据预处理
        if (isset($this->data['title'])) {
            $this->data['title'] = trim($this->data['title']);
        }
        return true;
    }
}
```

### 2. Validator（验证器）
用于**实现单个自定义验证逻辑**，复用验证规则,例如: ChinaCardValidator,身份证号码验证,仅用于验证身份证号码.

**继承关系**：
- 基础：`\Inhere\Validate\Validator\AbstractValidator`
- 项目扩展：`\DLaravel\Validator\Validator`（推荐）

**位置**：模块的 `Validators/` 目录

**示例**：
```php
namespace Modules\User\Validators;

use DLaravel\Validator\Validator;
use Modules\User\Models\User;

class UsernameValidator extends Validator
{
    public function validate($value, array $data): bool
    {
        $user = User::query()->where('username', $value)->first();

        if ($user) {
            // 在验证实例中设置额外数据
            $this->validationSet('user', $user);
            return true;
        }

        // 添加自定义错误消息
        return $this->addError('用户名不存在');
    }
}
```

## ValidationCore 扩展功能

项目扩展的 `ValidationCore` 提供额外功能：

### 1. validated() 方法
验证失败自动抛出异常：
```php
$validation = PostsCreateValidation::make($data)->validated();
// 验证通过后获取安全数据
$safeData = $validation->getSafeData();
```

### 2. 枚举验证
自动转换枚举值：
```php
protected array $cats = [
    'status' => StatusEnum::class,
];

public function getSafe(string $key, mixed $default = null): mixed
{
    // 自动返回枚举实例
    $status = $this->getSafe('status'); // StatusEnum 实例
}
```

### 3. 默认值
定义字段默认值：
```php
public function default(): array
{
    return [
        'page' => 1,
        'pageSize' => 15,
    ];
}

// 使用默认值获取
$page = $validation->getSafeByDefault('page');
```

### 4. JSON 数据验证
直接从请求 JSON 验证：
```php
$validation = PostsCreateValidation::makeByJson()->validated();
```

## Validator 扩展功能

项目扩展的 `Validator` 提供额外功能：

### 1. validationSet()
在验证实例中设置数据，供后续使用：
```php
$this->validationSet('user', $user);
```

### 2. addError()
添加自定义错误消息并返回 false：
```php
return $this->addError('验证失败的字段描述');
```

### 3. addErrorTpl()
使用模板添加错误消息：
```php
return $this->validationSet('user', '用户 {name} 不存在', ['name' => $value]);
```

## 常用验证规则配置

### 规则格式
```php
['字段', '验证器', '参数', '选项']
```

### 基础验证
```php
['field', 'required'],                    // 必填
['field', 'string', 'min' => 5, 'max' => 100],  // 字符串长度
['field', 'int', 'min' => 1, 'max' => 100],     // 整数范围
['field', 'in', [1, 2, 3]],              // 枚举值
['field', 'email'],                       // 邮箱格式
['field', 'url'],                         // URL 格式
```

### 过滤器
验证前自动过滤数据：
```php
['field', 'string', 'filter' => 'trim'],          // 去除空格
['field', 'int', 'filter' => 'int'],              // 转整数
['field', 'string', 'filter' => 'lowercase'],     // 转小写
['field', 'string', 'filter' => 'trim|upper'],    // 多个过滤器
```

### 自定义验证器
使用自定义 Validator：
```php
use Modules\User\Validators\UsernameValidator;

['username', new UsernameValidator($this), 'msg' => '用户名验证失败'],
```

### 条件验证
```php
// 前置条件
['field', 'required', 'when' => function($data) {
    return $data['status'] > 2;
}],

// 场景验证
['field', 'required', 'on' => 'create'],
['field', 'int', 'on' => 'update'],
```

### 错误消息
```php
// 单条规则消息
['field', 'required', 'msg' => '{attr} 不能为空'],

// 多字段消息
['field1,field2', 'required', 'msg' => [
    'field1' => '字段1消息',
    'field2' => '字段2消息',
]],
```

## 使用流程

### 1. 创建 Validation 类
```php
// 在模块 Validations/ 目录创建
class XxxValidation extends ValidationCore
{
    public function rules(): array
    {
        return [
            // 规则配置
        ];
    }
}
```

### 2. 创建 Validator 类
```php
// 在模块 Validators/ 目录创建
class XxxValidator extends Validator
{
    public function validate($value, array $data): bool
    {
        // 验证逻辑
        return true; // 或 false
    }
}
```

### 3. 在 Handler/Controller 中使用
```php
use Modules\Demo5\Validations\PostsCreateValidation;

public function handle(array $data): array
{
    $validation = PostsCreateValidation::make($data)->validated();
    $safeData = $validation->getSafeData();

    // 使用验证后的安全数据
    $title = $safeData['title'];

    // ...
}
```

## 最佳实践

1. **Validation 放在模块 Validations/ 目录**
2. **Validator 放在模块 Validators/ 目录**
3. **继承项目扩展基类**：`ValidationCore` 和 `Validator`
4. **Validation 负责规则配置**，Validator 负责单个验证逻辑
5. **使用 translates() 定义字段翻译**
6. **复杂验证使用自定义 Validator**
7. **数据预处理在 beforeValidate() 完成**
8. **使用 validated() 自动抛异常简化流程**

## 参考资源

详细内置验证器和过滤器列表，请参考：
- [built-in-rules.md](references/built-in-rules.md)

项目中使用示例：
- Demo5 模块：`Modules/Demo5/Validations/`
- User 模块：`Modules/User/Validators/`

## 验证失败处理

使用 `validated()` 时验证失败会抛出 `ValidateException`：

```php
try {
    $validation = XxxValidation::make($data)->validated();
    $safeData = $validation->getSafeData();
} catch (ValidateException $e) {
    // 错误消息：$e->getMessage()
    // 处理验证失败
}
```

不抛异常方式：
```php
$validation = XxxValidation::make($data)->validate();

if ($validation->isFail()) {
    $error = $validation->firstError();
    // 处理错误
}

$safeData = $validation->getSafeData();
```
