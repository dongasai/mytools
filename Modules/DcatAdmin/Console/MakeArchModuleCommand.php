<?php

namespace Modules\DcatAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * 创建符合项目架构的模块命令（基于模板复制）
 *
 * 使用方法：php artisan module:make-arch {ModuleName}
 *
 * 工作原理：
 * 1. 复制 Emptyarch 模板模块到新模块
 * 2. 替换所有命名空间、类名、变量名
 * 3. 更新 module.json 配置
 * 4. 生成完整可用的新模块
 */
class MakeArchModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make-arch {name} {--priority=20} {--type=feature}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '基于模板创建符合项目架构的模块';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * 模板模块名称
     */
    protected string $templateModule = 'Emptyarch';

    /**
     * 新模块名称
     */
    protected string $moduleName;

    /**
     * 新模块别名（小写）
     */
    protected string $moduleAlias;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->moduleName = $this->argument('name');
        $this->moduleAlias = Str::lower($this->moduleName);

        // 检查模块是否已存在
        if ($this->moduleExists()) {
            $this->error("模块 {$this->moduleName} 已存在！");
            return Command::FAILURE;
        }

        // 检查模板模块是否存在
        if (!$this->templateExists()) {
            $this->error("模板模块 {$this->templateModule} 不存在！");
            return Command::FAILURE;
        }

        $this->info("开始创建模块: {$this->moduleName}");

        // 复制模板模块
        $this->copyTemplateModule();

        // 替换所有命名空间和类名
        $this->replaceNamespaceAndClassNames();

        // 更新 module.json
        $this->updateModuleJson();

        $this->info("模块 {$this->moduleName} 创建成功！");
        $this->info("模块路径: Modules/{$this->moduleName}");
        $this->info("");
        $this->info("下一步：");
        $this->info("1. 完善 README.md 描述");
        $this->info("2. 创建数据库迁移文件");
        $this->info("3. 创建 Models 层");
        $this->info("4. 创建 Services 和 Logics 层");
        $this->info("5. 开发 DcatAdmin 后台功能");

        return Command::SUCCESS;
    }

    /**
     * 检查模块是否已存在
     */
    protected function moduleExists(): bool
    {
        return $this->files->isDirectory(base_path("Modules/{$this->moduleName}"));
    }

    /**
     * 检查模板模块是否存在
     */
    protected function templateExists(): bool
    {
        return $this->files->isDirectory(base_path("Modules/{$this->templateModule}"));
    }

    /**
     * 复制模板模块
     */
    protected function copyTemplateModule(): void
    {
        $this->info('复制模板模块...');

        $templatePath = base_path("Modules/{$this->templateModule}");
        $newModulePath = base_path("Modules/{$this->moduleName}");

        $this->copyDirectory($templatePath, $newModulePath);

        $this->info('模板模块复制完成');
    }

    /**
     * 递归复制目录
     */
    protected function copyDirectory(string $source, string $destination): void
    {
        $this->files->ensureDirectoryExists($destination);

        $items = $this->files->glob("{$source}/*");

        foreach ($items as $item) {
            $itemName = basename($item);
            $destPath = "{$destination}/{$itemName}";

            if ($this->files->isDirectory($item)) {
                $this->copyDirectory($item, $destPath);
            } else {
                $this->files->copy($item, $destPath);
            }
        }
    }

    /**
     * 替换所有命名空间和类名
     */
    protected function replaceNamespaceAndClassNames(): void
    {
        $this->info('替换命名空间和类名...');

        $modulePath = base_path("Modules/{$this->moduleName}");

        // 要处理的文件扩展名
        $extensions = ['php', 'json', 'md'];

        // 要替换的内容映射
        $replacements = [
            // 命名空间
            "Modules\\{$this->templateModule}" => "Modules\\{$this->moduleName}",
            "Modules\\\\{$this->templateModule}" => "Modules\\\\{$this->moduleName}",

            // 类名
            $this->templateModule => $this->moduleName,

            // 变量名（保持小写形式一致）
            Str::lower($this->templateModule) => Str::lower($this->moduleName),

            // 描述文本
            "{$this->templateModule}模块" => "{$this->moduleName}模块",
            "{$this->templateModule} 模块" => "{$this->moduleName} 模块",

            // 模板特有注释（需要删除或替换）
            "⚠️ **重要提示**：此模块为架构模板，不应用于实际业务开发。" => "",
            "**模板模块**：作为创建新模块的模板，包含完整的目录结构和基础文件。" => "",
            "此模块用于实际业务开发。" => "",
            "⚠️ 这是模板模块，不应用于实际业务开发" => "模块服务提供者",
            "模板模块不注册路由" => "注册路由服务提供者",
            "模板模块不注册命令" => "注册命令",
            "// \$this->app->register(RouteServiceProvider::class);" => "\$this->app->register(RouteServiceProvider::class);",
            "模板控制器，展示模块基本功能" => "仪表盘控制器",

            // README 模板特定内容
            "# {$this->templateModule} 模板模块" => "# {$this->moduleName} 模块",
            "{$this->moduleName} 模板模块" => "{$this->moduleName} 模块",
            "## 模块定位" => "## 模块定位\n\n待补充模块定位说明",
            "## 用途" => "## 核心功能\n\n- 功能1（待补充）\n- 功能2（待补充）\n- 功能3（待补充）",
            "当使用 `php artisan module:make-arch {ModuleName}` 创建新模块时，系统会：" => "",
            "1. 复制此模板模块到新模块\n2. 替换所有命名空间和类名\n3. 更新 module.json 配置\n4. 生成完整可用的新模块" => "",
            "## 包含内容" => "## 数据表前缀\n\n`{$this->moduleAlias}_`",
            "### 目录结构" => "",
            "- ✅ DcatAdmin（Controllers、Actions、Tools、Repositories、Forms、Metrics、Requests）" => "",
            "- ✅ Models、Services、Logics" => "",
            "- ✅ Providers（ServiceProvider、RouteServiceProvider、EventServiceProvider）" => "",
            "- ✅ Events、Listeners、Hooks" => "",
            "- ✅ Database（Migrations、Factories、Seeders）" => "",
            "- ✅ Tests、Docs、config" => "",
            "- ✅ routes（admin.php、api.php、web.php）" => "",
            "### 基础文件" => "",
            "- ✅ DashboardController（示例控制器）" => "",
            "- ✅ {$this->templateModule}Hook（钩子示例）" => "",
            "- ✅ {$this->templateModule}Parameter（钩子参数示例）" => "",
            "- ✅ {$this->templateModule}Result（钩子结果示例）" => "",
            "- ✅ 完整的路由文件" => "",
            "- ✅ module.json 配置" => "",
            "此模板遵循项目架构规范：" => "## 架构特点\n\n- 单模块全栈架构\n- 超管后台入口（DcatAdmin）",
            "- ServiceProvider 继承 `Modules\\ABase\\Support\\ServiceProvider`" => "",
            "- 目录结构符合项目规范" => "",
            "- 无冗余文件" => "",
            "- 包含完整的注释和文档" => "",
            "## 如何使用模板" => "## 模块依赖\n\n- ABase: 基础设施",
            "不要直接修改此模块，而是使用命令创建新模块：" => "",
            "```bash\nphp artisan module:make-arch YourModuleName\n```" => "",
            "**模块类型**: 模板模块（Template Module）" => "**模块类型**: {$this->option('type')}",
            "## 开发状态" => "## 开发状态\n\n🚧 开发中",
        ];

        // 递归处理所有文件
        $this->processFiles($modulePath, $extensions, $replacements);

        // 重命名特定文件
        $this->renameFiles($modulePath);

        // 清理 README 多余空行
        $this->cleanupReadme($modulePath);

        $this->info('命名空间和类名替换完成');
    }

    /**
     * 递归处理文件
     */
    protected function processFiles(string $directory, array $extensions, array $replacements): void
    {
        $items = $this->files->glob("{$directory}/*");

        foreach ($items as $item) {
            if ($this->files->isDirectory($item)) {
                // 递归处理子目录
                $this->processFiles($item, $extensions, $replacements);
            } else {
                // 检查文件扩展名
                $extension = pathinfo($item, PATHINFO_EXTENSION);
                if (in_array($extension, $extensions)) {
                    $this->replaceInFile($item, $replacements);
                }
            }
        }
    }

    /**
     * 在文件中进行替换
     */
    protected function replaceInFile(string $filePath, array $replacements): void
    {
        $content = $this->files->get($filePath);

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        $this->files->put($filePath, $content);
    }

    /**
     * 重命名文件
     */
    protected function renameFiles(string $modulePath): void
    {
        // ServiceProvider
        $oldProvider = "{$modulePath}/Providers/{$this->templateModule}ServiceProvider.php";
        $newProvider = "{$modulePath}/Providers/{$this->moduleName}ServiceProvider.php";
        if ($this->files->exists($oldProvider)) {
            $this->files->move($oldProvider, $newProvider);
        }

        // Hook 文件
        $hookPath = "{$modulePath}/Hooks/Definitions";
        $oldHook = "{$hookPath}/{$this->templateModule}Hook.php";
        $newHook = "{$hookPath}/{$this->moduleName}Hook.php";
        if ($this->files->exists($oldHook)) {
            $this->files->move($oldHook, $newHook);
        }

        // Parameter 文件
        $paramPath = "{$modulePath}/Hooks/Parameters";
        $oldParam = "{$paramPath}/{$this->templateModule}Parameter.php";
        $newParam = "{$paramPath}/{$this->moduleName}Parameter.php";
        if ($this->files->exists($oldParam)) {
            $this->files->move($oldParam, $newParam);
        }

        // Result 文件
        $resultPath = "{$modulePath}/Hooks/Results";
        $oldResult = "{$resultPath}/{$this->templateModule}Result.php";
        $newResult = "{$resultPath}/{$this->moduleName}Result.php";
        if ($this->files->exists($oldResult)) {
            $this->files->move($oldResult, $newResult);
        }
    }

    /**
     * 清理 README 多余空行
     */
    protected function cleanupReadme(string $modulePath): void
    {
        $readmePath = "{$modulePath}/README.md";
        if (!$this->files->exists($readmePath)) {
            return;
        }

        $content = $this->files->get($readmePath);

        // 移除多余空行（超过2个连续空行）
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // 移除开头的空行
        $content = preg_replace('/^\n+/', '', $content);

        // 移除结尾的空行
        $content = preg_replace('/\n+$/', "\n", $content);

        $this->files->put($readmePath, $content);
    }

    /**
     * 更新 module.json
     */
    protected function updateModuleJson(): void
    {
        $this->info('更新 module.json...');

        $moduleJsonPath = base_path("Modules/{$this->moduleName}/module.json");
        $content = $this->files->get($moduleJsonPath);

        // 解析 JSON
        $config = json_decode($content, true);

        // 更新配置
        $config['name'] = $this->moduleName;
        $config['alias'] = $this->moduleAlias;
        $config['description'] = "{$this->moduleName}模块 - 待补充描述";
        $config['keywords'] = [$this->moduleAlias];
        $config['priority'] = (int) $this->option('priority');
        $config['module_type'] = $this->option('type');
        $config['dcat_admin'] = true;
        $config['api'] = false;
        $config['api_proto'] = false;
        $config['module_requires'] = ['ABase'];

        // 移除模板标记
        unset($config['is_template']);

        // 保存
        $this->files->put($moduleJsonPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('module.json 更新完成');
    }
}