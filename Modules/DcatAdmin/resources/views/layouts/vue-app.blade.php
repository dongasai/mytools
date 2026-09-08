{{-- Vue 应用通用布局 --}}
{{-- 三种模式自动判断 --}}

@if(request()->get('standalone'))
    {{-- 模式1：独立页面（iframe 内部），无后台布局 --}}
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Vue 页面')</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            html, body { height: 100%; overflow: hidden; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        </style>
        @stack('styles')
    </head>
    <body>
        @yield('content')
        @stack('scripts')
    </body>
    </html>
@else
    {{-- 模式2和模式3：渲染 iframe 容器 --}}
    {{-- 模式2：pjax 请求，无后台布局包裹 --}}
    {{-- 模式3：普通请求，Controller 用 Content 包装器包裹后台布局 --}}
    <div class="vue-iframe-container">
        @if(!request()->pjax())
        {{-- 新标签打开按钮（仅模式3显示）--}}
        <a href="{{ url()->current() }}?standalone=1" target="_blank" class="vue-iframe-open-btn" title="在新标签页打开">
            <i class="feather icon-external-link"></i>
        </a>
        @endif

        <iframe
            src="{{ url()->current() }}?standalone=1"
            frameborder="0"
            width="100%"
            height="100%"
        ></iframe>
    </div>

    <style>
    .vue-iframe-container {
        position: relative;
        width: 100%;
        @if(request()->pjax())
        height: calc(100vh - 100px);
        @else
        margin: 0 -1.25rem;
        height: calc(100vh - 230px);
        @endif
        overflow: hidden;
    }

    .vue-iframe-container iframe {
        display: block;
        border: none;
        @if(request()->pjax())
        width: 100%;
        @else
        width: calc(100% + 2.5rem);
        @endif
        height: 100%;
    }

    @if(!request()->pjax())
    .vue-iframe-open-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        z-index: 1000;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 4px;
        color: #606266;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }

    .vue-iframe-open-btn:hover {
        background: #409eff;
        color: #fff;
        box-shadow: 0 4px 8px rgba(64, 158, 255, 0.3);
    }

    .vue-iframe-open-btn i {
        font-size: 16px;
    }
    @endif
    </style>
@endif