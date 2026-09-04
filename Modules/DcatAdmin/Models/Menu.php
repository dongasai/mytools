<?php

namespace Modules\DcatAdmin\Models;

use Dcat\Admin\Models\Menu as BaseMenu;
use Illuminate\Support\Facades\DB;

/**
 * 自定义菜单模型
 *
 * 新增菜单时自动使用最小可用ID（填补空缺）
 */
class Menu extends BaseMenu
{
    /**
     * 找到最小的可用菜单ID
     *
     * @return int 最小可用ID
     */
    protected function findMinAvailableId(): int
    {
        $table = $this->getTable();

        // 获取所有已存在的ID（按顺序排列）
        $existingIds = DB::table($table)
            ->orderBy('id')
            ->limit(1000) // 项目也就有1000个建菜单
            ->pluck('id')
            ->toArray();

        if (empty($existingIds)) {
            return 1;
        }

        // 找到第一个空缺的ID
        $maxId = max($existingIds);
        for ($i = 1; $i <= $maxId; $i++) {
            if (!in_array($i, $existingIds)) {
                return $i;
            }
        }

        // 如果没有空缺，返回最大值+1
        return $maxId + 1;
    }

    /**
     * 模型启动方法
     *
     * 在创建菜单时，自动分配最小可用ID
     */
    protected static function boot()
    {
        parent::boot();

        // 创建时自动分配最小可用ID
        static::creating(function ($model) {
            if (!$model->id) {
                $minId = $model->findMinAvailableId();
                $model->id = $minId;
            }
        });
    }
}