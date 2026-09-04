<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Modules\DcatAdmin\DcatAdmin\Metrics\Base\ButtonCard;

/**
 * 多按钮卡片示例（实际使用）
 *
 * 适合需要多个操作的场景，支持不同类型的按钮
 *
 * 使用场景：
 * - 数据管理（重置、同步）
 * - 批量操作（导出、删除）
 * - 多个操作选择
 *
 * 使用方式：
 * 在控制器中：$row->column(4, new MultiButtonCardExample());
 */
class MultiButtonCardExample extends ButtonCard
{
    protected $height = 300;

    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();
        $this->title('多按钮卡片示例');
    }

    /**
     * 定义按钮配置
     *
     * @return array
     */
    protected function getButtons(): array
    {
        return [
            [
                'action' => 'reset',
                'label' => '重置',
                'type' => 'warning',
                'icon' => 'fa-refresh',
                'confirm' => '确定要重置所有数据吗？',
                'confirmTitle' => '重置确认'
            ],
            [
                'action' => 'sync',
                'label' => '同步',
                'type' => 'success',
                'icon' => 'fa-sync',
                'confirm' => '确定要同步最新数据吗？',
                'confirmTitle' => '同步确认'
            ]
        ];
    }

    /**
     * 执行业务逻辑
     *
     * @param string $action
     * @return string
     */
    protected function executeAction(string $action): string
    {
        switch ($action) {
            case 'reset':
                \Log::info('MultiButtonCardExample: 执行重置操作');
                return '重置成功！';

            case 'sync':
                \Log::info('MultiButtonCardExample: 执行同步操作');
                return '同步成功！';
        }

        return '';
    }

    /**
     * 渲染卡片内容
     *
     * @param string|null $message
     * @return $this
     */
    public function withContent(?string $message)
    {
        $data = $this->getCardData();

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$this->renderAlert($message)}
    <div class="mb-2">
        <i class="fa fa-cogs fa-2x text-primary"></i>
    </div>
    <h5 class="font-weight-bold mb-2">数据概览</h5>
    <div class="d-flex justify-content-around mt-1 mb-2">
        <div>
            <div class="font-lg-1 text-primary">{$data['total']}</div>
            <small class="text-muted">总数</small>
        </div>
        <div>
            <div class="font-lg-1 text-success">{$data['active']}</div>
            <small class="text-muted">活跃</small>
        </div>
        <div>
            <div class="font-lg-1 text-warning">{$data['pending']}</div>
            <small class="text-muted">待处理</small>
        </div>
    </div>
    {$this->renderButtons()}
</div>
HTML
        );
    }

    /**
     * 获取卡片数据
     *
     * @return array
     */
    private function getCardData(): array
    {
        return [
            'total' => 150,
            'active' => 120,
            'pending' => 30,
        ];
    }
}
