<?php

namespace Modules\DcatAdmin\DcatAdmin\Helper;

use Dcat\Admin\Grid;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 表格辅助类
 * 提供通用的表格列配置方法
 */
class GridHelper
{
    protected Grid $grid;

    protected AdminController $controller;

    public function __construct(Grid $grid, AdminController $controller)
    {
        $this->grid = $grid;
        $this->controller = $controller;
    }

    /**
     * 添加ID列
     */
    public function columnId(string $field = 'id', string $label = 'ID'): Grid\Column
    {
        return $this->grid->column($field, $label)->sortable();
    }

    /**
     * 添加创建时间列
     */
    public function columnCreatedAt(string $field = 'created_at', string $label = '创建时间'): Grid\Column
    {
        return $this->grid->column($field, $label)->sortable();
    }

    /**
     * 添加更新时间列
     */
    public function columnUpdatedAt(string $field = 'updated_at', string $label = '更新时间'): Grid\Column
    {
        return $this->grid->column($field, $label)->sortable();
    }

    /**
     * 添加状态列
     */
    public function columnStatus(string $field = 'status', string $label = '状态', array $options = []): Grid\Column
    {
        $defaultOptions = [
            0 => '禁用',
            1 => '启用',
        ];

        $options = array_merge($defaultOptions, $options);

        return $this->grid->column($field, $label)
            ->using($options)
            ->dot([
                0 => 'danger',
                1 => 'success',
            ], 'warning');
    }

    /**
     * 添加操作列
     */
    public function columnActions(string $label = '操作', ?callable $callback = null): Grid\Column
    {
        $column = $this->grid->column('actions', $label);

        if ($callback) {
            $column->display($callback);
        }

        return $column;
    }

    /**
     * 添加图片列
     */
    public function columnImage(string $field, string $label, int $width = 50, int $height = 50): Grid\Column
    {
        return $this->grid->column($field, $label)->image('', $width, $height);
    }

    /**
     * 添加链接列
     */
    public function columnLink(string $field, string $label, string $target = '_blank'): Grid\Column
    {
        return $this->grid->column($field, $label)->link($target);
    }

    /**
     * 添加标签列
     */
    public function columnLabel(string $field, string $label, array $colors = []): Grid\Column
    {
        $column = $this->grid->column($field, $label)->label();

        if (! empty($colors)) {
            $column->using($colors);
        }

        return $column;
    }

    /**
     * 添加进度条列
     */
    public function columnProgressBar(string $field, string $label, string $style = 'primary', int $max = 100): Grid\Column
    {
        return $this->grid->column($field, $label)->progressBar($style, 'sm', $max);
    }

    /**
     * 添加开关列
     */
    public function columnSwitch(string $field, string $label): Grid\Column
    {
        return $this->grid->column($field, $label)->switch();
    }

    /**
     * 添加复选框列
     */
    public function columnCheckbox(string $field = 'id', string $label = ''): Grid\Column
    {
        return $this->grid->column($field, $label)->checkbox();
    }

    /**
     * 添加排序列
     */
    public function columnSort(string $field = 'sort', string $label = '排序'): Grid\Column
    {
        return $this->grid->column($field, $label)->sortable()->editable();
    }

    /**
     * 添加金额列
     */
    public function columnMoney(string $field, string $label, string $currency = '¥', int $decimals = 2): Grid\Column
    {
        return $this->grid->column($field, $label)->display(function ($value) use ($currency, $decimals) {
            return $currency.number_format($value, $decimals);
        });
    }

    /**
     * 添加百分比列
     */
    public function columnPercentage(string $field, string $label, int $decimals = 2): Grid\Column
    {
        return $this->grid->column($field, $label)->display(function ($value) use ($decimals) {
            return number_format($value, $decimals).'%';
        });
    }

    /**
     * 添加文件大小列
     */
    public function columnFileSize(string $field, string $label): Grid\Column
    {
        return $this->grid->column($field, $label)->display(function ($value) {
            return $this->formatBytes($value);
        });
    }

    /**
     * 格式化字节数
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2).' GridHelper.php'.$units[$power];
    }

    /**
     * 添加JSON列
     */
    public function columnJson(string $field, string $label): Grid\Column
    {
        return $this->grid->column($field, $label)->display(function ($value) {
            if (is_array($value)) {
                return '<pre>'.json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).'</pre>';
            }

            return $value;
        });
    }

    /**
     * 添加截断文本列
     */
    public function columnLimit(string $field, string $label, int $limit = 50): Grid\Column
    {
        return $this->grid->column($field, $label)->limit($limit);
    }

    /**
     * 添加复制按钮列
     */
    public function columnCopyable(string $field, string $label): Grid\Column
    {
        return $this->grid->column($field, $label)->copyable();
    }

    /**
     * 添加二维码列
     */
    public function columnQrcode(string $field, string $label): Grid\Column
    {
        return $this->grid->column($field, $label)->qrcode();
    }
}
