<?php

namespace Modules\Demo5\DcatAdmin\Forms;

use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\Demo5Comment;
use Modules\Demo5\Enums\CommentStatus;

/**
 * 添加评论表单
 */
class AddCommentForm extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单提交
     */
    public function handle(array $input)
    {
        // 获取通过 payload 传递的参数
        $postId = $this->payload['post_id'] ?? null;
        $post = $this->payload['post'] ?? null;

        // 验证文章是否存在
        if (!$post) {
            return $this->response()
                ->error('文章不存在');
        }

        // 获取表单字段数据
        $content = $input['content'] ?? '';

        // 验证内容
        if (empty(trim($content))) {
            return $this->response()
                ->error('评论内容不能为空');
        }

        try {
            // 创建评论 - 获取当前登录用户ID
            $userId = auth('admin')->id() ?: 1;

            $comment = new Demo5Comment();
            $comment->content = $content;
            $comment->post_id = $postId;
            $comment->user_id = $userId;
            $comment->status = CommentStatus::Approved; // 默认审核通过
            $comment->ip_address = request()->ip();
            $comment->user_agent = request()->userAgent();
            $comment->save();

            return $this->response()
                ->success('评论添加成功')
                ->refresh();

        } catch (\Exception $e) {
            return $this->response()
                ->error('添加评论失败：' . $e->getMessage());
        }
    }

    /**
     * 构建表单
     */
    public function form()
    {
        // 获取外部传递的参数
        $postId = $this->payload['post_id'] ?? null;
        $post = $this->payload['post'] ?? null;

        // 隐藏字段用于传递文章ID
        $this->hidden('post_id')->value($postId);

        // 显示文章信息
        if ($postId) {
            // 重新获取文章对象，确保是正确的对象类型
            $postObj = Demo5Post::find($postId);
            if ($postObj) {
                $this->html('
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="control-label">文章信息</label>
                        <div class="form-control-static" style="padding: 8px 12px; background-color: #f8f9fa; border-radius: 4px;">
                            <strong>ID:</strong> ' . $postObj->id . '<br>
                            <strong>标题:</strong> ' . $postObj->title . '
                        </div>
                    </div>
                ');
            }
        }

        // 评论内容文本域
        $this->textarea('content', '评论内容')
            ->placeholder('请输入评论内容...')
            ->rows(5)
            ->required()
            ->rules('required|string|max:1000');
    }

    /**
     * 设置表单默认值
     */
    public function default()
    {
        return [
            'content' => '',
        ];
    }
}