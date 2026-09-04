<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Console;

use DLaravel\Commands\Command;
use Modules\FeatureExcel\Logics\TemplateLogic;
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcel\Templates\Base\AbstractExportTemplate;
use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;

/**
 * 模板验证命令
 *
 * 验证 Excel/CSV 文件是否符合指定导入模板：
 * - 展示模板摘要与字段映射表
 * - 执行引擎标准验证 + 业务钩子验证，输出成功行数或错误表
 * - --dry-run 仅预览模板定义，不读取/验证文件
 *
 * 用法：
 *   php artisan featureexcel:validate storage/app/data.csv EnergyDataHourlyImportTemplate
 *   php artisan featureexcel:validate storage/app/data.csv "Modules\NtEnergy\FeatureExcelTemplates\EnergyDataHourlyImportTemplate" --dry-run
 */
class ValidateTemplateCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'featureexcel:validate {file : 待验证的Excel/CSV文件路径} {template : 模板类名（FQCN或短类名）} {--dry-run : 仅预览模板定义，不执行文件验证}';

    /**
     * 命令描述
     */
    protected $description = '验证Excel/CSV文件是否符合导入模板';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $file = $this->argument('file');
        $templateName = $this->argument('template');
        $dryRun = (bool) $this->option('dry-run');

        // 1. 解析模板类
        $template = $this->resolveTemplate($templateName);
        if ($template === null) {
            $this->error("无法找到模板类: {$templateName}");
            $this->info('支持 FQCN（如 Modules\NtEnergy\FeatureExcelTemplates\EnergyDataHourlyImportTemplate）或短类名（自动扫描各模块 ImportTemplates/ExportTemplates 目录）');

            return;
        }

        // 2. 展示模板摘要与字段映射表
        $this->showTemplateInfo($template);

        // 3. dry-run 模式：仅预览模板定义
        if ($dryRun) {
            $this->warn('--dry-run 模式：仅预览模板定义，未验证文件');

            return;
        }

        // 4. 验证文件
        $this->validateFile($file, $template);
    }

    /**
     * 解析模板类实例
     *
     * 优先按 FQCN 实例化，失败则扫描各模块 ImportTemplates/ExportTemplates 目录按短类名匹配
     *
     * @param  string  $templateName  模板类名（FQCN或短类名）
     * @return AbstractImportTemplate|AbstractExportTemplate|null 模板实例（未找到返回 null）
     */
    private function resolveTemplate(string $templateName): AbstractImportTemplate|AbstractExportTemplate|null
    {
        // 优先按 FQCN 实例化
        if (class_exists($templateName)) {
            $instance = new $templateName;
            if ($instance instanceof AbstractImportTemplate || $instance instanceof AbstractExportTemplate) {
                return $instance;
            }
            $this->error("类 {$templateName} 不是有效的模板类（需继承 AbstractImportTemplate/AbstractExportTemplate）");

            return null;
        }

        // 扫描各模块模板目录按短类名匹配
        $candidates = array_merge(
            glob(base_path('Modules/*/ImportTemplates/*.php')) ?: [],
            glob(base_path('Modules/*/ExportTemplates/*.php')) ?: []
        );

        $matchBaseName = trim($templateName, '\\');
        $matches = [];
        foreach ($candidates as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            if ($className === $matchBaseName) {
                $matches[] = $this->fileToClass($file);
            }
        }

        // 多模块存在同名模板类时提示用户使用 FQCN 消歧
        if (count($matches) > 1) {
            $this->error("短类名 {$templateName} 存在多个匹配，请使用完整类名: ".implode(', ', $matches));

            return null;
        }

        foreach ($matches as $fqcn) {
            if (class_exists($fqcn)) {
                return new $fqcn;
            }
        }

        return null;
    }

    /**
     * 将模板文件路径转换为完整类名
     *
     * PSR-4 映射：Modules/{模块}/{目录}/{类}.php → Modules\{模块}\{目录}\{类}
     * （目录名保持原样，如 NtEnergy/ImportTemplates）
     *
     * @param  string  $file  文件绝对路径
     * @return string 完整类名
     */
    private function fileToClass(string $file): string
    {
        $relative = ltrim(str_replace(base_path('Modules/'), '', $file), '/');
        $relative = str_replace('.php', '', $relative);

        return 'Modules\\'.str_replace('/', '\\', $relative);
    }

    /**
     * 展示模板摘要与字段映射表
     *
     * @param  AbstractImportTemplate|AbstractExportTemplate  $template  模板实例
     */
    private function showTemplateInfo(AbstractImportTemplate|AbstractExportTemplate $template): void
    {
        $summary = $template instanceof AbstractImportTemplate
            ? TemplateLogic::getImportTemplateSummary($template)
            : TemplateLogic::getExportTemplateSummary($template);

        $this->info('模板摘要:');
        $this->line("  名称: {$summary['name']}");
        if (! empty($summary['description'])) {
            $this->line("  描述: {$summary['description']}");
        }
        $this->line("  格式: {$summary['format']} | 字段数: {$summary['field_count']}");
        if ($template instanceof AbstractImportTemplate) {
            $this->line("  数据起始行: {$template->getStartRow()} | 表头行: {$template->getHeaderRow()}");
        }

        $this->info('字段映射:');
        $rows = [];
        foreach ($summary['fields'] as $fieldName => $field) {
            $rows[] = [
                '字段名' => $fieldName,
                '列号' => $field['column'],
                '名称' => $field['name'] ?? $fieldName,
                '类型' => $field['type'],
                '必填' => ($field['required'] ?? false) ? '是' : '否',
            ];
        }
        $this->table(['字段名', '列号', '名称', '类型', '必填'], $rows);
    }

    /**
     * 验证文件并输出结果
     *
     * @param  string  $file  文件路径
     * @param  AbstractImportTemplate|AbstractExportTemplate  $template  模板实例
     */
    private function validateFile(string $file, AbstractImportTemplate|AbstractExportTemplate $template): void
    {
        if (! $template instanceof AbstractImportTemplate) {
            $this->error('文件验证仅支持导入模板（AbstractImportTemplate），导出模板请使用 --dry-run 预览');

            return;
        }

        $this->info("开始验证文件: {$file}");

        $result = ModuleFeatureExcelService::importWithValidation($file, $template);

        if ($result->isSuccess()) {
            $data = $result->getData();
            $this->info('验证通过！共 '.count($data).' 行数据');

            $this->info('数据预览（前5行）:');
            $preview = array_slice($data, 0, 5);
            $this->table(array_keys($preview[0] ?? []), $preview);

            return;
        }

        $this->error('验证失败！错误如下:');
        $errorRows = [];
        foreach ($result->getErrors() as $rowIndex => $rowErrors) {
            foreach ($rowErrors as $message) {
                $errorRows[] = ['行索引' => $rowIndex, '错误' => $message];
            }
        }
        $this->table(['行索引', '错误'], $errorRows);
    }
}
