<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\Modal;
use Modules\Application\DcatAdmin\Forms\CacheClear;
use Modules\Application\DcatAdmin\Forms\CacheClearTag;
use Modules\Application\DcatAdmin\Forms\Setting;

/**
 * 工具列表
 */
class ToolController extends \Dcat\Admin\Http\Controllers\AdminController
{
    /**
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }

    private function grid()
    {
        $card = new Card('标题');
        $content = '';
        $model1 = Modal::make()
            ->lg()
            ->title('演示按钮')
            ->body(Setting::make())
            ->button('演示按钮');

        $content .= $model1->render();
        $content .= CacheClearTag::makeModal();
        $content .= CacheClear::make();

        $card->content($content);

        return $card;
    }
}
