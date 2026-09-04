<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SSH密钥对表迁移
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
return new class extends Migration
{
    /**
     * 表名
     */
    private string $tableName = 'fssh_key_pairs';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('密钥名称');
            $table->enum('type', ['rsa', 'ed25519', 'ecdsa'])->default('ed25519')->comment('密钥类型');
            $table->text('public_key')->comment('公钥');
            $table->text('private_key')->comment('私钥（加密存储）');
            $table->text('passphrase')->nullable()->comment('私钥密码（加密存储）');
            $table->string('fingerprint', 100)->comment('指纹');
            $table->string('comment', 255)->nullable()->comment('注释');
            $table->text('description')->nullable()->comment('描述');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('name', 'idx_name');
            $table->index('fingerprint', 'idx_fingerprint');
        });

        // 表注释
        DB::statement("ALTER TABLE `{$this->tableName}` COMMENT = 'SSH密钥对表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};