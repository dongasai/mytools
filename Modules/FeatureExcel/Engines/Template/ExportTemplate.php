<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Template;

/**
 * 导出模板对象
 *
 * 定义Excel/CSV导出的完整模板配置，
 * 包含格式、工作表名称、表头、字段映射、样式、文件名模式及存储策略等信息
 */
class ExportTemplate
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
     * 工作表名称
     */
    protected string $sheetName = 'Sheet1';

    /**
     * 表头行
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * 字段映射
     *
     * @var array<string, FieldMapping>
     */
    protected array $fields = [];

    /**
     * 样式配置
     *
     * @var array
     */
    protected array $styles = [];

    /**
     * 文件名模式
     */
    protected string $fileNamePattern = '';

    /**
     * 储存名字
     */
    protected string $storeName = '';

    /**
     * 存储策略
     */
    protected bool $persist = false;

    /**
     * 缓存TTL（秒）
     *
     * @var int|null
     */
    protected ?int $cacheTtl = 86400;

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
     * 获取工作表名称
     *
     * @return string 工作表名称
     */
    public function getSheetName(): string
    {
        return $this->sheetName;
    }

    /**
     * 设置工作表名称
     *
     * @param string $sheetName 工作表名称
     * @return static 支持链式调用
     */
    public function setSheetName(string $sheetName): static
    {
        $this->sheetName = $sheetName;
        return $this;
    }

    /**
     * 获取表头行
     *
     * @return array 表头行
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 设置表头行
     *
     * @param array $headers 表头行
     * @return static 支持链式调用
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;
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

    /**
     * 获取样式配置
     *
     * @return array 样式配置
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * 设置样式配置
     *
     * @param array $styles 样式配置
     * @return static 支持链式调用
     */
    public function setStyles(array $styles): static
    {
        $this->styles = $styles;
        return $this;
    }

    /**
     * 获取文件名模式
     *
     * @return string 文件名模式
     */
    public function getFileNamePattern(): string
    {
        return $this->fileNamePattern;
    }

    /**
     * 设置文件名模式
     *
     * @param string $fileNamePattern 文件名模式
     * @return static 支持链式调用
     */
    public function setFileNamePattern(string $fileNamePattern): static
    {
        $this->fileNamePattern = $fileNamePattern;
        return $this;
    }

    /**
     * 获取储存名字
     *
     * @return string 储存名字
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }

    /**
     * 设置储存名字
     *
     * @param string $storeName 储存名字
     * @return static 支持链式调用
     */
    public function setStoreName(string $storeName): static
    {
        $this->storeName = $storeName;
        return $this;
    }

    /**
     * 判断是否启用存储策略
     *
     * @return bool 是否启用存储策略
     */
    public function isPersist(): bool
    {
        return $this->persist;
    }

    /**
     * 设置存储策略
     *
     * @param bool $persist 是否启用存储策略
     * @return static 支持链式调用
     */
    public function setPersist(bool $persist): static
    {
        $this->persist = $persist;
        return $this;
    }

    /**
     * 获取缓存TTL
     *
     * @return int|null 缓存TTL（秒），null表示不缓存
     */
    public function getCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    /**
     * 设置缓存TTL
     *
     * @param int|null $cacheTtl 缓存TTL（秒），null表示不缓存
     * @return static 支持链式调用
     */
    public function setCacheTtl(?int $cacheTtl): static
    {
        $this->cacheTtl = $cacheTtl;
        return $this;
    }

    /**
     * 生成安全文件名
     *
     * 根据文件名模式和变量生成安全的导出文件名。
     * - 变量值过滤非法字符（只允许字母数字下划线连字符），限制长度50
     * - 移除未替换的占位符 {xxx}
     * - 清理多余分隔符
     * - 确保有扩展名（xlsx 或 csv）
     *
     * @param array $variables 文件名变量键值对
     * @return string 生成的安全文件名
     */
    public function generateFileName(array $variables = []): string
    {
        $ext = $this->format === 'csv' ? 'csv' : 'xlsx';

        // 无模式时使用默认文件名
        if ($this->fileNamePattern === '') {
            return 'export_' . date('Ymd_His') . '.' . $ext;
        }

        $fileName = $this->fileNamePattern;

        // 变量替换
        foreach ($variables as $key => $value) {
            // 过滤非法字符，只允许字母数字下划线连字符
            $safeValue = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $value);
            // 限制长度50
            $safeValue = substr($safeValue, 0, 50);
            $fileName = str_replace('{' . $key . '}', $safeValue, $fileName);
        }

        // 移除未替换的占位符 {xxx}
        $fileName = preg_replace('/\{[a-zA-Z0-9_]+\}/', '', $fileName);

        // 清理多余分隔符（连续的_或-替换为单个）
        $fileName = preg_replace('/[_\-]{2,}/', '_', $fileName);

        // 清理首尾分隔符
        $fileName = trim($fileName, '_-');

        // 确保有扩展名
        if (!preg_match('/\.(xlsx|csv)$/', $fileName)) {
            $fileName .= '.' . $ext;
        }

        return $fileName;
    }
}
