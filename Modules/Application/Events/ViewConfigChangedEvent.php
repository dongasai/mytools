<?php

namespace Modules\Application\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\System\Models\ViewConfig;

/**
 * 视图配置变更事件
 */
class ViewConfigChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 视图配置ID
     */
    public int $id;

    /**
     * 视图配置标题
     */
    public string $title;

    /**
     * 旧参数
     *
     * @var mixed
     */
    public $oldParams;

    /**
     * 新参数
     *
     * @var mixed
     */
    public $newParams;

    /**
     * 视图配置对象
     */
    public ViewConfig $viewConfig;

    /**
     * 管理员ID
     */
    public int $adminId;

    /**
     * 创建一个新的事件实例
     *
     * @param  mixed  $oldParams
     * @return void
     */
    public function __construct(ViewConfig $viewConfig, $oldParams, int $adminId = 0)
    {
        $this->id = $viewConfig->id;
        $this->title = $viewConfig->title;
        $this->oldParams = $oldParams;
        $this->newParams = $viewConfig->p1;
        $this->viewConfig = $viewConfig;
        $this->adminId = $adminId;
    }
}
