<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Events;

/**
 * 导入完成事件
 *
 * 数据导入成功完成后触发
 */
class ImportCompletedEvent
{
    /**
     * 模板名称
     */
    protected string $templateName = '';

    /**
     * 导入数据行数
     */
    protected int $dataCount = 0;

    /**
     * @param string $templateName 模板名称
     * @param int $dataCount 导入数据行数
     */
    public function __construct(string $templateName, int $dataCount)
    {
        $this->templateName = $templateName;
        $this->dataCount = $dataCount;
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
     * 获取导入数据行数
     *
     * @return int
     */
    public function getDataCount(): int
    {
        return $this->dataCount;
    }
}
