<?php

namespace Modules\FeatureSms\DcatAdmin\Repositories;

use Modules\FeatureSms\Models\SmsCode;
use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;

/**
 * 短信验证码仓库
 */
class SmsCodeRepository extends EloquentRepository
{
    protected $eloquentClass = SmsCode::class;
}
