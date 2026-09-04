<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Events;

/**
 * 导出完成事件
 *
 * 数据导出成功完成后触发
 */
class ExportCompletedEvent
{
    /**
     * 模板名称
     */
    protected string $templateName = '';

    /**
     * 导出文件URL
     */
    protected string $fileUrl = '';

    /**
     * @param string $templateName 模板名称
     * @param string $fileUrl 导出文件URL
     */
    public function __construct(string $templateName, string $fileUrl)
    {
        $this->templateName = $templateName;
        $this->fileUrl = $fileUrl;
    }

    /**
     * 获取模板名称
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * 获取导出文件URL
     *
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }
}
