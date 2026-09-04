<?php

namespace Modules\Application\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * 功能开关变更事件
 *
 * 当用户的功能开关状态发生变化时触发此事件
 * 监听器可订阅此事件进行扩展处理(如日志记录、通知发送等)
 */
class FeatureChanged
{
    use Dispatchable;

    /**
     * 用户ID
     *
     * @var int
     */
    public int $userId;

    /**
     * 功能标识
     *
     * @var string
     */
    public string $featureKey;

    /**
     * 变更前的状态
     *
     * @var bool
     */
    public bool $oldValue;

    /**
     * 变更后的状态
     *
     * @var bool
     */
    public bool $newValue;

    /**
     * 变更操作人ID(管理员或用户自己)
     *
     * @var int
     */
    public int $changedBy;

    /**
     * 变更原因
     *
     * @var string
     */
    public string $reason;

    /**
     * 变更时间
     *
     * @var string
     */
    public string $changedAt;

    /**
     * 构造方法
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param bool $oldValue 变更前的状态
     * @param bool $newValue 变更后的状态
     * @param int $changedBy 变更操作人ID
     * @param string $reason 变更原因
     */
    public function __construct(
        int $userId,
        string $featureKey,
        bool $oldValue,
        bool $newValue,
        int $changedBy,
        string $reason = ''
    ) {
        $this->userId = $userId;
        $this->featureKey = $featureKey;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
        $this->changedBy = $changedBy;
        $this->reason = $reason;
        $this->changedAt = now()->toDateTimeString();
    }
}