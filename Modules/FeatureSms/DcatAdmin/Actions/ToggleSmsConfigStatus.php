<?php

namespace Modules\FeatureSms\DcatAdmin\Actions;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Modules\FeatureSms\Models\SmsConfig;

/**
 * 切换短信配置开关状态
 */
class ToggleSmsConfigStatus extends RowAction
{
    /**
     * @var string
     */
    protected $title = '切换状态';

    /**
     * @var string
     */
    protected $htmlClasses = ['action-custom'];

    /**
     * Handle the action request.
     *
     * @return Response
     */
    public function handle(): Response
    {
        $config = $this->getKey();

        // 获取当前配置
        $smsConfig = SmsConfig::findOrFail($config);

        // 切换状态
        $smsConfig->is_open = !$smsConfig->is_open;
        $smsConfig->save();

        return $this->response()
            ->success('状态切换成功')
            ->refresh();
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        return '确定要切换这个短信配置的状态吗？';
    }

    /**
     * @return bool
     */
    public function allowed(): bool
    {
        return true;
    }
}
