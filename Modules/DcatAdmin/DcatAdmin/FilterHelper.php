<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Dcat\Admin\Grid\Filter;
use Modules\DcatAdmin\DcatAdmin\Grid\SelectTable;
use Modules\DcatAdmin\DcatAdmin\Traits\Options;

class FilterHelper
{
    use Options;

    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var AdminController
     */
    public $controller;

    public function __construct($filter, $controller)
    {
        $this->filter = $filter;
        $this->controller = $controller;
        $this->filter->expand();
        $this->filter->panel();

    }

    public function equal($field, $label = '')
    {
        $this->filter->equal($field, $label);
    }

    public function equalId($field = 'id', $label = 'ID')
    {
        $this->filter->equal($field, $label);
    }

    /**
     * in条件映射
     *
     * @return Filter\Where
     */
    public function inas($field, $field_origin, $label = '')
    {
        return $this->filter->where($field, function (\Illuminate\Database\Eloquent\Builder $query) use ($field_origin) {

            $query->whereIn($field_origin, explode(',', $this->input));

        }, $label);
    }

    /**
     * gt 条件映射
     *
     * @return Filter\Where
     */
    public function gtas($field, $field_origin, $label = '')
    {
        return $this->filter->where($field, function (\Illuminate\Database\Eloquent\Builder $query) use ($field_origin) {

            $query->where($field_origin, '>', $this->input);

        }, $label);
    }

    public function equaljson($field, $field1, $field2, $label = '')
    {
        $this->filter->where($field, function ($query) use ($field1, $field2) {

            $query->whereRaw(" $field1->\"$.$field2\" = '{$this->input}' ");

        }, $label);
    }

    public function equaljsonInt($field, $field1, $field2, $label = '')
    {
        $this->filter->where($field, function ($query) use ($field1, $field2) {

            $query->whereRaw(" $field1->\"$.$field2\" = {$this->input} ");

        }, $label);
    }

    /**
     * 字符串字段不为空
     *
     * @return void
     */
    public function columnNotSEmpey($field, $field2, $label = '')
    {
        $this->filter->where($field, function ($query) use ($field2) {

            $query->whereRaw(" `$field2`  != '' ");

        }, $label);
    }

    /**
     * 使用枚举 选择
     *
     * @return void
     */
    public function equalSelect($field, array $enmu, $label = '')
    {
        $this->filter->equal($field, $label)->select($enmu);
    }

    /**
     * 使用select 关联模型
     *
     * @return void
     */
    public function equalSelectModel($field, SelectTable $table, $label = '')
    {
        return $this->filter->equal($field)
            ->selectTable($table) // 设置渲染类实例，并传递自定义参数
            ->title($label)
            ->model($table->getModel(), $table->getModelSelectId(), $table->getModelViewName()); // 设置编辑数据显示
    }

    public function equalSelectVk($field, array $enmu, $label = '')
    {
        $this->filter->equal($field, $label)->select(array_flip($enmu));
    }

    /**
     * 等于
     *
     * @return Filter\Presenter\Presenter|Filter\Presenter\Radio
     */
    public function equalRadio($field, array $enmu, $label = '')
    {
        return $this->filter->equal($field, $label)->radio($enmu);
    }

    public function equalRadioModelCats($field, $label = '')
    {
        $cates = $this->filter->grid()->model()->repository()->model()->getCasts();
        $enmu = $cates[$field] ?? '';
        if ($enmu === '') {
            throw new \Exception("$field is not a model casts");
        }
        //        dump($cates,$enmu::getValueDescription());
        $values = $enmu::getValueDescription();

        return $this->filter->equal($field, $label)->radio($values);
    }

    public function equalRadioVk($field, array $enmu, $label = '')
    {
        return $this->filter->equal($field, $label)->radio(array_flip($enmu));
    }

    /**
     * 等于
     *
     * @param  array  $enmu
     * @return Filter\Presenter\Presenter|Filter\Presenter\Radio
     */
    public function equalRadioKv($field, array $kv, $label = '')
    {
        return $this->filter->equal($field, $label)->radio($kv);
    }

    /**
     * 多选默认值
     *
     * @return Filter\Presenter\Checkbox|Filter\Presenter\Presenter
     */
    public function checkboxDefault($field, $option, $default, $label = '')
    {
        $req = request($field);
        if (! $req) {
            /**
             * @var \Illuminate\Routing\Route $router
             */
            $router = request()->route();
            $p = request()->query();
            $p[$field] = $default;

            $to = route($router->getName(), $p);
            admin_exit(admin_redirect($to));

        }

        return $this->filter->where($field, function ($query) {}, $label)->checkbox($option)->default($default);

    }

    /**
     * 不等于判断
     *
     * @return Filter\Presenter\Presenter|Filter\Presenter\Radio
     */
    public function notequalRadio($field, array $enmu, $th = 'notEqual', $label = '')
    {
        $t = $field.'-'.$th;

        return $this->filter->notEqual($field, $label)->radio($this->useing($t, $enmu));
    }

    /**
     * 时间范围过滤器
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签
     * @return \Dcat\Admin\Grid\Filter\AbstractFilter
     */
    public function betweenDatetime(string $field, string $label)
    {
        return $this->filter->between($field, $label)->datetime();
    }
}
