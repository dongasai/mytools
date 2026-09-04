<?php

namespace Modules\Application\DcatAdmin\Repositories;

use Modules\Application\Models\ApplicationConfig;
use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;

class AppConfig extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = ApplicationConfig::class;
}
