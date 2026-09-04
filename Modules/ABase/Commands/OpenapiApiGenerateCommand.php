<?php

declare(strict_types=1);

namespace Modules\ABase\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * OpenAPI文档生成命令
 * php artisan openapi-api:generate
 */
class OpenapiApiGenerateCommand extends Command
{
    protected $signature = 'openapi-api:generate
                            {--force : 强制重新生成}';

    protected $description = '生成 OpenAPI API 文档（独立配置，输出到 public 目录）';

    /**
     * 命令内部配置，不依赖 config 文件
     */
    private function getConfig(): array
    {
        $publicDocsDir = public_path('api-docs');

        // 只扫描已启用的 API 模块
        $apiModulePaths = $this->getEnabledApiModulesPaths();

        return [
            'api' => [
                'title' => 'Demo Admin API',
                'description' => '基于 Laravel 12 + Dcat Admin 的演示项目 API 文档',
                'version' => '1.0.0',
            ],
            'paths' => [
                // 文档输出到 public 目录
                'docs' => $publicDocsDir,
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                // 注解扫描目录（仅扫描已启用的 API 模块）
                'annotations' => $apiModulePaths,
            ],
            'routes' => [
                'api' => 'api-docs/swagger.json',
            ],
            'scanOptions' => [
                // 排除目录
                'exclude' => [

                    // 排除 vendor 目录（除非有特定的 openapi 定义）
                    base_path('vendor'),
                ],
            ],
        ];
    }

    /**
     * 获取所有已启用的、后缀是 Api 的模块路径
     */
    private function getEnabledApiModulesPaths(): array
    {
        $paths = [];

        // 读取模块状态文件
        $statusesFile = base_path('modules_statuses.json');
        if (! File::exists($statusesFile)) {
            return $paths;
        }

        $statuses = json_decode(File::get($statusesFile), true);
        if (! is_array($statuses)) {
            return $paths;
        }

        // 获取所有启用的、名称以 Api 结尾的模块
        $apiModules = array_keys(array_filter($statuses, function ($enabled, $moduleName) {
            return $enabled === true && str_ends_with($moduleName, 'Api');
        }, ARRAY_FILTER_USE_BOTH));

        if (empty($apiModules)) {
            return $paths;
        }

        // 查找这些模块的实际路径
        $scanPaths = config('modules.scan.paths') ?? [];

        foreach ($scanPaths as $scanPath) {
            if (str_ends_with($scanPath, '/*')) {
                $basePath = substr($scanPath, 0, -2);
                if (is_dir($basePath)) {
                    $dirs = glob($basePath . '/*', GLOB_ONLYDIR);
                    if ($dirs) {
                        foreach ($dirs as $dir) {
                            $moduleName = basename($dir);
                            if (in_array($moduleName, $apiModules, true)) {
                                $paths[] = $dir;
                            }
                        }
                    }
                }
            }
        }

        return $paths;
    }

    public function handle(): int
    {
        $this->info('🔥 开始生成 OpenAPI-Api 文档...');
        $this->line('==========================================');

        // 先显示找到的 Api 模块
        $apiModulePaths = $this->getEnabledApiModulesPaths();
        if (! empty($apiModulePaths)) {
            $this->info('🎯 已启用的 API 模块:');
            foreach ($apiModulePaths as $path) {
                $moduleName = basename($path);
                $this->line("   ✅ {$moduleName} - {$path}");
            }
            $this->line('');
        } else {
            $this->warn('⚠️  未找到已启用的 API 模块');
            $this->line('');
        }

        $config = $this->getConfig();

        // 显示配置信息
        $this->info('📋 文档配置:');
        $this->line("   API标题: {$config['api']['title']}");
        $this->line("   版本: {$config['api']['version']}");
        $this->line("   输出目录: {$config['paths']['docs']}");
        $this->line('');

        // 检查注解目录并过滤不存在的
        $this->info('📂 检查注解目录...');
        $annotations = $config['paths']['annotations'] ?? [];

        if (empty($annotations)) {
            $this->error('❌ 未配置注解扫描目录');

            return self::FAILURE;
        }

        // 过滤掉不存在的目录
        $validAnnotations = [];
        foreach ($annotations as $annotationDir) {
            $exists = File::exists($annotationDir);
            $status = $exists ? '✅' : '⏭️ ';
            $this->line("   {$status} {$annotationDir}");

            if ($exists) {
                $validAnnotations[] = $annotationDir;
            } else {
                $this->line("      ⚠️  跳过不存在的目录");
            }
        }

        if (empty($validAnnotations)) {
            $this->error('❌ 没有有效的注解扫描目录');

            return self::FAILURE;
        }

        $this->line('');
        $this->info("✅ 有效扫描目录: " . count($validAnnotations) . ' 个');
        $this->line('');

        // 确保 public/api-docs 目录存在
        $docsDir = $config['paths']['docs'];
        if (! File::exists($docsDir)) {
            File::makeDirectory($docsDir, 0755, true);
            $this->info("📁 创建输出目录: {$docsDir}");
            $this->line('');
        }

        // 清理旧文档
        if ($this->option('force')) {
            $this->info('🧹 清理旧文档...');
            $jsonFile = $docsDir . '/' . $config['paths']['docs_json'];
            $yamlFile = $docsDir . '/' . $config['paths']['docs_yaml'];

            if (File::exists($jsonFile)) {
                File::delete($jsonFile);
                $this->line("   ✅ 删除: {$jsonFile}");
            }

            if (File::exists($yamlFile)) {
                File::delete($yamlFile);
                $this->line("   ✅ 删除: {$yamlFile}");
            }
            $this->line('');
        }

        // 执行生成
        $this->info('⚙️  正在生成文档...');

        try {
            $docs = 'default';
            $exclude = $config['scanOptions']['exclude'] ?? [];

            // 临时设置配置（仅用于 L5-Swagger 生成器）
            config([
                "l5-swagger.documentations.{$docs}" => [
                    'api' => $config['api'],
                    'paths' => [
                        'annotations' => $validAnnotations,
                        'docs' => $docsDir,
                        'docs_json' => $config['paths']['docs_json'],
                        'docs_yaml' => $config['paths']['docs_yaml'],
                    ],
                    'scanOptions' => [
                        'exclude' => $exclude,
                    ],
                ],
            ]);

            // 调用 L5-Swagger 生成器
            $generator = app(\L5Swagger\Generator::class);
            $generator->generateDocs($docs);

            $this->line('✅ 文档生成成功!');
            $this->line('');

            // 显示访问地址
            $url = $config['routes']['api'];
            $this->info('🌐 访问地址:');
            $this->line('   ' . config('app.url') . '/' . $url);
            $this->line('');

            // 显示文件位置
            $jsonFile = $docsDir . '/' . $config['paths']['docs_json'];
            $this->info('📄 文档文件:');
            $this->line('   ' . $jsonFile);
            $this->line('');

            // 显示文件大小
            if (File::exists($jsonFile)) {
                $size = File::size($jsonFile);
                $sizeKB = number_format($size / 1024, 2);
                $this->info('📊 文件大小:');
                $this->line("   {$sizeKB} KB");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ 文档生成失败!');
            $this->line('');
            $this->error('错误信息: ' . $e->getMessage());
            $this->line('');
            $this->warn('常见问题:');
            $this->line('   1. 检查注解目录配置是否正确');
            $this->line('   2. 检查 public/api-docs 目录权限是否可写');
            $this->line('   3. 检查 PHP 文件是否有语法错误');
            $this->line('   4. 运行 composer dump-autoload');
            $this->line('   5. 运行 php artisan optimize:clear');
            $this->line('');

            // 显示详细错误追踪（当使用 --verbose 时）
            if ($this->getOutput()->isVerbose()) {
                $this->line('异常追踪:');
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
