<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\FeatureAi\Models\AiProviderModel;

/**
 * 复制AI模型操作
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class CopyModelAction extends RowAction
{
    /**
     * 样式类（必须设置）
     */
    protected $htmlClasses = ['action-info'];

    /**
     * 按钮标题
     */
    public function title()
    {
        return '<i class="fa fa-copy"></i> 复制';
    }

    /**
     * 确认弹窗
     */
    public function confirm()
    {
        return '确定要复制此模型配置吗？';
    }

    /**
     * AJAX 处理
     */
    public function handle(): Response
    {
        try {
            $id = $this->getKey();

            // 查询原模型
            $originalModel = AiProviderModel::findOrFail($id);

            // 复制模型
            $newModel = $originalModel->replicate();
            $newModel->model_name = $originalModel->model_name.' (副本)';
            $newModel->is_active = 0; // 复制后默认禁用
            $newModel->save();

            return $this->response()
                ->success('模型复制成功，已创建副本')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()
                ->error('复制失败：'.$e->getMessage())
                ->refresh();
        }
    }
}
