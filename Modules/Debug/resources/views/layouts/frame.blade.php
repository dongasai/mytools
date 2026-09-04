<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Debug 工具 - {{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="/modules/debug/css/bootstrap.min.css">
    <link rel="stylesheet" href="/modules/debug/css/frame.css">
</head>
<body class="bg-light d-flex flex-column vh-100">
    <!-- 顶部导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark flex-shrink-0" style="height: 56px;">
        <div class="container-fluid">
            <a class="navbar-brand" href="/debug">Debug 工具</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#debugNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="debugNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-nav="index" onclick="loadPage('index'); return false;">首页</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-nav="routes" onclick="loadPage('routes'); return false;">路由列表</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-nav="server" onclick="loadPage('server'); return false;">服务器信息</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-nav="apiproto" onclick="loadPage('apiproto'); return false;">ApiProto API 测试</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- iframe 容器 -->
    <div class="iframe-container flex-grow-1" style="height: calc(100vh - 56px); overflow: hidden;">
        <!-- Loading overlay -->
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- iframe -->
        <iframe id="content-frame" src="/debug/index" class="w-100 h-100" style="border: none;"></iframe>
    </div>

    <script src="/modules/debug/js/bootstrap.min.js"></script>
    <script>
        // 根据 URL hash 加载初始页面
        (function() {
            const hash = window.location.hash.replace('#', '') || 'index';
            loadPage(hash);
        })();

        // 加载页面到 iframe
        function loadPage(page) {
            const iframe = document.getElementById('content-frame');
            const loadingOverlay = document.getElementById('loading-overlay');

            // 显示 loading
            loadingOverlay.style.display = 'flex';

            // 设置 iframe src（内容页面会在 iframe 中显示）
            iframe.src = '/debug/' + page;

            // 更新 URL hash
            window.location.hash = page;

            // 更新导航 active state
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.dataset.nav === page) {
                    link.classList.add('active');
                }
            });

            // iframe 加载完成后隐藏 loading
            iframe.onload = function() {
                loadingOverlay.style.display = 'none';
            };
        }

        // 监听浏览器前进/后退
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.replace('#', '') || 'index';
            loadPage(hash);
        });
    </script>
</body>
</html>