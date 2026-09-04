<?php

namespace Modules\Demo5\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\Demo5\Models\Demo5Post;
use Dcat\Admin\Grid;

/**
 * 最新文章列表
 *
 * 显示最近创建或更新的文章
 */
class RecentPosts extends Card
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('最新文章');
        $this->subTitle('最近创建或更新的文章列表');
        $this->height(400);

        $this->dropdown([
            '5' => '最新5条',
            '10' => '最新10条',
            '15' => '最新15条',
            '20' => '最新20条',
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
        $limit = (int) $request->get('option', '10');
        $this->withContent($this->getRecentPosts($limit));
    }

    /**
     * 写入数据
     *
     * @return void
     */
    public function fill()
    {
        $this->withContent($this->getRecentPosts(10));
    }

    /**
     * 获取最新文章
     *
     * @param int $limit
     * @return array
     */
    protected function getRecentPosts(int $limit): array
    {
        $posts = Demo5Post::with(['user'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->id,
                'title' => $post->title,
                'status' => $post->status,
                'status_label' => $post->getStatusLabel(),
                'author' => $post->user ? $post->user->username : '未知作者',
                'updated_at' => $post->updated_at->format('Y-m-d H:i'),
                'comment_count' => $post->comments()->count(),
            ];
        }

        return $data;
    }

    /**
     * 设置卡片内容
     *
     * @param array $posts
     * @return $this
     */
    protected function withContent(array $posts)
    {
        if (empty($posts)) {
            $html = '<div class="text-center text-muted p-4">暂无文章数据</div>';
            return $this->content($html);
        }

        $color = \Dcat\Admin\Admin::color();
        $success = $color->success();
        $warning = $color->warning();
        $info = $color->info();

        $rows = '';
        foreach ($posts as $post) {
            // 状态标签颜色
            $statusColor = match ($post['status']) {
                'published' => $success,
                'draft' => $warning,
                'archived' => $info,
                default => '#6c757d',
            };

            // 使用 admin_url 生成后台链接
            $showUrl = admin_url('module_demo5/posts/' . $post['id']);
            $editUrl = admin_url('module_demo5/posts/' . $post['id'] . '/edit');

            $rows .= <<<HTML
<tr>
    <td class="text-center">
        <span class="badge badge-primary">{$post['id']}</span>
    </td>
    <td>
        <a href="{$showUrl}" class="text-primary" target="_blank">
            {$post['title']}
        </a>
    </td>
    <td class="text-center">
        <span class="badge" style="background-color: {$statusColor}; color: white;">
            {$post['status_label']}
        </span>
    </td>
    <td class="text-center">
        <span class="text-muted">{$post['author']}</span>
    </td>
    <td class="text-center">
        <span class="badge badge-info">{$post['comment_count']}</span>
    </td>
    <td class="text-center">
        <small class="text-muted">{$post['updated_at']}</small>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-xs">
            <a href="{$showUrl}" class="btn btn-primary btn-xs" target="_blank" title="查看">
                <i class="feather icon-eye"></i>
            </a>
            <a href="{$editUrl}" class="btn btn-warning btn-xs" target="_blank" title="编辑">
                <i class="feather icon-edit"></i>
            </a>
        </div>
    </td>
</tr>
HTML;
        }

        $html = <<<HTML
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th class="text-center" style="width: 60px;">ID</th>
                <th>标题</th>
                <th class="text-center" style="width: 80px;">状态</th>
                <th class="text-center" style="width: 100px;">作者</th>
                <th class="text-center" style="width: 80px;">评论数</th>
                <th class="text-center" style="width: 120px;">更新时间</th>
                <th class="text-center" style="width: 100px;">操作</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>
</div>
HTML;

        return $this->content($html);
    }
}