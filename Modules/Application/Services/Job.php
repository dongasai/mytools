<?php

namespace Modules\Application\Services;

use Modules\System\Services\Modules;

class Job
{
    /**
     * 是否可以批量创建队列
     * 超出一定的堆积数额就不允许了
     *
     * @return void
     */
    public static function canPiliang()
    {
        $count = Modules\System\Services\Model\Job::query()
            ->where('available_at', '<', time() + 3)
            ->count();
        if ($count > 50) {
            return false;
        }

        return true;
    }
}
