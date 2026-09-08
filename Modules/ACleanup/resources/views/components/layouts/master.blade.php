<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '备份与清理管理')</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #ffffff;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 侧边栏 -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('backup-clean-admin.stats.dashboard') }}">
                                <i class="fas fa-home"></i> 备份与清理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backup-clean-admin.backup-plans.index') }}">
                                <i class="fas fa-shield-alt"></i> 独立备份计划
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backup-clean-admin.plans.index') }}">
                                <i class="fas fa-list"></i> 清理计划
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backup-clean-admin.tasks.index') }}">
                                <i class="fas fa-tasks"></i> 任务管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backup-clean-admin.backups.index') }}">
                                <i class="fas fa-database"></i> 备份管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backup-clean-admin.logs.index') }}">
                                <i class="fas fa-file-alt"></i> 执行日志
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backup-clean-admin.configs.index') }}">
                                <i class="fas fa-cog"></i> 配置管理
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- 主内容区 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title', '备份与清理管理')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> 导出
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar"></i> 今天
                            </button>
                        </div>
                    </div>
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    @yield('scripts')
</body>
</html>