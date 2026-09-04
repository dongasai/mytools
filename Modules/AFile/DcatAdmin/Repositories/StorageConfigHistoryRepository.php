<?php

namespace Modules\AFile\DcatAdmin\Repositories;

use Modules\DcatAdmin\DcatAdmin\Repository\EloquentRepository;
use Modules\AFile\Models\FileStorageConfigHistory;

/**
 * 存储配置历史记录数据仓库
 *
 * 提供存储配置历史记录数据的访问和操作，专门用于后台管理界面
 */
class StorageConfigHistoryRepository extends EloquentRepository
{
    protected $eloquentClass = FileStorageConfigHistory::class;
}
