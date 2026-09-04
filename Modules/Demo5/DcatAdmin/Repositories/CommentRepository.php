<?php

namespace Modules\Demo5\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\Demo5\Models\Demo5Comment;

/**
 * 评论数据仓库
 */
class CommentRepository extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Demo5Comment::class;



}