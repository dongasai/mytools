{{-- Vue 应用通用 Layout --}}
{{-- 自动判断 standalone 参数，决定渲染模式 --}}

@if(request()->get('standalone'))
    {{-- 独立页面模式（完整 HTML，用于 iframe） --}}
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
    {{-- iframe 容器模式 --}}
    <div class="vue-iframe-container">
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
    </style>
@endif