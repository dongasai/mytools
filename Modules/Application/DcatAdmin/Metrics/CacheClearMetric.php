<?php

namespace Modules\Application\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * 缓存管理 Metric
 * 展示缓存状态，通过按钮提供清理功能
 */
class CacheClearMetric extends Card
{
    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();
        $this->title('缓存管理');
        $this->height(300);  // 大型卡片：与其他配置检查 Metric 保持一致
    }

    /**
     * 获取 URI 标识
     * 确保每个 Metric 有唯一的标识
     */
    public function getUriKey()
    {
        return self::class;
    }

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        \Log::info('CacheClearMetric handle called', ['action' => $request->get('action')]);

        // 判断是否是清理缓存操作
        $message = null;

        if ($request->get('action') === 'clear') {
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                $message = '缓存清理成功！';
            } catch (\Exception $e) {
                $message = '缓存清理失败：' . $e->getMessage();
            }
        }

        // 获取缓存信息
        $cacheStores = config('cache.stores', []);
        $driverCount = count($cacheStores);
        $defaultDriver = config('cache.default', 'file');

        \Log::info('CacheClearMetric setting content', [
            'driverCount' => $driverCount,
            'defaultDriver' => $defaultDriver,
            'message' => $message
        ]);

        // 设置内容
        $this->withContent($message, $driverCount, $defaultDriver);
    }

    /**
     * 设置卡片内容
     *
     * @param string|null $message
     * @param int $driverCount
     * @param string $defaultDriver
     * @return $this
     */
    public function withContent(?string $message, int $driverCount, string $defaultDriver)
    {
        $alertHtml = '';
        if ($message) {
            $alertHtml = '<div class="alert alert-success mb-2"><i class="fa fa-check"></i> ' . $message . '</div>';
        }

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$alertHtml}
    <div class="mb-2">
        <i class="fa fa-database fa-2x text-info"></i>
    </div>
    <h5 class="font-weight-bold mb-1">缓存驱动: {$defaultDriver}</h5>
    <small class="text-muted mb-3">已配置 {$driverCount} 个缓存存储</small>
    <button class="btn btn-warning btn-sm cache-clear-btn" data-action="clear">
        <i class="fa fa-trash"></i> 清理所有缓存
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

        // 设置响应处理 - 内容加载后重新绑定点击事件
        $this->fetched(
            <<<JS
card.loading(false);
card.find('.metric-header').html(response.header);
card.find('.metric-content').html(response.content);
// 内容更新后，重新绑定按钮点击事件（带确认对话框）
card.find('.cache-clear-btn').off('click').on('click', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = btn.data();

    Dcat.confirm('确定要清理所有缓存吗？', '将清除应用缓存、配置缓存、路由缓存、视图缓存', function() {
        request(data);
    });
});
JS
        );

        // 注册按钮选择器 - 点击按钮时发送请求
        $clickable = "#{$id} .cache-clear-btn";
        $this->click($clickable);

        // 构建请求脚本并赋值给 $this->script
        $this->script = $this->buildRequestScript();

        return $this->script;
    }
}