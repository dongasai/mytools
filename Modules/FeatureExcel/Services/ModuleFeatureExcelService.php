<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Services;

use Modules\FeatureExcel\Engines\Export\CsvExportEngine;
use Modules\FeatureExcel\Engines\Export\ExcelExportEngine;
use Modules\FeatureExcel\Engines\Import\CsvImportEngine;
use Modules\FeatureExcel\Engines\Import\ExcelImportEngine;
use Modules\FeatureExcel\Engines\Validation\ImportResult;
use Modules\FeatureExcel\Logics\HookApplyLogic;
use Modules\FeatureExcel\Templates\Base\AbstractExportTemplate;
use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;

/**
 * FeatureExcel 对外统一服务类（门面）
 *
 * 面向业务模块的统一入口，接收抽象模板基类实例：
 * 内部编排现有引擎（标准验证/转换）+ HookApplyLogic（业务钩子）。
 *
 * 采用模板对象模式，业务模块需继承 AbstractImportTemplate/AbstractExportTemplate
 * 定义字段映射，通过本服务完成导入导出。
 *
 * @see AbstractImportTemplate 导入模板基类
 * @see AbstractExportTemplate 导出模板基类
 */
class ModuleFeatureExcelService
{
    /**
     * 导入文件（含引擎标准验证 + 业务钩子验证/转换）
     *
     * 流程：引擎完整流程（路径验证→读取→DataValidator→DataTransformer）
     * → 成功后再逐行应用业务钩子（validateRow/transformRow）。
     * 引擎错误原样返回，钩子错误格式兼容引擎。
     *
     * @param  string  $filePath  本地文件绝对路径
     * @param  AbstractImportTemplate  $template  导入模板（抽象基类实例）
     * @return ImportResult 导入结果
     */
    public static function importWithValidation(
        string $filePath,
        AbstractImportTemplate $template
    ): ImportResult {
        $engineResult = self::getImportEngine($template)::importWithValidation($filePath, $template);

        if (! $engineResult->isSuccess()) {
            return $engineResult;
        }

        return HookApplyLogic::applyImportHooks($engineResult->getData(), $template);
    }

    /**
     * 导入文件（不验证，仅业务转换）
     *
     * 跳过引擎标准验证与业务验证，仅应用 transformRow 业务转换。
     * 适用于已确认数据合法的场景。
     *
     * @param  string  $filePath  本地文件绝对路径
     * @param  AbstractImportTemplate  $template  导入模板（抽象基类实例）
     * @return array 转换后的数据数组
     */
    public static function import(string $filePath, AbstractImportTemplate $template): array
    {
        $data = self::getImportEngine($template)::import($filePath, $template);

        return array_map(
            static fn (array $row): array => $template->transformRow($row),
            $data
        );
    }

    /**
     * 导出数据（业务格式化 + 引擎标准转换）
     *
     * 流程：逐行应用 formatRow 业务钩子 → 引擎 export
     * （引擎内部再做 DataTransformer 字段级格式化）。
     *
     * @param  array  $data  导出数据
     * @param  AbstractExportTemplate  $template  导出模板（抽象基类实例）
     * @param  array  $options  导出选项
     *                          - data_fingerprint: string 数据指纹
     *                          - tenant_id: int|null 租户ID
     *                          - user_id: int 用户ID
     *                          - re_id: int 关联业务ID
     *                          - variables: array 文件名变量
     * @return string 文件下载URL
     */
    public static function export(
        array $data,
        AbstractExportTemplate $template,
        array $options = []
    ): string {
        $formattedData = HookApplyLogic::applyExportHooks($data, $template);

        return self::getExportEngine($template)::export($formattedData, $template, $options);
    }

    /**
     * 生成标准化数据指纹
     *
     * 业务模块应使用此方法生成指纹，确保导出缓存一致性。
     * 注意：指纹算法与导出格式无关（Excel/CSV 共用），统一由 ExcelExportEngine 提供。
     *
     * @param  array  $parameters  查询参数
     * @param  array|null  $data  数据数组（可选）
     * @return string 数据指纹
     */
    public static function generateDataFingerprint(array $parameters, ?array $data = null): string
    {
        return ExcelExportEngine::generateDataFingerprint($parameters, $data);
    }

    /**
     * 根据模板格式获取对应的导入引擎类
     *
     * @param  AbstractImportTemplate  $template  导入模板
     * @return string 引擎类名
     */
    private static function getImportEngine(AbstractImportTemplate $template): string
    {
        return $template->getFormat() === 'csv'
            ? CsvImportEngine::class
            : ExcelImportEngine::class;
    }

    /**
     * 根据模板格式获取对应的导出引擎类
     *
     * @param  AbstractExportTemplate  $template  导出模板
     * @return string 引擎类名
     */
    private static function getExportEngine(AbstractExportTemplate $template): string
    {
        return $template->getFormat() === 'csv'
            ? CsvExportEngine::class
            : ExcelExportEngine::class;
    }
}
