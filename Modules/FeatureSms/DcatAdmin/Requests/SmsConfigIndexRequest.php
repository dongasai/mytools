<?php

namespace Modules\FeatureSms\DcatAdmin\Requests;

use Dcat\Admin\Http\Requests\Request;

/**
 * 短信配置索引请求验证
 */
class SmsConfigIndexRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:100',
            'driver' => 'nullable|string|in:aliyun,tencent,huawei',
            'is_open' => 'nullable|boolean',
            'created_at_start' => 'nullable|date',
            'created_at_end' => 'nullable|date|after_or_equal:created_at_start',
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.string' => '配置名称必须是字符串',
            'name.max' => '配置名称不能超过100个字符',
            'driver.in' => '驱动必须是：阿里云、腾讯云、华为云',
            'is_open.boolean' => '开启状态必须是布尔值',
            'created_at_start.date' => '开始时间必须是有效日期',
            'created_at_end.date' => '结束时间必须是有效日期',
            'created_at_end.after_or_equal' => '结束时间必须晚于或等于开始时间',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => '配置名称',
            'driver' => '驱动',
            'is_open' => '开启状态',
            'created_at_start' => '开始时间',
            'created_at_end' => '结束时间',
        ];
    }
}
