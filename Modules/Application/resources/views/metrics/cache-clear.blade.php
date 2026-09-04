<div class="d-flex flex-column flex-wrap text-center">
    @isset($message)
    <div class="alert alert-success mb-2">
        <i class="fa fa-check"></i> {{ $message }}
    </div>
    @endisset

    <div class="mb-2">
        <i class="fa fa-database fa-2x text-info"></i>
    </div>

    <h5 class="font-weight-bold mb-1">缓存驱动: {{ $defaultDriver }}</h5>
    <small class="text-muted mb-3">已配置 {{ $driverCount }} 个缓存存储</small>

    @if($showButton ?? true)
    <button class="btn btn-warning btn-sm cache-clear-btn" data-action="clear">
        <i class="fa fa-trash"></i> 清理所有缓存
    </button>
    @endif
</div>