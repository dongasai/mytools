<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Modules\DcatAdmin\DcatAdmin\Metrics\Base\ButtonCard;

/**
 * 单按钮卡片示例（实际使用）
 *
 * 最简单的按钮卡片实现，适合单一操作场景
 *
 * 使用场景：
 * - 清理缓存
 * - 同步数据
 * - 发送通知
 * - 单一确认操作
 *
 * 使用方式：
 * 在控制器中：$row->column(4, new SingleButtonCardExample());
 */
class SingleButtonCardExample extends ButtonCard
{
    protected $height = 300;

    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();
        $this->title('单按钮卡片300');
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
                'action' => 'execute',
                'label' => '执行操作',
                'type' => 'primary',
                'icon' => 'fa-play',
                'confirm' => '确定要执行操作吗？',
                'confirmTitle' => '操作确认'
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
        if ($action === 'execute') {
            // 这里替换为实际的业务逻辑
            \Log::info('SingleButtonCardExample: 执行操作');

            // 模拟执行
            sleep(1);

            return '操作执行成功！';
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
        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$this->renderAlert($message)}
    <div class="mb-3">
        <i class="fa fa-rocket fa-2x text-primary"></i>
    </div>
    <h5 class="font-weight-bold mb-2">简单示例</h5>
    <p class="text-muted mb-3">这是一个最简单的单按钮卡片</p>
    {$this->renderButtons()}
</div>
HTML
        );
    }
}
