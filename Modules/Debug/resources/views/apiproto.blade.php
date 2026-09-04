<x-debug::layouts.content>
    <div class="row">
        <!-- 左栏：API 列表区 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ApiProto REST API 列表</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($apis as $index => $api)
                            <a href="#" class="list-group-item list-group-item-action api-card" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="method method-{{ strtolower($api['method']) }}">{{ $api['method'] }}</span>
                                    <small class="text-muted">{{ $api['name'] }}</small>
                                </div>
                                <div class="small text-truncate" style="font-family: monospace;">{{ $api['path'] }}</div>
                                <div class="small text-muted mt-1">{{ $api['description'] }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 右栏：请求/响应区 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">请求配置</h5>
                </div>
                <div class="card-body">
                    <!-- API 信息 -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">API 名称</label>
                        <input type="text" class="form-control" id="api-name" readonly>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">HTTP 方法</label>
                            <input type="text" class="form-control" id="api-method" readonly>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold">API 路径</label>
                            <input type="text" class="form-control" id="api-path" readonly>
                        </div>
                    </div>

                    <!-- 动态参数表 -->
                    <div class="mb-3" id="params-container" style="display: none;">
                        <label class="form-label fw-bold">请求参数</label>
                        <div id="params-table"></div>
                    </div>

                    <!-- Bearer Token -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bearer Token <small class="text-muted">(可选，用于认证接口)</small></label>
                        <input type="text" class="form-control" id="bearer-token" placeholder="输入 Token">
                    </div>

                    <!-- 操作按钮 -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" id="send-btn">发送请求</button>
                        <button class="btn btn-outline-secondary" id="clear-btn">清空</button>
                    </div>
                </div>
            </div>

            <!-- 响应展示 -->
            <div class="card mt-3" id="response-card" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">响应结果</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-info" id="copy-response-btn" title="复制响应内容">复制</button>
                        <span class="badge bg-secondary" id="response-status"></span>
                        <span class="badge bg-info" id="response-time"></span>
                    </div>
                </div>
                <div class="card-body">
                    <pre class="bg-dark text-light p-3 rounded" id="response-body" style="max-height: 400px; overflow-y: auto;"><code></code></pre>
                </div>
            </div>
        </div>
    </div>
</x-debug::layouts.content>

<script src="/modules/debug/js/jquery.min.js"></script>
<script>
(function() {
    const apis = @json($apis);

    // 点击 API 卡片
    $('.api-card').on('click', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        const api = apis[index];

        // 填充 API 信息
        $('#api-name').val(api.name);
        $('#api-method').val(api.method);
        $('#api-path').val(api.path);

        // 生成参数表
        generateParamsTable(api.params);

        // 高亮选中卡片
        $('.api-card').removeClass('active');
        $(this).addClass('active');
    });

    // 生成动态参数表
    function generateParamsTable(params) {
        const container = $('#params-container');
        const table = $('#params-table');

        if (params.length === 0) {
            container.hide();
            return;
        }

        table.empty();
        params.forEach(param => {
            const row = `
                <div class="row mb-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" value="${param}" readonly style="font-family: monospace;">
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-sm param-value" data-param="${param}" placeholder="输入 ${param} 的值">
                    </div>
                </div>
            `;
            table.append(row);
        });

        container.show();
    }

    // 发送请求
    $('#send-btn').on('click', function() {
        const method = $('#api-method').val();
        const path = $('#api-path').val();
        const token = $('#bearer-token').val();

        if (!method || !path) {
            alert('请先选择一个 API');
            return;
        }

        // 收集参数
        const params = {};
        $('.param-value').each(function() {
            const param = $(this).data('param');
            const value = $(this).val();
            if (value) {
                params[param] = value;
            }
        });

        // 构建请求
        const startTime = Date.now();
        const ajaxOptions = {
            url: path,
            method: method,
            dataType: 'json',
            beforeSend: function(xhr) {
                if (token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                }
            },
            success: function(data, textStatus, xhr) {
                const endTime = Date.now();
                const duration = endTime - startTime;
                showResponse(xhr.status, duration, data);
            },
            error: function(xhr) {
                const endTime = Date.now();
                const duration = endTime - startTime;
                let errorData;
                try {
                    errorData = JSON.parse(xhr.responseText);
                } catch(e) {
                    errorData = { error: xhr.responseText || 'Unknown error' };
                }
                showResponse(xhr.status, duration, errorData);
            }
        };

        // GET 请求：参数作为 query string
        if (method === 'GET' && Object.keys(params).length > 0) {
            ajaxOptions.data = params;
        }

        // POST 请求：参数作为 JSON body
        if (method === 'POST') {
            ajaxOptions.contentType = 'application/json';
            ajaxOptions.data = JSON.stringify(params);
        }

        $.ajax(ajaxOptions);
    });

    // 显示响应
    function showResponse(status, duration, data) {
        $('#response-status').text('HTTP ' + status).removeClass('bg-success bg-danger bg-warning').addClass(getStatusBadgeClass(status));
        $('#response-time').text(duration + 'ms');
        $('#response-body code').text(JSON.stringify(data, null, 2));
        $('#response-card').show();
    }

    // 获取状态码对应的 Badge 颜色
    function getStatusBadgeClass(status) {
        if (status >= 200 && status < 300) return 'bg-success';
        if (status >= 400 && status < 500) return 'bg-warning';
        if (status >= 500) return 'bg-danger';
        return 'bg-secondary';
    }

    // 复制响应内容
    $(document).on('click', '#copy-response-btn', function() {
        const text = $('#response-body code').text();
        if (!text || text === '等待请求...') return;
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        const btn = $('#copy-response-btn');
        btn.text('已复制');
        setTimeout(function() { btn.text('复制'); }, 1500);
    });

    // 清空
    $('#clear-btn').on('click', function() {
        $('#api-name').val('');
        $('#api-method').val('');
        $('#api-path').val('');
        $('#params-container').hide();
        $('#params-table').empty();
        $('#bearer-token').val('');
        $('#response-card').hide();
        $('.api-card').removeClass('active');
    });
})();
</script>