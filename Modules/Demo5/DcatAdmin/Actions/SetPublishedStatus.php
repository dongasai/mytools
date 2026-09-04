<?php

namespace Modules\Demo5\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Enums\PostStatus;

/**
 * 设为已发布状态操作
 */
class SetPublishedStatus extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    /**
     * HTML CSS类 - 成功操作
     */
    protected $htmlClasses = ['action-success'];

    /**
     * 按钮标题
     */
    public $title = '设为已发布';

    /**
     * 确认弹窗
     *
     * @return string|array|null
     */
    public function confirm()
    {
        return '确定要将这篇文章发布吗？发布后将对所有用户可见。';
    }

    /**
     * 处理请求
     */
    public function handle(): Response
    {
        try {
            // 获取当前行数据
            $post = Demo5Post::findOrFail($this->getKey());

            // 检查当前状态是否已经是已发布
            if ($post->status === PostStatus::Published->value) {
                return $this->response()
                    ->error('文章已经是已发布状态')
                    ->refresh();
            }

            // 检查是否可以编辑
            $currentStatus = PostStatus::fromValue($post->status);
            if ($currentStatus && !$currentStatus->canEdit()) {
                return $this->response()
                    ->error('当前状态的文章无法发布')
                    ->refresh();
            }

            // 验证文章内容是否完整
            if (empty($post->title) || empty($post->content)) {
                return $this->response()
                    ->error('文章标题或内容不能为空，无法发布')
                    ->refresh();
            }

            // 更新状态为已发布
            $post->update([
                'status' => PostStatus::Published->value,
                'published_at' => $post->published_at ?: now(), // 如果没有发布时间，使用当前时间
                'updated_at' => now(),
            ]);

            return $this->response()
                ->success('文章已成功发布！')
                ->refresh();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('设置已发布状态失败: ' . $e->getMessage(), [
                'post_id' => $this->getKey(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->response()
                ->error('操作失败: ' . $e->getMessage())
                ->refresh();
        }
    }


    /**
     * 判断是否显示该按钮
     *
     * @return bool
     */
    public function allowed()
    {
        $post = Demo5Post::find($this->getKey());

        if (!$post) {
            return false;
        }

        // 如果已经是已发布状态，不显示此按钮
        if ($post->status === PostStatus::Published->value) {
            return false;
        }

        // 检查当前状态是否允许编辑
        $currentStatus = PostStatus::fromValue($post->status);
        return $currentStatus ? $currentStatus->canEdit() : false;
    }
}