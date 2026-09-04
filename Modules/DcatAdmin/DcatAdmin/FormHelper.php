<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Dcat\Admin\Form;
use Modules\DcatAdmin\DcatAdmin\Traits\Options;
use Modules\DcatAdmin\DcatAdmin\Traits\UserID;

/**
 * 表单助手类 - 自动根据类属性生成表单字段
 *
 * 使用示例:
 * $formHelper = new FormHelper($form);
 * $formHelper->embedClassFields('user', '用户信息', [User::class]);
 *
 * 类属性注释示例:
 *
 * @label 用户名
 */
class FormHelper
{
    use Options, UserID;

    /**
     * @var Form
     */
    public $form;

    /**
     * @var AdminController
     */
    public $controller;

    public function __construct($form, $controller)
    {

        $this->form = $form;
        $this->controller = $controller;
    }

    public function enableBack()
    {
        $this->form->tools(function (Form\Tools $tools) {
            $tools->prepend();
        });
    }

    /**
     * @return Form\Field|Form\Field[]|\Illuminate\Support\Collection|null
     */
    public function field($field)
    {
        return $this->form->field($field);
    }

    /**
     * 嵌套表单
     * json,cats model
     *
     * @return Form\Field\Embeds
     *
     * @throws \ReflectionException
     */
    public function embedsCats($field, $lable)
    {
        $modeClass = get_class($this->form->repository()->model());
        $cates = $this->form->repository()->model()->getCasts();
        //        dump($cates);
        $class = $cates[$field] ?? '';
        $reflectionClass = new \ReflectionClass($class);

        return $this->form->embeds($field, $lable, function (\Dcat\Admin\Form\EmbeddedForm $form) use ($reflectionClass) {

            // 从注释中提取字段标签
            $getFieldLabel = function ($name, $comment) {
                if ($comment) {
                    // 优先提取@label标签
                    if (preg_match('/@label\s+([^\r\n]+)/', $comment, $matches)) {
                        return trim($matches[1]);
                    }

                    // 处理多行注释，提取第一行描述
                    $lines = explode("\n", $comment);
                    foreach ($lines as $line) {
                        // 匹配描述行 (以 * 开头但不包含@标签的行)
                        if (preg_match('/^\s*\*\s+([^@*]+)/', $line, $matches)) {
                            $desc = trim($matches[1]);
                            if (! empty($desc) && ! preg_match('/@\w+/', $desc)) {
                                return $desc;
                            }
                        }
                    }

                    // 最后提取@var类型后的描述
                    if (preg_match('/@var\s+\S+\s+([^\r\n]+)/', $comment, $matches)) {
                        $desc = trim($matches[1]);
                        if (! empty($desc)) {
                            return $desc;
                        }
                    }
                }

                // 默认使用字段名转换
                return ucwords(str_replace(['_', '-'], ' ', $name));
            };
            foreach ($reflectionClass->getProperties() as $property) {
                // 设置不同类型的表单
                $type = $property->getType() ? $property->getType()->getName() : null;
                $name = $property->getName();
                $comment = $property->getDocComment();

                // 根据类型设置不同表单字段
                switch ($type) {
                    case 'string':
                        $form->text($name, $getFieldLabel($name, $comment));
                        break;
                    case 'int':
                        $form->number($name, $getFieldLabel($name, $comment));
                        break;
                    case 'float':
                        $form->decimal($name, $getFieldLabel($name, $comment));
                        break;
                    case 'bool':
                        $form->switch($name, $getFieldLabel($name, $comment));
                        break;
                    default:
                        $form->text($name, $getFieldLabel($name, $comment));
                }
            }

            // 返回表单对象
            return $form;

        });
    }

    /**
     * 开关
     *
     * @param  $default
     * @return Form\Field\SwitchField
     */
    public function switch($field, $label = null)
    {
        return $this->form->switch($field, $label);
    }

    /**
     * number
     *
     * @return Form\Field\Number
     */
    public function number($field, $label = null)
    {
        return $this->form->number($field, $label);
    }

    /**
     * 字符串
     *
     * @return Form\Field\Text
     */
    public function text($field, $label = null)
    {
        return $this->form->text($field, $label);
    }

    public function hidden($field, $label = null)
    {
        return $this->form->hidden($field, $label);
    }

    /**
     * 单select
     *
     * @return Form\Field\Select
     */
    public function select($field, $label = null)
    {
        return $this->form->select($field, $label);
    }

    /**
     *  单select 带枚举的
     *
     * @param  string  $field  字段
     * @param  array  $enmu  枚举
     * @return Form\Field\Select|mixed
     */
    public function selectOption($field, $enmu, $label = null)
    {
        return $this->form->select($field, $label)->options($this->useing($field, $enmu));
    }

    public function selectOptionCast($field, $label = null)
    {
        $modeClass = get_class($this->form->repository()->model());
        $cates = $this->form->repository()->model()->getCasts();
        //        dump($cates);
        $enmu = $cates[$field] ?? '';
        if ($enmu === '') {
            throw new \Exception("$field is not a model $modeClass casts");
        }
        $values = $enmu::getValueDescription();

        return $this->form->select($field, $label)->options($values);
    }

    /**
     * 多 select
     *
     * @return Form\Field\MultipleSelect
     */
    public function multipleSelect($field, $label = null)
    {
        return $this->form->multipleSelect($field, $label);
    }

    /**
     * 多 select表,选择器
     *
     * @return Form\Field\MultipleSelectTable
     */
    public function multipleSelectTable($field, $label = null)
    {
        return $this->form->multipleSelectTable($field, $label);
    }

    /**
     * 多 select表,选择器
     *
     * @return Form\Field\SelectTable
     */
    public function selectTable($field, $label = null)
    {
        return $this->form->selectTable($field, $label);
    }

    /**
     * 多选
     *
     * @return Form\Field\Checkbox
     */
    public function checkbox($field, $label = null)
    {
        return $this->form->checkbox($field, $label);
    }

    /**
     * 单选
     *
     * @return Form\Field\Radio
     */
    public function radio($field, array $enmu, $label = null)
    {
        $lable = $lable ?? $field;

        return $this->form->radio($field, $label)->options($enmu);
    }

    /**
     * 列表
     *
     *
     * @return Form\Field\ListField
     */
    public function list($field, $label = null)
    {
        return $this->form->list($field, $label);
    }

    /**
     * 图片表单
     *
     * @return $this
     */
    public function image($field, $label = null)
    {
        $this->form->image($field, $label);

        return $this;
    }

    /**
     * @return $this
     */
    public function editor($field, $label = null)
    {
        $this->form->editor($field, $label);

        return $this;
    }

    public function multipleImage($field, $label = null)
    {
        $this->form->multipleImage($field, $label);

        return $this;
    }

    /**
     *  分割线
     *
     * @return $this
     */
    public function divider($title = null)
    {
        $this->form->divider($this->controller->_translation('divider-'.$title));

        return $this;
    }

    /**
     * 展示字段
     *
     * @return Form\Field\Display
     */
    public function display($field, $label = null)
    {
        return $this->form->display($field, $label);
    }

    /**
     * @return Form\Field\Display
     */
    public function displayOptions($field, $label = null)
    {
        $c = $this->controller;

        return $this->form->display($field, $label)->with(function ($value) use ($field, $c) {
            return $c->_option($field.'-'.$value);

        });
    }

    /**
     * 文本
     *
     * @return $this
     */
    public function textarea($field, $label = null)
    {
        return $this->form->textarea($field, $label);
    }

    /**
     * 增加表格
     *
     * @return Form\Field\Table
     */
    public function table($field, $call, $label = null)
    {
        return $this->form->table($field, $label, $call);
    }

    /**
     * tags
     *
     * @return Form\Field\Tags
     */
    public function tags($field, $label = null)
    {
        return $this->form->tags($field, $label);
    }

    /**
     * 关闭所有的 多余
     *
     * @return void
     */
    public function disableAll()
    {
        $this->form->disableDeleteButton();
        $this->form->disableCreatingCheck();
        $this->form->disableViewButton();
        //        $this->form->disableListButton();
        $this->form->disableViewCheck();
        $this->form->disableEditingCheck();
    }

    public function disableButton4()
    {
        $this->form->disableDeleteButton();
        $this->form->disableViewButton();
        //        $this->form->disableListButton();
        $this->form->disableViewCheck();
        $this->form->disableEditingCheck();
    }

    /**
     * select模型选项
     *
     * @return Form\Field\Select|mixed
     */
    public function selectModelOption($field, $label, $modelClass, $name = 'name', $id = 'id')
    {
        $options = $modelClass::all()->pluck($name, $id);

        return $this->form->select($field, $label)->options($options);

    }
}
