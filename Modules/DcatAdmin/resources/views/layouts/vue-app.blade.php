{{-- Vue 应用通用 Layout --}}
{{-- Content 自动处理 pjax（普通请求渲染后台布局，pjax 请求只返回内容） --}}
{{-- 布局只需判断 standalone 参数 --}}

@if(request()->get('standalone'))
    {{-- standalone 模式：完整 HTML，用于 iframe 嵌入 --}}
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Vue 页面')</title>
        @stack('styles')
    </head>
    <body>
        @yield('content')
        @stack('scripts')
    </body>
    </html>
@else
    {{-- 普通模式 / pjax 模式：iframe 容器 --}}
    {{-- Content 会自动决定是否渲染后台布局 --}}
    <div class="vue-iframe-container">
        <a href="{{ url()->current() }}?standalone=1" target="_blank" class="vue-iframe-open-btn" title="在新标签页打开">
            <i class="feather icon-external-link"></i>
        </a>

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
        margin: 0 -1.25rem;
        height: calc(100vh - 230px);
        overflow: hidden;
    }

    .vue-iframe-container iframe {
        display: block;
        border: none;
        width: calc(100% + 2.5rem);
        height: 100%;
    }

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
    </style>
@endif