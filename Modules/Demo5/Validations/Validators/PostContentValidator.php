<?php

namespace Modules\Demo5\Validations\Validators;

use DLaravel\Validator\Validator;
use DLaravel\Validation\ValidationCore;

/**
 * 文章内容验证器
 *
 * 验证内容的有效性，包括长度、链接数量和内容质量检查
 */
class PostContentValidator extends Validator
{
    /**
     * 最小长度
     *
     * @var int
     */
    protected int $minLength = 20;

    /**
     * 最大长度
     *
     * @var int
     */
    protected int $maxLength = 50000;

    /**
     * 最大链接数量
     *
     * @var int
     */
    protected int $maxLinks = 10;

    /**
     * 最少词数
     *
     * @var int
     */
    protected int $minWords = 10;

    /**
     * 构造函数
     *
     * @param ValidationCore $validation 验证核心实例
     * @param array $args 参数数组，可包含 'minLength', 'maxLength', 'maxLinks', 'minWords'
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
        if (isset($args['maxLinks'])) {
            $this->maxLinks = $args['maxLinks'];
        }
        if (isset($args['minWords'])) {
            $this->minWords = $args['minWords'];
        }
    }

    /**
     * 验证内容
     *
     * @param mixed $value 待验证的值
     * @param array $data 全部数据（可选）
     * @return bool 验证通过返回 true
     */
    public function validate(mixed $value, array $data = []): bool
    {
        // 必须是字符串
        if (!is_string($value)) {
            return $this->addError('内容必须是字符串');
        }

        $content = $value;

        // 不能为空
        if (empty($content)) {
            return $this->addError('内容不能为空');
        }

        // 验证长度（去除HTML标签后）
        $cleanContent = strip_tags($content);
        $length = mb_strlen($cleanContent, 'UTF-8');

        if ($length < $this->minLength) {
            return $this->addError("内容至少需要{$this->minLength}个字符");
        }

        if ($length > $this->maxLength) {
            return $this->addError("内容不能超过{$this->maxLength}个字符");
        }

        // 检查链接数量
        $linkCount = substr_count($content, 'http://') + substr_count($content, 'https://');
        if ($linkCount > $this->maxLinks) {
            return $this->addError("链接数量不能超过{$this->maxLinks}个");
        }

        // 检查内容质量（词数）
        $wordCount = str_word_count($cleanContent);
        if ($wordCount < $this->minWords) {
            return $this->addError('内容过于简单，请提供更有价值的信息');
        }

        return true;
    }
}