<?php

namespace Modules\AFile\Console;

use Illuminate\Console\Command;
use Modules\AFile\Services\StorageConfigService;

/**
 * 测试存储连接命令
 *
 * 通过配置ID测试存储驱动连接
 */
class TestStorageCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'storage:test {id : 存储配置ID}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '测试存储配置连接';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');

        $this->info("正在测试存储配置 ID: {$id}");

        $service = new StorageConfigService;
        $result = $service->testConnectionById((int) $id);

        if ($result['success']) {
            $this->info("✓ {$result['message']}");
            return Command::SUCCESS;
        }

        $this->error("✗ {$result['message']}");
        return Command::FAILURE;
    }
}