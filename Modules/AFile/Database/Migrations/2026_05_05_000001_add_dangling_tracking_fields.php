<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 为 file_files 表添加字段（幂等）
        $this->addTimestampColumn('file_files', 'used_at', '使用时间', 'status');
        $this->addTimestampColumn('file_files', 'dangling_at', '悬空时间', 'used_at');

        // 添加索引（幂等）
        $this->addIndex('file_files', ['status', 'created_at'], 'idx_status_created_at');
        $this->addIndex('file_files', ['status', 'dangling_at'], 'idx_status_dangling_at');

        // 为 file_imgs 表添加字段（幂等）
        $this->addTimestampColumn('file_imgs', 'used_at', '使用时间', 'status');
        $this->addTimestampColumn('file_imgs', 'dangling_at', '悬空时间', 'used_at');

        // 添加索引（幂等）
        $this->addIndex('file_imgs', ['status', 'created_at'], 'idx_status_created_at');
        $this->addIndex('file_imgs', ['status', 'dangling_at'], 'idx_status_dangling_at');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚 file_files 表
        Schema::table('file_files', function (Blueprint $table) {
            if (Schema::hasColumn('file_files', 'used_at')) {
                $table->dropColumn('used_at');
            }
            if (Schema::hasColumn('file_files', 'dangling_at')) {
                $table->dropColumn('dangling_at');
            }
        });
        $this->dropIndex('file_files', 'idx_status_created_at');
        $this->dropIndex('file_files', 'idx_status_dangling_at');

        // 回滚 file_imgs 表
        Schema::table('file_imgs', function (Blueprint $table) {
            if (Schema::hasColumn('file_imgs', 'used_at')) {
                $table->dropColumn('used_at');
            }
            if (Schema::hasColumn('file_imgs', 'dangling_at')) {
                $table->dropColumn('dangling_at');
            }
        });
        $this->dropIndex('file_imgs', 'idx_status_created_at');
        $this->dropIndex('file_imgs', 'idx_status_dangling_at');
    }

    /**
     * 添加时间戳字段（幂等：字段已存在则跳过）
     *
     * @param string $table 表名
     * @param string $column 字段名
     * @param string $comment 注释
     * @param string $after 在某字段之后
     */
    private function addTimestampColumn(string $table, string $column, string $comment, string $after): void
    {
        if (!Schema::hasColumn($table, $column)) {
            Schema::table($table, function (Blueprint $table) use ($column, $comment, $after) {
                $table->timestamp($column)->nullable()->comment($comment)->after($after);
            });
        }
    }

    /**
     * 添加索引（幂等：索引已存在则跳过）
     *
     * @param string $table 表名
     * @param array $columns 字段数组
     * @param string $indexName 索引名
     */
    private function addIndex(string $table, array $columns, string $indexName): void
    {
        if (!$this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * 删除索引（幂等：索引不存在则跳过）
     *
     * @param string $table 表名
     * @param string $indexName 索引名
     */
    private function dropIndex(string $table, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }

    /**
     * 检查索引是否存在（兼容 SQLite 和 MySQL）
     *
     * @param string $table 表名
     * @param string $indexName 索引名
     * @return bool
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return $connection->table('sqlite_master')
                ->where('type', 'index')
                ->where('name', $indexName)
                ->exists();
        }

        // MySQL 及其他数据库
        return $connection->table('information_schema.statistics')
            ->where('table_schema', $connection->getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
