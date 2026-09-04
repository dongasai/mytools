<?php

namespace Modules\AFile\Logics;

/**
 * 目录逻辑类（静态类）
 *
 * 提供目录相关的逻辑处理
 * 静态类，不允许实例化，所有方法均为静态方法
 */
class DirLogic
{
    /**
     * 获取用户目录
     *
     * @param  int  $userId  用户ID
     * @return string 用户目录路径
     */
    public static function getDir(int $userId): string
    {
        $md5 = md5($userId);

        return 'upload/'.substr($md5, 0, 2).'/'.$userId;
    }
}
