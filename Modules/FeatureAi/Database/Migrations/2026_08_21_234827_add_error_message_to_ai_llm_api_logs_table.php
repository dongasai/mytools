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
        Schema::table('ai_llm_api_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_llm_api_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('error_type')->comment('错误详细信息');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_llm_api_logs', function (Blueprint $table) {
            if (Schema::hasColumn('ai_llm_api_logs', 'error_message')) {
                $table->dropColumn('error_message');
            }
        });
    }
};
