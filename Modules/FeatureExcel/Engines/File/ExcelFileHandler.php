<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\File;

use Modules\FeatureExcel\Engines\Template\ExportTemplate;
use Modules\FeatureExcel\Engines\Template\ImportTemplate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Excel 文件处理引擎
 *
 * 负责 Excel 文件的读取和写入操作，支持全量读取、分块读取、
 * 普通写入和生成器写入。所有方法均为静态方法。
 */
class ExcelFileHandler
{
    /**
     * 读取 Excel 文件全部数据
     *
     * 使用 IOFactory 创建 Reader，按模板配置选择工作表，
     * 从 startRow 开始截取数据行，将列号映射为字段名。
     *
     * @param string $path Excel 文件路径
     * @param ImportTemplate $template 导入模板
     * @return array 关联数组，每行以字段名为键
     */
    public static function readExcelFile(string $path, ImportTemplate $template): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheet($template->getSheet());

        $allData = $sheet->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();

        $startRow = $template->getStartRow();
        $fields = $template->getFields();

        /** @var array<string, string> $columnMap 列号 => 字段名 */
        $columnMap = [];
        foreach ($fields as $fieldName => $field) {
            $column = $field->getColumn();
            if ($column !== '') {
                $columnMap[$column] = $fieldName;
            }
        }

        $result = [];
        $totalRows = count($allData);
        for ($row = $startRow; $row <= $totalRows; $row++) {
            if (!isset($allData[$row])) {
                continue;
            }

            $rowData = $allData[$row];
            $mappedRow = [];
            foreach ($columnMap as $col => $fName) {
                $mappedRow[$fName] = $rowData[$col] ?? null;
            }
            $result[] = $mappedRow;
        }

        return $result;
    }

    /**
     * 分块读取 Excel 文件（生成器方式）
     *
     * 使用 IReadFilter 限制每次读取的行范围，适合处理大文件。
     * 每块读取后释放内存，通过 yield 返回数据。
     *
     * @param string $path Excel 文件路径
     * @param ImportTemplate $template 导入模板
     * @param int $chunkSize 每块行数，默认 1000
     * @return \Generator 生成器，每次 yield 一块关联数组
     */
    public static function readExcelFileChunked(string $path, ImportTemplate $template, int $chunkSize = 1000): \Generator
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $startRow = $template->getStartRow();
        $fields = $template->getFields();

        /** @var array<string, string> $columnMap 列号 => 字段名 */
        $columnMap = [];
        foreach ($fields as $fieldName => $field) {
            $column = $field->getColumn();
            if ($column !== '') {
                $columnMap[$column] = $fieldName;
            }
        }

        /** @var int $maxRow 预估最大行数，先读取一次获取总行数 */
        $tempReader = IOFactory::createReaderForFile($path);
        $tempReader->setReadDataOnly(true);
        $tempSpreadsheet = $tempReader->load($path);
        $maxRow = $tempSpreadsheet->getSheet($template->getSheet())->getHighestDataRow();
        $tempSpreadsheet->disconnectWorksheets();
        unset($tempSpreadsheet, $tempReader);

        $currentRow = $startRow;
        while ($currentRow <= $maxRow) {
            $endRow = $currentRow + $chunkSize - 1;

            /** @var IReadFilter $filter 分块读取过滤器 */
            $filter = new class($currentRow, $endRow) implements IReadFilter {
                /** @var int 起始行 */
                protected int $startRow;

                /** @var int 结束行 */
                protected int $endRow;

                /**
                 * @param int $startRow 起始行
                 * @param int $endRow 结束行
                 */
                public function __construct(int $startRow, int $endRow)
                {
                    $this->startRow = $startRow;
                    $this->endRow = $endRow;
                }

                /**
                 * 判断单元格是否应被读取
                 *
                 * @param string $columnAddress 列地址
                 * @param int $row 行号
                 * @param string $worksheetName 工作表名称
                 * @return bool 是否读取
                 */
                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row >= $this->startRow && $row <= $this->endRow;
                }
            };

            $chunkReader = IOFactory::createReaderForFile($path);
            $chunkReader->setReadDataOnly(true);
            $chunkReader->setReadFilter($filter);

            $spreadsheet = $chunkReader->load($path);
            $sheet = $spreadsheet->getSheet($template->getSheet());
            $chunkData = $sheet->toArray(null, true, true, true);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $chunkReader);

            $result = [];
            for ($row = $currentRow; $row <= $endRow && $row <= $maxRow; $row++) {
                if (!isset($chunkData[$row])) {
                    continue;
                }

                $rowData = $chunkData[$row];
                $mappedRow = [];
                foreach ($columnMap as $col => $fName) {
                    $mappedRow[$fName] = $rowData[$col] ?? null;
                }
                $result[] = $mappedRow;
            }

            if (!empty($result)) {
                yield $result;
            }

            $currentRow = $endRow + 1;
        }
    }

    /**
     * 创建 Excel 文件
     *
     * 根据导出模板创建 Spreadsheet，写入表头和数据行，
     * 应用样式配置，保存到系统临时文件。
     *
     * @param array $data 数据行数组
     * @param ExportTemplate $template 导出模板
     * @return string 临时文件路径
     */
    public static function createExcelFile(array $data, ExportTemplate $template): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($template->getSheetName());

        $fields = $template->getFields();
        $headers = $template->getHeaders();
        $columnCount = count($fields);

        // 写入表头行
        $colIndex = 0;
        foreach ($headers as $header) {
            $colLetter = self::indexToColumn($colIndex);
            $sheet->setCellValue($colLetter . '1', $header);
            $colIndex++;
        }

        // 写入数据行
        $rowIndex = 2;
        foreach ($data as $row) {
            $colIndex = 0;
            foreach ($fields as $fieldName => $field) {
                $colLetter = self::indexToColumn($colIndex);
                $sheet->setCellValue($colLetter . $rowIndex, $row[$fieldName] ?? '');
                $colIndex++;
            }
            $rowIndex++;
        }

        // 应用样式
        $styles = $template->getStyles();
        if (!empty($styles)) {
            self::applyHeaderStyle($sheet, $columnCount, $styles);
            self::applyDataStyle($sheet, $rowIndex - 1, $columnCount, $styles);
        }

        // 保存到临时文件
        $tempPath = tempnam(sys_get_temp_dir(), 'fexcel_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $spreadsheet->disconnectWorksheets();

        return $tempPath;
    }

    /**
     * 从生成器创建 Excel 文件
     *
     * 逐行从生成器获取数据写入 Spreadsheet，定期清理内存，
     * 适合大数据量导出场景。
     *
     * @param \Generator $dataGenerator 数据生成器
     * @param ExportTemplate $template 导出模板
     * @param int $batchSize 批次大小，默认 1000
     * @return string 临时文件路径
     */
    public static function createExcelFileFromGenerator(\Generator $dataGenerator, ExportTemplate $template, int $batchSize = 1000): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($template->getSheetName());

        $fields = $template->getFields();
        $headers = $template->getHeaders();
        $columnCount = count($fields);

        // 写入表头行
        $colIndex = 0;
        foreach ($headers as $header) {
            $colLetter = self::indexToColumn($colIndex);
            $sheet->setCellValue($colLetter . '1', $header);
            $colIndex++;
        }

        // 逐行写入数据
        $rowIndex = 2;
        $batchCount = 0;
        foreach ($dataGenerator as $row) {
            $colIndex = 0;
            foreach ($fields as $fieldName => $field) {
                $colLetter = self::indexToColumn($colIndex);
                $sheet->setCellValue($colLetter . $rowIndex, $row[$fieldName] ?? '');
                $colIndex++;
            }
            $rowIndex++;
            $batchCount++;

            // 定期清理内存
            if ($batchCount % $batchSize === 0) {
                $spreadsheet->garbageCollect();
            }
        }

        // 应用样式
        $styles = $template->getStyles();
        if (!empty($styles)) {
            self::applyHeaderStyle($sheet, $columnCount, $styles);
            self::applyDataStyle($sheet, $rowIndex - 1, $columnCount, $styles);
        }

        // 保存到临时文件
        $tempPath = tempnam(sys_get_temp_dir(), 'fexcel_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $spreadsheet->disconnectWorksheets();

        return $tempPath;
    }

    /**
     * 列号转索引
     *
     * 将 Excel 列号（A, B, C...）转换为从 0 开始的索引。
     * 例如：A=0, B=1, Z=25, AA=26
     *
     * @param string $column Excel 列号
     * @return int 索引值
     */
    public static function columnToIndex(string $column): int
    {
        $index = 0;
        $length = strlen($column);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * 索引转列号
     *
     * 将从 0 开始的索引转换为 Excel 列号。
     * 例如：0=A, 25=Z, 26=AA
     *
     * @param int $index 索引值
     * @return string Excel 列号
     */
    public static function indexToColumn(int $index): string
    {
        $column = '';
        $index++;
        while ($index > 0) {
            $index--;
            $column = chr(ord('A') + ($index % 26)) . $column;
            $index = intdiv($index, 26);
        }
        return $column;
    }

    /**
     * 应用表头行样式
     *
     * 根据 styles 配置中的 header_row 设置，应用加粗、背景色、对齐等样式。
     *
     * @param Worksheet $sheet 工作表
     * @param int $columnCount 列数
     * @param array $styles 样式配置
     * @return void
     */
    public static function applyHeaderStyle(Worksheet $sheet, int $columnCount, array $styles): void
    {
        $headerStyles = $styles['header_row'] ?? [];
        if (empty($headerStyles) || $columnCount <= 0) {
            return;
        }

        $lastColumn = self::indexToColumn($columnCount - 1);
        $styleRange = 'A1:' . $lastColumn . '1';

        $styleArray = [];

        // 加粗
        if (isset($headerStyles['bold']) && $headerStyles['bold']) {
            $styleArray['font'] = ['bold' => true];
        }

        // 背景色
        if (isset($headerStyles['background'])) {
            $styleArray['fill'] = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => str_replace('#', '', $headerStyles['background'])],
            ];
        }

        // 对齐
        if (isset($headerStyles['alignment'])) {
            $styleArray['alignment'] = self::buildAlignment($headerStyles['alignment']);
        }

        if (!empty($styleArray)) {
            $sheet->getStyle($styleRange)->applyFromArray($styleArray);
        }
    }

    /**
     * 应用数据行样式
     *
     * 根据 styles 配置中的 data_rows 设置，应用对齐等样式。
     *
     * @param Worksheet $sheet 工作表
     * @param int $rowCount 数据行数（不含表头）
     * @param int $columnCount 列数
     * @param array $styles 样式配置
     * @return void
     */
    public static function applyDataStyle(Worksheet $sheet, int $rowCount, int $columnCount, array $styles): void
    {
        $dataStyles = $styles['data_rows'] ?? [];
        if (empty($dataStyles) || $rowCount <= 1 || $columnCount <= 0) {
            return;
        }

        $lastColumn = self::indexToColumn($columnCount - 1);
        $styleRange = 'A2:' . $lastColumn . $rowCount;

        $styleArray = [];

        // 对齐
        if (isset($dataStyles['alignment'])) {
            $styleArray['alignment'] = self::buildAlignment($dataStyles['alignment']);
        }

        if (!empty($styleArray)) {
            $sheet->getStyle($styleRange)->applyFromArray($styleArray);
        }
    }

    /**
     * 构建对齐样式对齐配置数组
     *
     * 将字符串对齐标识转换为 PhpSpreadsheet 对齐配置。
     *
     * @param string $alignment 对齐标识（center/left/right）
     * @return array 对齐配置数组
     */
    protected static function buildAlignment(string $alignment): array
    {
        $horizontalMap = [
            'center' => Alignment::HORIZONTAL_CENTER,
            'left' => Alignment::HORIZONTAL_LEFT,
            'right' => Alignment::HORIZONTAL_RIGHT,
        ];

        return [
            'horizontal' => $horizontalMap[$alignment] ?? Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ];
    }
}
