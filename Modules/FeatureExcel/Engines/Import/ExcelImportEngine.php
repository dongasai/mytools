<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Import;

use Modules\FeatureExcel\Engines\File\ExcelFileHandler;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;
use Modules\FeatureExcel\Engines\Transform\DataTransformer;
use Modules\FeatureExcel\Engines\Validation\DataValidator;
use Modules\FeatureExcel\Engines\Validation\ImportResult;

/**
 * Excel 导入引擎
 *
 * 继承 AbstractImportEngine，实现 Excel 文件导入完整流程：
 * 路径验证 → 文件读取 → 数据验证 → 数据转换。
 * 提供带验证和不带验证两种导入方式。
 */
class ExcelImportEngine extends AbstractImportEngine
{
    /**
     * 带验证的 Excel 导入
     *
     * 完整导入流程：路径验证 → 文件读取 → 数据验证 → 数据转换。
     * 验证失败时返回 ImportResult::failed，包含错误信息。
     *
     * @param  string  $filePath  Excel 文件路径
     * @param  ImportTemplate  $template  导入模板
     * @return ImportResult 导入结果
     */
    public static function importWithValidation(string $filePath, ImportTemplate $template): ImportResult
    {
        // 1. 解析路径并验证（路径安全失败属预期业务失败，转为失败结果）
        try {
            $filePath = self::resolveFilePath($filePath);
            self::validateFilePath($filePath);
        } catch (\InvalidArgumentException $e) {
            return ImportResult::failed([0 => [$e->getMessage()]]);
        }

        // 2. 读取 Excel 文件
        $rawData = ExcelFileHandler::readExcelFile($filePath, $template);

        // 3. 数据验证
        $validationResult = DataValidator::validateImportData($rawData, $template);

        // 4. 验证失败
        if (! $validationResult->isValid()) {
            return ImportResult::failed($validationResult->getErrors());
        }

        // 5. 数据转换
        $transformedData = DataTransformer::transformImportData($validationResult->getData(), $template);

        // 6. 返回成功结果
        return ImportResult::success($transformedData);
    }

    /**
     * 不带验证的 Excel 导入
     *
     * 简化导入流程：路径验证 → 文件读取 → 数据转换。
     * 跳过数据验证步骤，适用于已确认数据合法的场景。
     *
     * @param  string  $filePath  Excel 文件路径
     * @param  ImportTemplate  $template  导入模板
     * @return array 转换后的数据数组
     */
    public static function import(string $filePath, ImportTemplate $template): array
    {
        // 1. 解析路径并验证
        $filePath = self::resolveFilePath($filePath);
        self::validateFilePath($filePath);

        // 2. 读取 Excel 文件
        $rawData = ExcelFileHandler::readExcelFile($filePath, $template);

        // 3. 数据转换
        return DataTransformer::transformImportData($rawData, $template);
    }
}
