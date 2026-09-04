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
        Schema::create('fsms_config', function (Blueprint $table) {
            $table->id();
            $table->string('driver', 100)->comment('驱动');
            $table->boolean('is_open')->default(false)->comment('是否开启');
            $table->string('title', 200)->comment('标题');
            $table->string('desc', 500)->nullable()->comment('描述');
            $table->integer('type')->comment('类型');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('group', 100)->comment('分组');
            $table->timestamps();

            $table->index(['driver', 'group']);
            $table->index('is_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsms_config');
    }
};
