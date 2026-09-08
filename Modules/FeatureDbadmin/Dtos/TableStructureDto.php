<?php

namespace Modules\FeatureDbadmin\Dtos;

/**
 * 表结构数据传输对象
 *
 * 封装完整的表结构信息，包括列、索引、外键等
 */
class TableStructureDto
{
    /**
     * @param string $tableName 表名
     * @param string|null $engine 存储引擎
     * @param string|null $charset 字符集
     * @param string|null $collation 排序规则
     * @param int|null $rowCount 行数
     * @param string|null $tableSize 表大小
     * @param string|null $comment 表注释
     * @param string|null $createTime 创建时间
     * @param string|null $updateTime 更新时间
     * @param array<ColumnInfoDto> $columns 列信息数组
     * @param array<IndexInfoDto> $indexes 索引信息数组
     * @param array $foreignKeys 外键信息数组
     * @param string|null $createSql 创建表SQL
     */
    public function __construct(
        public string $tableName,
        public ?string $engine = null,
        public ?string $charset = null,
        public ?string $collation = null,
        public ?int $rowCount = null,
        public ?string $tableSize = null,
        public ?string $comment = null,
        public ?string $createTime = null,
        public ?string $updateTime = null,
        public array $columns = [],
        public array $indexes = [],
        public array $foreignKeys = [],
        public ?string $createSql = null,
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
            'table_name' => $this->tableName,
            'engine' => $this->engine,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'row_count' => $this->rowCount,
            'table_size' => $this->tableSize,
            'comment' => $this->comment,
            'create_time' => $this->createTime,
            'update_time' => $this->updateTime,
            'columns' => array_map(fn ($col) => $col->toArray(), $this->columns),
            'indexes' => array_map(fn ($idx) => $idx->toArray(), $this->indexes),
            'foreign_keys' => $this->foreignKeys,
            'create_sql' => $this->createSql,
        ];
    }
}
