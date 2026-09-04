# 通知模块验证规则类文档

## 验证规则类说明
本目录包含通知模块的所有验证规则类，用于定义通知相关的验证规则。

## 验证规则类列表

### 1. NotificationValidation
通知验证规则类，用于定义通知相关的验证规则。

```php
class NotificationValidation extends BaseValidation
{
    /**
     * 创建验证规则
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'template_id' => 'required|integer|exists:notification_templates,id',
            'type' => 'required|string|in:' . implode(',', NotificationType::getList()),
            'channel' => 'required|string|in:' . implode(',', NotificationChannel::getList()),
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'data' => 'nullable|array',
            'priority' => 'required|integer|in:' . implode(',', NotificationPriority::getList()),
            'status' => 'required|string|in:' . implode(',', NotificationStatus::getList())
        ];
    }

    /**
     * 更新验证规则
     *
     * @return array
     */
    public function updateRules()
    {
        return [
            'template_id' => 'sometimes|required|integer|exists:notification_templates,id',
            'type' => 'sometimes|required|string|in:' . implode(',', NotificationType::getList()),
            'channel' => 'sometimes|required|string|in:' . implode(',', NotificationChannel::getList()),
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'data' => 'nullable|array',
            'priority' => 'sometimes|required|integer|in:' . implode(',', NotificationPriority::getList()),
            'status' => 'sometimes|required|string|in:' . implode(',', NotificationStatus::getList())
        ];
    }

    /**
     * 获取验证消息
     *
     * @return array
     */
    public function messages()
    {
        return [
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
    }

    /**
     * 获取自定义验证规则
     *
     * @return array
     */
    public function customRules()
    {
        return [
            'validate_template_variables' => function ($attribute, $value, $parameters) {
                return $this->validateTemplateVariables($value);
            },
            'validate_channel_rules' => function ($attribute, $value, $parameters) {
                return $this->validateChannelRules($value);
            }
        ];
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
                return false;
            }

            // 验证变量类型
            if (!$this->validateVariableType($data['data'][$variable], $type)) {
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
        return isset($data['to']) && filter_var($data['to'], FILTER_VALIDATE_EMAIL);
    }

    /**
     * 验证短信规则
     *
     * @param array $data
     * @return bool
     */
    protected function validateSmsRules(array $data)
    {
        return isset($data['phone']) && preg_match('/^1[3-9]\d{9}$/', $data['phone']);
    }

    /**
     * 验证推送规则
     *
     * @param array $data
     * @return bool
     */
    protected function validatePushRules(array $data)
    {
        return isset($data['device_token']) && !empty($data['device_token']);
    }
}
```

### 2. TemplateValidation
模板验证规则类，用于定义通知模板相关的验证规则。

```php
class TemplateValidation extends BaseValidation
{
    /**
     * 创建验证规则
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:' . implode(',', NotificationType::getList()),
            'channel' => 'required|string|in:' . implode(',', NotificationChannel::getList()),
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|json',
            'status' => 'required|integer|in:0,1'
        ];
    }

    /**
     * 更新验证规则
     *
     * @return array
     */
    public function updateRules()
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|string|in:' . implode(',', NotificationType::getList()),
            'channel' => 'sometimes|required|string|in:' . implode(',', NotificationChannel::getList()),
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'variables' => 'nullable|json',
            'status' => 'sometimes|required|integer|in:0,1'
        ];
    }

    /**
     * 获取验证消息
     *
     * @return array
     */
    public function messages()
    {
        return [
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
    }

    /**
     * 获取自定义验证规则
     *
     * @return array
     */
    public function customRules()
    {
        return [
            'validate_variables' => function ($attribute, $value, $parameters) {
                return $this->validateVariables($value);
            }
        ];
    }

    /**
     * 验证变量定义
     *
     * @param string $value
     * @return bool
     */
    protected function validateVariables($value)
    {
        if (empty($value)) {
            return true;
        }

        $variables = json_decode($value, true);
        if (!$variables) {
            return false;
        }

        foreach ($variables as $variable => $type) {
            if (!in_array($type, ['string', 'integer', 'float', 'array', 'boolean'])) {
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
$validation = new NotificationValidation();

// 创建验证
$rules = $validation->createRules();
$messages = $validation->messages();
$customRules = $validation->customRules();

$validator = Validator::make($data, $rules, $messages, $customRules);

if ($validator->fails()) {
    $errors = $validator->errors();
}

// 更新验证
$rules = $validation->updateRules();
$messages = $validation->messages();
$customRules = $validation->customRules();

$validator = Validator::make($data, $rules, $messages, $customRules);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### 验证模板数据
```php
$validation = new TemplateValidation();

// 创建验证
$rules = $validation->createRules();
$messages = $validation->messages();
$customRules = $validation->customRules();

$validator = Validator::make($data, $rules, $messages, $customRules);

if ($validator->fails()) {
    $errors = $validator->errors();
}

// 更新验证
$rules = $validation->updateRules();
$messages = $validation->messages();
$customRules = $validation->customRules();

$validator = Validator::make($data, $rules, $messages, $customRules);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

## 注意事项
1. 所有验证规则类都继承自BaseValidation基类
2. 支持创建和更新两种验证规则
3. 支持自定义验证消息
4. 支持自定义验证规则
5. 支持变量类型验证
6. 支持渠道特定规则验证
7. 支持JSON格式验证
8. 支持验证规则扩展
9. 支持验证规则复用
10. 支持验证规则组合 
