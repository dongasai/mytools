<?php

namespace Modules\Demo5\DcatAdmin\Actions\Batch;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Modules\Demo5\Enums\CommentStatus;

/**
 * 批量审核通过评论
 */
class ApproveComments extends BatchAction
{
    /**
     * 按钮标题 - 使用属性设置
     *
     * @var string
     */
    protected $title = '审核通过';

    /**
     * 确认弹窗信息
     *
     * @return string|array|void
     */
    public function confirm()
    {
        return '确定要审核通过选中的评论吗？';
    }

    /**
     * 处理请求
     *
     * @return mixed
     */
    public function handle()
    {
        // 获取选中的评论ID
        $keys = $this->getKey();

        if (empty($keys)) {
            return $this->response()->error('请选择要操作的评论');
        }

        try {
            // 批量更新评论状态为已通过
            \Modules\Demo5\Models\Demo5Comment::whereIn('id', $keys)
                ->update(['status' => CommentStatus::Approved->value]);

            return $this->response()
                ->success('成功审核通过 ' . count($keys) . ' 条评论')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 设置请求参数
     *
     * @return array
     */
    public function parameters()
    {
        return [];
    }
}