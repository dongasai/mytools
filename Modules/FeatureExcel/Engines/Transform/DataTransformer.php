<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Transform;

use Modules\FeatureExcel\Engines\Template\ExportTemplate;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;

/**
 * 数据转换器
 *
 * 按模板配置进行数据类型转换和值映射
 */
class DataTransformer
{
    /**
     * 转换导入数据（原始值 → 业务值）
     *
     * @param  array  $rawData  原始数据（二维数组）
     * @param  ImportTemplate  $template  导入模板
     * @return array 转换后数据
     */
    public static function transformImportData(array $rawData, ImportTemplate $template): array
    {
        $result = [];

        foreach ($rawData as $row) {
            $transformedRow = [];
            foreach ($template->getFields() as $fieldName => $fieldMapping) {
                $value = $row[$fieldName] ?? null;
                // 应用字段默认值（空值时）
                if (($value === null || $value === '') && $fieldMapping->getDefault() !== null) {
                    $value = $fieldMapping->getDefault();
                }
                $transformedRow[$fieldName] = TypeTransformer::transform(
                    $value,
                    $fieldMapping->getTransform(),
                    $fieldMapping->getType(),
                    $fieldMapping->getTimezoneFrom(),
                    $fieldMapping->getTimezoneTo()
                );
            }
            $result[] = $transformedRow;
        }

        return $result;
    }

    /**
     * 转换导出数据（业务值 → 显示值）
     *
     * @param  array  $data  业务数据（二维数组）
     * @param  ExportTemplate  $template  导出模板
     * @return array 转换后数据
     */
    public static function transformExportData(array $data, ExportTemplate $template): array
    {
        $result = [];

        foreach ($data as $row) {
            $transformedRow = [];
            foreach ($template->getFields() as $fieldName => $fieldMapping) {
                $value = $row[$fieldName] ?? null;
                $transformedRow[$fieldName] = ValueTransformer::format(
                    $value,
                    $fieldMapping->getFormat(),
                    $fieldMapping->getType(),
                    $fieldMapping->getTimezoneFrom(),
                    $fieldMapping->getTimezoneTo()
                );
            }
            $result[] = $transformedRow;
        }

        return $result;
    }
}
