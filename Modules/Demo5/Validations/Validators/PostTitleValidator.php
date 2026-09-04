<?php

namespace Modules\Demo5\Validations\Validators;

use DLaravel\Validator\Validator;
use DLaravel\Validation\ValidationCore;

/**
 * 文章标题验证器
 *
 * 验证标题的有效性，包括长度、格式和内容质量检查
 */
class PostTitleValidator extends Validator
{
    /**
     * 最小长度
     *
     * @var int
     */
    protected int $minLength = 5;

    /**
     * 最大长度
     *
     * @var int
     */
    protected int $maxLength = 200;

    /**
     * 构造函数
     *
     * @param ValidationCore $validation 验证核心实例
     * @param array $args 参数数组，可包含 'minLength', 'maxLength'
     * @param string $message 错误消息
     */
    public function __construct(ValidationCore $validation, array $args = [], string $message = '')
    {
        parent::__construct($validation, $args, $message);

        if (isset($args['minLength'])) {
            $this->minLength = $args['minLength'];
        }
        if (isset($args['maxLength'])) {
            $this->maxLength = $args['maxLength'];
        }
    }

    /**
     * 验证标题
     *
     * @param mixed $value 待验证的值
     * @param array $data 全部数据（可选）
     * @return bool 验证通过返回 true
     */
    public function validate(mixed $value, array $data = []): bool
    {
        // 必须是字符串
        if (!is_string($value)) {
            return $this->addError('标题必须是字符串');
        }

        $title = trim($value);

        // 不能为空
        if (empty($title)) {
            return $this->addError('标题不能为空');
        }

        // 验证长度
        $length = mb_strlen($title, 'UTF-8');
        if ($length < $this->minLength) {
            return $this->addError("标题至少需要{$this->minLength}个字符");
        }

        if ($length > $this->maxLength) {
            return $this->addError("标题不能超过{$this->maxLength}个字符");
        }

        // 检查是否包含控制字符
        if (preg_match('/[\x00-\x1F\x7F]/', $title)) {
            return $this->addError('标题包含非法字符');
        }

        // 检查是否全为空格或标点符号
        if (!preg_match('/[\p{L}\p{N}]/u', $title)) {
            return $this->addError('标题必须包含至少一个字母或数字');
        }

        return true;
    }
}