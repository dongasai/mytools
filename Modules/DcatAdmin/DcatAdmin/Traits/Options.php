<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

trait Options
{
    public function useing($name, $keys)
    {
        $list = [];
        foreach ($keys as $key) {
            $list[$key] = $this->_option($name.'-'.$key);
        }

        //        dump($list);
        return $list;
    }

    public function _option($name)
    {
        return $name;
    }
}
