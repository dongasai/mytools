<?php

namespace Modules\FeatureSms\DcatAdmin\Repositories;

use Modules\FeatureSms\Models\SmsDbGateway;
use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;

/**
 * db驱动-短信记录仓库
 */
class SmsDbGatewayRepository extends EloquentRepository
{
    protected $eloquentClass = SmsDbGateway::class;
}
