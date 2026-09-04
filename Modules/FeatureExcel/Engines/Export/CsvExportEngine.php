<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Export;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\AFile\Services\ModuleFileService;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureExcel\Engines\File\CsvFileHandler;
use Modules\FeatureExcel\Engines\Template\ExportTemplate;
use Modules\FeatureExcel\Engines\Transform\DataTransformer;

/**
 * CSV导出引擎
 *
 * 继承AbstractExportEngine，实现CSV格式的数据导出，
 * 支持缓存、临时/持久存储、编码转换
 */
class CsvExportEngine extends AbstractExportEngine
{
    /**
     * 导出数据为CSV文件
     *
     * 完整流程：缓存检查 → 数据转换 → CSV文件生成 → AFile存储 → 缓存写入 → 返回URL
     *
     * @param array $data 导出数据（二维数组）
     * @param ExportTemplate $template 导出模板
     * @param array $options 导出选项
     *     - data_fingerprint: string 数据指纹
     *     - tenant_id: int|null 租户ID
     *     - user_id: int 用户ID
     *     - re_id: int 关联业务ID
     *     - variables: array 文件名变量
     * @return string 文件URL
     */
    public static function export(array $data, ExportTemplate $template, array $options = []): string
    {
        try {
            $dataFingerprint = $options['data_fingerprint'] ?? '';
            $tenantId = $options['tenant_id'] ?? null;
            $userId = (int) ($options['user_id'] ?? 0);
            $reId = (int) ($options['re_id'] ?? 0);
            $variables = $options['variables'] ?? [];

            // 1. 构建缓存标识
            $cacheKey = self::buildCacheKey($template->getName(), $dataFingerprint, $tenantId);

            // 2. 缓存命中检查
            if ($cacheKey !== '') {
                $cached = Cache::get($cacheKey);
                if ($cached !== null && self::verifyCachedFile($cached)) {
                    return $cached['url'] ?? '';
                }
            }

            // 3. 数据转换
            $transformedData = DataTransformer::transformExportData($data, $template);

            // 4. 创建CSV文件
            $localPath = CsvFileHandler::createCsvFile($transformedData, $template);

            // 5. 通过AFile保存
            $fileName = $template->generateFileName($variables);
            $storeName = $template->getStoreName();
            $persist = $template->isPersist();
            $fileContent = file_get_contents($localPath);
            if ($fileContent === false) {
                SystemLogService::error('feature_excel', '读取生成的CSV文件失败', [
                    'operation' => 'export',
                    'local_path' => $localPath,
                ]);
                throw new \RuntimeException('读取生成的CSV文件失败');
            }

            if ($persist) {
                // 持久保存：写入Laravel local磁盘 → uploadFileForPath
                $relativePath = 'fexcel/csv/' . date('Ymd') . '/' . $fileName;
                Storage::disk('local')->put($relativePath, $fileContent);
                $fileModel = ModuleFileService::uploadFileForPath($relativePath, $userId, $storeName, $reId, $fileName);
                $url = ModuleFileService::getFileUrl($fileModel->id);
                $cacheData = [
                    'persist' => true,
                    'file_id' => $fileModel->id ?? 0,
                    'url' => $url,
                ];
            } else {
                // 临时保存
                $tempPath = ModuleFileService::saveTempFile('csv', $fileContent);
                $url = ModuleFileService::getTempFileUrl($tempPath);
                $cacheData = [
                    'persist' => false,
                    'temp_path' => $tempPath,
                    'url' => $url,
                ];
            }

            // 6. 清理本地临时文件
            if (file_exists($localPath)) {
                unlink($localPath);
            }

            // 7. 写入缓存
            if ($cacheKey !== '') {
                $cacheTtl = $template->getCacheTtl();
                Cache::put($cacheKey, $cacheData, $cacheTtl);
            }

            // 8. 返回文件URL
            return $url;
        } catch (\Throwable $e) {
            SystemLogService::exception('feature_excel', $e, [
                'operation' => 'export',
                'template_name' => $template->getName(),
                'data_count' => count($data),
            ]);
            throw $e;
        }
    }
}
