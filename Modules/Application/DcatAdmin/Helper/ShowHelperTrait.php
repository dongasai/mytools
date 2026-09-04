<?php

namespace Modules\Application\DcatAdmin\Helper;

use Dcat\Admin\Show;
use Modules\Application\Enums\CONFIG_TYPE;
use Modules\Application\Enums\VIEW_TYPE;

/**
 * 详情页辅助特性
 *
 * 提供系统模块后台控制器的详情页构建功能的具体实现
 */
trait ShowHelperTrait
{
    /**
     * 显示配置键名
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldKeyname(string $field = 'keyname', string $label = '键名'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示配置标题
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldTitle(string $field = 'title', string $label = '标题'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示配置类型
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldConfigType(string $field = 'type', string $label = '类型'): Show\Field
    {
        return $this->show->field($field, $label)->as(function ($value) {
            $types = [
                CONFIG_TYPE::TYPE_INT->value => '整数',
                CONFIG_TYPE::TYPE_IMG->value => '图片',
                CONFIG_TYPE::TYPE_BOOL->value => '布尔值',
                CONFIG_TYPE::TYPE_STRING->value => '字符串',
                CONFIG_TYPE::TYPE_FLOAT->value => '浮点数',
                CONFIG_TYPE::TYPE_FILE->value => '文件',
                CONFIG_TYPE::TYPE_PERCENTAGE->value => '百分比',
                CONFIG_TYPE::TYPE_TIME->value => '时间',
                CONFIG_TYPE::TYPE_IS->value => '是否',
                CONFIG_TYPE::TYPE_JSON->value => 'JSON数组',
                CONFIG_TYPE::TYPE_EMBEDS->value => 'JSON键值对',
            ];

            return $types[$value] ?? '未知类型';
        });
    }

    /**
     * 显示配置值
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldValue(string $field = 'value', string $label = '值'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示配置分组
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldGroup(string $field = 'group', string $label = '分组'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示配置子分组
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldGroup2(string $field = 'group2', string $label = '子分组'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示配置描述
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldDesc(string $field = 'desc', string $label = '描述'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示配置选项
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldOptions(string $field = 'options', string $label = '选项'): Show\Field
    {
        return $this->show->field($field, $label)->json();
    }

    /**
     * 显示是否客户端可用
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldIsClient(string $field = 'is_client', string $label = '客户端可用'): Show\Field
    {
        return $this->show->field($field, $label)->as(function ($value) {
            return $value ? '是' : '否';
        });
    }

    /**
     * 显示视图类型
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldViewType(string $field = 'type1', string $label = '视图类型'): Show\Field
    {
        return $this->show->field($field, $label)->as(function ($value) {
            if ($value instanceof VIEW_TYPE) {
                return match ($value) {
                    VIEW_TYPE::PRIVATE => '私有',
                    VIEW_TYPE::PUBLIC => '公共',
                    default => $value->value
                };
            }

            return $value;
        });
    }

    /**
     * 显示路由名称
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldRouterName(string $field = 'router_name', string $label = '路由名称'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示参数
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldParams(string $field = 'p1', string $label = '参数'): Show\Field
    {
        return $this->show->field($field, $label)->json();
    }

    /**
     * 显示管理员ID
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldAdminId(string $field = 'admin_id', string $label = '管理员ID'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示创建时间
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldCreatedAt(string $field = 'created_at', string $label = '创建时间'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示更新时间
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldUpdatedAt(string $field = 'updated_at', string $label = '更新时间'): Show\Field
    {
        return $this->show->field($field, $label);
    }

    /**
     * 显示删除时间
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function fieldDeletedAt(string $field = 'deleted_at', string $label = '删除时间'): Show\Field
    {
        return $this->show->field($field, $label);
    }
}
