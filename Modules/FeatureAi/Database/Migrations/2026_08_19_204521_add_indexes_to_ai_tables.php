<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 为FeatureAi模块AI相关表添加数据库索引
 *
 * 此迁移在以下表中添加常用查询字段的索引，提升查询性能：
 * - ai_provider_models: provider_id, model_type, is_active
 * - ai_images: provider_id, model_id, status, created_at
 * - ai_tests: provider_id, model_id, test_type, status, created_at
 * - ai_test_results: test_id, is_success, created_at
 *
 * @author AI Assistant
 * @date 2026-08-19
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * 执行迁移：添加索引
     *
     * @return void
     */
    public function up(): void
    {
        // ai_provider_models 表索引
        Schema::table('ai_provider_models', function (Blueprint $table): void {
            $table->index('provider_id', 'idx_provider_models_provider_id');
            $table->index('model_type', 'idx_provider_models_model_type');
            $table->index('is_active', 'idx_provider_models_is_active');
        });

        // ai_images 表索引
        Schema::table('ai_images', function (Blueprint $table): void {
            $table->index('provider_id', 'idx_images_provider_id');
            $table->index('model_id', 'idx_images_model_id');
            $table->index('status', 'idx_images_status');
            $table->index('created_at', 'idx_images_created_at');
        });

        // ai_tests 表索引
        Schema::table('ai_tests', function (Blueprint $table): void {
            $table->index('provider_id', 'idx_tests_provider_id');
            $table->index('model_id', 'idx_tests_model_id');
            $table->index('test_type', 'idx_tests_test_type');
            $table->index('status', 'idx_tests_status');
            $table->index('created_at', 'idx_tests_created_at');
        });

        // ai_test_results 表索引
        Schema::table('ai_test_results', function (Blueprint $table): void {
            $table->index('test_id', 'idx_test_results_test_id');
            $table->index('is_success', 'idx_test_results_is_success');
            $table->index('created_at', 'idx_test_results_created_at');
        });
    }

    /**
     * 回滚迁移：删除索引
     *
     * @return void
     */
    public function down(): void
    {
        // ai_provider_models 表删除索引
        Schema::table('ai_provider_models', function (Blueprint $table): void {
            $table->dropIndex('idx_provider_models_provider_id');
            $table->dropIndex('idx_provider_models_model_type');
            $table->dropIndex('idx_provider_models_is_active');
        });

        // ai_images 表删除索引
        Schema::table('ai_images', function (Blueprint $table): void {
            $table->dropIndex('idx_images_provider_id');
            $table->dropIndex('idx_images_model_id');
            $table->dropIndex('idx_images_status');
            $table->dropIndex('idx_images_created_at');
        });

        // ai_tests 表删除索引
        Schema::table('ai_tests', function (Blueprint $table): void {
            $table->dropIndex('idx_tests_provider_id');
            $table->dropIndex('idx_tests_model_id');
            $table->dropIndex('idx_tests_test_type');
            $table->dropIndex('idx_tests_status');
            $table->dropIndex('idx_tests_created_at');
        });

        // ai_test_results 表删除索引
        Schema::table('ai_test_results', function (Blueprint $table): void {
            $table->dropIndex('idx_test_results_test_id');
            $table->dropIndex('idx_test_results_is_success');
            $table->dropIndex('idx_test_results_created_at');
        });
    }
};
