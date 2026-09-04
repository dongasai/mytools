<?php

namespace Modules\Application\DcatAdmin\Repositories;

use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;

class AdminActionLog extends EloquentRepository
{
    protected $relations = [
        'admin',
    ];

    /**
     * @var string
     */
    protected $eloquentClass = \Modules\DcatAdmin\Models\AdminActionlog::class;
}
