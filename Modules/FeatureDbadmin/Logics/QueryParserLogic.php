<?php

namespace Modules\FeatureDbadmin\Logics;

/**
 * SQL 解析逻辑
 *
 * 提供解析 SQL 语句、提取信息等功能
 */
class QueryParserLogic
{
    /**
     * 提取第一个关键字
     *
     * 去除注释和空白，返回第一个 SQL 关键字（大写）
     *
     * @param string $sql SQL 语句
     * @return string 第一个关键字（大写）
     */
    public static function extractFirstKeyword(string $sql): string
    {
        // 去除单行注释
        $sql = preg_replace('/--[^\n]*\n/', ' ', $sql);
        // 去除多行注释
        $sql = preg_replace('/\/\*.*?\*\//s', ' ', $sql);
        // 去除多余空白
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = trim($sql);

        // 提取第一个单词
        $words = explode(' ', $sql);
        $firstWord = strtoupper($words[0] ?? '');

        // 处理 WITH (CTE) 的情况
        if ($firstWord === 'WITH') {
            return 'SELECT';
        }

        return $firstWord;
    }

    /**
     * 判断是否读查询
     *
     * SELECT、SHOW、DESCRIBE、EXPLAIN 为读查询
     * 其他为写查询
     *
     * @param string $keyword SQL 关键字
     * @return bool 是否为读查询
     */
    public static function isReadQuery(string $keyword): bool
    {
        $readKeywords = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'DESC'];

        return in_array(strtoupper($keyword), $readKeywords, true);
    }

    /**
     * 判断是否危险查询
     *
     * 危险关键字: DROP、TRUNCATE、GRANT、REVOKE、ALTER USER
     *
     * @param string $sql SQL 语句
     * @return bool 是否为危险查询
     */
    public static function isDangerousQuery(string $sql): bool
    {
        // 去除注释
        $cleanSql = preg_replace('/--[^\n]*\n/', ' ', $sql);
        $cleanSql = preg_replace('/\/\*.*?\*\//s', ' ', $cleanSql);
        $cleanSql = preg_replace('/\s+/', ' ', $cleanSql);
        $cleanSql = strtoupper($cleanSql);

        $dangerousPatterns = [
            '\bDROP\s+(DATABASE|SCHEMA|TABLE)\b',
            '\bTRUNCATE\s+TABLE\b',
            '\bTRUNCATE\s+',
            '\bGRANT\b',
            '\bREVOKE\b',
            '\bALTER\s+USER\b',
            '\bDROP\s+USER\b',
            '\bDELETE\s+FROM\b',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $cleanSql)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 提取表名
     *
     * 从 FROM、JOIN、INTO、UPDATE 后提取表名
     *
     * @param string $sql SQL 语句
     * @return array 表名数组
     */
    public static function extractTableNames(string $sql): array
    {
        $tables = [];

        // 去除注释
        $cleanSql = preg_replace('/--[^\n]*\n/', ' ', $sql);
        $cleanSql = preg_replace('/\/\*.*?\*\//s', ' ', $cleanSql);
        $cleanSql = preg_replace('/\s+/', ' ', $cleanSql);

        // 提取 FROM 后的表名
        if (preg_match_all('/\bFROM\s+[`"]?(\w+)[`"]?/i', $cleanSql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // 提取 JOIN 后的表名
        if (preg_match_all('/\bJOIN\s+[`"]?(\w+)[`"]?/i', $cleanSql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // 提取 INTO 后的表名
        if (preg_match_all('/\bINTO\s+[`"]?(\w+)[`"]?/i', $cleanSql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // 提取 UPDATE 后的表名
        if (preg_match_all('/\bUPDATE\s+[`"]?(\w+)[`"]?/i', $cleanSql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // 提取 INSERT INTO 后的表名
        if (preg_match_all('/\bINSERT\s+INTO\s+[`"]?(\w+)[`"]?/i', $cleanSql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // 提取 DELETE FROM 后的表名
        if (preg_match_all('/\bDELETE\s+FROM\s+[`"]?(\w+)[`"]?/i', $cleanSql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // 去重并返回
        return array_unique($tables);
    }
}
