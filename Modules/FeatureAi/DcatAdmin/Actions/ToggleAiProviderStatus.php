<?php

namespace Modules\FeatureAi\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Modules\FeatureAi\Models\AiProvider;

/**
 * 切换AI服务提供商状态
 */
class ToggleAiProviderStatus extends RowAction
{
    /**
     * @var string
     */
    protected $title = '切换状态';

    /**
     * Handle the action request.
     *
     * @return Response
     */
    public function handle(): Response
    {
        $provider = AiProvider::findOrFail($this->getKey());

        // 切换状态: 1=启用, 2=禁用
        $provider->is_active = $provider->is_active === 1 ? 2 : 1;
        $provider->save();

        return $this->response()
            ->success('状态切换成功')
            ->refresh();
    }

    /**
     * 确认对话框提示信息
     *
     * @return string
     */
    public function confirm(): string
    {
        return '确定要切换这个供应商的状态吗？';
    }

    /**
     * 判断是否允许执行
     *
     * @return bool
     */
    public function allowed(): bool
    {
        return true;
    }
}
