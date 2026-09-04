<?php

namespace Modules\ABase\Support;

use Illuminate\Support\Facades\Cache;

class Trace
{
    public static $ob;

    public $data = [];

    public $error = [];

    public function __destruct()
    {
        if (app()->runningUnitTests() || defined('IS_UNITTEST')) {
            return true;
        }
        if (! function_exists('app') || ! app() || ! app()->bound('config')) {
            return true;
        }

        if (! defined('RUN_UNIQID')) {
            define('RUN_UNIQID', uniqid());
        }

        self::getCache()->put('trace_' . RUN_UNIQID, $this->data, 3600);
        $list = self::getCache()->get('trace_list', []);
        if (count($list) > 300) {
            array_shift($list);
            array_shift($list);
        }
        $list[] = RUN_UNIQID;

        self::getCache()->put('trace_list', $list, 3600);
    }

    /**
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public static function getCache()
    {

        return Cache::store();
    }

    /**
     * 获取数据
     *
     * @return mixed
     */
    public static function getData($unid)
    {
        return self::getCache()->get('trace_' . $unid, []);
    }

    public static function getlist()
    {
        return self::getCache()->get('trace_list', []);
    }

    public static function getOb()
    {
        if (! self::$ob) {
            return self::$ob = new Trace;
        }

        return self::$ob;
    }

    /**
     * 增加数据
     *
     * @param  string  $name
     * @param  mixed  $data
     * @return Trace
     */
    public static function addData($name, $data)
    {
        $ob = self::getOb();

        $ob->data[$name] = $data;

        return $ob;
    }

    /**
     * 增加错误
     *
     * @param  mixed  $data
     * @return Trace
     */
    public static function addError($data)
    {
        $ob = self::getOb();

        $ob->error[] = $data;

        return $ob;
    }

    /**
     * 追加数据
     *
     * @return Trace
     */
    public static function applyData($name, $data)
    {
        $ob = self::getOb();

        $ob->data[$name][] = $data;

        return $ob;
    }
}
