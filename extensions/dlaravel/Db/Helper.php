<?php

namespace DLaravel\Db;

use DLaravel\Helper\Logger;
use Illuminate\Support\Facades\DB;

/**
 * 数据库助手类
 */
class Helper
{
    /**
     * 检查是否已经开启事务
     *
     * @return void
     *
     * @throws \LogicException
     */
    public static function check_tr()
    {
        $level = DB::transactionLevel();
        if ($level === 0) {
            Logger::error('check_tr - transaction level is 0 没有开启事务');
            // 没有开启事务
            throw new \LogicException('transaction level is 0');
        }
        if ($level > 1) {
            // 事务嵌套
            Logger::error('check_tr - transaction level >1 事务嵌套 ');
            throw new \LogicException('transaction level > 1');
        }

    }
}
