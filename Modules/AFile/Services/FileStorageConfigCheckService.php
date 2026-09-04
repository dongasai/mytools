<?php

namespace Modules\AFile\Services;

use Illuminate\Support\Facades\DB;
use Modules\AFile\Models\FileStorageConfig;

/**
 * 文件存储配置检查服务
 *
 * 提供存储配置的检查和修复功能
 * 所有方法均为静态方法，符合 Service 层规范
 */
class FileStorageConfigCheckService
{
    /**
     * 检查存储配置是否有问题
     *
     * @param FileStorageConfig $config 存储配置模型
     * @return array 问题列表，空数组表示无问题
     */
    public static function check(FileStorageConfig $config): array
    {
        $issues = [];

        // 只检查本地存储
        if ($config->driver !== 'local') {
            return $issues;
        }

        $configArray = $config->config ?? [];
        $diskName = $config->name; // 获取磁盘名称

        // 1. 检查 root 配置
        if (!isset($configArray['root'])) {
            $issues[] = [
                'type' => 'missing_root',
                'message' => '缺少 root 配置',
            ];
            return $issues; // 没有 root，后续检查无法进行
        }

        $root = $configArray['root'];
        $projectPath = base_path();

        // 2. 检查路径是否在项目目录内
        if (!str_starts_with($root, $projectPath)) {
            $issues[] = [
                'type' => 'path_outside_project',
                'message' => "路径不在项目目录内: {$root}",
            ];
        }

        // 3. 检查路径是否存在
        if (!\is_dir($root)) {
            $issues[] = [
                'type' => 'path_not_exists',
                'message' => "路径不存在: {$root}",
            ];
        }

        // 4. 检查默认存储路径是否正确（Laravel 11+ 默认使用 storage/app/private）
        // 注意：不再强制检查默认路径，因为 Laravel 11+ 默认使用 storage/app/private
        // 如果项目有特殊需求，可以在此添加自定义检查逻辑

        // 5. 检查 url 配置（仅对 public 磁盘必须）
        if ($diskName === 'public' && !isset($configArray['url'])) {
            $issues[] = [
                'type' => 'missing_url',
                'message' => '缺少 URL 配置',
            ];
        }

        // 6. 检查符号链接
        $linkIssue = self::checkSymbolicLink($root, $diskName);
        if ($linkIssue) {
            $issues[] = $linkIssue;
        }

        return $issues;
    }

    /**
     * 检查符号链接是否存在且正确
     *
     * @param string $root 存储根目录
     * @param string $diskName 磁盘名称
     * @return array|null 问题信息，null 表示正常
     */
    public static function checkSymbolicLink(string $root, string $diskName): ?array
    {
        if ($diskName === 'public') {
            $linkPath = public_path('storage');
            $targetPath = storage_path('app/public');

            try {
                if (!\is_link($linkPath)) {
                    return [
                        'type' => 'symlink_missing',
                        'message' => '符号链接缺失: public/storage',
                    ];
                }

                $currentTarget = \readlink($linkPath);
                if ($currentTarget !== $targetPath) {
                    return [
                        'type' => 'symlink_incorrect',
                        'message' => "符号链接指向错误: {$currentTarget}，应为: {$targetPath}",
                    ];
                }
            } catch (\ErrorException $e) {
                // 处理 open_basedir 限制导致的错误
                if (str_contains($e->getMessage(), 'open_basedir')) {
                    // 符号链接指向了不允许的路径，需要修复
                    return [
                        'type' => 'symlink_forbidden',
                        'message' => '符号链接指向了不允许访问的路径，需要重新创建',
                    ];
                }
                throw $e;
            }
        }

        if ($diskName === 'local') {
            $linkPath = public_path('upload');
            $uploadPath = $root . '/upload';

            // 只有当 upload 目录存在时才检查符号链接
            if (\is_dir($uploadPath)) {
                try {
                    if (!\is_link($linkPath)) {
                        return [
                            'type' => 'symlink_missing',
                            'message' => '符号链接缺失: public/upload',
                        ];
                    }

                    $currentTarget = \readlink($linkPath);
                    if ($currentTarget !== $uploadPath) {
                        return [
                            'type' => 'symlink_incorrect',
                            'message' => "符号链接指向错误: {$currentTarget}，应为: {$uploadPath}",
                        ];
                    }
                } catch (\ErrorException $e) {
                    // 处理 open_basedir 限制导致的错误
                    if (str_contains($e->getMessage(), 'open_basedir')) {
                        // 符号链接指向了不允许的路径，需要修复
                        return [
                            'type' => 'symlink_forbidden',
                            'message' => '符号链接指向了不允许访问的路径，需要重新创建',
                        ];
                    }
                    throw $e;
                }
            }
        }

        return null;
    }

    /**
     * 修复存储配置
     *
     * @param FileStorageConfig $config 存储配置模型
     * @return array 修复结果，包含 fixes 数组和 success 标志
     */
    public static function fix(FileStorageConfig $config): array
    {
        // 只处理本地存储
        if ($config->driver !== 'local') {
            return [
                'success' => false,
                'fixes' => [],
                'message' => '只支持修复本地存储配置',
            ];
        }

        $configArray = $config->config ?? [];
        $fixes = [];
        $fixed = false;

        // 1. 修复 root 配置
        if (!isset($configArray['root'])) {
            $configArray['root'] = $config->is_default ? storage_path('app') : storage_path('app/' . $config->name);
            $fixes[] = "已添加 root 配置: {$configArray['root']}";
            $fixed = true;
        } else {
            $root = $configArray['root'];
            $projectPath = base_path();

            // 路径不在项目目录内
            if (!str_starts_with($root, $projectPath)) {
                $oldPath = $root;
                if ($config->name === 'public') {
                    $configArray['root'] = storage_path('app/public');
                } elseif ($config->name === 'local') {
                    $configArray['root'] = storage_path('app');
                } else {
                    $configArray['root'] = storage_path('app/' . $config->name);
                }
                $fixes[] = "路径已修复: {$oldPath} → {$configArray['root']}";
                $fixed = true;
            }

            // 默认存储路径不正确
            if ($config->is_default && $configArray['root'] !== storage_path('app')) {
                $oldPath = $configArray['root'];
                $configArray['root'] = storage_path('app');
                $fixes[] = "默认存储路径已修复: {$oldPath} → {$configArray['root']}";
                $fixed = true;
            }

            // 路径不存在，创建目录
            if (!\is_dir($configArray['root'])) {
                if (\mkdir($configArray['root'], 0755, true)) {
                    $fixes[] = "已创建目录: {$configArray['root']}";
                    $fixed = true;
                } else {
                    return [
                        'success' => false,
                        'fixes' => $fixes,
                        'message' => "无法创建目录: {$configArray['root']}",
                    ];
                }
            }
        }

        // 2. 修复 url 配置
        if (!isset($configArray['url'])) {
            $configArray['url'] = rtrim(config('app.url'), '/');
            $fixes[] = "已添加 URL 配置: {$configArray['url']}";
            $fixed = true;
        }

        // 3. 修复符号链接
        $linkResult = self::fixSymbolicLink($configArray['root'], $config->name);
        if ($linkResult) {
            $fixes[] = $linkResult;
            $fixed = true;
        }

        if ($fixed) {
            // 更新数据库
            $config->config = $configArray;
            $config->save();

            // 清除缓存
            StorageConfigService::clearCache();

            return [
                'success' => true,
                'fixes' => $fixes,
                'message' => '配置已修复',
            ];
        }

        return [
            'success' => true,
            'fixes' => [],
            'message' => '配置正常，无需修复',
        ];
    }

    /**
     * 修复符号链接
     *
     * @param string $root 存储根目录
     * @param string $diskName 磁盘名称
     * @return string|null 修复消息，null 表示无需修复
     */
    public static function fixSymbolicLink(string $root, string $diskName): ?string
    {
        if ($diskName === 'public') {
            $linkPath = public_path('storage');
            $targetPath = storage_path('app/public');

            // 创建目标目录（如果不存在）
            if (!\is_dir($targetPath)) {
                \mkdir($targetPath, 0755, true);
            }

            try {
                if (\is_link($linkPath)) {
                    $currentTarget = \readlink($linkPath);
                    if ($currentTarget === $targetPath) {
                        return null; // 符号链接正确
                    }
                    // 删除错误的符号链接
                    \unlink($linkPath);
                }
            } catch (\ErrorException $e) {
                // 处理 open_basedir 限制：符号链接指向了不允许的路径
                if (str_contains($e->getMessage(), 'open_basedir')) {
                    // 强制删除错误的符号链接
                    if (file_exists($linkPath)) {
                        \unlink($linkPath);
                    }
                } else {
                    throw $e;
                }
            }

            // 创建符号链接
            \symlink($targetPath, $linkPath);
            return "已创建符号链接: public/storage -> storage/app/public";
        }

        if ($diskName === 'local') {
            $linkPath = public_path('upload');
            $uploadPath = $root . '/upload';

            // 创建 upload 子目录（如果不存在）
            if (!\is_dir($uploadPath)) {
                \mkdir($uploadPath, 0755, true);
            }

            try {
                if (\is_link($linkPath)) {
                    $currentTarget = \readlink($linkPath);
                    if ($currentTarget === $uploadPath) {
                        return null; // 符号链接正确
                    }
                    // 删除错误的符号链接
                    \unlink($linkPath);
                }
            } catch (\ErrorException $e) {
                // 处理 open_basedir 限制：符号链接指向了不允许的路径
                if (str_contains($e->getMessage(), 'open_basedir')) {
                    // 强制删除错误的符号链接
                    if (file_exists($linkPath)) {
                        \unlink($linkPath);
                    }
                } else {
                    throw $e;
                }
            }

            // 创建符号链接
            \symlink($uploadPath, $linkPath);
            return "已创建符号链接: public/upload -> {$uploadPath}";
        }

        return null;
    }

    /**
     * 检查所有存储配置
     *
     * @return array 配置检查结果列表
     */
    public static function checkAll(): array
    {
        $configs = FileStorageConfig::where('driver', 'local')->get();
        $results = [];

        foreach ($configs as $config) {
            $issues = self::check($config);
            if (!empty($issues)) {
                $results[] = [
                    'id' => $config->id,
                    'name' => $config->name,
                    'issues' => $issues,
                ];
            }
        }

        return $results;
    }

    /**
     * 修复所有存储配置
     *
     * @return array 修复结果列表
     */
    public static function fixAll(): array
    {
        $configs = FileStorageConfig::where('driver', 'local')->get();
        $results = [];

        foreach ($configs as $config) {
            $issues = self::check($config);
            if (!empty($issues)) {
                $result = self::fix($config);
                $results[] = [
                    'id' => $config->id,
                    'name' => $config->name,
                    'result' => $result,
                ];
            }
        }

        return $results;
    }

    /**
     * 获取配置检查摘要（用于后台卡片展示）
     *
     * @return array 摘要信息，包含 total, issues, status
     */
    public static function getSummary(): array
    {
        $checkResults = self::checkAll();

        if (empty($checkResults)) {
            return [
                'total' => 0,
                'issues' => [],
                'status' => 'ok',
            ];
        }

        $total = 0;
        $issues = [];

        foreach ($checkResults as $result) {
            foreach ($result['issues'] as $issue) {
                $total++;
                $issues[] = "[{$result['name']}] {$issue['message']}";
            }
        }

        return [
            'total' => $total,
            'issues' => $issues,
            'status' => $total > 0 ? 'error' : 'ok',
        ];
    }
}