<?php

namespace Modules\Demo5\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\Demo5\Enums\CommentStatus as CommentStatusEnum;
use Modules\Demo5\Models\Demo5Comment;

/**
 * 评论状态统计图表
 *
 * 显示各种评论状态的统计信息
 */
class CommentStatus extends Card
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('评论状态统计');
        $this->height(300);
        $this->subTitle('评论审核概览');

        $this->dropdown([
            'all' => '全部评论',
            CommentStatusEnum::Approved->value => '已通过评论',
            CommentStatusEnum::Pending->value => '待审核评论',
            CommentStatusEnum::Rejected->value => '已拒绝评论',
        ]);
    }

    /**
     * 渲染模板
     *
     * @return string
     */
    public function render()
    {
        $this->fill();

        return parent::render();
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $filter = $request->get('option', 'all');

        switch ($filter) {
            case CommentStatusEnum::Approved->value:
                $this->subTitle('已通过评论统计');
                $this->withContent($this->getApprovedStats());
                break;
            case CommentStatusEnum::Pending->value:
                $this->subTitle('待审核评论统计');
                $this->withContent($this->getPendingStats());
                break;
            case CommentStatusEnum::Rejected->value:
                $this->subTitle('已拒绝评论统计');
                $this->withContent($this->getRejectedStats());
                break;
            case 'all':
            default:
                $this->subTitle('全部评论统计');
                $this->withContent($this->getAllStats());
        }
    }

    /**
     * 写入数据
     *
     * @return void
     */
    public function fill()
    {
        $this->withContent($this->getAllStats());
    }

    /**
     * 获取全部评论统计
     *
     * @return array
     */
    protected function getAllStats(): array
    {
        $total = Demo5Comment::count();
        $approved = Demo5Comment::where('status', CommentStatusEnum::Approved->value)->count();
        $pending = Demo5Comment::where('status', CommentStatusEnum::Pending->value)->count();
        $rejected = Demo5Comment::where('status', CommentStatusEnum::Rejected->value)->count();

        // 获取顶级评论数量
        $topLevel = Demo5Comment::whereNull('parent_id')->count();
        // 获取回复评论数量
        $replies = Demo5Comment::whereNotNull('parent_id')->count();

        // 获取最近7天的评论
        $recent = Demo5Comment::where('created_at', '>=', now()->subDays(7))->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'top_level' => $topLevel,
            'replies' => $replies,
            'recent' => $recent,
            'approved_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
            'pending_rate' => $total > 0 ? round(($pending / $total) * 100, 1) : 0,
            'rejected_rate' => $total > 0 ? round(($rejected / $total) * 100, 1) : 0,
            'top_level_rate' => $total > 0 ? round(($topLevel / $total) * 100, 1) : 0,
            'reply_rate' => $total > 0 ? round(($replies / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取已通过评论统计
     *
     * @return array
     */
    protected function getApprovedStats(): array
    {
        $approved = Demo5Comment::where('status', CommentStatusEnum::Approved->value);
        $total = $approved->count();

        $recentApproved = $approved->where('created_at', '>=', now()->subDays(7))->count();
        $topLevelApproved = $approved->whereNull('parent_id')->count();
        $replyApproved = $approved->whereNotNull('parent_id')->count();

        return [
            'total' => $total,
            'recent' => $recentApproved,
            'top_level' => $topLevelApproved,
            'replies' => $replyApproved,
            'recent_rate' => $total > 0 ? round(($recentApproved / $total) * 100, 1) : 0,
            'top_level_rate' => $total > 0 ? round(($topLevelApproved / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取待审核评论统计
     *
     * @return array
     */
    protected function getPendingStats(): array
    {
        $pending = Demo5Comment::where('status', CommentStatusEnum::Pending->value);
        $total = $pending->count();

        $oldPending = $pending->where('created_at', '<', now()->subDays(3))->count();
        $recentPending = $pending->where('created_at', '>=', now()->subDays(3))->count();

        return [
            'total' => $total,
            'old' => $oldPending,
            'recent' => $recentPending,
            'old_rate' => $total > 0 ? round(($oldPending / $total) * 100, 1) : 0,
            'recent_rate' => $total > 0 ? round(($recentPending / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取已拒绝评论统计
     *
     * @return array
     */
    protected function getRejectedStats(): array
    {
        $rejected = Demo5Comment::where('status', CommentStatusEnum::Rejected->value);
        $total = $rejected->count();

        $recentRejected = $rejected->where('created_at', '>=', now()->subDays(7))->count();
        $oldRejected = $rejected->where('created_at', '<', now()->subDays(7))->count();

        return [
            'total' => $total,
            'recent' => $recentRejected,
            'old' => $oldRejected,
            'recent_rate' => $total > 0 ? round(($recentRejected / $total) * 100, 1) : 0,
            'old_rate' => $total > 0 ? round(($oldRejected / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 设置卡片内容
     *
     * @param array $stats
     * @return $this
     */
    protected function withContent(array $stats)
    {
        $stats = $this->getAllStats(); // 强制使用全部统计数据

        $color = \Dcat\Admin\Admin::color();
        $primary = $color->primary();
        $success = $color->success();
        $warning = $color->warning();
        $danger = $color->danger();
        $info = $color->info();

        // 全部评论统计
        $html = <<<HTML
<div class="row text-center">
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">总评论</div>
        <div class="h3 mb-0 font-weight-bold text-primary">{$stats['total']}</div>
    </div>
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">已通过</div>
        <div class="h3 mb-0 font-weight-bold text-success">{$stats['approved']}</div>
        <div class="text-80">{$stats['approved_rate']}%</div>
    </div>
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">待审核</div>
        <div class="h3 mb-0 font-weight-bold text-warning">{$stats['pending']}</div>
        <div class="text-80">{$stats['pending_rate']}%</div>
    </div>
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">已拒绝</div>
        <div class="h3 mb-0 font-weight-bold text-danger">{$stats['rejected']}</div>
        <div class="text-80">{$stats['rejected_rate']}%</div>
    </div>
</div>
<div class="row text-center">
    <div class="col-4">
        <div class="text-80 mb-1">顶级评论</div>
        <div class="h5 mb-0 font-weight-bold" style="color: {$info}">{$stats['top_level']}</div>
        <div class="text-80">{$stats['top_level_rate']}%</div>
    </div>
    <div class="col-4">
        <div class="text-80 mb-1">回复评论</div>
        <div class="h5 mb-0 font-weight-bold text-info">{$stats['replies']}</div>
        <div class="text-80">{$stats['reply_rate']}%</div>
    </div>
    <div class="col-4">
        <div class="text-80 mb-1">最近评论</div>
        <div class="h5 mb-0 font-weight-bold" style="color: {$success}">{$stats['recent']}</div>
        <div class="text-80">最近7天</div>
    </div>
</div>
HTML;

        return $this->content($html);
    }
}