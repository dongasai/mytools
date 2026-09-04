<x-debug::layouts.content>
    <h1 class="mb-4">路由列表</h1>
    <p class="text-muted mb-3">共 {{ $totalCount }} 条路由，当前显示 {{ $filteredCount }} 条</p>

    <form method="GET" action="{{ route('debug.routes') }}" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="prefix" class="form-select">
                <option value="">全部前缀</option>
                @foreach($allPrefixes as $p)
                <option value="{{ $p }}" {{ $prefix === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="filter" value="{{ $filter }}" class="form-control" placeholder="过滤 URI / 名称 / Action">
        </div>
        <div class="col-auto">
            <input type="text" name="method" value="{{ $method }}" class="form-control" placeholder="HTTP 方法">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">过滤</button>
            <a href="/debug/routes" class="btn btn-outline-secondary">重置</a>
        </div>
    </form>

    @foreach($groupedRoutes as $prefixName => $routes)
    <div class="card mb-4">
        <div class="card-header">
            <strong>{{ $prefixName ?: '无前缀' }}</strong>
            <span class="badge bg-secondary ms-2">{{ $routes->count() }} 条</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Method</th>
                        <th>URI</th>
                        <th>Name</th>
                        <th>Action</th>
                        <th>Middleware</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routes as $route)
                    <tr>
                        <td><span class="method method-{{ strtolower(explode('|', $route['method'])[0]) }}">{{ $route['method'] }}</span></td>
                        <td><code>{{ $route['uri'] }}</code></td>
                        <td>{{ $route['name'] }}</td>
                        <td><small>{{ $route['action'] }}</small></td>
                        <td><small class="text-muted">{{ $route['middleware'] }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</x-debug::layouts.content>