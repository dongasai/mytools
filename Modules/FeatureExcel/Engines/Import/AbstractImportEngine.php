<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Import;

use Modules\AFile\Temporary;

/**
 * 导入引擎基类
 *
 * 提供导入引擎的公共方法，如路径安全验证
 */
abstract class AbstractImportEngine
{
    /**
     * 解析文件路径
     *
     * 安全约束：传入路径一律按相对路径解析，规则为「存储根目录 + 传入路径」：
     * - 去除前导斜杠，绝对路径输入不会逃逸存储根目录
     * - 禁止 .. 路径段，防止目录遍历攻击
     * 解析由 AFile 临时储存根目录完成（如 temp/202608/15/xxx.xlsx）。
     *
     * @param  string  $filePath  传入文件路径
     * @return string 解析后的绝对路径
     *
     * @throws \InvalidArgumentException 包含目录遍历路径段时抛出
     */
    protected static function resolveFilePath(string $filePath): string
    {
        // 去除前导斜杠（Unix 与 Windows 格式），按相对路径解析
        $filePath = ltrim($filePath, '/\\');

        // 禁止 .. 路径段（目录遍历）
        $segments = explode('/', str_replace('\\', '/', $filePath));
        if (in_array('..', $segments, true)) {
            throw new \InvalidArgumentException('文件路径不能包含目录遍历');
        }

        // 相对路径：通过 AFile 临时储存解析为存储根目录下的绝对路径
        return Temporary::getLocalPath($filePath);
    }

    /**
     * 验证文件路径合法性
     *
     * 确保文件路径位于 storage 目录下，防止路径遍历攻击
     *
     * @param  string  $filePath  文件路径
     *
     * @throws \InvalidArgumentException 文件路径非法或文件不存在
     */
    protected static function validateFilePath(string $filePath): void
    {
        $realPath = realpath($filePath);

        if ($realPath === false || ! str_starts_with($realPath, storage_path())) {
            throw new \InvalidArgumentException('非法文件路径');
        }

        if (! file_exists($realPath)) {
            throw new \InvalidArgumentException('文件不存在: '.basename($filePath));
        }
    }
}
