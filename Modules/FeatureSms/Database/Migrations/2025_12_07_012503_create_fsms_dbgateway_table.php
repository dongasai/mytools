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
        Schema::create('fsms_dbgateway', function (Blueprint $table) {
            $table->id();
            $table->string('tpl_id', 100)->comment('模板ID');
            $table->string('tpl_value', 500)->nullable()->comment('模板值');
            $table->string('key', 100)->comment('网关密钥');
            $table->string('universal_number', 20)->comment('通用号码');
            $table->string('mobile', 20)->comment('手机号');
            $table->text('content')->comment('短信内容');
            $table->string('idd_code', 10)->comment('国际区号');
            $table->string('zero_prefixed_number', 20)->comment('零前缀号码');
            $table->timestamps();
            $table->softDeletes();

            $table->index('mobile');
            $table->index(['tpl_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsms_dbgateway');
    }
};
