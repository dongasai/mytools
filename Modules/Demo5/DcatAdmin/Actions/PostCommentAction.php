<?php

namespace Modules\Demo5\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;



/**
 * 文章评论Action
 */
class PostCommentAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    /**
     * HTML CSS类 - 主要操作
     */
    protected $htmlClasses = ['action-primary'];

    /**
     * 按钮标题
     */
    public $title = '评论';



    /**
     * 处理请求
     */
    public function handle(Request $request): Response
    {
        // 获取文章ID
        $postId = $this->getKey();

        // 重定向到评论管理页面，并筛选当前文章的评论
        return $this->response()
            ->redirect(admin_url('module_demo5/comments?post_id=' . $postId));
    }
}