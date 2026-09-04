<?php

namespace Modules\DcatAdmin\Services;

use Illuminate\Support\Facades\File;

/**
 * 日志查看服务类
 *
 * 提供日志查看相关的服务方法，供其他模块调用
 */
class LogViewerService
{
    /**
     * 获取日志文件列表
     */
    public static function getLogFiles(): array
    {
        $path = storage_path('logs');
        $files = File::files($path);

        $logFiles = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $logFiles[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => self::formatFileSize($file->getSize()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // 按修改时间排序
        usort($logFiles, function ($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        return $logFiles;
    }

    /**
     * 获取日志文件内容
     *
     * @param  string  $filename  文件名
     * @param  int  $lines  行数
     */
    public static function getLogContent(string $filename, int $lines = 1000): array
    {
        $path = storage_path('logs/'.$filename);

        if (! File::exists($path)) {
            return [
                'error' => '文件不存在',
                'content' => [],
            ];
        }

        // 读取最后N行
        $content = self::tailFile($path, $lines);

        // 解析日志内容
        $parsedContent = self::parseLogContent($content);

        return [
            'filename' => $filename,
            'path' => $path,
            'size' => self::formatFileSize(File::size($path)),
            'modified' => date('Y-m-d H:i:s', File::lastModified($path)),
            'content' => $parsedContent,
        ];
    }

    /**
     * 清空日志文件
     *
     * @param  string  $filename  文件名
     */
    public static function clearLogFile(string $filename): bool
    {
        $path = storage_path('logs/'.$filename);

        if (! File::exists($path)) {
            return false;
        }

        // 清空文件内容
        file_put_contents($path, '');

        return true;
    }

    /**
     * 删除日志文件
     *
     * @param  string  $filename  文件名
     */
    public static function deleteLogFile(string $filename): bool
    {
        $path = storage_path('logs/'.$filename);

        if (! File::exists($path)) {
            return false;
        }

        // 删除文件
        return File::delete($path);
    }

    /**
     * 格式化文件大小
     *
     * @param  int  $size  文件大小（字节）
     */
    protected static function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2).' '.$units[$i];
    }

    /**
     * 读取文件最后N行
     *
     * @param  string  $path  文件路径
     * @param  int  $lines  行数
     */
    protected static function tailFile(string $path, int $lines): string
    {
        $file = fopen($path, 'r');
        $total_lines = count(file($path));

        if ($total_lines <= $lines) {
            $result = file_get_contents($path);
            fclose($file);

            return $result;
        }

        $line_counter = 0;
        $position = -1;
        $buffer = '';

        while ($line_counter < $lines && fseek($file, $position, SEEK_END) !== -1) {
            $char = fgetc($file);
            if ($char === "\n") {
                $line_counter++;
            }
            $buffer = $char.$buffer;
            $position--;
        }

        fclose($file);

        return $buffer;
    }

    /**
     * 解析日志内容
     *
     * @param  string  $content  日志内容
     */
    protected static function parseLogContent(string $content): array
    {
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $parsedContent = [];
        foreach ($matches as $match) {
            $parsedContent[] = [
                'datetime' => $match[1],
                'environment' => $match[2],
                'level' => $match[3],
                'message' => trim($match[4]),
            ];
        }

        return $parsedContent;
    }
}
