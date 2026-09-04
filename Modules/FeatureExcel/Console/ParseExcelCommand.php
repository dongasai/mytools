<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Console;

use DLaravel\Commands\Command;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Excel 解析命令
 *
 * 解析 .xlsx/.xls 文件（本地路径或 URL），将单元格数据对象化并 var_dump 输出：
 * - 每个单元格包含 type（PhpSpreadsheet 原生类型映射为 string/numeric/date/formula/boolean/error/null）、
 *   value（原始值）、formatted（格式化值），公式单元格额外附加 calculated（计算值）
 * - 默认输出前 100 行，可通过 --rows 调整；--sheet 指定工作表索引（默认第一个）
 *
 * 用法：
 *   php artisan feature-excel:parse public/execl_template/Demo.xlsx
 *   php artisan feature-excel:parse storage/app/data.xlsx --rows=50 --sheet=1
 *   php artisan feature-excel:parse https://example.com/data.xlsx
 */
class ParseExcelCommand extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'feature-excel:parse {path : Excel文件路径或URL} {--rows=100 : 最多输出行数} {--sheet=0 : 工作表索引，默认第一个}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '解析Excel文件并输出对象化单元格数据';

    /**
     * 执行命令
     *
     * 流程：参数校验 → 路径处理（本地校验/URL下载）→ 探测总行数 → 正式读取（可选行数过滤）
     * → 单元格对象化 → 输出摘要与数据。
     * URL 下载的临时文件通过 try/finally 保证 finally 中清理（finally 仅清理，不捕获异常）。
     * 继承 DLaravel Command 基类，handle() 已封装日志与统一异常处理。
     */
    public function handleRun(): void
    {
        $path = $this->argument('path');

        // 1. --rows 必须为正整数（先校验数字，避免 (int) 静默转换误导）
        $rowsOption = $this->option('rows');
        if (! is_numeric($rowsOption) || (int) $rowsOption <= 0) {
            $this->error("--rows 必须为正整数，当前值: {$rowsOption}");

            return;
        }
        $rows = (int) $rowsOption;

        $sheetIndex = (int) $this->option('sheet');

        // 2. 路径处理：URL 下载到临时文件，本地路径直接校验存在性
        // filter_var 对含中文的 URL 校验会失败，故额外用协议头判断（仅允许 http/https）
        $isUrl = filter_var($path, FILTER_VALIDATE_URL) !== false
            || str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://');

        $tempFile = null;
        try {
            if ($isUrl) {
                // @ 仅抑制下载失败时的 PHP warning，返回值显式校验并报错
                $context = stream_context_create(['http' => ['timeout' => 30]]);
                $content = @file_get_contents($path, false, $context);
                if ($content === false || $content === '') {
                    $this->error("下载失败: {$path}");

                    return;
                }

                $tempFile = sys_get_temp_dir().'/fexcel_parse_'.uniqid('', true).'.'.$this->extractUrlExtension($path);
                if (file_put_contents($tempFile, $content) === false) {
                    $this->error("写入临时文件失败: {$tempFile}");

                    return;
                }
                $filePath = $tempFile;
            } else {
                if (! is_file($path)) {
                    $this->error("文件不存在: {$path}");

                    return;
                }
                $filePath = $path;
            }

            // 3. 探测总行数（独立 reader 只读数据），同时校验工作表索引范围
            $detect = $this->detectTotalRows($filePath, $sheetIndex);
            if ($detect === null) {
                return;
            }

            // 4. 正式读取（保留公式与格式），行数超限时通过 read filter 截断
            $data = $this->readRows($filePath, $sheetIndex, $rows, $detect['total']);

            // 5. 输出摘要与数据
            $this->outputSummary($filePath, $sheetIndex, $detect['title'], $detect['total'], $rows);

            if ($data === []) {
                $this->info('文件中没有数据');

                return;
            }

            var_dump($data);
        } finally {
            // 仅清理临时文件，不捕获任何异常
            if ($tempFile !== null) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * 从 URL 中提取文件扩展名
     *
     * 无扩展名或非 Excel 扩展名时默认 .xlsx
     *
     * @param  string  $url  URL 地址
     * @return string 扩展名（不含点，如 xlsx/xls）
     */
    private function extractUrlExtension(string $url): string
    {
        $urlPath = (string) parse_url($url, PHP_URL_PATH);
        $ext = strtolower((string) pathinfo($urlPath, PATHINFO_EXTENSION));

        return in_array($ext, ['xlsx', 'xls'], true) ? $ext : 'xlsx';
    }

    /**
     * 探测工作表总数据行数
     *
     * 使用独立 reader 并 setReadDataOnly(true)（只读数据、不读公式与格式）探测总行数，
     * 同时校验 --sheet 索引范围（越界时输出错误返回 null）。
     * 探测完毕后立即 disconnectWorksheets() 释放内存。
     *
     * @param  string  $filePath  文件路径
     * @param  int  $sheetIndex  工作表索引
     * @return array{total:int, title:string}|null 总行数与工作表名称；sheet 越界返回 null
     */
    private function detectTotalRows(string $filePath, int $sheetIndex): ?array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);

        $sheetCount = $spreadsheet->getSheetCount();
        if ($sheetIndex < 0 || $sheetIndex >= $sheetCount) {
            $this->error("工作表索引 {$sheetIndex} 超出范围（共 {$sheetCount} 个工作表）");
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);

            return null;
        }

        $sheet = $spreadsheet->getSheet($sheetIndex);

        // 先取值再释放内存（disconnect 后 Worksheet 不可再访问）
        $total = $sheet->getHighestDataRow();
        $title = $sheet->getTitle();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $reader);

        return [
            'total' => $total,
            'title' => $title,
        ];
    }

    /**
     * 正式读取工作表数据并对象化
     *
     * reader 不调用 setReadDataOnly，保留公式与格式信息；
     * totalRows 超过 rows 时通过 IReadFilter 匿名类限制只读取前 rows 行，避免加载全部数据。
     * 数据组装完成后立即 disconnectWorksheets() 释放内存。
     *
     * @param  string  $filePath  文件路径
     * @param  int  $sheetIndex  工作表索引
     * @param  int  $rows  最多输出行数
     * @param  int  $totalRows  工作表总数据行数
     * @return array<int, array{row:int, cells:array<string, array<string, mixed>>}> 行对象化数据
     */
    private function readRows(string $filePath, int $sheetIndex, int $rows, int $totalRows): array
    {
        $reader = IOFactory::createReaderForFile($filePath);

        // 总行数超过限制时，只读取前 rows 行
        if ($totalRows > $rows) {
            $reader->setReadFilter(new class($rows) implements IReadFilter
            {
                /**
                 * 允许读取的最大行号
                 */
                private int $maxRow;

                /**
                 * 构造函数
                 *
                 * @param  int  $maxRow  允许读取的最大行号
                 */
                public function __construct(int $maxRow)
                {
                    $this->maxRow = $maxRow;
                }

                /**
                 * 判断单元格是否可读（仅限制行号范围，列不限制）
                 *
                 * @param  string  $columnAddress  列地址（如 A）
                 * @param  int  $row  行号
                 * @param  string  $worksheetName  工作表名称
                 * @return bool 是否读取该单元格
                 */
                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row <= $this->maxRow;
                }
            });
        }

        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getSheet($sheetIndex);

        $data = [];
        $maxRow = min($totalRows, $rows);
        $maxColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());

        // 逐行逐列组装对象化数据（行 1..maxRow，列 A..最高数据列）
        for ($row = 1; $row <= $maxRow; $row++) {
            $rowData = ['row' => $row, 'cells' => []];

            for ($columnIndex = 1; $columnIndex <= $maxColumnIndex; $columnIndex++) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $cell = $worksheet->getCell($column.$row);

                $value = $cell->getValue();

                // 空单元格（null 或空字符串）跳过
                if ($value === null || $value === '') {
                    continue;
                }

                // RichText 实例转为纯文本
                if ($value instanceof RichText) {
                    $value = $value->getPlainText();
                }

                $rowData['cells'][$column] = $this->buildCellInfo($cell, $value);
            }

            if ($rowData['cells'] !== []) {
                $data[] = $rowData;
            }
        }

        // 数据组装完毕立即释放内存
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $reader, $worksheet);

        return $data;
    }

    /**
     * 将单元格构建为对象化信息
     *
     * 结构：type（PhpSpreadsheet 原生类型映射）、value（原始值，date 类型转为 Y-m-d H:i:s 字符串）、
     * formatted（格式化值）；formula 类型额外附加 calculated（计算值）。
     * 数值型单元格按格式码判断是否为日期：是则 type=date 且 value 转为可读日期字符串。
     *
     * @param  Cell  $cell  单元格对象
     * @param  mixed  $value  原始值（已处理 RichText）
     * @return array{type:string, value:mixed, formatted:string, calculated?:mixed} 单元格信息
     */
    private function buildCellInfo(Cell $cell, mixed $value): array
    {
        $dataType = $cell->getDataType();
        $cellInfo = ['type' => 'null', 'value' => $value];

        switch ($dataType) {
            case DataType::TYPE_NUMERIC:
                // 数值型：按格式码判断是否为日期
                $formatCode = $cell->getStyle()->getNumberFormat()->getFormatCode();
                if (Date::isDateTimeFormatCode($formatCode)) {
                    $cellInfo['type'] = 'date';
                    // 仅当值为合法的日期序列号时才转换，否则保留原始值
                    if (is_numeric($value) && $value > 0) {
                        $cellInfo['value'] = Date::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
                    }
                } else {
                    $cellInfo['type'] = 'numeric';
                }
                break;

            case DataType::TYPE_STRING:
            case DataType::TYPE_INLINE:
            case DataType::TYPE_STRING2:
                $cellInfo['type'] = 'string';
                break;

            case DataType::TYPE_ISO_DATE:
                $cellInfo['type'] = 'date';
                break;

            case DataType::TYPE_FORMULA:
                $cellInfo['type'] = 'formula';
                // 公式计算可能因引用外部数据/循环引用等失败，仅捕获计算引擎异常（业务容错）。
                // 注意：getFormattedValue() 内部会触发一次计算，getCalculatedValue() 会再次计算
                try {
                    $cellInfo['formatted'] = $cell->getFormattedValue();
                    $cellInfo['calculated'] = $cell->getCalculatedValue();
                } catch (CalculationException $e) {
                    $cellInfo['formatted'] = (string) $cellInfo['value'];
                    $cellInfo['calculated'] = null;
                }
                break;

            case DataType::TYPE_BOOL:
                $cellInfo['type'] = 'boolean';
                break;

            case DataType::TYPE_ERROR:
                $cellInfo['type'] = 'error';
                break;

            case DataType::TYPE_NULL:
            default:
                $cellInfo['type'] = 'null';
                break;
        }

        // 非公式单元格的格式化值不涉及计算引擎，直接获取
        if ($dataType !== DataType::TYPE_FORMULA) {
            $cellInfo['formatted'] = $cell->getFormattedValue();
        }

        return $cellInfo;
    }

    /**
     * 输出解析摘要
     *
     * 包含文件路径、工作表名称与索引、总行数；总行数超过输出行数时附加截断提示。
     *
     * @param  string  $filePath  文件路径
     * @param  int  $sheetIndex  工作表索引
     * @param  string  $sheetTitle  工作表名称
     * @param  int  $totalRows  工作表总数据行数
     * @param  int  $rows  最多输出行数
     */
    private function outputSummary(string $filePath, int $sheetIndex, string $sheetTitle, int $totalRows, int $rows): void
    {
        $this->info("文件: {$filePath}");
        $this->info("工作表: {$sheetTitle}（索引 {$sheetIndex}）");
        $this->info("总行数: {$totalRows}");

        if ($totalRows > $rows) {
            $this->info("文件共 {$totalRows} 行，仅输出前 {$rows} 行");
        }
    }
}
