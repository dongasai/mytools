<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Export;

use Modules\AFile\Services\ModuleFileService;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureExcel\Engines\File\ExcelFileHandler;
use Modules\FeatureExcel\Engines\Template\ExportTemplate;
use Modules\FeatureExcel\Engines\Transform\DataTransformer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Excel 导出引擎
 *
 * 继承 AbstractExportEngine，实现 Excel 文件导出完整流程：
 * 缓存检查 → 数据转换 → 文件生成 → 文件保存 → 缓存写入。
 * 支持临时保存和持久保存两种模式。
 */
class ExcelExportEngine extends AbstractExportEngine
{
    /**
     * 执行 Excel 导出
     *
     * 完整导出流程：缓存检查 → 数据转换 → 文件生成 → 文件保存 → 缓存写入。
     * 根据 template 的 persist 配置决定临时保存或持久保存。
     *
     * @param array $data 导出数据
     * @param ExportTemplate $template 导出模板
     * @param array $options 导出选项
     *   - data_fingerprint: string 数据指纹
     *   - tenant_id: int|null 租户ID
     *   - user_id: int 用户ID
     *   - re_id: int 关联业务ID（默认0）
     *   - variables: array 文件名变量
     * @return string 文件 URL
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
            $cacheKey = '';
            if ($dataFingerprint !== '') {
                $cacheKey = self::buildCacheKey($template->getName(), $dataFingerprint, $tenantId);
            }

            // 2. 缓存命中检查
            if ($cacheKey !== '') {
                $cached = Cache::get($cacheKey);
                if ($cached !== null && self::verifyCachedFile($cached)) {
                    return $cached['url'] ?? '';
                }
            }

            // 3. 数据转换
            $transformedData = DataTransformer::transformExportData($data, $template);

            // 4. 创建 Excel 文件
            $localPath = ExcelFileHandler::createExcelFile($transformedData, $template);

            // 5. 通过 AFile 保存
            $fileContent = file_get_contents($localPath);
            if ($fileContent === false) {
                SystemLogService::error('feature_excel', '读取生成的Excel文件失败', [
                    'operation' => 'export',
                    'local_path' => $localPath,
                ]);
                throw new \RuntimeException('读取生成的Excel文件失败');
            }
            $fileName = $template->generateFileName($variables);

            if ($template->isPersist()) {
                // 持久保存：写入 Laravel Storage local 磁盘 → 调用 uploadFileForPath
                $relativePath = 'temp/fexcel_' . uniqid() . '.xlsx';
                Storage::disk('local')->put($relativePath, $fileContent);
                $fileModel = ModuleFileService::uploadFileForPath(
                    $relativePath,
                    $userId,
                    $template->getStoreName(),
                    $reId,
                    $fileName
                );
                $fileId = $fileModel->id ?? 0;
                $url = ModuleFileService::getFileUrl($fileId);
                $cacheValue = [
                    'persist' => true,
                    'file_id' => $fileId,
                    'url' => $url,
                ];
            } else {
                // 临时保存到公开存储（直接可访问）
                $url = ModuleFileService::saveTempFilePublic('xlsx', $fileContent);
                $cacheValue = [
                    'persist' => false,
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
                Cache::put($cacheKey, $cacheValue, $cacheTtl);
            }

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
