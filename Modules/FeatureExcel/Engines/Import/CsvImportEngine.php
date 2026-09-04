<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Import;

use Modules\FeatureExcel\Engines\File\CsvFileHandler;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;
use Modules\FeatureExcel\Engines\Transform\DataTransformer;
use Modules\FeatureExcel\Engines\Validation\DataValidator;
use Modules\FeatureExcel\Engines\Validation\ImportResult;

/**
 * CSV导入引擎
 *
 * 继承AbstractImportEngine，实现CSV格式的数据导入，
 * 支持路径安全验证、数据读取、验证和转换
 */
class CsvImportEngine extends AbstractImportEngine
{
    /**
     * 带验证的CSV导入
     *
     * 完整流程：路径验证 → CSV读取 → 数据验证 → 数据转换 → 返回结果
     * 验证失败时返回ImportResult::failed，包含错误详情
     *
     * @param  string  $filePath  文件路径
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

        // 2. 读取CSV文件
        $rawData = CsvFileHandler::readCsvFile($filePath, $template);

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
     * 无验证的CSV导入
     *
     * 流程：路径验证 → CSV读取 → 数据转换 → 返回数组
     * 适用于已确认数据合法性的场景
     *
     * @param  string  $filePath  文件路径
     * @param  ImportTemplate  $template  导入模板
     * @return array 转换后的数据数组
     */
    public static function import(string $filePath, ImportTemplate $template): array
    {
        // 1. 解析路径并验证
        $filePath = self::resolveFilePath($filePath);
        self::validateFilePath($filePath);

        // 2. 读取CSV文件
        $rawData = CsvFileHandler::readCsvFile($filePath, $template);

        // 3. 数据转换
        return DataTransformer::transformImportData($rawData, $template);
    }
}
