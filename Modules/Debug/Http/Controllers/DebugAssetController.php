<?php

declare(strict_types=1);

namespace Modules\Debug\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * 调试静态资源控制器
 *
 * 提供模块内所有静态资源的访问
 *
 * @package Modules\Debug\Http\Controllers
 */
class DebugAssetController extends Controller
{
    /**
     * MIME类型映射
     *
     * @var array
     */
    private array $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'mjs' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'eot' => 'application/vnd.ms-fontobject',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'html' => 'text/html',
        'txt' => 'text/plain',
        'map' => 'application/json',
    ];

    /**
     * 返回静态资源
     *
     * @param string $path 相对路径
     * @return Response
     */
    public function show(string $path): Response
    {
        $fullPath = base_path("Modules/Debug/resources/assets/{$path}");
        // dd($path);
        if (!file_exists($fullPath)) {
            abort(404, "File not found: {$path}");
        }

        // 安全检查：防止目录遍历攻击
        $realPath = realpath($fullPath);
        $assetsPath = realpath(base_path('Modules/Debug/resources/assets'));
        if (!str_starts_with($realPath, $assetsPath)) {
            abort(403, 'Access denied');
        }

        $content = file_get_contents($fullPath);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = $this->mimeTypes[$extension] ?? 'application/octet-stream';

        return response($content)
            ->setStatusCode(200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
