<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Carbon\CarbonInterface;
use Dcat\Admin\Grid;
use Dcat\Admin\Widgets\Table;
use Illuminate\Support\Arr;
use Modules\DcatAdmin\DcatAdmin\Grid\Views\GridHeader;
use Modules\DcatAdmin\DcatAdmin\Traits\AdminId;
use Modules\DcatAdmin\DcatAdmin\Traits\UserID;

class GridHelper
{
    use AdminId, \Modules\DcatAdmin\DcatAdmin\Traits\Options, UserID;

    /**
     * @var Grid
     */
    public $grid;

    /**
     * @var AdminController
     */
    public $controller;

    public function __construct($grid, $controller)
    {
        $this->grid = $grid;
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
        });
        $this->controller = $controller;
        if (request('in_iframe')) {
            $grid->model()->setConstraints(['in_iframe' => 1]);

        }
        GridHeader::gridHeader($grid);

    }

    public function columnId()
    {
        return $this->grid->column('id', 'ID')->sortable();
    }

    /**
     * 创建时间 - 使用统一的时间格式
     *
     * @param  string  $field  字段名，默认为created_at
     * @param  string  $label  标签名，默认为创建时间
     * @return Grid\Column
     */
    public function columnCreatedAt($field = 'created_at', $label = '创建时间')
    {
        return $this->columnDateTime($field, $label);
    }

    /**
     * 更新时间 - 使用统一的时间格式
     *
     * @param  string  $field  字段名，默认为updated_at
     * @param  string  $label  标签名，默认为更新时间
     * @return Grid\Column
     */
    public function columnUpdatedAt($field = 'updated_at', $label = '更新时间')
    {
        return $this->columnDateTime($field, $label);
    }

    /**
     * 时间信息组合列 - 将创建时间和更新时间组合显示
     *
     * @param  string  $createdAtField  创建时间字段名，默认为created_at
     * @param  string  $updatedAtField  更新时间字段名，默认为updated_at
     * @param  string  $label  标签名，默认为时间信息
     * @return Grid\Column
     */
    public function columnTimes($createdAtField = 'created_at', $updatedAtField = 'updated_at', $label = '时间信息')
    {
        return $this->grid->column($createdAtField, $label)->display(function ($createdAt) use ($updatedAtField) {
            $updatedAt = $this->{$updatedAtField} ?? '';

            // 格式化创建时间
            $createdAtFormatted = $this->formatDateTime($createdAt);
            $createdAtHtml = "<div><small class='text-muted'>创建:</small> {$createdAtFormatted}</div>";

            // 格式化更新时间
            $updatedAtHtml = '';
            if ($updatedAt) {
                $updatedAtFormatted = $this->formatDateTime($updatedAt);
                $updatedAtHtml = "<div><small class='text-muted'>更新:</small> {$updatedAtFormatted}</div>";
            }

            return $createdAtHtml.$updatedAtHtml;
        })->sortable();
    }

    /**
     * 格式化时间的私有方法
     *
     * @param  mixed  $value  时间值
     * @return string 格式化后的时间字符串
     */
    private function formatDateTime($value)
    {
        // 检查空值（但不包括0，因为0是有效的时间戳）
        if (is_null($value) || $value === '') {
            return '-';
        }

        // 如果是时间戳，转换为日期时间字符串
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }

        // 如果是Carbon实例或DateTime对象
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // 如果是字符串，尝试转换为标准格式
        if (is_string($value)) {
            try {
                $date = new \DateTime($value);

                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return $value; // 如果转换失败，返回原值
            }
        }

        return $value;
    }

    public function columnIdDesc()
    {
        $this->grid->column('id', 'ID')->sortable();
        $this->grid->model()->orderByDesc('id');

        return $this;
    }

    public function disableAll()
    {
        $this->grid->disableCreateButton();
        $this->grid->disableBatchActions();
        $this->grid->disableDeleteButton();
        $this->grid->disableEditButton();
        $this->grid->disableQuickEditButton();
        $this->grid->disableViewButton();

        //        $this->grid->disableBatchActions();
        return $this;
    }

    /**
     * @return $this
     */
    public function columnView($field, $view, $label = '')
    {
        $this->grid->column($field, $label)->view($view);

        return $this;
    }

    public function columnImg($field, $width = 100, $hei = 100, $label = '')
    {
        $this->grid->column($field, $label)->display(function ($p2) {
            //            dump($p2);
            return Img::img2imgurl($p2);
        })->image('', $width, $hei);
    }

    public function columnExpand($field, $fields = [], $label = '')
    {
        $controller = $this->controller;
        $this->grid->column($field, $label)->expand(function (Grid\Displayers\Expand $exped) use ($fields, $controller) {
            //            dump(func_get_args());
            $headers = [
                'admin.kv.name',
                'admin.kv.value',
            ];
            $data = [];
            //            $exped->row;
            //            dd($exped);
            foreach ($fields as $f) {
                $data[] = [
                    $controller->_label($f), $exped->row->$f,
                ];
            }

            return Table::make($headers, $data);
        });
    }

    /**
     * 翻译 字段内容
     *
     * @return void
     */
    public function columnTranslation($field, $label = '')
    {
        $this->grid->column($field, $label)->display(function ($value, $column, $controller) {
            //            dd(func_get_args());
            /**
             * @var AdminController $controller
             */
            return $controller->_translation('key.'.$value);
        }, $this->controller);
    }

    /**
     * 展示的时候除以100
     *
     * @param  string  $field
     * @return void
     */
    public function columnc100($field, $label = '')
    {
        $this->grid->column($field, $label)->display(function ($p2) {
            return $p2 / 100;
        });
    }

    public function columnc1000($field, $label = '')
    {
        return $this->grid->column($field, $label)->display(function ($p2) {
            return $p2 / 1000;
        });
    }

    /**
     * 展示 时间戳
     *
     * @param  string  $field
     * @return void
     */
    public function columnAt($field, $label = '')
    {
        $this->grid->column($field, $label)->display(function ($p2) {
            return date(\DateTime::W3C, $p2);
        });
    }

    public function columnAtd($field, $label = '')
    {
        return $this->grid->column($field, $label)->display(function (/* \Carbon\Carbon */ $p2) {
            if (! $p2) {
                return null;
            }

            //            dd($p2);
            return $p2->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
        })->width(105);
    }

    /**
     * 标准时间格式列 - 年-月-日 时:分:秒
     *
     * @param  string  $field  字段名
     * @param  string  $label  标签名
     * @return Grid\Column
     */
    public function columnDateTime($field, $label = '')
    {
        return $this->grid->column($field, $label)->display(function ($value) {
            return self::formatDateTimeStatic($value);
        })->sortable();
    }

    /**
     * 使用枚举展示
     *
     * @return $this
     */
    public function columnUseing($field, $enmu, $label = '')
    {
        $this->grid->column($field, $label)->use($enmu);

        return $this;
    }

    /**
     * 使用枚举展示
     *
     * @param  $enmu
     */
    public function columnUseingEnmu($field, $enmuClass, $label = ''): Grid\Column
    {
        $option = $enmuClass::getValueDescription();
        $default = '';

        //        dump($option);
        return $this->grid->column($field, $label)->display(function ($value) use ($option, $default) {
            if (is_null($value)) {
                return $default;
            }
            if ($value instanceof \UnitEnum) {
                $value = $value->value;
            }

            return Arr::get($option, $value, $default);
        });
    }

    public function columnUsingVk($field, $enmu, $label = '')
    {
        $res = array_flip($enmu);

        //        dd($res);
        return $this->grid->column($field, $label)->using($res);

    }

    public function columnUsingkv($field, $enmu, $label = '')
    {
        //        dump($enmu);
        return $this->grid->column($field, $label)->using($enmu);
    }

    /**
     * 使用枚举展示.new
     *
     * @return Grid\Column
     */
    public function fieldUseing($name, array $keys, $label = '')
    {
        return $this->grid->column($name, $label)->using($this->useing($name, $keys));
    }

    /**
     * 使用lab
     *
     * @param  array  $keys
     * @return Grid\Column
     */
    public function fieldLable1($name, $label = '')
    {
        $c = $this->controller;

        return $this->grid->column($name, $label)->display(function ($value, Grid\Column $column) use ($c) {
            //            $list = explode(',',$c)
            $list = [];
            foreach ($value as $item) {
                //                dd($this);
                $list[] = $c->_option($column->getName().'-'.$item);
            }

            return $list;
        })->label();
    }

    /**
     * 模型字段展示-cats
     *
     * @return Grid\Column
     *
     * @throws \Exception
     */
    public function columnModelCats($name, $default = null, $label = '', $edit = false)
    {
        $cates = $this->grid->model()->repository()->model()->getCasts();
        $enmu = $cates[$name] ?? '';
        if ($enmu === '') {
            throw new \Exception("$name is not a model casts");
        }
        $values = $enmu::getValueDescription();

        if ($edit) {

            $res = $this->grid->column($name)->display(function ($value) use ($default) {
                if (is_null($value)) {
                    return $default;
                }
                if ($value instanceof \UnitEnum) {
                    $value = $value->value();
                }

                return $value;
            })->radio($values);
        } else {
            $res = $this->grid->column($name, $label)->display(function ($value) use ($values, $default) {
                if (is_null($value)) {
                    return $default;
                }
                if ($value instanceof \UnitEnum) {
                    $value = $value->value();
                }

                return Arr::get($values, $value, $default);
            });
        }

        return $res;
    }

    /**
     * 格式化时间的静态方法
     *
     * @param  mixed  $value  时间值
     * @return string 格式化后的时间字符串
     */
    private static function formatDateTimeStatic($value)
    {
        // 检查空值（但不包括0，因为0是有效的时间戳）
        if (is_null($value) || $value === '') {
            return '-';
        }

        // 如果是时间戳，转换为日期时间字符串
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', $value);
        }

        // 如果是Carbon实例或DateTime对象
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // 如果是字符串，尝试转换为标准格式
        if (is_string($value)) {
            try {
                $date = new \DateTime($value);

                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return $value; // 如果转换失败，返回原值
            }
        }

        return $value;
    }
}
