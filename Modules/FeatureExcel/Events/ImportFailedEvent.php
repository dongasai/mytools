<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Events;

/**
 * 导入失败事件
 *
 * 数据导入失败后触发
 */
class ImportFailedEvent
{
    /**
     * 模板名称
     */
    protected string $templateName = '';

    /**
     * 错误信息
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * @param string $templateName 模板名称
     * @param array $errors 错误信息
     */
    public function __construct(string $templateName, array $errors)
    {
        $this->templateName = $templateName;
        $this->errors = $errors;
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
     * 获取错误信息
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
