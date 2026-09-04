@extends('module_dcatadmin::layouts.simple')

@section('title', 'Module Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">Module Admin</h1>
            <p class="lead text-muted">管理后台功能模块，提供后台管理、日志记录、缓存管理等功能</p>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card h-100 text-center">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i data-feather="settings" width="32" class="text-primary mb-3"></i>
                        <h5 class="card-title">系统管理</h5>
                        <p class="card-text text-muted">系统配置与设置管理</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100 text-center">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i data-feather="file-text" width="32" class="text-success mb-3"></i>
                        <h5 class="card-title">日志记录</h5>
                        <p class="card-text text-muted">操作日志与系统监控</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100 text-center">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i data-feather="database" width="32" class="text-info mb-3"></i>
                        <h5 class="card-title">缓存管理</h5>
                        <p class="card-text text-muted">系统缓存优化与清理</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ admin_base_path('/') }}" class="btn btn-primary btn-lg">
                <i data-feather="arrow-right" width="16"></i> 进入后台管理
            </a>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化Feather图标
        feather.replace();

        // 添加卡片悬停效果
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'all 0.3s ease';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush