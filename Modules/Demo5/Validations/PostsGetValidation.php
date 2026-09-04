<?php

namespace Modules\Demo5\Validations;

use Inhere\Validate\Validation;
use Modules\Demo5\Validations\Validators\PostIdValidator;

/**
 * 获取文章详情验证类
 *
 * 验证获取文章详情请求的数据
 */
class PostsGetValidation extends Validation
{
    /**
     * 是否检查文章存在性
     *
     * @var bool
     */
    protected bool $checkExists = false;

    /**
     * 构造函数
     *
     * @param array $data 待验证的数据
     * @param bool $checkExists 是否检查文章存在性
     */
    public function __construct(array $data = [], bool $checkExists = false)
    {
        $this->checkExists = $checkExists;
        parent::__construct($data);
    }

    /**
     * 定义验证规则
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // 文章ID验证
            ['id', 'required', 'msg' => '文章ID不能为空'],
            ['id', new PostIdValidator($this->checkExists), 'msg' => '{attr}无效或不存在'],
        ];
    }

    /**
     * 定义字段翻译
     *
     * @return array
     */
    public function translates(): array
    {
        return [
            'id' => '文章ID',
        ];
    }

    /**
     * 验证前的处理
     *
     * @return bool
     */
    public function beforeValidate(): bool
    {
        // 数据预处理：转换为整数
        if (isset($this->data['id'])) {
            $this->data['id'] = (int) $this->data['id'];
        }

        return true;
    }
}