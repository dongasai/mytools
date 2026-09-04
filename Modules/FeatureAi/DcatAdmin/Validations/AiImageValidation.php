<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Validations;

use DLaravel\Validation\ValidationCore;
use Modules\FeatureAi\Enums\AiImageStatus;

/**
 * AI图片生成记录验证类
 *
 * 完成参数验证：provider_id必填、prompt_text必填、status枚举值、cost非负数等
 */
class AiImageValidation extends ValidationCore
{
    /**
     * 定义验证规则
     *
     * @param  array  $rules  验证规则数组
     * @return array
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

            // user_id 整数验证（可选）
            ['user_id', 'integer', 'msg' => '{attr}必须是整数', 'skipOnEmpty' => true],

            // user_id 正整数验证（可选）
            ['user_id', 'min', 'value' => 1, 'msg' => '{attr}必须是正整数', 'skipOnEmpty' => true],

            // prompt_text 必填验证
            ['prompt_text', 'required', 'msg' => '{attr}不能为空'],

            // prompt_text 字符串验证
            ['prompt_text', 'string', 'msg' => '{attr}必须是字符串'],

            // prompt_text 最小长度验证
            ['prompt_text', 'minLength', 'min' => 1, 'msg' => '{attr}长度不能少于1个字符'],

            // prompt_text 最大长度验证
            ['prompt_text', 'maxLength', 'max' => 2000, 'msg' => '{attr}长度不能超过2000个字符'],

            // image_url 字符串验证（可选）
            ['image_url', 'string', 'msg' => '{attr}必须是字符串', 'skipOnEmpty' => true],

            // image_url 最大长度验证（可选）
            ['image_url', 'maxLength', 'max' => 512, 'msg' => '{attr}长度不能超过512个字符', 'skipOnEmpty' => true],

            // image_size 字符串验证（可选）
            ['image_size', 'string', 'msg' => '{attr}必须是字符串', 'skipOnEmpty' => true],

            // image_size 最大长度验证（可选）
            ['image_size', 'maxLength', 'max' => 32, 'msg' => '{attr}长度不能超过32个字符', 'skipOnEmpty' => true],

            // cost 数字验证（可选）
            ['cost', 'number', 'msg' => '{attr}必须是数字', 'skipOnEmpty' => true],

            // cost 非负数验证（可选）
            ['cost', 'min', 'value' => 0, 'msg' => '{attr}不能为负数', 'skipOnEmpty' => true],

            // status 必填验证
            ['status', 'required', 'msg' => '{attr}不能为空'],

            // status 整数验证
            ['status', 'integer', 'msg' => '{attr}必须是整数'],

            // status 枚举值验证（1待处理/2处理中/3成功/4失败）
            ['status', 'in', 'range' => array_map(fn ($case) => $case->value, AiImageStatus::cases()), 'msg' => '{attr}必须是有效的图片状态'],
        ];
    }

    /**
     * 定义字段翻译
     *
     * @return array
     */
    public function translates(): array
    {
        return [
            'provider_id' => 'AI提供商ID',
            'model_id' => 'AI模型ID',
            'user_id' => '用户ID',
            'prompt_text' => '图片生成提示文本',
            'image_url' => '生成图片URL',
            'image_size' => '图片尺寸',
            'cost' => '生成成本',
            'status' => '图片生成状态',
        ];
    }
}