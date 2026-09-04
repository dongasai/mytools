<?php

namespace Modules\Demo5\Validations;

use Inhere\Validate\Validation;

/**
 * 文章列表验证类
 *
 * 验证文章列表请求的分页参数
 */
class PostsListValidation extends Validation
{
    /**
     * 定义验证规则
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // 页码验证
            ['page', 'integer', 'min' => 1, 'max' => 1000, 'msg' => '页码必须是1-1000之间的整数'],

            // 每页数量验证
            ['pageSize', 'integer', 'min' => 1, 'max' => 100, 'msg' => '每页数量必须是1-100之间的整数'],
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
            'page' => '页码',
            'pageSize' => '每页数量',
        ];
    }

    /**
     * 定义默认值
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'page' => 1,
            'pageSize' => 20,
        ];
    }

    /**
     * 验证前的处理
     *
     * @return bool
     */
    public function beforeValidate(): bool
    {
        // 设置默认值
        if (!isset($this->data['page'])) {
            $this->data['page'] = 1;
        }

        if (!isset($this->data['pageSize'])) {
            $this->data['pageSize'] = 20;
        }

        // 数据预处理：转换为整数
        $this->data['page'] = (int) $this->data['page'];
        $this->data['pageSize'] = (int) $this->data['pageSize'];

        return true;
    }
}