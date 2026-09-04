<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Widget;

class Reload extends Widget
{
    protected $title = 'Reload';

    protected $view = 'module_dcatadmin::metrics.reload';

    /**
     * 刷新时间
     *
     * @var int
     */
    protected $sRoload = 60;

    /**
     * 弹出提示
     *
     * @var bool
     */
    public $notice = false;

    public function __construct()
    {
        // 设置卡片高度样式
        $this->style('min-height: 150px;');

        $ms = $this->sRoload * 1000;
        $script = <<<JS
// js
if(typeof reloadindex == 'undefined'){
    var reloads = {$this->sRoload};
         var notice  = '{$this->notice}';
    var reloadindex = setInterval(function(){
        if(reloads  < 0){
            window.location.reload();
        }
        reloads --;
        $('.reload_2').html(reloads);
        console.log('reloads:', reloads)
    }, 1000);
    if(notice){
         Dcat.warning('页面将在 {$this->sRoload} 秒后自动刷新', null, {
                timeOut: 5000, // 5秒后自动消失
            });
    }

}



JS;

        Admin::script($script);
    }
}
