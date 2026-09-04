<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 重命名表：feature_ai_llm_api_logs → ai_llm_api_logs
     * 统一表名前缀为 ai_
     */
    public function up(): void
    {
        // 检查旧表是否存在
        if (Schema::hasTable('feature_ai_llm_api_logs')) {
            // 检查新表是否不存在
            if (!Schema::hasTable('ai_llm_api_logs')) {
                Schema::rename('feature_ai_llm_api_logs', 'ai_llm_api_logs');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 检查新表是否存在
        if (Schema::hasTable('ai_llm_api_logs')) {
            // 检查旧表是否不存在
            if (!Schema::hasTable('feature_ai_llm_api_logs')) {
                Schema::rename('ai_llm_api_logs', 'feature_ai_llm_api_logs');
            }
        }
    }
};