<?php

namespace Modules\FeatureSms\DcatAdmin\Actions\Batch;

use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Actions\Response;
use Modules\FeatureSms\Models\SmsConfig;

/**
 * 批量删除短信配置
 */
class BatchDeleteSmsConfigs extends BatchAction
{
    /**
     * @var string
     */
    protected $title = '批量删除';

    /**
     * @var string
     */
    protected $htmlClasses = ['btn', 'btn-danger'];

    /**
     * Handle the action request.
     *
     * @return Response
     */
    public function handle(): Response
    {
        // 获取选中的配置ID
        $keys = $this->getSelectedKeys();

        if (empty($keys)) {
            return $this->response()->error('请选择要删除的配置');
        }

        // 批量删除
        SmsConfig::whereIn('id', $keys)->delete();

        return $this->response()
            ->success("成功删除 {$keys->count()} 个配置")
            ->refresh();
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        $keys = $this->getSelectedKeys();

        if (empty($keys)) {
            return '请先选择要删除的配置';
        }

        return "确定要删除选中的 {$keys->count()} 个短信配置吗？此操作不可恢复！";
    }

    /**
     * @return bool
     */
    public function allowed(): bool
    {
        return true;
    }
}
