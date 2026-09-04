<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

use Modules\User1\Models\User1;
use Modules\Application\Admin\LazyRenderable\UserInfo;
use Modules\Application\Admin\LazyRenderable\UserTable;

trait UserID
{
    public function selectTableUserID($field = 'user_id', $label = '用户ID')
    {

        $this->form->selectTable($field, $label)
            ->dialogWidth('80%')
            ->from(UserTable::make())
            ->model(User1::class, 'id', 'username')->required(); // 设置编辑数据显示
    }

    public function columnUserID($field = 'user_id', $label = '用户ID')
    {
        $this->grid->column($field, $label)->sortable()->expand(function () use ($field) {
            return UserInfo::make([
                'user_id' => $this->$field,
            ]);
        });
    }

    public function columnUserIDInfo($field = 'user_id', $label = '用户ID')
    {
        $this->grid->column($field, $label)->sortable()->expand(function () use ($field) {
            return UserInfo::make([
                'user_id' => $this->$field,
            ]);
        });
        $this->grid->column('user_info.nickname', '昵称');

    }
}
