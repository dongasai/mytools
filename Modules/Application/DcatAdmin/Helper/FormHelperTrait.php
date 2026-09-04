<?php

namespace Modules\Application\DcatAdmin\Helper;

use Dcat\Admin\Form\Field;
use Modules\Application\Enums\CONFIG_TYPE;
use Modules\Application\Enums\VIEW_TYPE;

/**
 * 表单辅助特性
 *
 * 提供系统模块后台控制器的表单构建功能的具体实现
 */
trait FormHelperTrait
{
    /**
     * 添加配置键名输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textKeyname(string $field = 'keyname', string $label = '键名'): Field\Text
    {
        return $this->form->text($field, $label)
            ->required()
            ->rules('required|max:100|unique:app_configs,keyname,{{id}}')
            ->help('配置键名，唯一标识，不可重复');
    }

    /**
     * 添加配置标题输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textTitle(string $field = 'title', string $label = '标题'): Field\Text
    {
        return $this->form->text($field, $label)
            ->required()
            ->rules('required|max:100')
            ->help('配置标题，用于显示');
    }

    /**
     * 添加配置类型选择
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function selectConfigType(string $field = 'type', string $label = '类型'): Field\Select
    {
        return $this->form->select($field, $label)
            ->options([
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
            ])
            ->required()
            ->help('配置值的数据类型');
    }

    /**
     * 添加配置值输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textareaValue(string $field = 'value', string $label = '值'): Field\Textarea
    {
        return $this->form->textarea($field, $label)
            ->rows(3)
            ->help('配置的值，根据类型不同有不同的格式要求');
    }

    /**
     * 添加配置分组输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textGroup(string $field = 'group', string $label = '分组'): Field\Text
    {
        return $this->form->text($field, $label)
            ->help('配置分组，用于分类管理');
    }

    /**
     * 添加配置子分组输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textGroup2(string $field = 'group2', string $label = '子分组'): Field\Text
    {
        return $this->form->text($field, $label)
            ->help('配置子分组，用于更细粒度的分类管理');
    }

    /**
     * 添加配置描述输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textareaDesc(string $field = 'desc', string $label = '描述'): Field\Textarea
    {
        return $this->form->textarea($field, $label)
            ->rows(3)
            ->help('配置的详细描述');
    }

    /**
     * 添加配置选项输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textareaOptions(string $field = 'options', string $label = '选项'): Field\Textarea
    {
        return $this->form->textarea($field, $label)
            ->rows(3)
            ->help('JSON格式的选项配置，如：{"option1":"选项1","option2":"选项2"}');
    }

    /**
     * 添加是否客户端可用选择
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function switchIsClient(string $field = 'is_client', string $label = '客户端可用'): Field\SwitchField
    {
        return $this->form->switch($field, $label)
            ->default(false)
            ->help('是否允许客户端访问此配置');
    }

    /**
     * 添加视图类型选择
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function selectViewType(string $field = 'type1', string $label = '视图类型'): Field\Select
    {
        return $this->form->select($field, $label)
            ->options([
                VIEW_TYPE::PRIVATE->value => '私有',
                VIEW_TYPE::PUBLIC->value => '公共',
            ])
            ->default(VIEW_TYPE::PRIVATE->value)
            ->help('视图的访问权限类型');
    }

    /**
     * 添加路由名称输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textRouterName(string $field = 'router_name', string $label = '路由名称'): Field\Text
    {
        return $this->form->text($field, $label)
            ->help('视图对应的路由名称');
    }

    /**
     * 添加参数输入
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     */
    public function textareaParams(string $field = 'p1', string $label = '参数'): Field\Textarea
    {
        return $this->form->textarea($field, $label)
            ->rows(3)
            ->help('JSON格式的参数配置');
    }
}
