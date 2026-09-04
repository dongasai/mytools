<?php

namespace Modules\Application\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\ABase\Models\SysRequestLog;

/**
 * 请求日志数据仓库
 */
class RequestLog extends EloquentRepository
{
    protected $eloquentClass = SysRequestLog::class;
}