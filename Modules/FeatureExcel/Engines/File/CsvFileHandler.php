<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\File;

use Modules\FeatureExcel\Engines\Template\ImportTemplate;
use Modules\FeatureExcel\Engines\Template\ExportTemplate;

/**
 * CSV文件处理引擎
 *
 * 提供CSV文件的读取和创建功能，
 * 支持BOM检测、编码转换、列号映射等CSV特有处理
 */
class CsvFileHandler
{
    /**
     * UTF-8 BOM 标记
     */
    public const BOM_UTF8 = "\xEF\xBB\xBF";

    /**
     * 读取CSV文件并按模板映射为关联数组
     *
     * 打开文件后检测并跳过BOM，按headerRow读取表头行建立列索引映射，
     * 从startRow开始读取数据行，自动检测编码并转为UTF-8
     *
     * @param string $path 文件路径
     * @param ImportTemplate $template 导入模板
     * @return array 关联数组（字段名 => 值）的二维数组
     * @throws \InvalidArgumentException 文件无法打开
     */
    public static function readCsvFile(string $path, ImportTemplate $template): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \InvalidArgumentException('无法打开CSV文件: ' . basename($path));
        }

        // 检测并跳过BOM
        self::skipBom($handle);

        $headerRow = $template->getHeaderRow();
        $startRow = $template->getStartRow();
        $fields = $template->getFields();

        // 构建列索引到字段名的映射
        $columnMap = self::buildColumnMap($fields);

        // 跳过表头行之前的行，读取表头行
        $currentRow = 1;
        $headerValues = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($currentRow === $headerRow) {
                $headerValues = self::decodeRow($row);
            }
            if ($currentRow >= $headerRow) {
                break;
            }
            $currentRow++;
        }

        // 如果有表头行，优先用表头文本匹配字段名；否则用列号映射
        $indexToField = self::resolveIndexMapping($headerValues, $columnMap, $fields);

        // 跳过表头行和数据起始行之间的行
        for ($skip = $headerRow + 1; $skip < $startRow; $skip++) {
            fgetcsv($handle);
        }

        // 读取数据行
        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            $decodedRow = self::decodeRow($row);
            $mappedRow = [];

            foreach ($indexToField as $index => $fieldName) {
                $mappedRow[$fieldName] = $decodedRow[$index] ?? '';
            }

            // 跳过全空行
            if (self::isRowEmpty($mappedRow)) {
                continue;
            }

            $data[] = $mappedRow;
        }

        fclose($handle);

        return $data;
    }

    /**
     * 创建CSV文件
     *
     * 按模板配置写入BOM、表头和数据行，支持编码转换，
     * 文件生成到系统临时目录
     *
     * @param array $data 导出数据（二维数组）
     * @param ExportTemplate $template 导出模板
     * @param string $encoding 目标编码，默认UTF-8
     * @param bool $includeBom 是否包含BOM标记
     * @return string 临时文件路径
     */
    public static function createCsvFile(array $data, ExportTemplate $template, string $encoding = 'UTF-8', bool $includeBom = true): string
    {
        $tempPath = sys_get_temp_dir() . '/csv_export_' . uniqid() . '.csv';
        $handle = fopen($tempPath, 'w');

        if ($handle === false) {
            throw new \InvalidArgumentException('无法创建CSV临时文件');
        }

        // 写入BOM
        if ($includeBom && $encoding === 'UTF-8') {
            fwrite($handle, self::BOM_UTF8);
        }

        // 写入表头
        $headers = $template->getHeaders();
        if (!empty($headers)) {
            $encodedHeaders = self::encodeRow($headers, $encoding);
            fputcsv($handle, $encodedHeaders);
        }

        // 按字段column排序输出数据行
        $sortedFields = self::sortFieldsByColumn($template->getFields());

        foreach ($data as $row) {
            $outputRow = [];
            foreach ($sortedFields as $fieldName => $_) {
                $outputRow[] = (string) ($row[$fieldName] ?? '');
            }
            $encodedRow = self::encodeRow($outputRow, $encoding);
            fputcsv($handle, $encodedRow);
        }

        fclose($handle);

        return $tempPath;
    }

    /**
     * 行编码转换
     *
     * 将行数据从UTF-8转换为目标编码
     *
     * @param array $row 行数据
     * @param string $encoding 目标编码
     * @return array 转换后的行数据
     */
    public static function encodeRow(array $row, string $encoding): array
    {
        if ($encoding === 'UTF-8' || $encoding === '') {
            return $row;
        }

        return array_map(static function (string $value) use ($encoding): string {
            $converted = mb_convert_encoding($value, $encoding, 'UTF-8');
            return $converted !== false ? $converted : $value;
        }, $row);
    }

    /**
     * 行解码（自动检测编码转UTF-8）
     *
     * 对行中每个值自动检测编码并转换为UTF-8
     *
     * @param array $row 行数据
     * @return array 解码后的行数据
     */
    public static function decodeRow(array $row): array
    {
        return array_map(static function (string $value): string {
            if ($value === '') {
                return $value;
            }

            $detected = mb_detect_encoding($value, ['UTF-8', 'GBK', 'GB2312', 'BIG5', 'ASCII'], true);

            if ($detected === false || $detected === 'UTF-8' || $detected === 'ASCII') {
                return $value;
            }

            $converted = mb_convert_encoding($value, 'UTF-8', $detected);
            return $converted !== false ? $converted : $value;
        }, $row);
    }

    /**
     * 列号转索引
     *
     * 将Excel列号（A, B, C...Z, AA, AB...）转换为0基索引
     *
     * @param string $column 列号（A=0, B=1, Z=25, AA=26）
     * @return int 列索引
     */
    public static function columnToIndex(string $column): int
    {
        $index = 0;
        $length = strlen($column);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord(strtoupper($column[$i])) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * 检测并跳过BOM标记
     *
     * 读取文件前3字节判断是否为UTF-8 BOM，若是则跳过
     *
     * @param resource $handle 文件句柄
     * @return void
     */
    private static function skipBom($handle): void
    {
        $bom = fread($handle, 3);

        if ($bom !== self::BOM_UTF8) {
            // 非BOM，回退到文件开头
            rewind($handle);
        }
    }

    /**
     * 构建列索引到字段名的映射
     *
     * 根据字段映射的column属性（A, B, C...）转换为0基索引映射
     *
     * @param array<string, \Modules\FeatureExcel\Engines\Template\FieldMapping> $fields 字段映射
     * @return array<int, string> 列索引 => 字段名
     */
    private static function buildColumnMap(array $fields): array
    {
        $map = [];

        foreach ($fields as $fieldName => $fieldMapping) {
            $column = $fieldMapping->getColumn();
            if ($column !== '') {
                $map[self::columnToIndex($column)] = $fieldName;
            }
        }

        return $map;
    }

    /**
     * 解析索引映射
     *
     * 优先使用表头文本匹配字段名，回退到列号映射
     *
     * @param array $headerValues 表头行值
     * @param array<int, string> $columnMap 列号映射
     * @param array<string, \Modules\FeatureExcel\Engines\Template\FieldMapping> $fields 字段映射
     * @return array<int, string> 列索引 => 字段名
     */
    private static function resolveIndexMapping(array $headerValues, array $columnMap, array $fields): array
    {
        // 有表头行时，尝试用表头文本匹配字段名
        if (!empty($headerValues)) {
            $nameToField = [];
            foreach ($fields as $fieldName => $fieldMapping) {
                $nameToField[$fieldMapping->getName()] = $fieldName;
            }

            $mapping = [];
            foreach ($headerValues as $index => $headerText) {
                $headerText = trim($headerText);
                if (isset($nameToField[$headerText])) {
                    $mapping[$index] = $nameToField[$headerText];
                } elseif (isset($columnMap[$index])) {
                    $mapping[$index] = $columnMap[$index];
                }
            }

            if (!empty($mapping)) {
                return $mapping;
            }
        }

        // 回退到列号映射
        return $columnMap;
    }

    /**
     * 判断行是否全空
     *
     * @param array $row 映射后的行数据
     * @return bool 是否全空
     */
    private static function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== '' && $value !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * 按字段column排序
     *
     * 根据字段的column属性（A, B, C...）排序，确保导出列顺序正确
     *
     * @param array<string, \Modules\FeatureExcel\Engines\Template\FieldMapping> $fields 字段映射
     * @return array<string, \Modules\FeatureExcel\Engines\Template\FieldMapping> 排序后的字段映射
     */
    private static function sortFieldsByColumn(array $fields): array
    {
        uasort($fields, static function ($a, $b): int {
            $indexA = self::columnToIndex($a->getColumn());
            $indexB = self::columnToIndex($b->getColumn());
            return $indexA <=> $indexB;
        });

        return $fields;
    }
}
