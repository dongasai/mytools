<?php

namespace Modules\Application\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Application\Models\ApplicationConfig;

/**
 * 配置变更事件
 */
class ConfigChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 配置键名
     */
    public string $keyname;

    /**
     * 旧值
     *
     * @var mixed
     */
    public $oldValue;

    /**
     * 新值
     *
     * @var mixed
     */
    public $newValue;

    /**
     * 配置对象
     */
    public ApplicationConfig $config;

    /**
     * 管理员ID
     */
    public int $adminId;

    /**
     * 创建一个新的事件实例
     *
     * @param  mixed  $oldValue
     * @return void
     */
    public function __construct(ApplicationConfig $config, $oldValue, int $adminId = 0)
    {
        $this->keyname = $config->keyname;
        $this->oldValue = $oldValue;
        $this->newValue = $config->value;
        $this->config = $config;
        $this->adminId = $adminId;
    }
}
