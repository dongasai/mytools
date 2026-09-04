<?php

namespace Modules\AFile\DcatAdmin\Repositories;

use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;
use Modules\AFile\Models\FileFile;

/**
 * 文件数据仓库
 *
 * 提供文件数据的访问和操作功能
 */
class FileRepository extends EloquentRepository
{
    protected $eloquentClass = FileFile::class;

    /**
     * 默认加载的关联关系
     *
     * @var array
     */
    protected $with = ['storageConfig'];
}
