<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SSH服务器分组模型
 *
 * @property int $id
 * @property string $name 分组名称
 * @property int|null $parent_id 父分组ID
 * @property string|null $description 描述
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class ServerGroup extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'fssh_server_groups';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'parent_id',
        'description',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
        ];
    }

    /**
     * 父分组关系
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 子分组关系
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * 分组下的服务器
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'group_id');
    }

    /**
     * 获取层级路径名称
     */
    public function getFullPathName(): string
    {
        $names = [$this->name];
        $parent = $this->parent;

        while ($parent !== null) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $names);
    }
}