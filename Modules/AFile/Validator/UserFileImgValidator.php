<?php

namespace Modules\AFile\Validator;

use DLaravel\Validator\Validator;
use Modules\AFile\Models\FileImg;

/**
 * 文件归属验证器
 */
class UserFileImgValidator extends Validator
{
    /**
     * 验证文件是否属于用户
     *
     * @param  mixed  $value  文件ID
     * @param  array  $data  数据数组
     * @return bool 验证结果
     */
    public function validate(mixed $value, array $data): bool
    {
        $ufiled = $this->args[0] ?? 'user_id';
        $user_id = $data[$ufiled] ?? null;

        return self::check($user_id, $value);
    }

    /**
     * 检查文件是否属于用户
     *
     * @param  int  $user_id  用户ID
     * @param  int  $file_id  文件ID
     * @return bool 检查结果
     */
    public function check($user_id, $file_id)
    {
        $file = FileImg::query()->where([
            'user_id' => $user_id,
            'id' => $file_id,
        ])->first();
        if ($file) {
            return true;
        }

        return false;
    }
}
