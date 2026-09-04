<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * module:create {name : 模块名称} {--namespace= : 命名空间(默认和模块名相同)}
 */
class CreateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:create {name : 模块名称} {--namespace= : 命名空间(默认和模块名相同)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '基于Demo5模块创建新模块';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moduleName = $this->argument('name');
        $namespace = $this->option('namespace') ?: $moduleName;

        // 验证模块名
        if (empty($moduleName)) {
            $this->error('模块名不能为空');

            return 1;
        }

        // 检查模块是否已存在
        if (is_dir(base_path("Modules/{$moduleName}"))) {
            $this->error("模块 {$moduleName} 已存在");

            return 1;
        }

        $this->info("开始创建模块: {$moduleName} (命名空间: {$namespace})");

        try {
            // 复制Demo5模块
            $this->copyModule($moduleName, $namespace);

            // 更新命名空间
            $this->updateNamespaces($moduleName, $namespace);

            // 重命名服务提供者文件
            $this->renameServiceProviderFile($moduleName, $namespace);

            // 更新配置文件
            $this->updateModuleConfig($moduleName, $namespace);

            $this->info("✅ 模块 {$moduleName} 创建成功！");
            $this->info('📁 路径: '.base_path("Modules/{$moduleName}"));
            $this->info("🔧 命名空间: Modules\\{$namespace}");
            $this->info('📋 别名: '.strtolower($moduleName));

        } catch (\Exception $e) {
            $this->error('❌ 创建模块失败: '.$e->getMessage());
            // 清理可能已创建的文件
            if (is_dir(base_path("Modules/{$moduleName}"))) {
                File::deleteDirectory(base_path("Modules/{$moduleName}"));
            }

            return 1;
        }

        return 0;
    }

    /**
     * 复制Demo5模块到新模块
     */
    private function copyModule(string $moduleName, string $namespace): void
    {
        $sourcePath = base_path('Modules/Demo5');
        $targetPath = base_path("Modules/{$moduleName}");

        if (! is_dir($sourcePath)) {
            throw new \Exception('Demo5模块不存在');
        }

        // 创建目标目录
        if (! File::makeDirectory($targetPath, 0755, true)) {
            throw new \Exception("无法创建模块目录: {$targetPath}");
        }

        // 复制所有文件
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPathItem = $targetPath.DIRECTORY_SEPARATOR.$iterator->getSubPathName();

            if ($item->isDir()) {
                if (! File::makeDirectory($targetPathItem, 0755, true)) {
                    throw new \Exception("无法创建目录: {$targetPathItem}");
                }
            } else {
                if (! File::copy($item, $targetPathItem)) {
                    throw new \Exception("无法复制文件: {$item->getPathname()}");
                }
            }
        }

        $this->info('✓ 已复制Demo5模块文件');
    }

    /**
     * 更新所有文件中的命名空间
     */
    private function updateNamespaces(string $moduleName, string $namespace): void
    {
        $modulePath = base_path("Modules/{$moduleName}");

        // 需要替换的模式
        $patterns = [
            'Modules\\\\Demo5' => "Modules\\\\{$namespace}",
            'Modules\\\\Demo5\\\\' => "Modules\\\\{$namespace}\\\\",
            'Demo5' => $moduleName,
            'demo5' => strtolower($moduleName),
            'Demo5ServiceProvider' => "{$namespace}ServiceProvider",
        ];

        // 如果命名空间与模块名不同，需要额外的替换
        if ($namespace !== $moduleName) {
            // 先将Demo5相关的替换为模块名（处理类名）
            $patterns['Demo5'] = $moduleName;
            // 再将模块名的命名空间替换为自定义命名空间
            $patterns["Modules\\\\{$moduleName}"] = "Modules\\\\{$namespace}";
            $patterns["Modules\\\\{$moduleName}\\\\"] = "Modules\\\\{$namespace}\\\\";
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = File::get($file->getPathname());
                $updatedContent = $content;

                foreach ($patterns as $search => $replace) {
                    $updatedContent = str_replace($search, $replace, $updatedContent);
                }

                // 如果命名空间与模块名不同，需要额外处理命名空间
                if ($namespace !== $moduleName) {
                    // 替换文件中错误的命名空间
                    $updatedContent = str_replace("Modules\\{$moduleName}\\", "Modules\\{$namespace}\\", $updatedContent);
                    $updatedContent = str_replace("Modules\\\\{$moduleName}\\\\", "Modules\\\\{$namespace}\\\\", $updatedContent);
                }

                // 如果内容有变化，写回文件
                if ($updatedContent !== $content) {
                    File::put($file->getPathname(), $updatedContent);
                }
            }
        }

        $this->info('✓ 已更新命名空间');
    }

    /**
     * 更新模块配置文件
     */
    private function updateModuleConfig(string $moduleName, string $namespace): void
    {
        $configFile = base_path("Modules/{$moduleName}/module.json");

        if (! File::exists($configFile)) {
            throw new \Exception("模块配置文件不存在: {$configFile}");
        }

        $config = json_decode(File::get($configFile), true);

        // 更新配置
        $config['name'] = $moduleName;
        $config['alias'] = strtolower($moduleName);
        $config['providers'] = ["Modules\\{$namespace}\\Providers\\{$namespace}ServiceProvider"];

        // 保存配置
        File::put($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('✓ 已更新模块配置');
    }

    /**
     * 重命名服务提供者文件
     */
    private function renameServiceProviderFile(string $moduleName, string $namespace): void
    {
        $oldFile = base_path("Modules/{$moduleName}/app/Providers/Demo5ServiceProvider.php");
        $newFile = base_path("Modules/{$moduleName}/app/Providers/{$namespace}ServiceProvider.php");

        if (File::exists($oldFile)) {
            if (! File::move($oldFile, $newFile)) {
                throw new \Exception('无法重命名服务提供者文件');
            }
            $this->info('✓ 已重命名服务提供者文件');
        }
    }
}
