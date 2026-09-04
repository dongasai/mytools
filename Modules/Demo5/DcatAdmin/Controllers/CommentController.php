<?php

namespace Modules\Demo5\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Form;
use Modules\Demo5\DcatAdmin\Repositories\CommentRepository;
use Modules\Demo5\Enums\CommentStatus;
use Modules\Demo5\Models\Demo5Comment;

/**
 * 评论管理控制器
 */
class CommentController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CommentRepository(), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('post.title', '文章')->width('200px')->limit(30);
            $grid->column('user_id', '评论者ID')->width('120px');
            $grid->column('content', '内容')->width('300px')->limit(50);
            $grid->column('status', '状态')->display(function ($value) {
                // 处理传入的可能是字符串或CommentStatus枚举对象的情况
                if ($value instanceof CommentStatus) {
                    $status = $value;
                } else {
                    $status = CommentStatus::fromValue($value);
                }
                return $status ? $status->getBadgeHtml() : '<span class="badge badge-secondary">未知</span>';
            })->width('100px');
            $grid->column('parent_id', '父评论')->display(function ($value) {
                return $value ? "#{$value}" : '<span class="text-muted">顶级评论</span>';
            })->width('100px');
            $grid->column('ip_address', 'IP地址')->width('130px');
            $grid->column('created_at', '评论时间')->width('160px')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('status', '状态')->select(CommentStatus::getSelectOptions());
                $filter->like('content', '内容');
                // 更改为 panel 布局
                $filter->panel();
                // 默认展开
                $filter->expand();
                $filter->equal('user_id', '评论者ID');
                $filter->whereHas('post', function ($query) {
                    $query->where('title', 'like', "%{$this->input}%");
                }, '文章');
                $filter->equal('post_id', '文章ID');
                $filter->equal('parent_id', '评论类型')->select([
                    '' => '全部',
                    '0' => '顶级评论',
                    '1' => '回复评论',
                ]);
                $filter->between('created_at', '评论时间')->datetime();
            });

            // 如果有post_id参数，自动筛选
            if (request('post_id')) {
                $grid->model()->where('post_id', request('post_id'));
            }

            $grid->quickSearch(['content', 'post.title']);

            // 批量操作
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                $batch->add(new \Modules\Demo5\DcatAdmin\Actions\Batch\ApproveComments());
                $batch->add(new \Modules\Demo5\DcatAdmin\Actions\Batch\RejectComments());
            });


        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new CommentRepository(), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('post.title', '文章');
            $show->field('user_id', '评论者ID');
            $show->field('content', '内容')->unescape();
            $show->field('status', '状态')->using(CommentStatus::getSelectOptions());
            $show->field('parent_id', '父评论')->as(function ($value) {
                return $value ? "#{$value}" : '顶级评论';
            });
            $show->field('ip_address', 'IP地址');
            $show->field('user_agent', '用户代理')->limit(50);
            $show->field('created_at', '评论时间')->as(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
            $show->field('updated_at', '更新时间')->as(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            // 显示回复评论
            $show->relation('replies', '回复评论', function ($model) {
                $grid = new Grid(new \Modules\Demo5\Models\Demo5Comment());
                $grid->model()->where('parent_id', $model->id)->latest();
                $grid->column('user_id', '回复者ID');
                $grid->column('content', '内容')->limit(30);
                $grid->column('status', '状态')->display(function ($value) {
                    // 处理传入的可能是字符串或CommentStatus枚举对象的情况
                    if ($value instanceof CommentStatus) {
                        $status = $value;
                    } else {
                        $status = CommentStatus::fromValue($value);
                    }
                    return $status ? $status->getBadgeHtml() : '<span class="badge badge-secondary">未知</span>';
                });
                $grid->column('created_at', '回复时间')->display(function ($value) {
                    return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
                });
                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableBatchActions();
                $grid->disableFilter();
                return $grid;
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
        return Form::make(new CommentRepository(), function (Form $form) {
            $form->display('id', 'ID');

            // 创建模式下可选择文章和用户
            if ($form->isCreating()) {
                $form->select('post_id', '文章')
                    ->options(function () {
                        return \Modules\Demo5\Models\Demo5Post::pluck('title', 'id');
                    })
                    ->required();

                $form->number('user_id', '评论者ID')
                    ->required()
                    ->rules('required|integer|min:1');

                $form->textarea('content', '内容')
                    ->rows(5)
                    ->required();

                $form->select('status', '状态')
                    ->options(CommentStatus::getSelectOptions())
                    ->default(CommentStatus::getDefault()->value)
                    ->required();

                $form->text('ip_address', 'IP地址')
                    ->default('127.0.0.1');
            } else {
                // 编辑模式下显示关联信息
                $form->display('post.title', '文章');
                $form->display('user_id', '评论者ID');

                $form->textarea('content', '内容')
                    ->readonly()
                    ->rows(5);

                $form->select('status', '状态')
                    ->options(CommentStatus::getSelectOptions())
                    ->required();
            }

            $form->display('parent_id', '父评论')->with(function ($value) {
                return $value ? "#{$value}" : '顶级评论';
            });

            if (!$form->isCreating()) {
                $form->display('ip_address', 'IP地址');
                $form->display('user_agent', '用户代理');
                $form->display('created_at', '评论时间');
            }

            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();

            // 禁用编辑某些字段
            $form->disableSubmitButton(false);
        });
    }

    /**
     * Get the title of the page.
     *
     * @return string
     */
    public function title()
    {
        return '评论管理';
    }
}