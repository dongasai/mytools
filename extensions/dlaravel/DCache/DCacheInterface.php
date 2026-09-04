<?php

namespace DLaravel\DCache;

/**
 * DCache 接口
 *
 * 定义缓存类必须实现的方法
 */
interface DCacheInterface
{
    /**
     * 缓存时间(秒)
     */
    public static function getTtl(): int;

    /**
     * 防重复执行时间(秒)
     */
    public static function getPreventDuplication(): int;

    /**
     * 获取新数据（可见性：public 或 protected）
     * 注意：抽象类可以使用 protected，业务类继承后对外不可见
     */
    public static function getNewData(array $parameter = []): mixed;



    /**
     * 清除缓存
     */
    public static function clearCache(array $parameter = []): void;

    /**
     * 强制刷新缓存
     */
    public static function refreshCache(array $parameter = []): mixed;
}