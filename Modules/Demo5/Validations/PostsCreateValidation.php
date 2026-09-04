<?php

namespace Modules\Demo5\Validations;

use Inhere\Validate\Validation;
use Modules\Demo5\Validations\Validators\PostTitleValidator;
use Modules\Demo5\Validations\Validators\PostContentValidator;

/**
 * 创建文章验证类
 *
 * 验证创建文章请求的数据
 */
class PostsCreateValidation extends Validation
{
    /**
     * 定义验证规则
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // 标题验证
            ['title', 'required', 'msg' => '标题不能为空'],
            ['title', new PostTitleValidator(), 'msg' => '{attr}不符合要求'],

            // 内容验证
            ['content', 'required', 'msg' => '内容不能为空'],
            ['content', new PostContentValidator(), 'msg' => '{attr}不符合要求'],
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
            'title' => '文章标题',
            'content' => '文章内容',
        ];
    }

    /**
     * 验证前的处理
     *
     * @return bool
     */
    public function beforeValidate(): bool
    {
        // 数据预处理：去除多余空格
        if (isset($this->data['title'])) {
            $this->data['title'] = trim($this->data['title']);
        }

        if (isset($this->data['content'])) {
            $this->data['content'] = trim($this->data['content']);
        }

        return true;
    }
}