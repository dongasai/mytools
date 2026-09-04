<?php

namespace Modules\Demo5\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\Demo5\Models\Demo5Post;

/**
 * 文章仓库类
 */
class PostRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = Demo5Post::class;
}