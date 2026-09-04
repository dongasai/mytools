<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Templates\Base;

use Modules\FeatureExcel\Engines\Template\FieldMapping;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;

/**
 * 抽象导入模板基类
 *
 * 业务模块通过继承本类定义导入模板（继承自引擎层 ImportTemplate，
 * 可直接传入导入引擎使用）：
 * 1. 实现 defineFields() 定义字段映射（使用 FieldMapping 流式接口）
 * 2. 可选重写 validateRow() 实现行级业务验证
 * 3. 可选重写 transformRow() 实现行级业务转换
 *
 * 子类属性覆盖示例（编译期常量）：
 * ```php
 * class EnergyDataImportTemplate extends AbstractImportTemplate
 * {
 *     protected string $name = '能源数据导入模板';
 *     protected int $startRow = 3;
 *
 *     protected function defineFields(): array
 *     {
 *         return [
 *             'meter_id' => FieldMapping::make('A', 'integer')->name('表计ID')->required(),
 *         ];
 *     }
 * }
 * ```
 *
 * 注意：
 * - 属性覆盖仅支持编译期常量；需要运行时计算的属性请在子类构造函数中设置
 * - 钩子由 Logics\HookApplyLogic 在引擎标准流程（验证+转换）成功后逐行调用
 */
abstract class AbstractImportTemplate extends ImportTemplate
{
    /**
     * 定义字段映射
     *
     * 返回字段映射数组，key 为业务字段名（如 meter_id），
     * value 为 FieldMapping 实例（推荐流式接口构建）
     *
     * @return array<string, FieldMapping> 字段映射
     */
    abstract protected function defineFields(): array;

    /**
     * 构造函数
     *
     * 调用 defineFields() 并将字段映射注入模板对象
     */
    public function __construct()
    {
        $this->setFields($this->defineFields());
    }

    /**
     * 行级业务验证钩子
     *
     * 在引擎标准验证通过后对每行数据调用，此时数据已完成字段级验证和类型转换。
     * 返回 null 表示通过；返回字符串作为失败原因（错误信息格式：
     * "第{行号}行 业务验证: {原因}"）。
     *
     * 公开方法：由 Logics\HookApplyLogic 跨类调用（模板扩展点 API）
     *
     * @param  array  $row  单行数据（已完成字段级验证和类型转换）
     * @param  int  $rowNumber  Excel 实际行号（数据索引 + startRow）
     * @return string|null 失败原因（null 表示通过）
     */
    public function validateRow(array $row, int $rowNumber): ?string
    {
        return null;
    }

    /**
     * 行级业务转换钩子
     *
     * 在 validateRow 通过后对每行数据调用，可进行业务语义转换
     * （如单位换算、字段补全、值映射）。
     *
     * 公开方法：由 Logics\HookApplyLogic 跨类调用（模板扩展点 API）
     *
     * @param  array  $row  单行数据
     * @return array 转换后的数据行
     */
    public function transformRow(array $row): array
    {
        return $row;
    }
}
