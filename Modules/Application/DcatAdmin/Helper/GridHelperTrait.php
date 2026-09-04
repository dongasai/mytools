<?php

namespace Modules\Application\DcatAdmin\Helper;

use Dcat\Admin\Grid\Column;
use Modules\Application\Enums\CONFIG_TYPE;
use Modules\Application\Enums\VIEW_TYPE;

/**
 * 列表页辅助特性
 *
 * 提供系统模块后台控制器的列表页构建功能的具体实现
 */
trait GridHelperTrait
{
    /**
     * 添加ID列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnId(string $field = 'id', string $label = 'ID'): Column
    {
        return $this->grid->column($field, $label)->sortable();
    }

    /**
     * 添加配置键名列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnKeyname(string $field = 'keyname', string $label = '键名'): Column
    {
        return $this->grid->column($field, $label);
    }

    /**
     * 添加配置标题列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnTitle(string $field = 'title', string $label = '标题'): Column
    {
        return $this->grid->column($field, $label);
    }

    /**
     * 添加配置类型列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnConfigType(string $field = 'type', string $label = '类型'): Column
    {
        return $this->grid->column($field, $label)->display(function ($value) {
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
     * 添加配置值列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     * @param  int  $limit  限制长度
     */
    public function columnValue(string $field = 'value', string $label = '值', int $limit = 30): Column
    {
        return $this->grid->column($field, $label)->limit($limit);
    }

    /**
     * 添加配置分组列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnGroup(string $field = 'group', string $label = '分组'): Column
    {
        return $this->grid->column($field, $label);
    }

    /**
     * 添加配置子分组列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnGroup2(string $field = 'group2', string $label = '子分组'): Column
    {
        return $this->grid->column($field, $label);
    }

    /**
     * 添加配置描述列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     * @param  int  $limit  限制长度
     */
    public function columnDesc(string $field = 'desc', string $label = '描述', int $limit = 30): Column
    {
        return $this->grid->column($field, $label)->limit($limit);
    }

    /**
     * 添加是否客户端可用列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnIsClient(string $field = 'is_client', string $label = '客户端可用'): Column
    {
        return $this->grid->column($field, $label)->bool();
    }

    /**
     * 添加视图类型列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnViewType(string $field = 'type1', string $label = '视图类型'): Column
    {
        return $this->grid->column($field, $label)->display(function ($value) {
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
     * 添加路由名称列
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function columnRouterName(string $field = 'router_name', string $label = '路由名称'): Column
    {
        return $this->grid->column($field, $label);
    }
}
