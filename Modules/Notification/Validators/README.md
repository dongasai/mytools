# 通知模块验证器类文档

## 验证器类说明
本目录包含通知模块的所有验证器类，用于验证通知相关的数据。

## 验证器类列表

### 1. NotificationValidator
通知验证器，用于验证通知数据的有效性。

```php
class NotificationValidator extends BaseValidator
{
    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [
        'template_id' => 'required|integer|exists:notification_templates,id',
        'type' => 'required|string|in:' . implode(',', NotificationType::getList()),
        'channel' => 'required|string|in:' . implode(',', NotificationChannel::getList()),
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'data' => 'nullable|array',
        'priority' => 'required|integer|in:' . implode(',', NotificationPriority::getList()),
        'status' => 'required|string|in:' . implode(',', NotificationStatus::getList())
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $messages = [
        'template_id.required' => '模板ID不能为空',
        'template_id.exists' => '模板不存在',
        'type.required' => '通知类型不能为空',
        'type.in' => '无效的通知类型',
        'channel.required' => '通知渠道不能为空',
        'channel.in' => '无效的通知渠道',
        'title.required' => '通知标题不能为空',
        'title.max' => '通知标题不能超过255个字符',
        'content.required' => '通知内容不能为空',
        'priority.required' => '优先级不能为空',
        'priority.in' => '无效的优先级',
        'status.required' => '状态不能为空',
        'status.in' => '无效的状态'
    ];

    /**
     * 验证通知数据
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data)
    {
        // 1. 验证基本数据
        if (!$this->validateBasic($data)) {
            return false;
        }

        // 2. 验证模板变量
        if (!$this->validateTemplateVariables($data)) {
            return false;
        }

        // 3. 验证渠道特定规则
        if (!$this->validateChannelRules($data)) {
            return false;
        }

        return true;
    }

    /**
     * 验证基本数据
     *
     * @param array $data
     * @return bool
     */
    protected function validateBasic(array $data)
    {
        return $this->validate($data, $this->rules, $this->messages);
    }

    /**
     * 验证模板变量
     *
     * @param array $data
     * @return bool
     */
    protected function validateTemplateVariables(array $data)
    {
        // 获取模板信息
        $template = NotificationTemplate::find($data['template_id']);
        if (!$template) {
            $this->errors->add('template_id', '模板不存在');
            return false;
        }

        // 获取模板变量
        $variables = json_decode($template->variables, true);
        if (!$variables) {
            return true;
        }

        // 验证变量是否存在
        foreach ($variables as $variable => $type) {
            if (!isset($data['data'][$variable])) {
                $this->errors->add('data', "缺少模板变量：{$variable}");
                return false;
            }

            // 验证变量类型
            if (!$this->validateVariableType($data['data'][$variable], $type)) {
                $this->errors->add('data', "变量 {$variable} 类型错误");
                return false;
            }
        }

        return true;
    }

    /**
     * 验证变量类型
     *
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    protected function validateVariableType($value, $type)
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_numeric($value) && is_int($value + 0);
            case 'float':
                return is_numeric($value);
            case 'array':
                return is_array($value);
            case 'boolean':
                return is_bool($value);
            default:
                return true;
        }
    }

    /**
     * 验证渠道特定规则
     *
     * @param array $data
     * @return bool
     */
    protected function validateChannelRules(array $data)
    {
        switch ($data['channel']) {
            case NotificationChannel::MAIL:
                return $this->validateMailRules($data);
            case NotificationChannel::SMS:
                return $this->validateSmsRules($data);
            case NotificationChannel::PUSH:
                return $this->validatePushRules($data);
            default:
                return true;
        }
    }

    /**
     * 验证邮件规则
     *
     * @param array $data
     * @return bool
     */
    protected function validateMailRules(array $data)
    {
        if (!isset($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            $this->errors->add('to', '无效的邮箱地址');
            return false;
        }
        return true;
    }

    /**
     * 验证短信规则
     *
     * @param array $data
     * @return bool
     */
    protected function validateSmsRules(array $data)
    {
        if (!isset($data['phone']) || !preg_match('/^1[3-9]\d{9}$/', $data['phone'])) {
            $this->errors->add('phone', '无效的手机号码');
            return false;
        }
        return true;
    }

    /**
     * 验证推送规则
     *
     * @param array $data
     * @return bool
     */
    protected function validatePushRules(array $data)
    {
        if (!isset($data['device_token']) || empty($data['device_token'])) {
            $this->errors->add('device_token', '设备令牌不能为空');
            return false;
        }
        return true;
    }
}
```

### 2. TemplateValidator
模板验证器，用于验证通知模板数据的有效性。

```php
class TemplateValidator extends BaseValidator
{
    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [
        'name' => 'required|string|max:100',
        'type' => 'required|string|in:' . implode(',', NotificationType::getList()),
        'channel' => 'required|string|in:' . implode(',', NotificationChannel::getList()),
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'variables' => 'nullable|json',
        'status' => 'required|integer|in:0,1'
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $messages = [
        'name.required' => '模板名称不能为空',
        'name.max' => '模板名称不能超过100个字符',
        'type.required' => '通知类型不能为空',
        'type.in' => '无效的通知类型',
        'channel.required' => '通知渠道不能为空',
        'channel.in' => '无效的通知渠道',
        'title.required' => '通知标题不能为空',
        'title.max' => '通知标题不能超过255个字符',
        'content.required' => '通知内容不能为空',
        'variables.json' => '变量定义格式错误',
        'status.required' => '状态不能为空',
        'status.in' => '无效的状态'
    ];

    /**
     * 验证模板数据
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data)
    {
        // 1. 验证基本数据
        if (!$this->validateBasic($data)) {
            return false;
        }

        // 2. 验证变量定义
        if (!$this->validateVariables($data)) {
            return false;
        }

        return true;
    }

    /**
     * 验证基本数据
     *
     * @param array $data
     * @return bool
     */
    protected function validateBasic(array $data)
    {
        return $this->validate($data, $this->rules, $this->messages);
    }

    /**
     * 验证变量定义
     *
     * @param array $data
     * @return bool
     */
    protected function validateVariables(array $data)
    {
        if (!isset($data['variables'])) {
            return true;
        }

        $variables = json_decode($data['variables'], true);
        if (!$variables) {
            $this->errors->add('variables', '变量定义格式错误');
            return false;
        }

        foreach ($variables as $variable => $type) {
            if (!in_array($type, ['string', 'integer', 'float', 'array', 'boolean'])) {
                $this->errors->add('variables', "变量 {$variable} 类型错误");
                return false;
            }
        }

        return true;
    }
}
```

## 使用示例

### 验证通知数据
```php
$validator = new NotificationValidator();
$data = [
    'template_id' => 1,
    'type' => NotificationType::SYSTEM,
    'channel' => NotificationChannel::MAIL,
    'title' => '测试通知',
    'content' => '这是一条测试通知',
    'data' => [
        'name' => '张三',
        'amount' => 100
    ],
    'priority' => NotificationPriority::NORMAL,
    'status' => NotificationStatus::PENDING,
    'to' => 'example@example.com'
];

if ($validator->validate($data)) {
    // 验证通过，处理数据
} else {
    // 验证失败，获取错误信息
    $errors = $validator->getErrors();
}
```

### 验证模板数据
```php
$validator = new TemplateValidator();
$data = [
    'name' => '测试模板',
    'type' => NotificationType::SYSTEM,
    'channel' => NotificationChannel::MAIL,
    'title' => '测试通知',
    'content' => '这是一条测试通知',
    'variables' => json_encode([
        'name' => 'string',
        'amount' => 'integer'
    ]),
    'status' => 1
];

if ($validator->validate($data)) {
    // 验证通过，处理数据
} else {
    // 验证失败，获取错误信息
    $errors = $validator->getErrors();
}
```

## 注意事项
1. 所有验证器类都继承自BaseValidator基类
2. 验证规则使用Laravel的验证规则语法
3. 支持自定义验证消息
4. 支持复杂的验证逻辑
5. 支持变量类型验证
6. 支持渠道特定规则验证
7. 支持JSON格式验证
8. 支持错误信息收集
9. 支持验证结果缓存
10. 支持验证规则扩展 
