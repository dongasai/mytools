<?php

namespace Modules\Application\DcatAdmin\Repositories;

use DLaravel\Model\RequestLog;
use Modules\DcatAdmin\DcatAdmin\Repository\Eloquent9999999Repository;

/**
 * 请求日志
 */
class RequireLog extends Eloquent9999999Repository
{
    /**
     * @var string
     */
    protected $eloquentClass = RequestLog::class;
}
