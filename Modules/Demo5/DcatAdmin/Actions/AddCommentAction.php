<?php

namespace Modules\Demo5\DcatAdmin\Actions;

use Modules\DcatAdmin\DcatAdmin\RowAction;
use Dcat\Admin\Widgets\Modal;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\DcatAdmin\Forms\AddCommentForm;


/**
 * 添加评论Action
 */
class AddCommentAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    /**
     * HTML CSS类 - 成功操作
     */
    protected $htmlClasses = ['action-success'];

    /**
     * 按钮标题
     */
    protected $title = '添加评论';

    /**
     * 按钮图标
     */
    public function icon(): string
    {
        return 'feather icon-plus-circle';
    }

    /**
     * 渲染模态框和表单
     */
    public function render2()
    {
        // 获取当前文章信息
        $postId = $this->getKey();
        $post = Demo5Post::find($postId);

        // 创建表单实例并传递文章ID
        $form = AddCommentForm::make()
            ->payload([
                'post_id' => $postId,
                'post' => $post
            ]);

        return Modal::make()
            ->lg()
            ->title('添加评论')
            ->body($form)
            ->button(
                <<<HTML
<i class="{$this->icon()}"></i> {$this->title}
HTML
            );
    }
}