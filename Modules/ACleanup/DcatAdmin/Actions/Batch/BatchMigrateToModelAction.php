<?php

namespace Modules\AClean\DcatAdmin\Actions\Batch;

use Modules\AClean\Models\CleanupConfig;
use Modules\AClean\Models\CleanupPlanContent;
use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

/**
 * 批量迁移到Model类Action
 *
 * 批量将使用表名的旧数据迁移为使用Model类
 */
class BatchMigrateToModelAction extends BatchAction
{
    /**
     * 按钮标题
     */
    protected $title = '批量迁移到Model';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $ids = $this->getKey();

        if (empty($ids)) {
            return $this->response()->error('请选择要迁移的记录');
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $content = CleanupPlanContent::findOrFail($id);

                // 跳过已经使用Model类的记录
                if (! empty($content->model_class)) {
                    continue;
                }

                // 检查是否有表名
                if (empty($content->table_name)) {
                    $errors[] = "ID {$id}: 没有表名";
                    $failedCount++;

                    continue;
                }

                // 查找对应的Model类
                $config = CleanupConfig::where('table_name', $content->table_name)
                    ->whereNotNull('model_class')
                    ->where('model_class', '!=', '')
                    ->first();

                if (! $config) {
                    $errors[] = "ID {$id}: 未找到表 {$content->table_name} 对应的Model类";
                    $failedCount++;

                    continue;
                }

                // 验证Model类是否存在
                if (! class_exists($config->model_class)) {
                    $errors[] = "ID {$id}: Model类不存在 {$config->model_class}";
                    $failedCount++;

                    continue;
                }

                // 更新为使用Model类
                $content->update([
                    'model_class' => $config->model_class,
                ]);

                $successCount++;

            } catch (\Exception $e) {
                $errors[] = "ID {$id}: ".$e->getMessage();
                $failedCount++;
            }
        }

        // 构建结果消息
        $message = "迁移完成！成功: {$successCount} 个，失败: {$failedCount} 个";

        if (! empty($errors)) {
            $message .= "\n\n失败详情:\n".implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= "\n... 还有 ".(count($errors) - 10).' 个错误';
            }
        }

        if ($failedCount > 0) {
            return $this->response()
                ->warning($message)
                ->refresh();
        } else {
            return $this->response()
                ->success($message)
                ->refresh();
        }
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认批量迁移到Model类？',
            '此操作将查找对应的Model类并批量更新配置，建议在迁移前备份数据。',
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        return true;
    }
}
