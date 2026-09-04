<?php

namespace Modules\AFile\Services;

use Illuminate\Support\Facades\Storage;
use Modules\AFile\Logics\StorageConfig;
use Modules\Application\Services\SystemLogService;

/**
 * 临时文件服务类
 *
 * 提供临时文件处理相关的服务
 */
class TemporaryService
{
    /**
     * 获取临时文件存储磁盘
     *
     * 统一从数据库默认存储配置读取
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function getDisk()
    {
        return Storage::disk(StorageConfig::getTempStorage());
    }

    /**
     * 获取临时文件目录
     *
     * 统一使用 temp/ 作为一级目录，方便定期清理
     *
     * @return string 临时文件目录
     */
    public function getDir()
    {
        return 'temp/'.date('Ym/d/');
    }

    /**
     * 获取临时文件存储路径
     *
     * @param  string  $ext  文件扩展名
     * @return string 存储路径
     */
    private function getStorePath($ext)
    {
        $path = $this->getDir().uniqid().'.'.$ext;

        return $path;
    }

    /**
     * 保存临时文件
     *
     * @param  string  $ext  文件扩展名
     * @param  string  $fileString  文件内容
     * @return string 存储路径
     *
     * @throws \Exception 存储失败时抛出异常
     */
    public function save($ext, $fileString)
    {
        try {
            $path = $this->getStorePath($ext);

            $res = $this->getDisk()->put($path, $fileString);
            if (! $res) {
                throw new \Exception('临时储存失败');
            }

            return $path;
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'saveTempFile',
                'ext' => $ext,
            ]);
            throw $e;
        }
    }

    /**
     * 保存临时文件到公开存储（可直接访问）
     *
     * 用于需要公开访问的临时文件（如导出文件）
     * 文件保存到 public 磁盘，返回完整的公开访问 URL
     *
     * @param  string  $ext  文件扩展名
     * @param  string  $fileString  文件内容
     * @return string 公开访问 URL
     *
     * @throws \Exception 存储失败时抛出异常
     */
    public function savePublic(string $ext, string $fileString): string
    {
        try {
            $path = $this->getStorePath($ext);

            $res = Storage::disk('public')->put($path, $fileString);
            if (! $res) {
                throw new \Exception('临时文件保存到公开存储失败');
            }

            $url = Storage::disk('public')->url($path);

            // 修复双斜杠问题：url() 返回 /storage/path，如果 path 以 / 开头会导致 //storage
            $url = str_replace('//storage', '/storage', $url);

            return $url;
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'savePublic',
                'ext' => $ext,
            ]);
            throw $e;
        }
    }

    /**
     * 获取临时文件下载URL
     *
     * @param  string  $path  文件路径（相对路径）
     * @return string 下载URL
     */
    public function getDownUrl($path)
    {
        // $path 格式：temp/202608/14/xxx.xlsx（由 getDir() 生成）
        $res = $this->getDisk()->url($path);

        return $res;
    }

    /**
     * 获取临时文件下载响应
     *
     * @param  string  $path  文件路径
     * @return \Illuminate\Http\Response 下载响应
     */
    public function getDown($path)
    {
        $disk = $this->getDisk();

        $res = $disk->read($path);
        $headers = [];
        $headers['Content-Type'] = $disk->mimeType($path);
        $headers['Content-Length'] = $disk->size($path);
        $resp = response('', 200, $headers)->setContent($res);

        return $resp;
    }

    /**
     * 路径转URL
     *
     * @param  string  $path  文件路径
     * @return string URL
     */
    public function path2url($path)
    {
        $new = substr($path, strpos($path, 'temp'));

        return $new;
    }

    /**
     * 获取本地临时文件路径
     *
     * @return string 本地临时文件路径
     */
    public function getTempLocalFile()
    {
        // 使用 Laravel storage 目录代替系统临时目录，避免生产环境权限问题
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $file = $tempDir.'/'.uniqid();

        return $file;
    }

    /**
     * 删除临时文件
     *
     * @param  string  $path  文件路径
     * @return bool 是否成功
     */
    public function delete(string $path): bool
    {
        return $this->getDisk()->delete($path);
    }

    /**
     * 检查临时文件是否存在
     *
     * @param  string  $path  文件路径
     * @return bool 是否存在
     */
    public function exists(string $path): bool
    {
        return $this->getDisk()->exists($path);
    }

    /**
     * 获取临时文件本地绝对路径
     *
     * 注意：仅对 local/public 等本地磁盘有效，不支持云存储
     *
     * @param  string  $path  相对路径
     * @return string 本地绝对路径
     */
    public function getLocalPath(string $path): string
    {
        return $this->getDisk()->path($path);
    }
}
