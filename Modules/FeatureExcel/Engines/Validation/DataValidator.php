<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Validation;

use Inhere\Validate\Validation;
use Modules\FeatureExcel\Engines\Template\FieldMapping;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;

/**
 * 数据验证器
 *
 * 使用 inhere/php-validate 动态构建验证规则，按项目规范
 */
class DataValidator
{
    /**
     * 验证导入数据
     *
     * @param  array  $data  原始数据（二维数组）
     * @param  ImportTemplate  $template  导入模板
     * @return ValidationResult 验证结果
     */
    public static function validateImportData(array $data, ImportTemplate $template): ValidationResult
    {
        $errors = [];
        $validatedData = [];

        // 从模板配置构建验证规则
        $rules = self::buildRulesFromTemplate($template);

        foreach ($data as $rowIndex => $row) {
            // 使用 Validation::make() 动态验证
            $validator = Validation::make($row, $rules);
            $validator->validate();

            if ($validator->isOk()) {
                // 保留完整原始行数据：
                // getSafeData() 仅返回验证规则覆盖的字段，string 类型且无 required/validation
                // 规则的字段会被静默丢弃（如 gender/phone/email 等 nullable string 字段）
                $validatedData[] = $row;
            } else {
                // 映射到 Excel 实际行号
                $excelRow = $rowIndex + $template->getStartRow();
                $rowErrors = [];
                foreach ($validator->getErrors() as $error) {
                    $fieldName = self::getFieldName($error['name'], $template);
                    $rowErrors[$error['name']] = "第{$excelRow}行 {$fieldName}: ".$error['msg'];
                }
                $errors[$rowIndex] = $rowErrors;
            }
        }

        if (empty($errors)) {
            return ValidationResult::valid($validatedData);
        }

        return ValidationResult::invalid($errors);
    }

    /**
     * 从模板配置构建验证规则数组
     *
     * @param  ImportTemplate  $template  导入模板
     * @return array 验证规则数组
     */
    private static function buildRulesFromTemplate(ImportTemplate $template): array
    {
        $rules = [];

        foreach ($template->getFields() as $fieldName => $fieldMapping) {
            // 必填规则
            if ($fieldMapping->isRequired()) {
                $rules[] = [$fieldName, 'required', 'msg' => "{$fieldMapping->getName()}不能为空"];
            }

            // 类型规则
            $typeRules = self::getTypeRules($fieldName, $fieldMapping);
            $rules = array_merge($rules, $typeRules);

            // 自定义验证规则
            $customRules = self::parseCustomValidation($fieldName, $fieldMapping);
            $rules = array_merge($rules, $customRules);
        }

        return $rules;
    }

    /**
     * 根据字段类型生成基础验证规则
     *
     * @param  string  $fieldName  字段名
     * @param  FieldMapping  $mapping  字段映射
     * @return array 验证规则数组
     */
    private static function getTypeRules(string $fieldName, FieldMapping $mapping): array
    {
        return match ($mapping->getType()) {
            'integer' => [[$fieldName, 'integer', 'msg' => "{$mapping->getName()}必须是整数"]],
            'float', 'numeric' => [[$fieldName, 'float', 'msg' => "{$mapping->getName()}必须是数字"]],
            'date' => [[$fieldName, 'date', 'format' => 'Y-m-d', 'msg' => "{$mapping->getName()}日期格式无效"]],
            'datetime' => [[$fieldName, 'date', 'format' => 'Y-m-d H:i:s', 'msg' => "{$mapping->getName()}时间格式无效"]],
            default => [],
        };
    }

    /**
     * 解析模板中的自定义验证规则
     *
     * 支持格式：'min:1', 'in:a,b,c', 'max:100'
     *
     * @param  string  $fieldName  字段名
     * @param  FieldMapping  $mapping  字段映射
     * @return array 验证规则数组
     */
    private static function parseCustomValidation(string $fieldName, FieldMapping $mapping): array
    {
        $rules = [];
        $validation = $mapping->getValidation();

        if (empty($validation)) {
            return $rules;
        }

        $type = $mapping->getType();

        foreach ($validation as $rule) {
            if (str_contains($rule, ':')) {
                [$ruleName, $ruleValue] = explode(':', $rule, 2);
                $rules[] = match ($ruleName) {
                    'min' => self::buildMinRule($fieldName, $type, $ruleValue, $mapping->getName()),
                    'max' => self::buildMaxRule($fieldName, $type, $ruleValue, $mapping->getName()),
                    'in' => [$fieldName, 'in', 'range' => explode(',', $ruleValue), 'msg' => "{$mapping->getName()}值不在允许范围内"],
                    default => [],
                };
            } else {
                $rules[] = [$fieldName, $rule, 'msg' => "{$mapping->getName()}验证失败"];
            }
        }

        return array_filter($rules);
    }

    /**
     * 构建 min 验证规则
     *
     * 根据字段类型选择合适的验证器：float 类型用 float 规则的 min 参数，
     * integer 类型用 integer 规则的 min 参数
     *
     * @param  string  $fieldName  字段名
     * @param  string  $type  字段类型
     * @param  string  $ruleValue  规则值
     * @param  string  $label  字段中文名
     * @return array 验证规则
     */
    private static function buildMinRule(string $fieldName, string $type, string $ruleValue, string $label): array
    {
        if ($type === 'float' || $type === 'numeric') {
            return [$fieldName, 'float', 'min' => (float) $ruleValue, 'msg' => "{$label}最小值为{$ruleValue}"];
        }

        if ($type === 'integer') {
            return [$fieldName, 'integer', 'min' => (int) $ruleValue, 'msg' => "{$label}最小值为{$ruleValue}"];
        }

        // string 等文本类型：通用 min 验证器（字符串按长度、数字按数值比较）
        return [$fieldName, 'min', 'minRange' => (int) $ruleValue, 'msg' => "{$label}最小长度为{$ruleValue}"];
    }

    /**
     * 构建 max 验证规则
     *
     * @param  string  $fieldName  字段名
     * @param  string  $type  字段类型
     * @param  string  $ruleValue  规则值
     * @param  string  $label  字段中文名
     * @return array 验证规则
     */
    private static function buildMaxRule(string $fieldName, string $type, string $ruleValue, string $label): array
    {
        if ($type === 'float' || $type === 'numeric') {
            return [$fieldName, 'float', 'max' => (float) $ruleValue, 'msg' => "{$label}最大值为{$ruleValue}"];
        }

        if ($type === 'integer') {
            return [$fieldName, 'integer', 'max' => (int) $ruleValue, 'msg' => "{$label}最大值为{$ruleValue}"];
        }

        // string 等文本类型：通用 max 验证器（字符串按长度、数字按数值比较）
        return [$fieldName, 'max', 'maxRange' => (int) $ruleValue, 'msg' => "{$label}最大长度为{$ruleValue}"];
    }

    /**
     * 获取字段中文名
     *
     * @param  string  $field  字段标识
     * @param  ImportTemplate  $template  导入模板
     * @return string 字段中文名
     */
    private static function getFieldName(string $field, ImportTemplate $template): string
    {
        $fields = $template->getFields();

        return $fields[$field]?->getName() ?? $field;
    }
}
