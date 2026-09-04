<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Tests\Unit;

use Modules\FeatureExcel\Engines\Template\FieldMapping;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;
use Modules\FeatureExcel\Engines\Validation\DataValidator;
use PHPUnit\Framework\TestCase;

/**
 * DataValidator 回归测试
 *
 * 覆盖：验证通过后完整行保留（修复 getSafeData 丢弃无规则字段的 bug）
 */
class DataValidatorTest extends TestCase
{
    /**
     * 验证通过的行必须保留全部字段（含无验证规则的 string 字段）
     *
     * 回归场景：string 类型且无 required/validation 规则的字段（如 nullable 的
     * gender/phone/email），修复前被 getSafeData() 静默丢弃。
     */
    public function test_validation_keeps_fields_without_rules(): void
    {
        $template = new ImportTemplate;
        $template->setName('测试模板');
        $template->setStartRow(3);
        $template->setFields([
            // required 字段：生成 required 规则
            'name' => FieldMapping::make('A', 'string')->name('姓名')->required(),
            // string 类型无规则字段：不生成任何验证规则（bug 场景）
            'remark' => FieldMapping::make('B', 'string')->name('备注')->nullable(),
            // date 类型字段：生成 date 类型规则
            'birthday' => FieldMapping::make('C', 'date')->name('生日')->nullable(),
        ]);

        $data = [
            [
                'name' => '张三',
                'remark' => '测试备注',
                'birthday' => '1990-01-01',
            ],
        ];

        $result = DataValidator::validateImportData($data, $template);

        $this->assertTrue($result->isValid());
        $validated = $result->getData();

        // 无规则字段必须保留（修复前会被 getSafeData 丢弃）
        $this->assertSame('张三', $validated[0]['name']);
        $this->assertSame('测试备注', $validated[0]['remark']);
        $this->assertSame('1990-01-01', $validated[0]['birthday']);
    }

    /**
     * 验证失败的行必须返回含 Excel 行号的错误（行号 = 数组下标 + startRow）
     */
    public function test_validation_error_contains_excel_row_number(): void
    {
        $template = new ImportTemplate;
        $template->setName('测试模板');
        $template->setStartRow(3);
        $template->setFields([
            'name' => FieldMapping::make('A', 'string')->name('姓名')->required(),
        ]);

        $data = [
            ['name' => '张三'],
            [],  // 缺必填字段 → 验证失败，Excel 行号应为 3 + 1 = 4
        ];

        $result = DataValidator::validateImportData($data, $template);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();

        // 错误结构：$errors[行下标][字段名] = "第N行 字段中文名: 错误消息"
        $this->assertArrayHasKey(1, $errors);
        $this->assertStringContainsString('第4行', $errors[1]['name']);
        $this->assertStringContainsString('姓名', $errors[1]['name']);
    }

    /**
     * string 字段的 min/max 规则必须按字符串长度校验
     *
     * 回归场景：修复前 buildMinRule/buildMaxRule 将 string 类型的 min/max
     * 错误地生成为 integer 验证规则，非数字文本（如"测试产品A"）被误判失败。
     */
    public function test_string_min_max_uses_length_validation(): void
    {
        $template = new ImportTemplate;
        $template->setName('测试模板');
        $template->setStartRow(3);
        $template->setFields([
            'name' => FieldMapping::make('A', 'string')
                ->name('产品名称')
                ->required()
                ->validation(['min:1', 'max:100']),
        ]);

        // 合法中文值必须通过
        $validData = [['name' => '测试产品A']];
        $result = DataValidator::validateImportData($validData, $template);

        $this->assertTrue($result->isValid());
        $this->assertSame('测试产品A', $result->getData()[0]['name']);

        // 超长值必须失败，错误消息使用长度措辞
        $longName = str_repeat('产', 101);
        $invalidData = [['name' => $longName]];
        $result = DataValidator::validateImportData($invalidData, $template);

        $this->assertFalse($result->isValid());
        $errors = $result->getErrors();
        $this->assertStringContainsString('第3行', $errors[0]['name']);
        $this->assertStringContainsString('产品名称最大长度为100', $errors[0]['name']);
    }
}
