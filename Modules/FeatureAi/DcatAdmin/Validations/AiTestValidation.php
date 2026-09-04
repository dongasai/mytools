<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Validations;

use DLaravel\Validation\ValidationCore;
use Modules\FeatureAi\Enums\AiTestStatus;

/**
 * AI集成测试验证类.
 *
 * 完成参数验证：provider_id必填、test_type必须是有效枚举值、test_name必填、status必须是枚举值、统计字段非负数等
 */
class AiTestValidation extends ValidationCore
{
    /**
     * 测试类型允许值.
     */
    public const TEST_TYPES = ['connect', 'response', 'cost', 'image'];

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

            // model_id 整数验证（可选）
            ['model_id', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // model_id 正整数验证（可选）
            ['model_id', 'min', 'value' => 1, 'msg' => '{attr}必须是正整数', 'skipOnEmpty' => true],

            // test_type 必填验证
            ['test_type', 'required', 'msg' => '{attr}不能为空'],

            // test_type 字符串验证
            ['test_type', 'string', 'msg' => '{attr}必须是字符串'],

            // test_type 枚举值验证（connect/response/cost/image）
            ['test_type', 'in', 'range' => self::TEST_TYPES, 'msg' => '{attr}必须是有效的测试类型'],

            // test_name 必填验证
            ['test_name', 'required', 'msg' => '{attr}不能为空'],

            // test_name 字符串类型验证
            ['test_name', 'string', 'msg' => '{attr}必须是字符串'],

            // test_name 长度验证
            ['test_name', 'string', 'max' => 128, 'msg' => '{attr}长度不能超过128字符'],

            // test_config_json 字符串验证（可选）
            ['test_config_json', 'string', 'msg' => '{attr}必须是字符串', 'skipOnEmpty' => true],

            // status 必填验证
            ['status', 'required', 'msg' => '{attr}不能为空'],

            // status 整数验证
            ['status', 'integer', 'msg' => '{attr}必须是整数'],

            // status 枚举值验证（1待执行/2执行中/3成功/4失败）
            ['status', 'in', 'range' => array_map(fn ($case) => $case->value, AiTestStatus::cases()), 'msg' => '{attr}必须是有效的测试状态'],

            // total_tests 整数验证（可选）
            ['total_tests', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // total_tests 非负数验证（可选）
            ['total_tests', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // success_count 整数验证（可选）
            ['success_count', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // success_count 非负数验证（可选）
            ['success_count', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // fail_count 整数验证（可选）
            ['fail_count', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // fail_count 非负数验证（可选）
            ['fail_count', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // avg_response_time_ms 整数验证（可选）
            ['avg_response_time_ms', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // avg_response_time_ms 非负数验证（可选）
            ['avg_response_time_ms', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],
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
            'provider_id' => 'AI提供商ID',
            'model_id' => 'AI模型ID',
            'test_type' => '测试类型',
            'test_name' => '测试名称',
            'test_config_json' => '测试配置JSON',
            'status' => '测试状态',
            'total_tests' => '总测试次数',
            'success_count' => '成功次数',
            'fail_count' => '失败次数',
            'avg_response_time_ms' => '平均响应时间(毫秒)',
        ];
    }
}