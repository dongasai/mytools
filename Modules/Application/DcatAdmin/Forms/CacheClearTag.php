<?php

namespace Modules\Application\DcatAdmin\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use DLaravel\Helper\CacheTag;
use Modules\DcatAdmin\DcatAdmin\Traits\ModalLazyRenderable;

/**
 * 清楚缓存
 */
class CacheClearTag extends Form implements LazyRenderable
{
    use LazyWidget;
    use ModalLazyRenderable;

    public string $title = '删除 - 缓存标签';

    // 处理表单提交请求
    public function handle(array $input)
    {

        $tag = $input['tag'];

        CacheTag::tags_clear([$tag]);
        // return $this->response()->error('Your error message.');

        return $this->response()->success('Processed successfully.')->refresh();
    }

    // 构建表单
    public function form()
    {
        // Since v1.6.5 弹出确认弹窗
        $this->confirm('您确定要提交表单吗', 'content');

        $this->text('tag')->required();
    }
}
