<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa fa-list-alt"></i> 执行日志（最新10条）
        </h3>
    </div>

    <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
        <ul class="list-unstyled mb-0">
            @forelse($logs as $log)
                @php
                    $levelClass = match($log['level'] ?? 'info') {
                        'error' => 'text-danger',
                        'warning' => 'text-warning',
                        default => 'text-muted',
                    };
                @endphp
                <li class="mb-2">
                    <small class="text-muted">{{ $log['time'] }}</small>
                    <span class="ml-2 {{ $levelClass }}">{{ $log['message'] }}</span>
                </li>
            @empty
                <li class="text-muted">暂无日志</li>
            @endforelse
        </ul>
    </div>
</div>