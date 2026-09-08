<?php

namespace Modules\FeatureDbadmin\Dtos;

/**
 * 索引信息数据传输对象
 *
 * 封装数据库表的索引结构信息
 */
class IndexInfoDto
{
    /**
     * @param string $name 索引名称
     * @param string $type 索引类型（PRIMARY, UNIQUE, INDEX, FULLTEXT）
     * @param array $columns 索引列数组
     * @param string|null $algorithm 索引算法
     * @param string|null $comment 索引注释
     * @param int|null $cardinality 基数
     */
    public function __construct(
        public string $name,
        public string $type,
        public array $columns,
        public ?string $algorithm = null,
        public ?string $comment = null,
        public ?int $cardinality = null,
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
            'columns' => $this->columns,
            'algorithm' => $this->algorithm,
            'comment' => $this->comment,
            'cardinality' => $this->cardinality,
        ];
    }
}
