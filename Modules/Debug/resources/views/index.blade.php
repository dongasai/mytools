<x-debug::layouts.content>
    <h1 class="mb-4">Debug 工具</h1>
    <div class="row g-4">
        @foreach($tools as $tool)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $tool['name'] }}</h5>
                    <p class="card-text text-muted">{{ $tool['description'] }}</p>
                </div>
                <div class="card-footer bg-transparent">
                    <button class="btn btn-primary btn-sm" onclick="if(window.parent && window.parent.loadPage) { window.parent.loadPage('{{ $tool['page'] }}'); } else { window.location.href = '/debug/{{ $tool['page'] }}'; }">
                        打开
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-debug::layouts.content>