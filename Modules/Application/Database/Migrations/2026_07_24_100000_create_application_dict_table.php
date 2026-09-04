<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建通用字典表:
     * application_dict - 通用字典表(用于存储各类字典数据)
     */
    public function up(): void
    {
        Schema::create('application_dict', function (Blueprint $table) {
            $table->id()->comment('字典ID');
            $table->string('dict_type', 32)->comment('字典类型');
            $table->string('dict_label', 100)->comment('字典标签(显示文本)');
            $table->string('dict_value', 100)->comment('字典值');
            $table->integer('dict_sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态(1=启用 0=禁用)');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->timestamps();

            $table->index('dict_type', 'idx_dict_type');
            $table->index('status', 'idx_dict_status');
            $table->unique(['dict_type', 'dict_value'], 'uk_type_value');
            $table->comment('通用字典表');
        });

        // 添加表注释(MySQL) - 仅在 MySQL 环境下执行
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `application_dict` COMMENT '通用字典表'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_dict');
    }
};