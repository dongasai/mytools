<?php

namespace Modules\Demo5\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Enums\PostStatus;

/**
 * 设为草稿状态操作
 */
class SetDraftStatus extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    /**
     * HTML CSS类 - 警告操作
     */
    protected $htmlClasses = ['action-warning'];

    /**
     * 按钮标题
     */
    public $title = '设为草稿';

    /**
     * 确认弹窗
     *
     * @return string|array|null
     */
    public function confirm()
    {
        return '确定要将这篇文章设为草稿状态吗？';
    }

    /**
     * 处理请求
     */
    public function handle(): Response
    {
        try {
            // 获取当前行数据
            $post = Demo5Post::findOrFail($this->getKey());

            // 检查当前状态是否已经是草稿
            if ($post->status === PostStatus::Draft->value) {
                return $this->response()
                    ->error('文章已经是草稿状态')
                    ->refresh();
            }

            // 检查是否可以编辑
            $currentStatus = PostStatus::fromValue($post->status);
            if ($currentStatus && !$currentStatus->canEdit()) {
                return $this->response()
                    ->error('当前状态的文章无法修改为草稿')
                    ->refresh();
            }

            // 更新状态为草稿
            $post->update([
                'status' => PostStatus::Draft->value,
                'updated_at' => now(),
            ]);

            return $this->response()
                ->success('已成功设置为草稿状态')
                ->refresh();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('设置草稿状态失败: ' . $e->getMessage(), [
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

        // 如果已经是草稿状态，不显示此按钮
        if ($post->status === PostStatus::Draft->value) {
            return false;
        }

        // 检查当前状态是否允许编辑
        $currentStatus = PostStatus::fromValue($post->status);
        return $currentStatus ? $currentStatus->canEdit() : false;
    }
}