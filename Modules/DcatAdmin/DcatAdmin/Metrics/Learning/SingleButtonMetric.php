<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;

/**
 * 单按钮卡片示例
 *
 * 最简单的按钮卡片实现：只有一个操作按钮
 *
 * 使用方式：
 * 在控制器中：$row->column(4, new SingleButtonMetric());
 */
class SingleButtonMetric extends Card
{
    protected $height = 150;

    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();
        $this->title('单按钮卡片');
    }

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        // 判断是否点击了按钮
        $message = null;

        if ($request->get('action') === 'execute') {
            // 执行你的业务逻辑
            // 例如：清理缓存、同步数据、发送通知等

            \Log::info('SingleButtonMetric: 按钮被点击，执行操作');

            // 模拟执行操作
            sleep(1);  // 模拟耗时操作

            $message = '操作执行成功！';
        }

        // 设置卡片内容
        $this->withContent($message);
    }

    /**
     * 设置卡片内容
     *
     * @param string|null $message
     * @return $this
     */
    public function withContent(?string $message)
    {
        $alertHtml = '';
        if ($message) {
            $alertHtml = '<div class="alert alert-success mb-2"><i class="fa fa-check-circle"></i> ' . $message . '</div>';
        }

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$alertHtml}
    <div class="mb-3">
        <i class="fa fa-rocket fa-2x text-primary"></i>
    </div>
    <h5 class="font-weight-bold mb-2">简单示例</h5>
    <p class="text-muted mb-3">这是一个最简单的单按钮卡片</p>
    <button class="btn btn-primary action-btn" data-action="execute">
        <i class="fa fa-play"></i> 执行操作
    </button>
</div>
HTML
        );
    }

    /**
     * 添加交互脚本
     */
    public function addScript()
    {
        if (! $this->allowBuildRequest()) {
            return;
        }

        $id = $this->id();

        // 设置 loading 效果
        $this->fetching(
            <<<JS
var card = $('#{$id}');
card.loading();
JS
        );

        // 设置响应处理 - 内容加载后重新绑定按钮点击事件
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

    // 显示确认对话框
    Dcat.confirm('确定要执行操作吗？', '点击确定后将执行操作', function() {
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
}