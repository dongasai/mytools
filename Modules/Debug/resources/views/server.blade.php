<x-debug::layouts.content>
    <h1 class="mb-4">服务器信息</h1>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <strong>Scheme</strong>
                    <div class="h4 mt-2">{{ $scheme }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <strong>isSecure</strong>
                    <div class="h4 mt-2">{{ $isSecure ? 'true' : 'false' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <strong>HTTPS</strong>
                    <div class="h4 mt-2">{{ $https }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <strong>SERVER_PORT</strong>
                    <div class="h4 mt-2">{{ $serverPort }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>$_SERVER 变量</strong>
            <span class="badge bg-secondary ms-2">{{ $serverVars->count() }} 项</span>
            <form method="GET" action="{{ route('debug.server') }}" class="d-inline ms-3">
                <input type="text" name="filter" value="{{ request('filter') }}" class="form-control form-control-sm d-inline-block" style="width:200px" placeholder="过滤关键字">
                <button type="submit" class="btn btn-sm btn-primary">过滤</button>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 300px;">变量名</th>
                        <th>值</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filtered = request('filter') ? $serverVars->filter(fn($v, $k) => stripos($k, request('filter')) !== false || stripos((string)$v, request('filter')) !== false) : $serverVars;
                    @endphp
                    @foreach($filtered as $key => $value)
                    <tr>
                        <td><code>{{ $key }}</code></td>
                        <td><code>{{ $value }}</code></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-debug::layouts.content>
