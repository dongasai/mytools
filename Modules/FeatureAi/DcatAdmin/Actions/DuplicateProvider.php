<?php

namespace Modules\FeatureAi\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;
use Modules\FeatureAi\Models\AiProvider;

/**
 * 复制AI供应商操作
 */
class DuplicateProvider extends RowAction
{
    /**
     * 按钮标题
     *
     * @return string
     */
    public function title()
    {
        return '📁 复制';
    }

    /**
     * 确认提示信息
     *
     * @return string|void
     */
    public function confirm()
    {
        return '确认复制此供应商配置？';
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request)
    {
        // 获取当前行ID
        $id = $this->getKey();

        // 查询供应商
        $provider = AiProvider::with('models')->findOrFail($id);

        // 创建新记录
        $newProvider = $provider->replicate();
        $newProvider->provider_name = $provider->provider_name . ' (副本)';
        $newProvider->is_active = 0; // 复制后默认禁用
        $newProvider->save();

        // 复制关联的模型
        foreach ($provider->models as $model) {
            $newModel = $model->replicate();
            $newModel->provider_id = $newProvider->id;
            $newModel->save();
        }

        return $this->response()
            ->success('复制成功')
            ->refresh();
    }

    /**
     * 设置按钮样式
     *
     * @return string
     */
    public function html()
    {
        return <<<HTML
<a {$this->formatHtmlAttributes()} class="{$this->getElementClass()}" style="color: #6c757d; margin-left: 5px;">
    {$this->title()}
</a>
HTML;
    }
}
