<?php

namespace DLaravel\Validator;

use App\Module\Ulogic\Services\UserAddressService;

class IsOnlineAddress extends Validator
{

    /**
     * @param mixed $value
     * @param array $data
     * @return bool
     * 检测是否uraus地址
     */
    public function validate(mixed $value, array $data): bool
    {
        $sysStr = '0xuraus';

        $prefix = substr($data['to_address'], 0, 7); // 截取前 7 位
        if ($sysStr === $prefix) {
            return false;
        }

        return true;
    }
}
