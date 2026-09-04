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
        Schema::create('file_template', function (Blueprint $table) {
            $table->id();
            $table->string('unid', 500)->nullable()->comment('标识');
            $table->unsignedInteger('file_id')->nullable();
            $table->string('title', 500)->comment('模板标题');
            $table->string('desc', 500)->nullable()->comment('描述');  // desc是保留字，Laravel会自动处理
            $table->unsignedTinyInteger('status')->default(1)->nullable();
            $table->string('group', 100)->nullable()->comment('分组');  // group是保留字，Laravel会自动处理
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_template');
    }
};
