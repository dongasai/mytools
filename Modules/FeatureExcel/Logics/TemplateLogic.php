<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Logics;

use Modules\FeatureExcel\Engines\Template\ImportTemplate;
use Modules\FeatureExcel\Engines\Template\ExportTemplate;

/**
 * 模板逻辑
 *
 * 提供模板相关的数据组装和格式化逻辑
 */
class TemplateLogic
{
    /**
     * 获取导入模板的字段摘要信息
     *
     * 提取模板的关键信息用于展示，不含完整 FieldMapping 对象
     *
     * @param ImportTemplate $template 导入模板
     * @return array 摘要信息
     */
    public static function getImportTemplateSummary(ImportTemplate $template): array
    {
        $fields = [];
        foreach ($template->getFields() as $name => $mapping) {
            $fields[$name] = [
                'column' => $mapping->getColumn(),
                'name' => $mapping->getName(),
                'type' => $mapping->getType(),
                'required' => $mapping->isRequired(),
            ];
        }

        return [
            'name' => $template->getName(),
            'description' => $template->getDescription(),
            'format' => $template->getFormat(),
            'field_count' => count($template->getFields()),
            'fields' => $fields,
        ];
    }

    /**
     * 获取导出模板的字段摘要信息
     *
     * @param ExportTemplate $template 导出模板
     * @return array 摘要信息
     */
    public static function getExportTemplateSummary(ExportTemplate $template): array
    {
        $fields = [];
        foreach ($template->getFields() as $name => $mapping) {
            $fields[$name] = [
                'column' => $mapping->getColumn(),
                'type' => $mapping->getType(),
                'format' => $mapping->getFormat(),
            ];
        }

        return [
            'name' => $template->getName(),
            'description' => $template->getDescription(),
            'format' => $template->getFormat(),
            'field_count' => count($template->getFields()),
            'persist' => $template->isPersist(),
            'fields' => $fields,
        ];
    }
}
