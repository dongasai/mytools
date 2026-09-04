<?php

namespace Modules\Application\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumExpression;
use DLaravel\Enum\EnumToString;

/**
 * 字典类型
 */
enum DICT_TYPE: string
{
    use EnumCore, EnumExpression, EnumToString;

    /**
     * 性别
     */
    case GENDER = 'gender';

    /**
     * 用户状态
     */
    case USER_STATUS = 'user_status';

    /**
     * 启用状态
     */
    case ENABLE_STATUS = 'enable_status';

    /**
     * 是否
     */
    case YES_NO = 'yes_no';

    /**
     * 短信场景
     */
    case SMS_SCENE = 'sms_scene';

    /**
     * 企业能耗分类
     */
    case COMPANY_ENERGY_CLASSIFY = 'company_energy_classify';

    /**
     * 蒸汽压力
     */
    case STEAM_PRES = 'steam_pres';

}