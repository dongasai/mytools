<?php

namespace Modules\AFile\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Models\FileImg;
use Modules\AFile\Models\FileStorageConfig;
use Modules\AFile\Models\FileStorageConfigHistory;
use Modules\AFile\Models\FileTemplate;

/**
 * AFile模块主Seeder
 *
 * 负责协调模块所有Seeder的执行和数据清理
 */
class AFileDatabaseSeeder extends Seeder
{
    /**
     * 运行Seeder
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('开始填充AFile模块数据...');

        // 1. 存储配置（最先执行，其他表依赖此配置）
        $this->call(FileStorageConfigSeeder::class);

        // 2. 其他Seeder（如有需要）
        // $this->call(FileTemplateSeeder::class);

        $this->command->info('AFile模块数据填充完成！');
        $this->showStatistics();
    }

    /**
     * 清理所有数据
     *
     * @return void
     */
    public function cleanup(): void
    {
        $this->command->info('开始清理AFile模块数据...');

        // 按依赖关系逆向清理
        FileTemplate::query()->delete();
        FileStorageConfigHistory::query()->delete();
        FileStorageConfig::query()->delete();
        FileImg::query()->delete();
        FileFile::query()->delete();

        $this->command->info('AFile模块数据清理完成！');
        $this->showStatistics();
    }

    /**
     * 显示统计信息
     *
     * @return void
     */
    protected function showStatistics(): void
    {
        $stats = [
            'file_storage_configs' => FileStorageConfig::count(),
            'file_storage_config_histories' => FileStorageConfigHistory::count(),
            'file_files' => FileFile::count(),
            'file_imgs' => FileImg::count(),
            'file_template' => FileTemplate::count(),
        ];

        $this->command->info('数据统计:');
        foreach ($stats as $table => $count) {
            $this->command->info("- {$table}: {$count}");
        }
    }
}