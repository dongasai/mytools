<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Base;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;

/**
 * 按钮卡片基类
 *
 * 封装按钮卡片的通用逻辑，简化按钮卡片开发
 *
 * 使用方式：
 * 1. 继承此类
 * 2. 实现 getButtons() 定义按钮配置
 * 3. 实现 executeAction() 处理业务逻辑
 * 4. 实现 withContent() 渲染内容
 *
 * 示例：
 * ```php
 * class CacheClearMetric extends ButtonCard
 * {
 *     protected function getButtons(): array
 *     {
 *         return [
 *             ['action' => 'clear', 'label' => '清理缓存', 'confirm' => '确定清理？']
 *         ];
 *     }
 *
 *     protected function executeAction(string $action): string
 *     {
 *         if ($action === 'clear') {
 *             Artisan::call('cache:clear');
 *             return '缓存清理成功！';
 *         }
 *         return '';
 *     }
 *
 *     public function withContent(?string $message)
 *     {
 *         return $this->content(<<<HTML
 * <div class="text-center">
 *     {$this->renderAlert($message)}
 *     <h5>缓存管理</h5>
 *     {$this->renderButtons()}
 * </div>
 * HTML
 *         );
 *     }
 * }
 * ```
 */
abstract class ButtonCard extends Card
{
    /**
     * 获取按钮配置
     *
     * 返回格式：
     * ```php
     * [
     *     [
     *         'action' => 'clear',           // 必须：操作标识
     *         'label' => '清理缓存',          // 必须：按钮文字
     *         'type' => 'primary',           // 可选：按钮类型（primary/success/danger/warning/info）
     *         'icon' => 'fa-refresh',        // 可选：图标类名
     *         'confirm' => '确定清理缓存？',  // 可选：确认提示
     *         'confirmTitle' => '清理确认',  // 可选：确认标题
     *     ]
     * ]
     * ```
     *
     * @return array
     */
    abstract protected function getButtons(): array;

    /**
     * 执行按钮操作
     *
     * @param string $action 操作标识
     * @return string 操作结果消息
     */
    abstract protected function executeAction(string $action): string;

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $action = $request->get('action');
        $message = null;

        if ($action) {
            $message = $this->executeAction($action);
        }

        $this->withContent($message);
    }

    /**
     * 添加交互脚本
     *
     * @return string|void
     */
    public function addScript()
    {
        if (! $this->allowBuildRequest()) {
            return;
        }

        $id = $this->id();

        // 请求前的 loading 效果
        $this->fetching(
            <<<JS
var card = $('#{$id}');
card.loading();
JS
        );

        // 响应后重新绑定事件
        $this->fetched(
            <<<JS
card.loading(false);
card.find('.metric-header').html(response.header);
card.find('.metric-content').html(response.content);

// 关键：重新绑定按钮点击事件
card.find('.action-btn').off('click').on('click', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = btn.data();
    var confirm = btn.data('confirm') || '确定执行此操作？';
    var confirmTitle = btn.data('confirm-title') || '操作确认';

    Dcat.confirm(confirmTitle, confirm, function() {
        request(data);
    });
});
JS
        );

        // 注册按钮选择器
        $clickable = "#{$id} .action-btn";
        $this->click($clickable);

        // 构建请求脚本
        $this->script = $this->buildRequestScript();

        return $this->script;
    }

    /**
     * 渲染按钮 HTML
     *
     * @return string
     */
    protected function renderButtons(): string
    {
        $buttons = $this->getButtons();

        if (empty($buttons)) {
            return '';
        }

        // 单按钮：直接渲染
        if (count($buttons) === 1) {
            return $this->renderSingleButton($buttons[0]);
        }

        // 多按钮：使用 btn-group
        return $this->renderButtonGroup($buttons);
    }

    /**
     * 渲染单个按钮
     *
     * @param array $config
     * @return string
     */
    protected function renderSingleButton(array $config): string
    {
        $type = $config['type'] ?? 'primary';
        $icon = isset($config['icon']) ? '<i class="'.$config['icon'].'"></i> ' : '';
        $confirm = $config['confirm'] ?? '确定执行此操作？';
        $confirmTitle = $config['confirmTitle'] ?? '操作确认';

        return sprintf(
            '<button class="btn btn-%s action-btn" data-action="%s" data-confirm="%s" data-confirm-title="%s">%s%s</button>',
            $type,
            $config['action'],
            $confirm,
            $confirmTitle,
            $icon,
            $config['label']
        );
    }

    /**
     * 渲染按钮组
     *
     * @param array $buttons
     * @return string
     */
    protected function renderButtonGroup(array $buttons): string
    {
        $html = '<div class="btn-group" role="group">';

        foreach ($buttons as $config) {
            $type = $config['type'] ?? 'primary';
            $size = $config['size'] ?? 'sm';
            $icon = isset($config['icon']) ? '<i class="'.$config['icon'].'"></i> ' : '';
            $confirm = $config['confirm'] ?? '确定执行此操作？';
            $confirmTitle = $config['confirmTitle'] ?? '操作确认';

            $html .= sprintf(
                '<button class="btn btn-%s btn-%s action-btn" data-action="%s" data-confirm="%s" data-confirm-title="%s">%s%s</button>',
                $size,
                $type,
                $config['action'],
                $confirm,
                $confirmTitle,
                $icon,
                $config['label']
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * 渲染提示消息
     *
     * @param string|null $message
     * @param string $type 提示类型（success/danger/warning/info）
     * @return string
     */
    protected function renderAlert(?string $message, string $type = 'success'): string
    {
        if (! $message) {
            return '';
        }

        $icon = $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        return sprintf(
            '<div class="alert alert-%s mb-2"><i class="fa %s"></i> %s</div>',
            $type,
            $icon,
            $message
        );
    }
}