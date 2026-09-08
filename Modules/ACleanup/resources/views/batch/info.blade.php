<div class="card">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fa fa-info-circle"></i> 基本信息
            </h6>
            <div>
                @if($batchListUrl)
                    <a href="{{ $batchListUrl }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-list"></i> 批次列表
                    </a>
                @endif
                @if($planListUrl)
                    <a href="{{ $planListUrl }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-calendar"></i> 计划列表
                    </a>
                @endif
                @php($statusEnum = \Modules\AClean\Enums\BATCH_STATUS::tryFrom($batch->batch_status))
                @if($statusEnum)
                    <span class="badge {{ $statusEnum->cssClass() }}">
                        {{ $statusEnum->icon() }} {{ $statusEnum->label() }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body py-2 px-3">
        {{-- 第一行：批次基本信息 --}}
        <div class="row mb-1">
            <div class="col-12">
                <small class="text-muted">批次：</small>
                <span class="mr-3">
                    <small class="text-muted">ID</small> <strong>{{ $batch->id }}</strong>
                </span>
                <span class="mr-3">
                    <small class="text-muted">表数</small> <span class="text-info font-weight-bold">{{ $batch->total_tables }}</span>
                </span>
                <span class="mr-3">
                    <small class="text-muted">已处理</small> <span class="text-success font-weight-bold">{{ $batch->processed_tables }}</span>
                    @if($batch->failed_tables > 0)
                        <span class="text-danger">({{ $batch->failed_tables }}失败)</span>
                    @endif
                </span>
                <span class="mr-3">
                    <small class="text-muted">创建</small> {{ $batch->created_at->format('m-d H:i') }}
                </span>
                <span class="mr-3">
                    <small class="text-muted">开始</small> {{ $batch->started_at ? $batch->started_at->format('H:i:s') : '-' }}
                </span>
                <span class="mr-3">
                    <small class="text-muted">执行</small> <span class="text-primary font-weight-bold">{{ $elapsedTime }}</span>
                </span>
            </div>
        </div>

        {{-- 第二行：备份计划基本信息 --}}
        <div class="row">
            <div class="col-12">
                @if($plan)
                    <small class="text-muted">计划：</small>
                    <span class="mr-3">
                        <strong>{{ $plan->plan_name }}</strong>
                    </span>
                    <span class="mr-3">
                        @php($backupType = \Modules\AClean\Enums\BACKUP_TYPE::tryFrom($plan->backup_type))
                        <small class="text-muted">类型</small>
                        @if($backupType)
                            <span class="badge badge-info">{{ $backupType->label() }}</span>
                        @else
                            -
                        @endif
                    </span>
                    <span class="mr-3">
                        @php($compressionType = \Modules\AClean\Enums\COMPRESSION_TYPE::tryFrom($plan->compression_type))
                        <small class="text-muted">压缩</small> {{ $compressionType ? $compressionType->label() : '-' }}
                    </span>
                    <span class="mr-3">
                        <small class="text-muted">保留</small>
                        @if($plan->retention_days == 0)
                            <span class="text-info">永久</span>
                        @else
                            {{ $plan->retention_days }}天
                        @endif
                    </span>
                    <span class="mr-3">
                        @php($tables = is_array($plan->selected_tables) ? $plan->selected_tables : [])
                        <small class="text-muted">目标表</small> <span class="text-info font-weight-bold">{{ count($tables) }}</span>个
                    </span>
                @else
                    <span class="text-warning"><i class="fa fa-exclamation-triangle"></i> 未找到关联的备份计划</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 自动刷新脚本 --}}
@if($batch->batch_status === \Modules\AClean\Enums\BATCH_STATUS::IN_PROGRESS->value)
    <script>
        // 每2秒刷新页面
        setTimeout(function() {
            location.reload();
        }, 2000);
    </script>
@endif