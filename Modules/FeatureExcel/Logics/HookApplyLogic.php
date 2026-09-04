<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Logics;

use Modules\FeatureExcel\Engines\Validation\ImportResult;
use Modules\FeatureExcel\Templates\Base\AbstractExportTemplate;
use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;

/**
 * 钩子应用逻辑
 *
 * 将业务模板的钩子方法（validateRow/transformRow/formatRow）逐行应用到数据，
 * 在引擎标准流程（验证+转换）之后（导出为之前）执行，属于模板的扩展点调用层。
 */
class HookApplyLogic
{
    /**
     * 应用导入钩子（业务验证 + 业务转换）
     *
     * 对每行数据依次应用 validateRow（失败收集错误）与 transformRow（转换数据）。
     * 错误结构：$errors[$rowIndex] 为行级错误列表（与引擎 DataValidator 同构），
     * 引擎字段级错误以字段名为 key，业务钩子错误以数字索引追加（消费方按值遍历即可）。
     * 空数据返回 ImportResult::success([])。
     *
     * @param  array  $rows  引擎标准流程处理后的数据行数组（0-based 连续索引）
     * @param  AbstractImportTemplate  $template  导入模板
     * @return ImportResult 导入结果（有钩子错误返回 failed，否则 success）
     */
    public static function applyImportHooks(
        array $rows,
        AbstractImportTemplate $template
    ): ImportResult {
        $successData = [];
        $errors = [];
        $startRow = $template->getStartRow();

        foreach ($rows as $index => $row) {
            $rowNumber = $startRow + $index;

            // 1. 应用业务验证钩子
            $errorMsg = $template->validateRow($row, $rowNumber);
            if ($errorMsg !== null && $errorMsg !== '') {
                $errors[$index][] = "第{$rowNumber}行 业务验证: {$errorMsg}";

                continue;
            }

            // 2. 应用业务转换钩子
            $successData[] = $template->transformRow($row);
        }

        return empty($errors)
            ? ImportResult::success($successData)
            : ImportResult::failed($errors);
    }

    /**
     * 应用导出钩子（业务格式化）
     *
     * 对每行数据应用 formatRow 钩子，在引擎标准转换前执行。
     *
     * @param  array  $rows  数据行数组
     * @param  AbstractExportTemplate  $template  导出模板
     * @return array 格式化后的数据行数组
     */
    public static function applyExportHooks(
        array $rows,
        AbstractExportTemplate $template
    ): array {
        $formattedData = [];

        foreach ($rows as $row) {
            $formattedData[] = $template->formatRow($row);
        }

        return $formattedData;
    }
}
