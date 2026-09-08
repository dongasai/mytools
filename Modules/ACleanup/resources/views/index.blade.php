@extends('components.layouts.master')

@section('title', '数据清理管理')

@section('content')
<div class="backup-clean-module container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">数据清理管理</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCleanupModal">
                            <i class="fas fa-plus"></i> 新建清理计划
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>计划名称</th>
                                    <th>类型</th>
                                    <th>状态</th>
                                    <th>最后执行时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        暂无清理计划
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 新建清理计划模态框 -->
<div class="modal fade" id="createCleanupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新建清理计划</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="backupCleanForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="planName" class="form-label">计划名称</label>
                                <input type="text" class="form-control" id="planName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="planType" class="form-label">计划类型</label>
                                <select class="form-select" id="planType" required>
                                    <option value="">请选择</option>
                                    <option value="1">全量清理</option>
                                    <option value="2">模块清理</option>
                                    <option value="3">分类清理</option>
                                    <option value="4">自定义清理</option>
                                    <option value="5">混合清理</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">计划描述</label>
                        <textarea class="form-control" id="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveCleanupPlan()">保存</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function saveCleanupPlan() {
    // 保存清理计划的 JavaScript 逻辑
    console.log('Saving backup-clean plan...');
}
</script>
@endsection