<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Templates\Base;

use Modules\FeatureExcel\Engines\Template\ExportTemplate;
use Modules\FeatureExcel\Engines\Template\FieldMapping;

/**
 * 抽象导出模板基类
 *
 * 业务模块通过继承本类定义导出模板（继承自引擎层 ExportTemplate，
 * 可直接传入导出引擎使用）：
 * 1. 实现 defineFields() 定义字段映射（使用 FieldMapping 流式接口）
 * 2. 可选重写 formatRow() 实现行级业务格式化
 *
 * 子类属性覆盖示例（编译期常量）：
 * ```php
 * class EnergyDataExportTemplate extends AbstractExportTemplate
 * {
 *     protected string $name = '能源数据导出模板';
 *     protected array $headers = ['表计ID', '日期', '能耗值'];
 *     protected string $fileNamePattern = 'energy_{date}';
 *
 *     protected function defineFields(): array
 *     {
 *         return [
 *             'meter_id' => FieldMapping::make('A', 'integer')->name('表计ID'),
 *         ];
 *     }
 * }
 * ```
 *
 * 注意：
 * - 属性覆盖仅支持编译期常量；需要运行时计算的属性请在子类构造函数中设置
 * - 钩子由 Logics\HookApplyLogic 在引擎标准转换前逐行调用
 */
abstract class AbstractExportTemplate extends ExportTemplate
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
     * 行级业务格式化钩子
     *
     * 在引擎标准转换（ValueTransformer 字段级格式化）前对每行数据调用，
     * 可进行业务行级加工（如字段拼接、状态映射、默认值补全）。
     *
     * 公开方法：由 Logics\HookApplyLogic 跨类调用（模板扩展点 API）
     *
     * @param  array  $row  单行数据
     * @return array 格式化后的数据行
     */
    public function formatRow(array $row): array
    {
        return $row;
    }
}
