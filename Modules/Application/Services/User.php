<?php

namespace Modules\Application\Services;

class User
{
    /**
     * 系统UID
     */
    public static function isSysUid($id): bool
    {
        if ($id < 10000) {
            return true;
        }

        return false;
    }
}
