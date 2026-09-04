<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;

/**
 * 按钮卡片示例
 *
 * 演示如何使用 Metric 实现带确认对话框的按钮操作
 *
 * 使用方式：
 * 在控制器中：$row->column(4, new ActionButtonMetric());
 */
class ActionButtonMetric extends Card
{
    protected $height = 200;

    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();
        $this->title('操作按钮示例');
    }

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        // 判断是否执行操作
        $message = null;
        $action = $request->get('action');

        if ($action === 'reset') {
            // 演示：执行重置操作
            \Log::info('ActionButtonMetric: 执行重置操作');
            $message = '重置成功！';
        } elseif ($action === 'sync') {
            // 演示：执行同步操作
            \Log::info('ActionButtonMetric: 执行同步操作');
            $message = '同步成功！';
        }

        // 获取卡片数据
        $data = $this->getCardData();

        // 设置内容
        $this->withContent($message, $data);
    }

    /**
     * 获取卡片数据
     *
     * @return array
     */
    protected function getCardData(): array
    {
        return [
            'total' => 150,
            'active' => 120,
            'pending' => 30,
        ];
    }

    /**
     * 设置卡片内容
     *
     * @param string|null $message
     * @param array $data
     * @return $this
     */
    public function withContent(?string $message, array $data)
    {
        $alertHtml = '';
        if ($message) {
            $alertHtml = '<div class="alert alert-success mb-2"><i class="fa fa-check"></i> ' . $message . '</div>';
        }

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$alertHtml}
    <div class="mb-3">
        <i class="fa fa-cogs fa-2x text-primary"></i>
    </div>
    <h5 class="font-weight-bold mb-1">数据概览</h5>
    <div class="d-flex justify-content-around mt-2 mb-3">
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
    <div class="btn-group" role="group">
        <button class="btn btn-sm btn-info action-btn" data-action="reset">
            <i class="fa fa-refresh"></i> 重置
        </button>
        <button class="btn btn-sm btn-success action-btn" data-action="sync">
            <i class="fa fa-sync"></i> 同步
        </button>
    </div>
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

// 重新绑定按钮点击事件（关键！）
card.find('.action-btn').off('click').on('click', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = btn.data();
    var action = data.action;

    // 根据不同操作显示不同确认信息
    var messages = {
        'reset': {
            title: '确定要重置吗？',
            content: '这将重置所有数据到初始状态'
        },
        'sync': {
            title: '确定要同步吗？',
            content: '这将同步最新数据'
        }
    };

    var msg = messages[action] || {title: '确定操作？', content: ''};

    Dcat.confirm(msg.title, msg.content, function() {
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