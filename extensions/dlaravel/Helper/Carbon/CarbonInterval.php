<?php

namespace DLaravel\Helper\Carbon;

class CarbonInterval
{
    public static function secondsCascadeForHumans($sec)
    {
        return \Carbon\CarbonInterval::seconds($sec)->cascade()->forHumans();
    }
}
