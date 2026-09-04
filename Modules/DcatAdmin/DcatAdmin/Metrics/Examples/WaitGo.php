<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Widget;

/**
 * 倒计时跳转
 */
class WaitGo extends Widget
{
    protected $title = 'WaitGo';

    protected $view = 'module_dcatadmin::metrics.waitgo';

    /**
     * 等待秒数
     *
     * @var int
     */
    protected $sWait = 30;

    /**
     * 目标网址
     *
     * @var string
     */
    public $go_url = '';

    /**
     * 弹出提示
     *
     * @var bool
     */
    public $notice = false;

    public function __construct($go_url = '', $sWait = 30)
    {
        // 设置卡片高度样式
        $this->style('min-height: 150px;');

        if ($go_url) {
            $this->go_url = $go_url;
        }
        if ($sWait) {
            $this->sWait = $sWait;
        }

        $this->variables['sWait'] = $this->sWait;
        $this->variables['notice'] = $this->notice;
        $this->variables['go_url'] = $this->go_url;

        $script = <<<JS
// js

if(typeof waitindex == 'undefined'){
    window.waitindex_waits = {$this->sWait};
    var go_url = '{$this->go_url}';
     var notice  = '{$this->notice}';
    var waitindex = setInterval(function(){
        if(waitindex_waits  < 0){
            clearInterval(waitindex);
            window.location.replace(go_url);
        }
        window.waitindex_waits --;
        $('.wait_go').html(window.waitindex_waits);
        console.log('waitindex :', window.waitindex_waits,go_url)
    }, 1000);
    if(notice){
        Dcat.warning('页面将在 {$this->sWait} 秒后前往'+go_url, null, {
        timeOut: 5000 // 5秒后自动消失
    });
    }

}



JS;

        Admin::script($script);
    }
}
