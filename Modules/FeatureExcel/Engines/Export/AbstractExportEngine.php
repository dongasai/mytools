<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Export;

use Modules\AFile\Services\ModuleFileService;
use Modules\AFile\Temporary;

/**
 * 导出引擎基类
 *
 * 提供导出引擎的公共方法，如缓存标识构建、缓存文件验证、数据指纹生成
 */
abstract class AbstractExportEngine
{
    /**
     * 构建缓存标识（含租户隔离）
     *
     * @param string $templateName 模板名称
     * @param string $dataFingerprint 数据指纹
     * @param int|null $tenantId 租户ID
     * @return string 缓存key（空字符串表示不缓存）
     */
    protected static function buildCacheKey(string $templateName, string $dataFingerprint, ?int $tenantId = null): string
    {
        if (empty($dataFingerprint)) {
            return '';
        }

        return implode(':', [
            'fexcel',
            'export',
            $tenantId ?? 'global',
            $templateName,
            md5($dataFingerprint),
        ]);
    }

    /**
     * 验证缓存文件是否仍可用
     *
     * @param array $cached 缓存数据
     * @return bool 文件是否仍可用
     */
    protected static function verifyCachedFile(array $cached): bool
    {
        if ($cached['persist'] ?? false) {
            // 持久文件通过 AFile 检查
            $fileId = $cached['file_id'] ?? 0;
            return $fileId > 0 && ModuleFileService::fileExists($fileId);
        }

        // 临时文件通过 AFile Temporary 检查
        $tempPath = $cached['temp_path'] ?? '';
        return !empty($tempPath) && Temporary::exists($tempPath);
    }

    /**
     * 标准化数据指纹生成
     *
     * 业务模块应使用此方法生成指纹，确保一致性。
     * 大数据集用前100行采样 + 行数 + 参数哈希，避免 serialize 性能问题
     *
     * @param array $parameters 查询参数（自动排序确保一致性）
     * @param array|null $data 数据数组（大数据集用采样哈希）
     * @return string 数据指纹
     */
    public static function generateDataFingerprint(array $parameters, ?array $data = null): string
    {
        ksort($parameters);

        $fingerprint = ['params' => $parameters];

        if ($data !== null) {
            $sampleSize = min(count($data), 100);
            $fingerprint['data_sample'] = array_slice($data, 0, $sampleSize);
            $fingerprint['data_count'] = count($data);
        }

        return md5(json_encode($fingerprint));
    }
}
