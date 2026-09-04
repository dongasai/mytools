<?php

namespace Modules\Application\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumExpression;
use DLaravel\Enum\EnumToString;

enum VIEW_TYPE: string
{
    use EnumCore, EnumExpression,EnumToString;

    /**
     * 私有的
     */
    case PRIVATE = 'private';

    /**
     * 公共的
     */
    case PUBLIC = 'public';

}
