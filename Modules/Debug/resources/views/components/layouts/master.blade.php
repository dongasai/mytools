<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Debug - {{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="/modules/debug/css/bootstrap.min.css">
    <style>
        .method { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 0.75em; font-weight: bold; }
        .method-get { background: #0d6efd; color: #fff; }
        .method-post { background: #198754; color: #fff; }
        .method-put { background: #fd7e14; color: #fff; }
        .method-patch { background: #fd7e14; color: #fff; }
        .method-delete { background: #dc3545; color: #fff; }
        .method-head { background: #6f42c1; color: #fff; }
        .method-options { background: #6c757d; color: #fff; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        {{ $slot }}
    </div>
    <script src="/modules/debug/js/bootstrap.min.js"></script>
    <script>
        // iframe 检测：如果不在 iframe 中且没有 embed 参数，跳转到主框架
        (function() {
            const isInIframe = window.self !== window.top;
            const urlParams = new URLSearchParams(window.location.search);
            const isEmbedMode = urlParams.has('embed');

            if (!isInIframe && !isEmbedMode) {
                const path = window.location.pathname.replace('/debug/', '').replace('/debug', '');
                window.top.location.href = '/debug/frame#' + path;
            }
        })();
    </script>
</body>
</html>