<?php

declare(strict_types=1);

namespace Modules\Debug\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * ApiProto API 测试控制器
 *
 * 提供 ApiProto 模块 REST API 测试功能
 *
 * @package Modules\Debug\Http\Controllers
 */
class DebugApiProtoController extends Controller
{
    /**
     * ApiProto API 测试页面
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $apis = [
            [
                'name' => 'Proto 文件列表',
                'method' => 'GET',
                'path' => '/api/apiproto/proto-files',
                'description' => '获取 proto 文件列表（支持筛选）',
                'params' => ['module', 'package'],
            ],
            [
                'name' => 'Proto 文件详情',
                'method' => 'GET',
                'path' => '/api/apiproto/proto-file',
                'description' => '获取单个 proto 文件详情',
                'params' => ['path'],
            ],
            [
                'name' => 'Proto 文件内容',
                'method' => 'GET',
                'path' => '/api/apiproto/proto-content',
                'description' => '获取 proto 文件原始内容',
                'params' => ['path'],
            ],
            [
                'name' => 'Proto 模块汇总',
                'method' => 'GET',
                'path' => '/api/apiproto/proto-modules',
                'description' => '获取模块 proto 信息汇总',
                'params' => [],
            ],
            [
                'name' => 'Proto 文件搜索',
                'method' => 'GET',
                'path' => '/api/apiproto/proto-search',
                'description' => '搜索 proto 文件',
                'params' => ['keyword', 'module', 'search_messages'],
            ],
            [
                'name' => 'Proto Message 列表',
                'method' => 'GET',
                'path' => '/api/apiproto/proto-messages',
                'description' => '获取所有 Message 定义列表',
                'params' => ['module', 'package', 'name_pattern'],
            ],
            [
                'name' => '刷新缓存',
                'method' => 'POST',
                'path' => '/api/apiproto/proto-refresh-cache',
                'description' => '刷新 proto 缓存（需要认证）',
                'params' => [],
            ],
        ];

        return view('debug::apiproto', compact('apis'));
    }
}