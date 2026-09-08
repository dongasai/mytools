<?php

namespace Modules\FeatureDbadmin\Dtos;

/**
 * 列信息数据传输对象
 *
 * 封装数据库表的列结构信息
 */
class ColumnInfoDto
{
    /**
     * @param string $name 列名
     * @param string $type 数据类型
     * @param string|null $collation 排序规则
     * @param string|null $nullable 是否可为空
     * @param string|null $default 默认值
     * @param string|null $extra 额外信息
     * @param string|null $comment 列注释
     * @param int|null $maxLength 最大长度
     * @param int|null $precision 精度
     * @param int|null $scale 小数位
     * @param bool $isPrimaryKey 是否主键
     * @param bool $isAutoIncrement 是否自增
     * @param bool $isUnique 是否唯一
     * @param bool $isIndex 是否有索引
     */
    public function __construct(
        public string $name,
        public string $type,
        public ?string $collation = null,
        public ?string $nullable = null,
        public ?string $default = null,
        public ?string $extra = null,
        public ?string $comment = null,
        public ?int $maxLength = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public bool $isPrimaryKey = false,
        public bool $isAutoIncrement = false,
        public bool $isUnique = false,
        public bool $isIndex = false,
    ) {
    }

    /**
     * 转换为数组
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'collation' => $this->collation,
            'nullable' => $this->nullable,
            'default' => $this->default,
            'extra' => $this->extra,
            'comment' => $this->comment,
            'max_length' => $this->maxLength,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'is_primary_key' => $this->isPrimaryKey,
            'is_auto_increment' => $this->isAutoIncrement,
            'is_unique' => $this->isUnique,
            'is_index' => $this->isIndex,
        ];
    }
}
