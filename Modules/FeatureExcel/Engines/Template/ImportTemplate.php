<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Template;

/**
 * 导入模板对象
 *
 * 定义Excel/CSV导入的完整模板配置，
 * 包含格式、Sheet位置、数据起始行、表头行及字段映射等信息
 */
class ImportTemplate
{
    /**
     * 模板名称
     */
    protected string $name = '';

    /**
     * 描述
     */
    protected string $description = '';

    /**
     * 格式（excel/csv）
     */
    protected string $format = 'excel';

    /**
     * Sheet索引
     */
    protected int $sheet = 0;

    /**
     * 数据起始行
     */
    protected int $startRow = 2;

    /**
     * 表头行
     */
    protected int $headerRow = 1;

    /**
     * 字段映射
     *
     * @var array<string, FieldMapping>
     */
    protected array $fields = [];

    /**
     * 获取模板名称
     *
     * @return string 模板名称
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 设置模板名称
     *
     * @param string $name 模板名称
     * @return static 支持链式调用
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 获取描述
     *
     * @return string 描述
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * 设置描述
     *
     * @param string $description 描述
     * @return static 支持链式调用
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * 获取格式
     *
     * @return string 格式（excel/csv）
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * 设置格式
     *
     * @param string $format 格式（excel/csv）
     * @return static 支持链式调用
     */
    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /**
     * 获取Sheet索引
     *
     * @return int Sheet索引
     */
    public function getSheet(): int
    {
        return $this->sheet;
    }

    /**
     * 设置Sheet索引
     *
     * @param int $sheet Sheet索引
     * @return static 支持链式调用
     */
    public function setSheet(int $sheet): static
    {
        $this->sheet = $sheet;
        return $this;
    }

    /**
     * 获取数据起始行
     *
     * @return int 数据起始行
     */
    public function getStartRow(): int
    {
        return $this->startRow;
    }

    /**
     * 设置数据起始行
     *
     * @param int $startRow 数据起始行
     * @return static 支持链式调用
     */
    public function setStartRow(int $startRow): static
    {
        $this->startRow = $startRow;
        return $this;
    }

    /**
     * 获取表头行
     *
     * @return int 表头行
     */
    public function getHeaderRow(): int
    {
        return $this->headerRow;
    }

    /**
     * 设置表头行
     *
     * @param int $headerRow 表头行
     * @return static 支持链式调用
     */
    public function setHeaderRow(int $headerRow): static
    {
        $this->headerRow = $headerRow;
        return $this;
    }

    /**
     * 获取字段映射
     *
     * @return array<string, FieldMapping> 字段映射
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * 设置字段映射
     *
     * @param array<string, FieldMapping> $fields 字段映射
     * @return static 支持链式调用
     */
    public function setFields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }
}
