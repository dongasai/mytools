<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Dcat\Admin\Show;
use Illuminate\Support\Arr;
use Modules\AFile\Services\ImgService;
use Modules\DcatAdmin\DcatAdmin\Show\Divider;
use Modules\DcatAdmin\DcatAdmin\Show\Expand;
use Modules\DcatAdmin\DcatAdmin\Traits\Options;

class ShowHelper
{
    use Options;

    /**
     * @var Show
     */
    public $show;

    /**
     * @var AdminController
     */
    public $controller;

    public function __construct($show, $controller)
    {
        $this->show = $show;
        $this->controller = $controller;
        $this->disableButton();
    }

    public function disableButton()
    {
        $this->show->disableEditButton();
        $this->show->disableQuickEdit();
        $this->show->disableDeleteButton();

    }

    public function field($name, $label = '')
    {
        return $this->show->field($name, $label);
    }

    public function divider($name)
    {
        return $this->show->fields()->push(new Divider($this->controller->_translation('divider.'.$name)));
    }

    /**
     * 图片字段
     *
     * @return Show\Field
     */
    public function fieldImg($name, $label = '')
    {
        return $this->show->field($name, $label)->as(function ($pp) {
            //            dump($pp);
            return ImgService::img2imgurl($pp);
        })->image();
    }

    public function fieldTimes($name, $label = '')
    {
        return $this->show->field($name, $label)->as(function ($ts) {
            //            dump($pp);
            return date('Y-m-d H:i:s', $ts);
        });
    }

    /**
     * 用户Id 字段
     *
     * @return Expand
     */
    public function fieldUserId($name = 'user_id', $label = '')
    {
        return $this->show->field($name, $label)->expand(\Modules\Application\Admin\LazyRenderable\UserInfo::make([
            'user_id' => $this->show->model()->$name,
        ]));
    }

    /**
     * 扩展使用
     *
     * @return Expand
     */
    public function fieldExpland($name, $make, $label = '')
    {
        return $this->show->field($name, $label)->expand($make);
    }

    public function fieldUseing($name, array $keys, $label = '')
    {
        $this->show->field($name, $label)->using($this->useing($name, $keys));
    }

    public function fieldModelCats($name, $label = '', $default = 0)
    {
        //        dd();

        $cates = $this->show->model()->getCasts();
        $enmu = $cates[$name] ?? '';
        if ($enmu === '') {
            throw new \Exception("$name is not a model casts");
        }
        //        dump($cates,$enmu::getValueDescription());
        $values = $enmu::getValueDescription();

        $this->show->field($name, $label)->as(function ($value) use ($values, $default) {
            if (is_null($value)) {
                return $default;
            }
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }

            return Arr::get($values, $value, $default);
        });
    }

    /**
     * 显示使用Cast类型转换的字段，并使用友好的标签和HTML表格展示
     *
     * @param  string  $name  字段名称
     * @param  string  $label  字段标签
     * @return \Dcat\Admin\Show\Field
     *
     * @throws \Exception 如果字段没有对应的Cast类
     */
    public function fieldModelCatsJson($name, $label = '')
    {
        $casts = $this->show->model()->getCasts();
        $castClass = $casts[$name] ?? '';

        if ($castClass === '') {
            throw new \Exception("$name is not a model casts");
        }

        return $this->show->field($name, $label)->as(function ($attributes) use ($castClass) {
            if (empty($attributes)) {
                return '<div class="empty-data">(无数据)</div>';
            }

            // 使用反射获取Cast类的属性注释
            $reflectionClass = new \ReflectionClass($castClass);
            $rows = [];

            foreach ($attributes as $key => $value) {
                try {
                    // 尝试获取属性
                    $property = $reflectionClass->getProperty($key);
                    $docComment = $property->getDocComment();

                    // 从注释中提取属性描述
                    $label = $key;
                    if ($docComment && preg_match('/\*\s+([^@\n]+)/', $docComment, $matches)) {
                        // 去除星号和前后空白
                        $label = trim(str_replace('*', '', $matches[1]));
                    }

                    // 格式化值，处理不同类型
                    if (is_bool($value)) {
                        $displayValue = $value ? '<span class="text-success">\u662f</span>' : '<span class="text-danger">\u5426</span>';
                    } elseif (is_array($value) || is_object($value)) {
                        $displayValue = '<code>'.htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)).'</code>';
                    } else {
                        $displayValue = htmlspecialchars((string) $value);
                    }

                    $rows[] = [
                        'label' => $label,
                        'value' => $displayValue,
                    ];
                } catch (\ReflectionException $e) {
                    // 如果属性不存在，使用原始键名
                    $rows[] = [
                        'label' => $key,
                        'value' => is_array($value) || is_object($value) ?
                            '<code>'.htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)).'</code>' :
                            htmlspecialchars((string) $value),
                    ];
                }
            }

            // 生成HTML表格
            $html = '<table class="table table-bordered table-striped table-hover" style="width: 100%; max-width: 100%;">';
            $html .= '<thead><tr><th style="width: 30%">属性</th><th>值</th></tr></thead>';
            $html .= '<tbody>';

            foreach ($rows as $row) {
                $html .= '<tr>';
                $html .= '<td><strong>'.$row['label'].'</strong></td>';
                $html .= '<td>'.$row['value'].'</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            return $html;
        })->unescape();
    }
}
