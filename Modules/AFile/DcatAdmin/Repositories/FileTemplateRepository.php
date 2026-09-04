<?php

namespace Modules\AFile\DcatAdmin\Repositories;

use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;
use Modules\AFile\Models\FileTemplate;

/**
 * 文件模板数据仓库
 *
 * 提供文件模板数据的访问和操作功能
 */
class FileTemplateRepository extends EloquentRepository
{
    protected $eloquentClass = FileTemplate::class;
}
