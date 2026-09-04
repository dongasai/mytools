<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Console;

use DLaravel\Commands\Command;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Excel 模板生成命令
 *
 * 根据导入模板定义自动生成 Excel 模板文件
 */
class GenerateTemplateCommand extends Command
{
    protected $signature = 'featureexcel:generate-template
        {template : 模板类名（FQCN或短类名）}
        {--output=public/excel_templates : 输出目录}
        {--filename= : 自定义文件名}';

    protected $description = '根据导入模板定义生成Excel模板文件';

    public function handleRun(): void
    {
        try {
            $templateName = $this->argument('template');
            $outputDir = $this->option('output');
            $customFilename = $this->option('filename');

            // 1. 解析模板类
            $template = $this->resolveTemplate($templateName);
            if ($template === null) {
                $this->error("无法找到模板类: {$templateName}");

                return;
            }

            if (! ($template instanceof AbstractImportTemplate)) {
                $this->error('仅支持导入模板（AbstractImportTemplate）');

                return;
            }

            // 2. 确保输出目录存在
            if (! is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // 3. 生成文件名
            $filename = $customFilename ?: ($template->getName() ?: '导入模板');
            $filename = $filename.'.xlsx';
            $filepath = $outputDir.'/'.$filename;

            // 4. 生成 Excel 文件
            $this->generateExcel($template, $filepath);
            $this->info("模板文件生成成功: {$filepath}");
            $this->newLine();
            $this->line("模板名称: {$template->getName()}");
            $this->line('字段数量: '.count($template->getFields()));
        } catch (\Throwable $e) {
            SystemLogService::exception('feature_excel', $e, [
                'operation' => 'generateTemplate',
                'template' => $this->argument('template') ?? '',
            ]);
            $this->error("生成失败: {$e->getMessage()}");
        }
    }

    /**
     * 解析模板类实例
     */
    private function resolveTemplate(string $templateName): ?AbstractImportTemplate
    {
        // 优先按 FQCN 实例化
        if (class_exists($templateName)) {
            $instance = new $templateName;
            if ($instance instanceof AbstractImportTemplate) {
                return $instance;
            }
        }

        // 扫描各模块模板目录按短类名匹配
        $candidates = glob(base_path('Modules/*/FeatureExcelTemplates/*.php')) ?: [];
        $matchBaseName = trim($templateName, '\\');

        foreach ($candidates as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            if ($className === $matchBaseName) {
                $fqcn = $this->fileToClass($file);
                if ($fqcn !== null && class_exists($fqcn)) {
                    return new $fqcn;
                }
            }
        }

        return null;
    }

    /**
     * 将模板文件路径转换为完整类名
     */
    private function fileToClass(string $file): string
    {
        $relative = ltrim(str_replace(base_path('Modules/'), '', $file), '/');
        $relative = str_replace('.php', '', $relative);

        return 'Modules\\'.str_replace('/', '\\', $relative);
    }

    /**
     * 生成 Excel 文件
     */
    private function generateExcel(AbstractImportTemplate $template, string $filepath): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('数据');

        $fields = $template->getFields();
        $colIndex = 0;

        // 1. 设置表头（第1行）
        foreach ($fields as $fieldName => $fieldMapping) {
            $colLetter = chr(65 + $colIndex);
            $columnName = $fieldMapping->getName() ?: $fieldName;

            $sheet->setCellValue($colLetter.'1', $columnName);
            $sheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            // 设置列宽
            $width = match ($fieldMapping->getType()) {
                'integer', 'float' => 12,
                'date', 'datetime' => 15,
                default => 20,
            };
            $sheet->getColumnDimension($colLetter)->setWidth($width);

            $colIndex++;
        }

        // 2. 设置示例数据（第2行）
        $colIndex = 0;
        foreach ($fields as $fieldName => $fieldMapping) {
            $colLetter = chr(65 + $colIndex);

            // 根据类型生成示例值
            $sampleValue = $this->generateSampleValue($fieldMapping);
            $sheet->setCellValue($colLetter.'2', $sampleValue);

            $colIndex++;
        }

        // 3. 给表头第一个单元格添加批注说明
        $firstColLetter = chr(65);
        $comment = $sheet->getComment($firstColLetter.'1');
        $commentText = $comment->getText();
        $commentText->createTextRun('第2行为示例数据，导入时自动跳过；请从第3行开始填写实际数据');

        // 4. 保存文件
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
    }

    /**
     * 根据字段类型生成示例值
     */
    private function generateSampleValue($fieldMapping): mixed
    {
        // 优先使用默认值
        if ($fieldMapping->getDefault() !== null) {
            return $fieldMapping->getDefault();
        }

        // 根据类型生成示例
        return match ($fieldMapping->getType()) {
            'integer' => 10,
            'float' => 100.50,
            'date' => date('Y-m-d'),
            'datetime' => date('Y-m-d H:i:s'),
            'boolean' => '是',
            default => '示例'.($fieldMapping->getName() ?: '数据'),
        };
    }
}
