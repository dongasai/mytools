<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Template;

/**
 * 字段映射对象
 *
 * 对应模板配置中单个字段的完整定义，
 * 包含列位置、数据类型、验证规则、转换方式等完整映射信息
 */
class FieldMapping
{
    /**
     * Excel列号（A, B, C...）
     */
    protected string $column = '';

    /**
     * 列名（用于表头和错误提示）
     */
    protected string $name = '';

    /**
     * 数据类型：integer/float/string/date/datetime/boolean
     */
    protected string $type = 'string';

    /**
     * 是否必填
     */
    protected bool $required = false;

    /**
     * 验证规则（inhere/php-validate 格式）
     */
    protected array $validation = [];

    /**
     * 数据转换：int/float/trim/datetime:format/date:format
     */
    protected string $transform = '';

    /**
     * 输出格式（仅导出）：number:N / date:format
     */
    protected string $format = '';

    /**
     * 允许为空
     */
    protected bool $nullable = false;

    /**
     * 默认值
     */
    protected mixed $default = null;

    /**
     * 源时区
     */
    protected string $timezoneFrom = 'Asia/Shanghai';

    /**
     * 目标时区
     */
    protected string $timezoneTo = 'UTC';

    /**
     * 流式构建器：创建字段映射
     *
     * @param  string  $column  Excel列号（A, B, C...）
     * @param  string  $type  数据类型：integer/float/string/date/datetime/boolean
     * @return static 新的字段映射实例
     */
    public static function make(string $column, string $type = 'string'): static
    {
        $mapping = new static;
        $mapping->column = $column;
        $mapping->type = $type;

        return $mapping;
    }

    /**
     * 流式设置列名
     *
     * @param  string  $name  列名（用于表头和错误提示）
     * @return static 支持链式调用
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 流式设置数据类型
     *
     * @param  string  $type  数据类型：integer/float/string/date/datetime/boolean
     * @return static 支持链式调用
     */
    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 流式设置是否必填
     *
     * @param  bool  $isRequired  是否必填
     * @return static 支持链式调用
     */
    public function required(bool $isRequired = true): static
    {
        $this->required = $isRequired;

        return $this;
    }

    /**
     * 流式设置验证规则
     *
     * @param  array  $rules  inhere/php-validate 格式的验证规则
     * @return static 支持链式调用
     */
    public function validation(array $rules): static
    {
        $this->validation = $rules;

        return $this;
    }

    /**
     * 流式设置数据转换方式
     *
     * @param  string  $transform  数据转换：int/float/trim/datetime:format/date:format
     * @return static 支持链式调用
     */
    public function transform(string $transform): static
    {
        $this->transform = $transform;

        return $this;
    }

    /**
     * 流式设置输出格式
     *
     * @param  string  $format  输出格式（仅导出）：number:N / date:format
     * @return static 支持链式调用
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * 流式设置是否允许为空
     *
     * @param  bool  $isNullable  是否允许为空
     * @return static 支持链式调用
     */
    public function nullable(bool $isNullable = true): static
    {
        $this->nullable = $isNullable;

        return $this;
    }

    /**
     * 流式设置默认值
     *
     * @param  mixed  $default  默认值
     * @return static 支持链式调用
     */
    public function defaultValue(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * 流式设置源时区和目标时区
     *
     * @param  string  $from  源时区
     * @param  string  $to  目标时区
     * @return static 支持链式调用
     */
    public function timezone(string $from, string $to): static
    {
        $this->timezoneFrom = $from;
        $this->timezoneTo = $to;

        return $this;
    }

    /**
     * 从配置数组创建字段映射
     *
     * @param  array  $config  字段配置数组
     * @return static 新的字段映射实例
     */
    public static function fromArray(array $config): static
    {
        $mapping = new static;
        $mapping->column = $config['column'] ?? '';
        $mapping->name = $config['name'] ?? '';
        $mapping->type = $config['type'] ?? 'string';
        $mapping->required = $config['required'] ?? false;
        $mapping->validation = $config['validation'] ?? [];
        $mapping->transform = $config['transform'] ?? '';
        $mapping->format = $config['format'] ?? '';
        $mapping->nullable = $config['nullable'] ?? false;
        $mapping->default = $config['default'] ?? null;
        $mapping->timezoneFrom = $config['timezone_from'] ?? 'Asia/Shanghai';
        $mapping->timezoneTo = $config['timezone_to'] ?? 'UTC';

        return $mapping;
    }

    /**
     * 获取Excel列号
     *
     * @return string 列号（A, B, C...）
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * 设置Excel列号
     *
     * @param  string  $column  列号（A, B, C...）
     * @return static 支持链式调用
     */
    public function setColumn(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    /**
     * 获取列名
     *
     * @return string 列名（用于表头和错误提示）
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 设置列名
     *
     * @param  string  $name  列名（用于表头和错误提示）
     * @return static 支持链式调用
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 获取数据类型
     *
     * @return string 数据类型：integer/float/string/date/datetime/boolean
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * 设置数据类型
     *
     * @param  string  $type  数据类型：integer/float/string/date/datetime/boolean
     * @return static 支持链式调用
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 判断是否必填
     *
     * @return bool 是否必填
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * 设置是否必填
     *
     * @param  bool  $required  是否必填
     * @return static 支持链式调用
     */
    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }

    /**
     * 获取验证规则
     *
     * @return array inhere/php-validate 格式的验证规则
     */
    public function getValidation(): array
    {
        return $this->validation;
    }

    /**
     * 设置验证规则
     *
     * @param  array  $validation  inhere/php-validate 格式的验证规则
     * @return static 支持链式调用
     */
    public function setValidation(array $validation): static
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * 获取数据转换方式
     *
     * @return string 数据转换：int/float/trim/datetime:format/date:format
     */
    public function getTransform(): string
    {
        return $this->transform;
    }

    /**
     * 设置数据转换方式
     *
     * @param  string  $transform  数据转换：int/float/trim/datetime:format/date:format
     * @return static 支持链式调用
     */
    public function setTransform(string $transform): static
    {
        $this->transform = $transform;

        return $this;
    }

    /**
     * 获取输出格式
     *
     * @return string 输出格式（仅导出）：number:N / date:format
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * 设置输出格式
     *
     * @param  string  $format  输出格式（仅导出）：number:N / date:format
     * @return static 支持链式调用
     */
    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * 判断是否允许为空
     *
     * @return bool 是否允许为空
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * 设置是否允许为空
     *
     * @param  bool  $nullable  是否允许为空
     * @return static 支持链式调用
     */
    public function setNullable(bool $nullable): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * 获取默认值
     *
     * @return mixed 默认值
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * 设置默认值
     *
     * @param  mixed  $default  默认值
     * @return static 支持链式调用
     */
    public function setDefault(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * 获取源时区
     *
     * @return string 源时区
     */
    public function getTimezoneFrom(): string
    {
        return $this->timezoneFrom;
    }

    /**
     * 设置源时区
     *
     * @param  string  $timezoneFrom  源时区
     * @return static 支持链式调用
     */
    public function setTimezoneFrom(string $timezoneFrom): static
    {
        $this->timezoneFrom = $timezoneFrom;

        return $this;
    }

    /**
     * 获取目标时区
     *
     * @return string 目标时区
     */
    public function getTimezoneTo(): string
    {
        return $this->timezoneTo;
    }

    /**
     * 设置目标时区
     *
     * @param  string  $timezoneTo  目标时区
     * @return static 支持链式调用
     */
    public function setTimezoneTo(string $timezoneTo): static
    {
        $this->timezoneTo = $timezoneTo;

        return $this;
    }
}
