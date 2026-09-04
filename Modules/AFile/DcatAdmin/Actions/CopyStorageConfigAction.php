<?php

namespace Modules\AFile\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\AFile\Models\FileStorageConfig;

/**
 * 复制存储配置 Action
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class CopyStorageConfigAction extends RowAction
{
    /**
     * 样式类（必须设置）
     *
     * @var array
     */
    protected $htmlClasses = ['action-success'];

    /**
     * Action 标题
     *
     * @return string
     */
    public function title()
    {
        return '<i class="fa fa-copy"></i> 复制';
    }

    /**
     * 确认对话框
     *
     * @return array|string
     */
    public function confirm()
    {
        return ['确定要复制此存储配置吗？', '将创建一个新的配置副本'];
    }

    /**
     * 处理 Action 请求
     *
     * @return Response
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        try {
            // 获取原配置（必须自己查询数据库）
            $originalConfig = FileStorageConfig::findOrFail($id);

            // 创建新配置
            $newConfig = $originalConfig->replicate();

            // 生成唯一的副本名称
            $newConfig->name = $this->generateUniqueName(
                $originalConfig->name,
                $originalConfig->env
            );

            // 清除默认存储标识
            $newConfig->is_default = 0;

            // 保存新配置
            $newConfig->save();

            // 跳转到编辑页面
            return $this->response()
                ->success('复制成功')
                ->redirect(route('afile.storage-configs.edit', ['storage_config' => $newConfig->id]));

        } catch (\Exception $e) {
            return $this->response()
                ->error('复制失败：' . $e->getMessage())
                ->refresh();
        }
    }

    /**
     * 生成唯一的副本名称
     *
     * @param string $originalName 原始名称
     * @param string $env 环境标识
     * @return string
     */
    protected function generateUniqueName(string $originalName, string $env): string
    {
        $baseName = $originalName . ' (副本)';
        $counter = 1;

        // 检查是否存在同名副本
        while (FileStorageConfig::where('name', $baseName)
            ->where('env', $env)
            ->exists()) {

            $counter++;
            $baseName = $originalName . ' (副本 ' . $counter . ')';
        }

        return $baseName;
    }
}