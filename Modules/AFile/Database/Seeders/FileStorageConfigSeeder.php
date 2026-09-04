<?php

namespace Modules\AFile\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AFile\Enums\STORAGE_DRIVER;
use Modules\AFile\Models\FileStorageConfig;

/**
 * 存储配置Seeder
 *
 * 初始化文件存储配置，创建local默认驱动
 */
class FileStorageConfigSeeder extends Seeder
{
    /**
     * 运行Seeder
     *
     * @return void
     */
    public function run(): void
    {
        // 清理现有数据
        FileStorageConfig::query()->delete();

        // 获取当前环境
        $env = app()->environment();

        // 创建local驱动配置
        $localConfig = FileStorageConfig::create([
            'name' => STORAGE_DRIVER::LOCAL->value,
            'driver' => STORAGE_DRIVER::LOCAL->value,
            'config' => [
                'root' => storage_path('app'),
                'throw' => true,
            ],
            'description' => STORAGE_DRIVER::LOCAL->description(),
            'is_default' => true,
            'is_temp' => true,
            'status' => true,
            'env' => $env,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $this->command->info("创建存储配置成功:");
        $this->command->info("- 名称: {$localConfig->name}");
        $this->command->info("- 驱动: {$localConfig->driver}");
        $this->command->info("- 环境: {$localConfig->env}");
        $this->command->info("- 默认存储: " . ($localConfig->is_default ? '是' : '否'));
        $this->command->info("- 临时存储: " . ($localConfig->is_temp ? '是' : '否'));

        // 创建OSS驱动配置（配置项暂留空，需后续补充）
        $ossConfig = FileStorageConfig::create([
            'name' => STORAGE_DRIVER::OSS->value,
            'driver' => STORAGE_DRIVER::OSS->value,
            'config' => [
                'access_key_id'     => env('ALIYUN_ACCESS_KEY_ID', ''),      // OSS 驱动要求的键名
                'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET', ''), // OSS 驱动要求的键名
                'bucket'            => env('ALIYUN_PROJECT_NAME', ''),      // Bucket 名称
                'endpoint'          => 'oss-cn-hangzhou.aliyuncs.com',      // OSS endpoint
                'is_cname'          => false,                                // 是否使用自定义域名
                'use_ssl'           => true,                                 // 是否使用 HTTPS
                'signatureVersion'  => 'v1',                                 // 签名版本
                'region'            => '',                                   // 区域（v4签名需要）
                'options'           => [],                                   // 全局配置参数
                'macros'            => [],                                   // 自定义宏
            ],
            'description' => STORAGE_DRIVER::OSS->description(),
            'is_default' => false,
            'is_temp' => false,
            'status' => false,  // 配置未完成，暂时禁用
            'env' => $env,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $this->command->info("");
        $this->command->info("创建OSS配置成功:");
        $this->command->info("- 名称: {$ossConfig->name}");
        $this->command->info("- 驱动: {$ossConfig->driver}");
        $this->command->info("- 状态: " . ($ossConfig->status ? '启用' : '禁用（配置待补充）'));
        $this->command->info("- AccessKey ID: " . ($ossConfig->config['access_key_id'] ?: '空'));
        $this->command->info("- Bucket: " . ($ossConfig->config['bucket'] ?: '空'));
        $this->command->warn("⚠️ OSS配置未完成，请在DcatAdmin后台补充配置信息");

        // 统计
        $count = FileStorageConfig::count();
        $this->command->info("");
        $this->command->info("存储配置总数: {$count}");
    }

    /**
     * 清理数据（逆操作）
     *
     * @return void
     */
    public function cleanup(): void
    {
        FileStorageConfig::query()->delete();
        $this->command->info('已清理所有存储配置数据');
    }
}