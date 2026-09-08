<?php

namespace Modules\AClean\DcatAdmin\Providers;

use Nwidart\Modules\Traits\PathNamespace;

class AdminServiceProvider extends \Modules\ABase\Support\ServiceProvider
{
    use PathNamespace;

    protected string $name = 'AClean';

    // module_开头,只有一个`_`
    protected string $nameLower = 'module_acleanadmin';

    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();
    }

    public function register(): void
    {
        // 注册后台特定服务
    }

    public function provides(): array
    {
        return [];
    }
}
