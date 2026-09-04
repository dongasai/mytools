<?php

declare(strict_types=1);

namespace Modules\Debug\Http\Controllers;

use Illuminate\View\View;

/**
 * Debug 主框架控制器
 * 负责渲染包含导航和 iframe 的主框架页面
 */
class DebugFrameController
{
    /**
     * 渲染主外壳页面（包含导航 + iframe）
     *
     * @return View
     */
    public function frame(): View
    {
        // 外壳页面，iframe 内容由前端根据 hash 加载
        return view('debug::layouts.frame');
    }
}