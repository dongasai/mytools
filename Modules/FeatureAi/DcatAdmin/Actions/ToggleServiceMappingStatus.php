<?php

namespace Modules\FeatureAi\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Modules\FeatureAi\Models\AiServiceMapping;

/**
 * 切换AI服务映射状态
 */
class ToggleServiceMappingStatus extends RowAction
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
        $mapping = AiServiceMapping::findOrFail($this->getKey());

        // 切换状态: 1=启用, 2=禁用
        $mapping->is_active = $mapping->is_active === 1 ? 2 : 1;
        $mapping->save();

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
        return '确定要切换这个映射的状态吗？';
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
