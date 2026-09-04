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
        Schema::create('fsms_code', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20)->comment('手机号');
            $table->string('token', 100)->comment('令牌');
            $table->string('type', 50)->comment('验证码类型');
            $table->string('code_value', 10)->comment('验证码值');
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['mobile', 'type']);
            $table->index('token');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsms_code');
    }
};
