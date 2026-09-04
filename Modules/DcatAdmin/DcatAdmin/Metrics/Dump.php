<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics;

class Dump
{
    public static function dumpvar($data)
    {
        return view('admin/dump', ['dump_var' => $data]);
    }
}
