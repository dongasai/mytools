<?php

namespace Modules\Application\DcatAdmin\Tools;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Application\Services\ConfigService;
use Dcat\Admin\Admin;

/**
 * 系统配置缓存刷新工具
 *
 * 用于在后台管理界面中刷新系统配置缓存数据
 */
class RefreshCacheTool extends AbstractTool
{
    /**
     * 是否显示按钮
     *
     * @var bool
     */
    protected $shouldDisplay;

    /**
     * 按钮样式
     *
     * @var string
     */
    protected $style = 'btn btn-primary waves-effect';

    /**
     * 构造函数
     *
     * @param  bool  $shouldDisplay  是否显示按钮
     */
    public function __construct(bool $shouldDisplay = true)
    {
        $this->shouldDisplay = $shouldDisplay;
    }

    /**
     * 按钮标题
     *
     * @return string
     */
    public function title()
    {
        return '刷新缓存';
    }

    /**
     * 确认提示
     *
     * @return string
     */
    public function confirm()
    {
        return '确定要刷新系统配置缓存吗？这将重新加载所有配置数据。';
    }

    /**
     * 处理请求
     *
     * @return mixed
     */
    public function handle(Request $request)
    {
        // 刷新缓存
        ConfigService::clear_cache();
        ConfigService::setConfigTime();

        // 记录操作日志
        Log::info('System config cache refreshed by admin', [
            'admin_id' => Admin::user()->id ?? null,
            'admin_name' => Admin::user()->name ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ]);

        return $this->response()
            ->success('缓存刷新成功，所有配置已重新加载')
            ->refresh();
    }

    /**
     * 渲染按钮
     *
     * @return string
     */
    public function render()
    {
        if (! $this->shouldDisplay) {
            return '';
        }

        return parent::render();
    }

    /**
     * 判断是否应该显示按钮
     *
     * @return bool
     */
    public static function shouldDisplay(): bool
    {
        return true;
    }

    /**
     * 获取按钮图标
     *
     * @return string
     */
    protected function icon()
    {
        return 'fa fa-refresh';
    }
}