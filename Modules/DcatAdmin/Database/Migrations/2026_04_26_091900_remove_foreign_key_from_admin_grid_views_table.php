<?php

declare(strict_types=1);

namespace Modules\DcatAdmin\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 移除 admin_grid_views 表的外键约束
 *
 * 背景：外键约束在高并发场景下可能导致死锁问题
 * 解决方案：移除外键约束，改为应用层处理数据一致性
 */
return new class() extends Migration
{
    /**
     * 迁移文件名称
     */
    protected string $tableName = 'admin_grid_views';

    /**
     * 执行迁移
     */
    public function up(): void
    {
        try {
            Schema::table($this->tableName, function (Blueprint $table): void {
                // 删除外键约束（如果存在）
                $table->dropForeign(['admin_id']);
            });
        } catch (\Exception $e) {
            // 外键不存在时忽略错误
            if (!str_contains($e->getMessage(), "Can't DROP")) {
                throw $e;
            }
        }

        // 注意：保留索引，不删除
        // 索引对查询性能有重要作用，只是移除外键约束
    }

    /**
     * 回滚迁移
     *
     * 注意：不恢复外键约束（遵循"避免使用外键"设计原则）
     */
    public function down(): void
    {
        // 不恢复外键约束（遵循数据库设计原则）
    }
};
