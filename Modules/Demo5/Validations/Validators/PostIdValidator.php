<?php

namespace Modules\Demo5\Validations\Validators;

use DLaravel\Validator\Validator;
use DLaravel\Validation\ValidationCore;
use Modules\Demo5\Models\Demo5Post;

/**
 * 文章ID验证器
 *
 * 验证文章ID的有效性，包括格式和存在性检查
 */
class PostIdValidator extends Validator
{
    /**
     * 是否验证存在性
     *
     * @var bool
     */
    protected bool $checkExists = true;

    /**
     * 构造函数
     *
     * @param ValidationCore $validation 验证核心实例
     * @param array $args 参数数组，可包含 'checkExists'
     * @param string $message 错误消息
     */
    public function __construct(ValidationCore $validation, array $args = [], string $message = '')
    {
        parent::__construct($validation, $args, $message);

        if (isset($args['checkExists'])) {
            $this->checkExists = $args['checkExists'];
        }
    }

    /**
     * 验证文章ID
     *
     * @param mixed $value 待验证的值
     * @param array $data 全部数据（可选）
     * @return bool 验证通过返回 true
     */
    public function validate(mixed $value, array $data = []): bool
    {
        // 必须是整数或数字字符串
        if (!is_int($value) && !ctype_digit((string)$value)) {
            return $this->addError('文章ID必须是整数');
        }

        $postId = (int) $value;

        // 必须是正整数
        if ($postId <= 0) {
            return $this->addError('文章ID必须是正整数');
        }

        // 检查文章是否存在
        if ($this->checkExists) {
            $post = Demo5Post::find($postId);
            if (!$post) {
                return $this->addError('文章不存在');
            }
        }

        return true;
    }
}