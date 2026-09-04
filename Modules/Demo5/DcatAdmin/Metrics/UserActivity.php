<?php

namespace Modules\Demo5\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\Demo5\Models\Demo5Post;
use Modules\Demo5\Models\Demo5Comment;

/**
 * 内容活动统计图表
 *
 * 显示文章和评论相关的统计信息
 */
class UserActivity extends Card
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('内容活动统计');
        $this->subTitle('文章和评论参与度概览');
        $this->height(300);

        $this->dropdown([
            'all' => '全部内容',
            'posts' => '文章统计',
            'comments' => '评论统计',
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
            case 'posts':
                $this->subTitle('文章统计');
                $this->withContent($this->getPostsStats());
                break;
            case 'comments':
                $this->subTitle('评论统计');
                $this->withContent($this->getCommentsStats());
                break;
            case 'all':
            default:
                $this->subTitle('全部内容统计');
                $this->withContent($this->getAllContentStats());
        }
    }

    /**
     * 写入数据
     *
     * @return void
     */
    public function fill()
    {
        $this->withContent($this->getAllContentStats());
    }

    /**
     * 获取全部内容统计
     *
     * @return array
     */
    protected function getAllContentStats(): array
    {
        // 文章统计
        $totalPosts = Demo5Post::count();
        $publishedPosts = Demo5Post::where('status', 'published')->count();
        $draftPosts = Demo5Post::where('status', 'draft')->count();
        $archivedPosts = Demo5Post::where('status', 'archived')->count();
        $recentPosts = Demo5Post::where('created_at', '>=', now()->subDays(30))->count();

        // 评论统计
        $totalComments = Demo5Comment::count();
        $approvedComments = Demo5Comment::where('status', 'approved')->count();
        $pendingComments = Demo5Comment::where('status', 'pending')->count();
        $rejectedComments = Demo5Comment::where('status', 'rejected')->count();
        $recentComments = Demo5Comment::where('created_at', '>=', now()->subDays(7))->count();

        return [
            'total_posts' => $totalPosts,
            'published_posts' => $publishedPosts,
            'draft_posts' => $draftPosts,
            'archived_posts' => $archivedPosts,
            'recent_posts' => $recentPosts,
            'total_comments' => $totalComments,
            'approved_comments' => $approvedComments,
            'pending_comments' => $pendingComments,
            'rejected_comments' => $rejectedComments,
            'recent_comments' => $recentComments,
            'publish_rate' => $totalPosts > 0 ? round(($publishedPosts / $totalPosts) * 100, 1) : 0,
            'comment_approval_rate' => $totalComments > 0 ? round(($approvedComments / $totalComments) * 100, 1) : 0,
        ];
    }

    /**
     * 获取文章统计
     *
     * @return array
     */
    protected function getPostsStats(): array
    {
        $total = Demo5Post::count();
        $published = Demo5Post::where('status', 'published')->count();
        $draft = Demo5Post::where('status', 'draft')->count();
        $archived = Demo5Post::where('status', 'archived')->count();
        $recent = Demo5Post::where('created_at', '>=', now()->subDays(30))->count();
        $publishedThisMonth = Demo5Post::where('status', 'published')
            ->where('published_at', '>=', now()->subMonth())->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'archived' => $archived,
            'recent' => $recent,
            'published_this_month' => $publishedThisMonth,
            'publish_rate' => $total > 0 ? round(($published / $total) * 100, 1) : 0,
            'draft_rate' => $total > 0 ? round(($draft / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取评论统计
     *
     * @return array
     */
    protected function getCommentsStats(): array
    {
        $total = Demo5Comment::count();
        $approved = Demo5Comment::where('status', 'approved')->count();
        $pending = Demo5Comment::where('status', 'pending')->count();
        $rejected = Demo5Comment::where('status', 'rejected')->count();
        $recent = Demo5Comment::where('created_at', '>=', now()->subDays(7))->count();
        $topLevel = Demo5Comment::whereNull('parent_id')->count();
        $replies = Demo5Comment::whereNotNull('parent_id')->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'recent' => $recent,
            'top_level' => $topLevel,
            'replies' => $replies,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
            'pending_rate' => $total > 0 ? round(($pending / $total) * 100, 1) : 0,
            'reply_rate' => $total > 0 ? round(($replies / $total) * 100, 1) : 0,
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
        $stats = $this->getAllContentStats(); // 强制使用全部统计数据

        $color = \Dcat\Admin\Admin::color();
        $primary = $color->primary();
        $success = $color->success();
        $warning = $color->warning();
        $danger = $color->danger();
        $info = $color->info();

        // 内容统计展示
        $html = <<<HTML
<div class="row text-center">
    <div class="col-6 mb-3">
        <div class="text-80 mb-1">总文章</div>
        <div class="h3 mb-0 font-weight-bold text-primary">{$stats['total_posts']}</div>
    </div>
    <div class="col-6 mb-3">
        <div class="text-80 mb-1">已发布</div>
        <div class="h3 mb-0 font-weight-bold text-success">{$stats['published_posts']}</div>
        <div class="text-80">{$stats['publish_rate']}%</div>
    </div>
</div>
<div class="row text-center">
    <div class="col-4 mb-2">
        <div class="text-80 mb-1">草稿</div>
        <div class="h5 mb-0 font-weight-bold text-warning">{$stats['draft_posts']}</div>
    </div>
    <div class="col-4 mb-2">
        <div class="text-80 mb-1">已归档</div>
        <div class="h5 mb-0 font-weight-bold text-secondary">{$stats['archived_posts']}</div>
    </div>
    <div class="col-4 mb-2">
        <div class="text-80 mb-1">最近文章</div>
        <div class="h5 mb-0 font-weight-bold" style="color: {$info}">{$stats['recent_posts']}</div>
    </div>
</div>
<div class="row text-center mt-2">
    <div class="col-6">
        <div class="text-80 mb-1">总评论</div>
        <div class="h5 mb-0 font-weight-bold text-info">{$stats['total_comments']}</div>
    </div>
    <div class="col-6">
        <div class="text-80 mb-1">已审核</div>
        <div class="h5 mb-0 font-weight-bold" style="color: {$success}">{$stats['approved_comments']}</div>
        <div class="text-80">{$stats['comment_approval_rate']}%</div>
    </div>
</div>
HTML;

        return $this->content($html);
    }
}