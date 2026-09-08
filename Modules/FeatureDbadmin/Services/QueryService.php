<?php

namespace Modules\FeatureDbadmin\Services;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Models\QueryHistory;
use Modules\FeatureDbadmin\Dtos\QueryResultDto;
use Modules\FeatureDbadmin\Enums\QueryType;
use Modules\FeatureDbadmin\Enums\QueryStatus;

/**
 * SQL 查询执行服务
 *
 * 执行 SQL 查询、验证查询、解析查询类型、保存历史
 * 所有方法均为静态方法
 */
class QueryService
{
    /**
     * 危险 SQL 关键字列表
     */
    private const DANGEROUS_KEYWORDS = [
        'DROP',
        'TRUNCATE',
        'GRANT',
        'REVOKE',
        'ALTER USER',
    ];

    /**
     * 执行 SQL 查询
     *
     * 调用 validateQuery() 验证 SQL
     * 调用 parseQueryType() 解析类型
     * 使用动态连接执行查询
     * 记录执行时间和行数
     * 调用 saveQueryHistory() 保存历史
     *
     * @param int $connectionId 连接ID
     * @param string $sql SQL 查询语句
     * @return QueryResultDto 查询结果 DTO
     */
    public static function executeQuery(int $connectionId, string $sql): QueryResultDto
    {
        // 验证 SQL 安全性
        if (!self::validateQuery($sql)) {
            $result = new QueryResultDto(
                success: false,
                message: 'SQL 包含危险关键字，执行被拒绝',
                data: [],
                rowCount: 0,
                executionTime: 0,
                columns: []
            );

            // 保存失败历史（用户ID为0表示未记录）
            self::saveQueryHistory(
                userId: 0,
                connectionId: $connectionId,
                sql: $sql,
                type: QueryType::SELECT,
                executionTime: 0,
                rowCount: 0,
                status: QueryStatus::FAILED,
                errorMessage: 'SQL 包含危险关键字'
            );

            return $result;
        }

        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return new QueryResultDto(
                success: false,
                message: '连接不存在',
                data: [],
                rowCount: 0,
                executionTime: 0,
                columns: []
            );
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        // 解析查询类型
        $queryType = self::parseQueryType($sql);

        // 记录开始时间
        $startTime = microtime(true);

        $data = [];
        $rowCount = 0;
        $columns = [];
        $status = QueryStatus::SUCCESS;
        $errorMessage = null;

        // 根据查询类型执行
        if ($queryType === QueryType::SELECT) {
            $data = DB::connection($connectionName)->select($sql);
            $rowCount = count($data);
            if (!empty($data)) {
                $columns = array_keys((array) $data[0]);
            }
        } else {
            $affected = DB::connection($connectionName)->statement($sql);
            $rowCount = $affected ? 1 : 0;
        }

        // 计算执行时间（毫秒）
        $executionTime = (int) round((microtime(true) - $startTime) * 1000);

        // 保存查询历史
        self::saveQueryHistory(
            userId: 0,
            connectionId: $connectionId,
            sql: $sql,
            type: $queryType,
            executionTime: $executionTime,
            rowCount: $rowCount,
            status: $status,
            errorMessage: $errorMessage
        );

        return new QueryResultDto(
            success: true,
            message: '查询执行成功',
            data: $data,
            rowCount: $rowCount,
            executionTime: $executionTime,
            columns: $columns
        );
    }

    /**
     * 验证查询安全性
     *
     * 禁止危险关键字：DROP、TRUNCATE、GRANT、REVOKE、ALTER USER
     *
     * @param string $sql SQL 查询语句
     * @return bool true=安全，false=危险
     */
    public static function validateQuery(string $sql): bool
    {
        $upperSql = strtoupper(trim($sql));

        foreach (self::DANGEROUS_KEYWORDS as $keyword) {
            // 检查关键字是否在 SQL 中（以单词边界匹配）
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            if (preg_match($pattern, $upperSql)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 解析查询类型
     *
     * 分析 SQL 第一个关键字
     *
     * @param string $sql SQL 查询语句
     * @return QueryType 查询类型枚举
     */
    public static function parseQueryType(string $sql): QueryType
    {
        $upperSql = strtoupper(trim($sql));
        $firstWord = strtok($upperSql, " \t\n\r\f\v(");

        return match ($firstWord) {
            'SELECT' => QueryType::SELECT,
            'INSERT' => QueryType::INSERT,
            'UPDATE' => QueryType::UPDATE,
            'DELETE' => QueryType::DELETE,
            default => QueryType::DDL,
        };
    }

    /**
     * 保存查询历史
     *
     * 使用 QueryHistory 模型保存
     *
     * @param int $userId 用户ID
     * @param int $connectionId 连接ID
     * @param string $sql SQL 查询语句
     * @param QueryType $type 查询类型
     * @param int $executionTime 执行时间（毫秒）
     * @param int $rowCount 返回行数
     * @param QueryStatus $status 执行状态
     * @param string|null $errorMessage 错误信息
     * @return void
     */
    public static function saveQueryHistory(
        int $userId,
        int $connectionId,
        string $sql,
        QueryType $type,
        int $executionTime,
        int $rowCount,
        QueryStatus $status,
        ?string $errorMessage = null
    ): void {
        $connection = Connection::find($connectionId);
        $connectionName = $connection ? $connection->name : 'unknown';

        QueryHistory::record([
            'user_id' => $userId,
            'connection_name' => $connectionName,
            'sql_query' => $sql,
            'query_type' => $type->value,
            'execution_time' => $executionTime,
            'row_count' => $rowCount,
            'status' => $status->value,
            'error_message' => $errorMessage,
            'executed_at' => now(),
        ]);
    }
}
