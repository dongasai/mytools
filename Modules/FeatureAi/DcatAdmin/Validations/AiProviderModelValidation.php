<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Validations;

use DLaravel\Validation\ValidationCore;
use Modules\FeatureAi\Enums\AiModelType;

/**
 * AI提供商模型验证类.
 *
 * 完成参数验证：provider_id必填、model_name必填、model_type必须是枚举值、max_tokens必须是正整数、成本字段非负数、is_active必须是0或1
 */
class AiProviderModelValidation extends ValidationCore
{
    /**
     * 定义验证规则.
     *
     * @param  array  $rules  自定义规则数组
     * @return array  验证规则列表
     */
    public function rules(array $rules = []): array
    {
        return [
            // provider_id 必填验证
            ['provider_id', 'required', 'msg' => '{attr}不能为空'],

            // provider_id 整数验证
            ['provider_id', 'integer', 'msg' => '{attr}必须是整数'],

            // provider_id 正整数验证
            ['provider_id', 'min', 'value' => 1, 'msg' => '{attr}必须是正整数'],

            // model_name 必填验证
            ['model_name', 'required', 'msg' => '{attr}不能为空'],

            // model_name 字符串类型验证
            ['model_name', 'string', 'msg' => '{attr}必须是字符串'],

            // model_name 长度验证
            ['model_name', 'string', 'max' => 128, 'msg' => '{attr}长度不能超过128字符'],

            // model_type 必填验证
            ['model_type', 'required', 'msg' => '{attr}不能为空'],

            // model_type 枚举值验证
            ['model_type', 'in', 'range' => array_map(fn ($case) => $case->value, AiModelType::cases()), 'msg' => '{attr}必须是有效的模型类型'],

            // max_tokens 整数验证（可选）
            ['max_tokens', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // max_tokens 正整数验证（可选）
            ['max_tokens', 'min', 'value' => 1, 'msg' => '{attr}必须是正整数', 'skipOnEmpty' => true],

            // cost_per_input_token 数字验证（可选）
            ['cost_per_input_token', 'number', 'msg' => '{attr}必须是数字', 'skipOnEmpty' => true],

            // cost_per_input_token 非负数验证（可选）
            ['cost_per_input_token', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // cost_per_output_token 数字验证（可选）
            ['cost_per_output_token', 'number', 'msg' => '{attr}必须是数字', 'skipOnEmpty' => true],

            // cost_per_output_token 非负数验证（可选）
            ['cost_per_output_token', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // is_active 整数验证（可选）
            ['is_active', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // is_active 范围验证（可选，0或1）
            ['is_active', 'in', 'range' => [0, 1], 'msg' => '{attr}必须是0或1', 'skipOnEmpty' => true],
        ];
    }

    /**
     * 定义字段翻译.
     *
     * @return array  字段名称映射
     */
    public function translates(): array
    {
        return [
            'provider_id' => '提供商ID',
            'model_name' => '模型名称',
            'model_type' => '模型类型',
            'max_tokens' => '最大tokens',
            'cost_per_input_token' => '输入tokens单价',
            'cost_per_output_token' => '输出tokens单价',
            'is_active' => '是否启用',
        ];
    }
}