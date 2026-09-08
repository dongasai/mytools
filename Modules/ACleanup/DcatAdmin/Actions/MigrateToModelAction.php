<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupConfig;
use Modules\AClean\Models\CleanupPlanContent;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 迁移到Model类Action
 *
 * 将使用表名的旧数据迁移为使用Model类
 */
class MigrateToModelAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '迁移到Model';

    /**
     * 按钮图标
     */
    protected $icon = 'fa-arrow-up';

    /**
     * 处理请求
     */
    protected function run(Request $request)
    {
        $contentId = $this->getKey();

        $content = CleanupPlanContent::findOrFail($contentId);

        // 检查是否已经使用Model类
        if (! empty($content->model_class)) {
            return $this->response()
                ->error('此记录已经使用Model类，无需迁移');
        }

        // 检查是否有表名
        if (empty($content->table_name)) {
            return $this->response()
                ->error('此记录没有表名，无法迁移');
        }

        // 查找对应的Model类
        // 首先尝试直接匹配表名
        $config = CleanupConfig::where('table_name', $content->table_name)
            ->whereNotNull('model_class')
            ->where('model_class', '!=', '')
            ->first();

        // 如果没找到，尝试去掉前缀再查找
        if (! $config && str_starts_with($content->table_name, '')) {
            $tableNameWithoutPrefix = substr($content->table_name, 4); // 去掉 '' 前缀
            $config = CleanupConfig::where('table_name', $tableNameWithoutPrefix)
                ->whereNotNull('model_class')
                ->where('model_class', '!=', '')
                ->first();
        }

        if (! $config) {
            return $this->response()
                ->error('未找到表 ' . $content->table_name . ' 对应的Model类配置');
        }

        // 验证Model类是否存在
        if (! class_exists($config->model_class)) {
            return $this->response()
                ->error('Model类不存在：' . $config->model_class);
        }

        // 更新为使用Model类
        $content->update([
            'model_class' => $config->model_class,
        ]);

        return $this->response()
            ->success('迁移成功！已更新为使用Model类：' . class_basename($config->model_class))
            ->refresh();
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认迁移到Model类？',
            '此操作将查找对应的Model类并更新配置，建议在迁移前备份数据。',
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        // 只对没有Model类的记录显示此按钮
        $content = $this->row;

        return empty($content->model_class);
    }

    /**
     * 按钮样式
     */
    public function html()
    {
        return '<a class="btn btn-sm btn-warning ' . $this->getElementClass() . '" title="' . $this->title . '">
                    <i class="' . $this->icon . '"></i> ' . $this->title . '
                </a>';
    }
}
