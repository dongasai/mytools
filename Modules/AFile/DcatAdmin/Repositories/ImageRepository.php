<?php

namespace Modules\AFile\DcatAdmin\Repositories;

use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;
use Modules\AFile\Models\FileImg;

/**
 * 图片数据仓库
 *
 * 提供图片数据的访问和操作功能
 */
class ImageRepository extends EloquentRepository
{
    protected $eloquentClass = FileImg::class;

    /**
     * 默认加载的关联关系
     *
     * @var array
     */
    protected $with = ['storageConfig'];
}
