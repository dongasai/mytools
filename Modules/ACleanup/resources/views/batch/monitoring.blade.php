<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa fa-dashboard"></i> 批次执行状态监控
        </h3>
        <div class="card-tools">
            <span class="badge badge-info" id="refresh-time">
                最后刷新: {{ \Carbon\Carbon::now()->format('H:i:s') }}
            </span>
        </div>
    </div>

    <div class="card-body">
        {{-- 批次基本信息 --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="info-box-text">批次ID</span>
                                <span class="info-box-number">{{ $batch->id }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="info-box-text">计划名称</span>
                                <span class="info-box-number">{{ $plan ? $plan->plan_name : '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="info-box-text">开始时间</span>
                                <span class="info-box-number" id="started-at">
                                    {{ $batch->started_at ? $batch->started_at->format('H:i:s') : '-' }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <span class="info-box-text">已执行时间</span>
                                <span class="info-box-number" id="elapsed-time">{{ $elapsedTime }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 进度条 --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         style="width: {{ $batch->progress_percent }}%"
                         id="progress-bar"
                         aria-valuenow="{{ $batch->progress_percent }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        <span id="progress-text">{{ $batch->progress_percent }}%</span>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <strong id="status-label">
                        @php
                            $status = \Modules\AClean\Enums\BATCH_STATUS::tryFrom($batch->batch_status);
                        @endphp
                        {{ $status ? $status->icon() . ' ' . $status->label() : '-' }}
                    </strong>
                </div>
            </div>
        </div>

        {{-- 左右分栏：进度统计 + 执行日志 --}}
        <div class="row">
            {{-- 左侧：进度统计 --}}
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-chart-pie"></i> 进度统计
                        </h3>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" width="40%">总表数</td>
                                <td class="text-right" id="total-tables">
                                    <strong>{{ $batch->total_tables }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">已处理表数</td>
                                <td class="text-right" id="processed-tables">
                                    <strong class="text-success">{{ $batch->processed_tables }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">失败表数</td>
                                <td class="text-right" id="failed-tables">
                                    <strong class="{{ $batch->failed_tables > 0 ? 'text-danger' : 'text-muted' }}">
                                        {{ $batch->failed_tables }}
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">当前处理表</td>
                                <td class="text-right" id="current-table">
                                    <code>{{ $batch->current_table ?? '-' }}</code>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">平均速度</td>
                                <td class="text-right" id="avg-speed">
                                    <strong>{{ number_format($batch->avg_speed ?? 0) }}</strong> 条/秒
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 右侧：执行日志 --}}
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-list-alt"></i> 执行日志（最新10条）
                        </h3>
                    </div>
                    <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                        <ul class="list-unstyled mb-0" id="execution-logs">
                            @forelse($logs as $log)
                                <li class="mb-2">
                                    <small class="text-muted">{{ $log['time'] }}</small>
                                    <span class="ml-2">{{ $log['message'] }}</span>
                                </li>
                            @empty
                                <li class="text-muted">暂无日志</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 自动刷新脚本 --}}
    @if($batch->batch_status === \Modules\AClean\Enums\BATCH_STATUS::IN_PROGRESS->value)
        <script>
            // 每2秒刷新一次
            setInterval(function() {
                $.ajax({
                    url: '{{ admin_url("backup-clean-admin/batches/{$batch->id}/refresh") }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // 更新刷新时间
                        $('#refresh-time').text('最后刷新: ' + data.refresh_time);

                        // 更新进度条
                        $('#progress-bar').css('width', data.progress_percent + '%');
                        $('#progress-bar').attr('aria-valuenow', data.progress_percent);
                        $('#progress-text').text(data.progress_percent + '%');

                        // 更新状态
                        $('#status-label').html(data.batch_status_icon + ' ' + data.batch_status_label);

                        // 更新统计信息
                        $('#total-tables strong').text(data.total_tables);
                        $('#processed-tables strong').text(data.processed_tables);
                        $('#failed-tables strong').text(data.failed_tables);
                        $('#current-table code').text(data.current_table);
                        $('#avg-speed strong').text(number_format(data.avg_speed));
                        $('#started-at').text(data.started_at);
                        $('#elapsed-time').text(data.elapsed_time);

                        // 更新日志
                        var logsHtml = '';
                        data.logs.forEach(function(log) {
                            logsHtml += '<li class="mb-2">' +
                                '<small class="text-muted">' + log.time + '</small>' +
                                '<span class="ml-2">' + log.message + '</span>' +
                                '</li>';
                        });
                        $('#execution-logs').html(logsHtml);

                        // 如果批次已完成，停止刷新并提示
                        if (data.batch_status !== {{ \Modules\AClean\Enums\BATCH_STATUS::IN_PROGRESS->value }}) {
                            // 显示完成提示
                            toastr.success('备份任务已结束');

                            // 3秒后刷新页面
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        }
                    },
                    error: function() {
                        console.error('刷新失败');
                    }
                });
            }, 2000);

            // 数字格式化
            function number_format(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        </script>
    @endif
</div>