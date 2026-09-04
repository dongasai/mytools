<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Validations;

use DLaravel\Validation\ValidationCore;

/**
 * AI测试结果验证类.
 *
 * 完成参数验证：test_id必填、test_sequence整数、is_success枚举、响应时间和tokens非负数、cost非负数
 *
 * 说明：该模型在后台为只读模式（内嵌在AiTest详情页），因此验证规则相对简单，仅验证关键字段
 */
class AiTestResultValidation extends ValidationCore
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
            // test_id 必填验证
            ['test_id', 'required', 'msg' => '{attr}不能为空'],

            // test_id 整数验证
            ['test_id', 'integer', 'msg' => '{attr}必须是整数'],

            // test_id 正整数验证
            ['test_id', 'min', 'value' => 1, 'msg' => '{attr}必须是正整数'],

            // test_sequence 整数验证（可选）
            ['test_sequence', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // test_sequence 正整数验证（可选）
            ['test_sequence', 'min', 'value' => 1, 'msg' => '{attr}必须是正整数', 'skipOnEmpty' => true],

            // is_success 整数验证（可选）
            ['is_success', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // is_success 枚举值验证（可选，1成功/2失败）
            ['is_success', 'in', 'range' => [1, 2], 'msg' => '{attr}必须是1或2', 'skipOnEmpty' => true],

            // response_time_ms 整数验证（可选）
            ['response_time_ms', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // response_time_ms 非负数验证（可选）
            ['response_time_ms', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // input_tokens 整数验证（可选）
            ['input_tokens', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // input_tokens 非负数验证（可选）
            ['input_tokens', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // output_tokens 整数验证（可选）
            ['output_tokens', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // output_tokens 非负数验证（可选）
            ['output_tokens', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // cost 数字验证（可选）
            ['cost', 'number', 'msg' => '{attr}必须是数字', 'skipOnEmpty' => true],

            // cost 非负数验证（可选）
            ['cost', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // response_text 字符串验证（可选）
            ['response_text', 'string', 'msg' => '{attr}必须是字符串', 'skipOnEmpty' => true],

            // error_message 字符串验证（可选）
            ['error_message', 'string', 'msg' => '{attr}必须是字符串', 'skipOnEmpty' => true],
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
            'test_id' => '测试记录ID',
            'test_sequence' => '测试序号',
            'is_success' => '是否成功',
            'response_time_ms' => '响应时间(毫秒)',
            'input_tokens' => '输入tokens',
            'output_tokens' => '输出tokens',
            'cost' => '成本',
            'response_text' => '响应文本',
            'error_message' => '错误信息',
        ];
    }
}
