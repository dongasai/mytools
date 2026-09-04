<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\AiImage;

/**
 * AI图片日志仓库
 */
class AiImageRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = AiImage::class;

    /**
     * 获取Grid数据（预加载关联模型）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getGridQuery()
    {
        return AiImage::with(['provider', 'model']);
    }
}
