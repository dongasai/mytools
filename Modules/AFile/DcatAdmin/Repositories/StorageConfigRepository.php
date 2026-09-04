<?php

namespace Modules\AFile\DcatAdmin\Repositories;

use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;
use Modules\AFile\Models\FileStorageConfig;

/**
 * 存储配置数据仓库
 *
 * 提供存储配置数据的访问和操作，专门用于后台管理界面
 */
class StorageConfigRepository extends EloquentRepository
{
    protected $eloquentClass = FileStorageConfig::class;
}
