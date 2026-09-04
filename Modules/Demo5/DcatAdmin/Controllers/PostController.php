<?php

namespace Modules\Demo5\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Modules\Demo5\DcatAdmin\Actions\PostCommentAction;
use Modules\Demo5\DcatAdmin\Actions\AddCommentAction;
use Modules\Demo5\DcatAdmin\Actions\SetDraftStatus;
use Modules\Demo5\DcatAdmin\Actions\SetPublishedStatus;
use Modules\Demo5\DcatAdmin\Repositories\PostRepository;
use Modules\Demo5\Enums\PostStatus;

/**
 * 文章管理控制器
 */
class PostController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new PostRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('title', '标题')->width('300px')->limit(50);
            $grid->column('user_id', '作者ID')->width('120px');

            // 添加评论数量列
            $grid->column('comment_count', '评论数')->display(function () {
                $count = $this->comments()->count();

                return $count > 0
                    ? '<span class="badge badge-info">'.$count.'</span>'
                    : '<span class="badge badge-secondary">0</span>';
            })->width('80px')->sortable();

            $grid->column('status', '状态')->display(function ($value) {
                $status = PostStatus::fromValue($value);

                return $status ? $status->getBadgeHtml() : '<span class="badge badge-secondary">未知</span>';
            })->width('100px');
            $grid->column('published_at', '发布时间')->width('160px');
            $grid->column('created_at', '创建时间')->width('160px')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            $grid->filter(function (Grid\Filter $filter) {
                // 更改为 panel 布局
                $filter->panel();
                // 默认展开
                $filter->expand();
                $filter->equal('status', '状态')->select(function () {
                    return collect(PostStatus::cases())->pluck('label', 'value')->toArray();
                });
                $filter->like('title', '标题');
                $filter->equal('user_id', '作者ID');
                $filter->between('published_at', '发布时间')->datetime();
                $filter->between('created_at', '创建时间')->datetime();
            });

            $grid->quickSearch(['title', 'content']);
            $grid->enableDialogCreate();
            $grid->showQuickEditButton();

            // 自定义操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new AddCommentAction);
                $actions->append(new PostCommentAction);
                $actions->append(new SetDraftStatus);
                $actions->append(new SetPublishedStatus);
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new PostRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('title', '标题');
            $show->field('content', '内容')->unescape();
            $show->field('status', '状态')->as(function ($value) {
                $status = PostStatus::fromValue($value);

                return $status ? $status->getLabel() : '未知';
            });
            $show->field('user_id', '作者ID');
            $show->field('published_at', '发布时间');
            $show->field('created_at', '创建时间')->as(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
            $show->field('updated_at', '更新时间')->as(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new PostRepository, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('title', '标题')
                ->required()
                ->rules('required|string|max:255');

            $form->editor('content', '内容')
                ->required()
                ->rules('required|string');

            $form->select('status', '状态')
                ->options(function () {
                    return collect(PostStatus::cases())->pluck('label', 'value')->toArray();
                })
                ->default(PostStatus::Draft->value)
                ->required();

            $form->number('user_id', '作者ID')
                ->required()
                ->rules('required|integer|min:1');

            $form->datetime('published_at', '发布时间')
                ->help('选择发布时间，留空则使用当前时间');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();

            // 保存前处理
            $form->saving(function (Form $form) {
                if ($form->status === PostStatus::Published->value && ! $form->published_at) {
                    $form->published_at = now();
                }
            });
        });
    }

    /**
     * Get the title of the page.
     *
     * @return string
     */
    public function title()
    {
        return '文章管理';
    }
}