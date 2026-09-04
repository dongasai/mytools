<?php

namespace Modules\Demo5\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\Demo5\Models\Demo5Post;

/**
 * 文章状态统计图表
 *
 * 显示各种文章状态的统计信息
 */
class PostStatus extends Card
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('文章状态统计');
        $this->subTitle('博客文章概览');
        $this->height(300);

        $this->dropdown([
            'all' => '全部文章',
            'published' => '已发布文章',
            'draft' => '草稿文章',
            'archived' => '已归档文章',
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
            case 'published':
                $this->subTitle('已发布文章统计');
                $this->withContent($this->getPublishedStats());
                break;
            case 'draft':
                $this->subTitle('草稿文章统计');
                $this->withContent($this->getDraftStats());
                break;
            case 'archived':
                $this->subTitle('已归档文章统计');
                $this->withContent($this->getArchivedStats());
                break;
            case 'all':
            default:
                $this->subTitle('全部文章统计');
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
     * 获取全部文章统计
     *
     * @return array
     */
    protected function getAllStats(): array
    {
        $total = Demo5Post::count();
        $published = Demo5Post::where('status', 'published')->count();
        $draft = Demo5Post::where('status', 'draft')->count();
        $archived = Demo5Post::where('status', 'archived')->count();

        // 获取有评论的文章数量
        $withComments = Demo5Post::whereHas('comments')->count();

        // 获取最近发布的文章数量（最近30天）
        $recentPublished = Demo5Post::where('status', 'published')
            ->where('published_at', '>=', now()->subDays(30))
            ->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'archived' => $archived,
            'with_comments' => $withComments,
            'recent_published' => $recentPublished,
            'published_rate' => $total > 0 ? round(($published / $total) * 100, 1) : 0,
            'draft_rate' => $total > 0 ? round(($draft / $total) * 100, 1) : 0,
            'archived_rate' => $total > 0 ? round(($archived / $total) * 100, 1) : 0,
            'comment_rate' => $total > 0 ? round(($withComments / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取已发布文章统计
     *
     * @return array
     */
    protected function getPublishedStats(): array
    {
        $published = Demo5Post::where('status', 'published');
        $total = $published->count();

        $recentPublished = $published->where('published_at', '>=', now()->subDays(30))->count();
        $withComments = $published->whereHas('comments')->count();
        $recentWithComments = $published->whereHas('comments', function ($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();

        return [
            'total' => $total,
            'recent' => $recentPublished,
            'with_comments' => $withComments,
            'recent_with_comments' => $recentWithComments,
            'recent_rate' => $total > 0 ? round(($recentPublished / $total) * 100, 1) : 0,
            'comment_rate' => $total > 0 ? round(($withComments / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取草稿文章统计
     *
     * @return array
     */
    protected function getDraftStats(): array
    {
        $drafts = Demo5Post::where('status', 'draft');
        $total = $drafts->count();

        $oldDrafts = $drafts->where('created_at', '<', now()->subDays(30))->count();
        $recentDrafts = $drafts->where('created_at', '>=', now()->subDays(30))->count();

        return [
            'total' => $total,
            'old' => $oldDrafts,
            'recent' => $recentDrafts,
            'old_rate' => $total > 0 ? round(($oldDrafts / $total) * 100, 1) : 0,
            'recent_rate' => $total > 0 ? round(($recentDrafts / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 获取已归档文章统计
     *
     * @return array
     */
    protected function getArchivedStats(): array
    {
        $archived = Demo5Post::where('status', 'archived');
        $total = $archived->count();

        $recentArchived = $archived->where('updated_at', '>=', now()->subDays(30))->count();
        $oldArchived = $archived->where('updated_at', '<', now()->subDays(30))->count();

        return [
            'total' => $total,
            'recent' => $recentArchived,
            'old' => $oldArchived,
            'recent_rate' => $total > 0 ? round(($recentArchived / $total) * 100, 1) : 0,
            'old_rate' => $total > 0 ? round(($oldArchived / $total) * 100, 1) : 0,
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
        $info = $color->info();

        // 全部文章统计
        $html = <<<HTML
<div class="row text-center">
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">总文章</div>
        <div class="h3 mb-0 font-weight-bold text-primary">{$stats['total']}</div>
    </div>
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">已发布</div>
        <div class="h3 mb-0 font-weight-bold text-success">{$stats['published']}</div>
        <div class="text-80">{$stats['published_rate']}%</div>
    </div>
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">草稿</div>
        <div class="h3 mb-0 font-weight-bold text-warning">{$stats['draft']}</div>
        <div class="text-80">{$stats['draft_rate']}%</div>
    </div>
    <div class="col-3 mb-3">
        <div class="text-80 mb-1">已归档</div>
        <div class="h3 mb-0 font-weight-bold text-muted">{$stats['archived']}</div>
        <div class="text-80">{$stats['archived_rate']}%</div>
    </div>
</div>
<div class="row text-center">
    <div class="col-4">
        <div class="text-80 mb-1">有评论</div>
        <div class="h5 mb-0 font-weight-bold" style="color: {$info}">{$stats['with_comments']}</div>
        <div class="text-80">{$stats['comment_rate']}%</div>
    </div>
    <div class="col-4">
        <div class="text-80 mb-1">最近发布</div>
        <div class="h5 mb-0 font-weight-bold text-info">{$stats['recent_published']}</div>
        <div class="text-80">最近30天</div>
    </div>
    <div class="col-4">
        <div class="text-80 mb-1">发布率</div>
        <div class="h5 mb-0 font-weight-bold" style="color: {$success}">{$stats['published_rate']}%</div>
        <div class="text-80">占总文章比</div>
    </div>
</div>
HTML;

        return $this->content($html);
    }
}