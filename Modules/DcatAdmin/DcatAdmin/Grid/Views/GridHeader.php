<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Views;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Modules\Application\Enums\VIEW_TYPE;
use Modules\DcatAdmin\Models\AdminGridView;

class GridHeader
{
    protected $grid;

    public function __construct(\Dcat\Admin\Grid $grid)
    {
        redirect()->setIntendedUrl(URL::full());
        $this->grid = $grid;
    }

    public function getBs()
    {
        $bs = [];

        $router_name = Route::getCurrentRoute()->getName();

        // 视图按钮
        $listPublic = AdminGridView::query()
            ->where('type1', VIEW_TYPE::PUBLIC)
            ->where('router_name', $router_name)
            ->get();
        $listPrivate = AdminGridView::query()
            ->where('type1', VIEW_TYPE::PRIVATE)
            ->where('router_name', $router_name)

            ->get();
        $_viewid = request('_viewid');
        $_viewnow = '';
        foreach ($listPrivate as $value) {
            $bs[] = new GridHeaderButtonPrivate($value);
            if ($_viewid == $value->id) {
                $bs[] = new GridHeaderButtonUpdate2($value);
            }
        }
        foreach ($listPublic as $value) {
            $bs[] = new GridHeaderButtonPublic($value);
            if ($_viewid == $value->id) {
                $bs[] = new GridHeaderButtonUpdate2($value);
            }
        }
        $bs[] = new GridHeaderButtonSave;
        if ($_viewid) {
            $bs[] = new GridHeaderButtonEdit;
        }

        return $bs;
    }

    public static function gridTools(\Dcat\Admin\Grid $grid)
    {
        $new = new static($grid);
        $grid->tools($new->getBs());
    }

    public static function gridHeader(\Dcat\Admin\Grid $grid)
    {

        $new = new static($grid);
        $s = ' <br>';
        $bs = $new->getBs();
        //        dd($bs);
        foreach ($bs as $b) {
            $s .= $b.' ';
        }
        $grid->header($s);
    }
}
