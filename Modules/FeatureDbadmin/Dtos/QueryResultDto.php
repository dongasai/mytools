<?php

namespace Modules\FeatureDbadmin\Dtos;

/**
 * 查询结果数据传输对象
 *
 * 封装 SQL 查询执行的完整结果，包括数据、元数据和执行统计
 */
class QueryResultDto
{
    /**
     * @param bool $success 查询是否成功执行
     * @param string $message 执行结果消息（成功提示或错误信息）
     * @param array<array> $data 查询返回的数据行数组
     * @param int $rowCount 影响或返回的行数
     * @param int $executionTime 执行耗时（毫秒）
     * @param array<string> $columns 结果集的列名数组
     */
    public function __construct(
        public bool $success = true,
        public string $message = '',
        public array $data = [],
        public int $rowCount = 0,
        public int $executionTime = 0,
        public array $columns = [],
    ) {}

    /**
     * 转换为数组
     *
     * 将查询结果 DTO 转换为关联数组，适用于 API 响应
     *
     * @return array 查询结果的数组表示
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'row_count' => $this->rowCount,
            'execution_time' => $this->executionTime,
            'columns' => $this->columns,
        ];
    }

    /**
     * 转换为 JSON 字符串
     *
     * 将查询结果序列化为 JSON 格式字符串
     *
     * @return string JSON 格式的查询结果
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
