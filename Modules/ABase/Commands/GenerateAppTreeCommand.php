<?php

namespace Modules\ABase\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 生成 app 目录的文件树并保存到 app/tree.md
 */
class GenerateAppTreeCommand extends Command
{
    /**
     * 命令的名称和签名。
     *
     * @var string
     */
    protected $signature = 'ucore:generate-apptree';

    /**
     * 命令的描述。
     *
     * @var string
     */
    protected $description = '生成 app 目录的文件树并保存到 app/tree.md';

    /**
     * 统计数据
     */
    protected array $statistics = [
        'total_files' => 0,
        'total_directories' => 0,
        'php_files' => 0,
        'non_php_files' => 0,
        'classes_with_description' => 0,
        'classes_without_description' => 0,
        'file_types' => [],
        'modules' => [],
        'largest_files' => [],
        'large_files_by_lines' => [], // 行数大于500的文件
        'total_lines' => 0,
        'average_lines_per_file' => 0,
        'class_types' => [
            'class' => 0,
            'interface' => 0,
            'trait' => 0,
            'enum' => 0,
        ],
    ];

    /**
     * 执行控制台命令。
     *
     * @return int
     */
    public function handle()
    {
        $appPath = base_path('app');
        $outputFile = base_path('app/tree.md');

        // 初始化统计数据
        $this->resetStatistics();

        $tree = $this->generateDirectoryTree($appPath);

        // 添加统计信息
        $statistics = $this->generateStatistics();
        $content = $tree . "\n" . $statistics;

        File::put($outputFile, $content);

        $this->info('File tree generated successfully at app/tree.md');
        $this->displayStatistics();

        return Command::SUCCESS;
    }

    /**
     * 生成目录树结构为字符串
     *
     * @param  string  $directory  目录路径
     * @param  string  $prefix  前缀，用于格式化输出
     * @return string 返回目录树的字符串表示
     */
    private function generateDirectoryTree(string $directory, string $prefix = ''): string
    {
        $tree = '';
        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                $tree .= $prefix . $file . "\n";
                $this->updateDirectoryStatistics();
                $tree .= $this->generateDirectoryTree($filePath, $prefix . '    ');
            } else {
                // 对于文件，添加类信息
                $fileInfo = $this->getFileInfo($filePath, $file);
                $this->updateFileStatistics($filePath, $file, $fileInfo);
                $tree .= $prefix . $fileInfo . "\n";
            }
        }

        return $tree;
    }

    /**
     * 获取文件信息（文件名 + 类名和简述）
     *
     * @param  string  $filePath  文件完整路径
     * @param  string  $fileName  文件名
     * @return string 格式化的文件信息
     */
    private function getFileInfo(string $filePath, string $fileName): string
    {
        // 只处理 PHP 文件
        if (! str_ends_with($fileName, '.php')) {
            return $fileName;
        }

        $classInfo = $this->extractClassInfo($filePath);

        if ($classInfo) {
            return $fileName . ' - ' . $classInfo;
        }

        return $fileName;
    }

    /**
     * 从 PHP 文件中提取类名和简述
     *
     * @param  string  $filePath  文件路径
     * @return string|null 类名和简述，格式：ClassName: 类简述
     */
    private function extractClassInfo(string $filePath): ?string
    {
        try {
            $content = File::get($filePath);

            // 提取类名
            $className = $this->extractClassName($content);
            if (! $className) {
                return null;
            }

            // 提取类注释中的简述
            $description = $this->extractClassDescription($content);

            if ($description) {
                return $className . ': ' . $description;
            }

            return $className;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 提取类名
     *
     * @param  string  $content  文件内容
     * @return string|null 类名
     */
    private function extractClassName(string $content): ?string
    {
        // 匹配 class、interface、trait、enum 声明
        $patterns = [
            '/class\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
            '/interface\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
            '/trait\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
            '/enum\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * 提取类注释中的简述
     *
     * @param  string  $content  文件内容
     * @return string|null 类简述
     */
    private function extractClassDescription(string $content): ?string
    {
        // 匹配类声明前的文档注释 - 改进的正则表达式
        $pattern = '/\/\*\*\s*(.*?)\s*\*\/\s*(?:abstract\s+|final\s+)?(?:class|interface|trait|enum)\s+[a-zA-Z_][a-zA-Z0-9_]*/s';

        if (preg_match($pattern, $content, $matches)) {
            $docComment = $matches[1];

            // 清理注释格式，提取第一行有意义的描述
            $lines = explode("\n", $docComment);

            foreach ($lines as $line) {
                $line = trim($line);
                $line = preg_replace('/^\*\s*/', '', $line); // 移除行首的 *
                $line = trim($line);

                // 跳过空行和 @标签
                if (empty($line) || str_starts_with($line, '@')) {
                    continue;
                }

                // 返回第一行有意义的描述
                return $line;
            }
        }

        // 如果上面的模式没有匹配，尝试更宽松的模式
        $pattern2 = '/\/\*\*\s*\n\s*\*\s*([^\n\*@]+)/';
        if (preg_match($pattern2, $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * 重置统计数据
     */
    private function resetStatistics(): void
    {
        $this->statistics = [
            'total_files' => 0,
            'total_directories' => 0,
            'php_files' => 0,
            'non_php_files' => 0,
            'classes_with_description' => 0,
            'classes_without_description' => 0,
            'file_types' => [],
            'modules' => [],
            'largest_files' => [],
            'large_files_by_lines' => [],
            'total_lines' => 0,
            'average_lines_per_file' => 0,
            'class_types' => [
                'class' => 0,
                'interface' => 0,
                'trait' => 0,
                'enum' => 0,
            ],
        ];
    }

    /**
     * 更新文件统计信息
     *
     * @param  string  $filePath  文件路径
     * @param  string  $fileName  文件名
     * @param  string|null  $classInfo  类信息
     */
    private function updateFileStatistics(string $filePath, string $fileName, ?string $classInfo): void
    {
        $this->statistics['total_files']++;

        // 统计文件类型
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (! isset($this->statistics['file_types'][$extension])) {
            $this->statistics['file_types'][$extension] = 0;
        }
        $this->statistics['file_types'][$extension]++;

        // 统计 PHP 文件
        if ($extension === 'php') {
            $this->statistics['php_files']++;

            // 统计类描述
            if ($classInfo && strpos($classInfo, ':') !== false) {
                $this->statistics['classes_with_description']++;
            } else {
                $this->statistics['classes_without_description']++;
            }

            // 统计类类型
            $this->updateClassTypeStatistics($filePath);
        } else {
            $this->statistics['non_php_files']++;
        }

        // 统计模块
        $this->updateModuleStatistics($filePath);

        // 统计大文件
        $this->updateLargestFiles($filePath, $fileName);

        // 统计文件行数
        $this->updateLineStatistics($filePath, $fileName);
    }

    /**
     * 更新目录统计信息
     */
    private function updateDirectoryStatistics(): void
    {
        $this->statistics['total_directories']++;
    }

    /**
     * 更新类类型统计
     *
     * @param  string  $filePath  文件路径
     */
    private function updateClassTypeStatistics(string $filePath): void
    {
        try {
            $content = File::get($filePath);

            if (preg_match('/\bclass\s+[a-zA-Z_][a-zA-Z0-9_]*/', $content)) {
                $this->statistics['class_types']['class']++;
            } elseif (preg_match('/\binterface\s+[a-zA-Z_][a-zA-Z0-9_]*/', $content)) {
                $this->statistics['class_types']['interface']++;
            } elseif (preg_match('/\btrait\s+[a-zA-Z_][a-zA-Z0-9_]*/', $content)) {
                $this->statistics['class_types']['trait']++;
            } elseif (preg_match('/\benum\s+[a-zA-Z_][a-zA-Z0-9_]*/', $content)) {
                $this->statistics['class_types']['enum']++;
            }
        } catch (\Exception $e) {
            // 忽略文件读取错误
        }
    }

    /**
     * 更新模块统计
     *
     * @param  string  $filePath  文件路径
     */
    private function updateModuleStatistics(string $filePath): void
    {
        // 检查是否在 Module 目录下
        if (strpos($filePath, '/Module/') !== false) {
            $parts = explode('/Module/', $filePath);
            if (count($parts) > 1) {
                $moduleParts = explode('/', $parts[1]);
                $moduleName = $moduleParts[0];

                if (! isset($this->statistics['modules'][$moduleName])) {
                    $this->statistics['modules'][$moduleName] = 0;
                }
                $this->statistics['modules'][$moduleName]++;
            }
        }
    }

    /**
     * 更新最大文件统计
     *
     * @param  string  $filePath  文件路径
     * @param  string  $fileName  文件名
     */
    private function updateLargestFiles(string $filePath, string $fileName): void
    {
        try {
            $fileSize = filesize($filePath);
            $this->statistics['largest_files'][] = [
                'name' => $fileName,
                'size' => $fileSize,
                'path' => $filePath,
            ];

            // 只保留前10个最大的文件
            usort($this->statistics['largest_files'], function ($a, $b) {
                return $b['size'] - $a['size'];
            });

            if (count($this->statistics['largest_files']) > 10) {
                $this->statistics['largest_files'] = array_slice($this->statistics['largest_files'], 0, 10);
            }
        } catch (\Exception $e) {
            // 忽略文件大小获取错误
        }
    }

    /**
     * 更新文件行数统计
     *
     * @param  string  $filePath  文件路径
     * @param  string  $fileName  文件名
     */
    private function updateLineStatistics(string $filePath, string $fileName): void
    {
        try {
            $content = File::get($filePath);
            $lineCount = substr_count($content, "\n") + 1;

            // 累计总行数
            $this->statistics['total_lines'] += $lineCount;

            // 如果行数大于500，添加到大文件列表
            if ($lineCount > 500) {
                $this->statistics['large_files_by_lines'][] = [
                    'name' => $fileName,
                    'lines' => $lineCount,
                    'path' => $filePath,
                    'size' => filesize($filePath),
                ];

                // 按行数排序，只保留前20个
                usort($this->statistics['large_files_by_lines'], function ($a, $b) {
                    return $b['lines'] - $a['lines'];
                });

                if (count($this->statistics['large_files_by_lines']) > 20) {
                    $this->statistics['large_files_by_lines'] = array_slice($this->statistics['large_files_by_lines'], 0, 20);
                }
            }
        } catch (\Exception $e) {
            // 忽略文件读取错误
        }
    }

    /**
     * 生成统计信息
     */
    private function generateStatistics(): string
    {
        $stats = $this->statistics;
        $output = "\n" . str_repeat('=', 80) . "\n";
        $output .= "📊 项目统计信息\n";
        $output .= str_repeat('=', 80) . "\n\n";

        // 基础统计
        $output .= "📁 **文件和目录统计**\n";
        $output .= "- 总文件数: {$stats['total_files']}\n";
        $output .= "- 总目录数: {$stats['total_directories']}\n";
        $output .= "- PHP 文件: {$stats['php_files']}\n";
        $output .= "- 非 PHP 文件: {$stats['non_php_files']}\n\n";

        // 行数统计
        $averageLines = $stats['total_files'] > 0 ? round($stats['total_lines'] / $stats['total_files'], 1) : 0;
        $largeFilesCount = count($stats['large_files_by_lines']);

        $output .= "📏 **代码行数统计**\n";
        $output .= '- 总代码行数: ' . number_format($stats['total_lines']) . "\n";
        $output .= "- 平均每文件行数: {$averageLines}\n";
        $output .= "- 大文件数量 (>500行): {$largeFilesCount}\n\n";

        // 类描述统计
        $totalClasses = $stats['classes_with_description'] + $stats['classes_without_description'];
        $descriptionPercentage = $totalClasses > 0 ? round(($stats['classes_with_description'] / $totalClasses) * 100, 1) : 0;

        $output .= "📝 **类注释统计**\n";
        $output .= "- 有描述的类: {$stats['classes_with_description']}\n";
        $output .= "- 无描述的类: {$stats['classes_without_description']}\n";
        $output .= "- 注释覆盖率: {$descriptionPercentage}%\n\n";

        // 类类型统计
        $output .= "🏗️ **类类型统计**\n";
        foreach ($stats['class_types'] as $type => $count) {
            if ($count > 0) {
                $output .= '- ' . ucfirst($type) . ": {$count}\n";
            }
        }
        $output .= "\n";

        // 文件类型统计
        if (! empty($stats['file_types'])) {
            $output .= "📄 **文件类型统计**\n";
            arsort($stats['file_types']);
            foreach ($stats['file_types'] as $ext => $count) {
                $ext = $ext ?: '(无扩展名)';
                $output .= "- .{$ext}: {$count}\n";
            }
            $output .= "\n";
        }

        // 模块统计
        if (! empty($stats['modules'])) {
            $output .= "📦 **模块统计**\n";
            arsort($stats['modules']);
            foreach ($stats['modules'] as $module => $count) {
                $output .= "- {$module}: {$count} 个文件\n";
            }
            $output .= "\n";
        }

        // 最大文件统计（按文件大小）
        if (! empty($stats['largest_files'])) {
            $output .= "💾 **最大文件 - 按文件大小 (Top 5)**\n";
            $topFiles = array_slice($stats['largest_files'], 0, 5);
            foreach ($topFiles as $file) {
                $size = $this->formatFileSize($file['size']);
                $relativePath = str_replace(base_path('app') . '/', '', $file['path']);
                $output .= "- {$file['name']}: {$size} ({$relativePath})\n";
            }
            $output .= "\n";
        }

        // 大文件统计（按行数）
        if (! empty($stats['large_files_by_lines'])) {
            $output .= "📏 **大文件 - 按代码行数 (>500行, Top 10)**\n";
            $topFiles = array_slice($stats['large_files_by_lines'], 0, 10);
            foreach ($topFiles as $file) {
                $size = $this->formatFileSize($file['size']);
                $relativePath = str_replace(base_path('app') . '/', '', $file['path']);
                $output .= "- {$file['name']}: {$file['lines']} 行 ({$size}) - {$relativePath}\n";
            }
            $output .= "\n";
        }

        // 生成时间
        $output .= "⏰ **生成信息**\n";
        $output .= '- 生成时间: ' . date('Y-m-d H:i:s') . "\n";
        $output .= "- 生成命令: php artisan ucore:generate-apptree\n";
        $output .= "- DLaravel 版本: 1.0\n\n";

        $output .= str_repeat('=', 80) . "\n";
        $output .= "🎉 文件树生成完成！\n";
        $output .= str_repeat('=', 80);

        return $output;
    }

    /**
     * 在控制台显示统计信息
     */
    private function displayStatistics(): void
    {
        $stats = $this->statistics;

        $this->newLine();
        $this->info('📊 统计信息:');
        $this->line("文件总数: {$stats['total_files']}");
        $this->line("目录总数: {$stats['total_directories']}");
        $this->line("PHP 文件: {$stats['php_files']}");

        // 行数统计
        $averageLines = $stats['total_files'] > 0 ? round($stats['total_lines'] / $stats['total_files'], 1) : 0;
        $largeFilesCount = count($stats['large_files_by_lines']);
        $this->line('总代码行数: ' . number_format($stats['total_lines']));
        $this->line("平均每文件行数: {$averageLines}");
        $this->line("大文件数量 (>500行): {$largeFilesCount}");

        $totalClasses = $stats['classes_with_description'] + $stats['classes_without_description'];
        $descriptionPercentage = $totalClasses > 0 ? round(($stats['classes_with_description'] / $totalClasses) * 100, 1) : 0;
        $this->line("类注释覆盖率: {$descriptionPercentage}%");

        if (! empty($stats['modules'])) {
            $moduleCount = count($stats['modules']);
            $this->line("模块数量: {$moduleCount}");
        }
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
