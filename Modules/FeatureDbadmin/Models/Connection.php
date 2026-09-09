<?php

namespace Modules\FeatureDbadmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\DcatAdmin\Models\Administrator;

/**
 * 数据库连接配置模型
 *
 * @property int $id 连接ID
 * @property int|null $created_by 创建者ID
 * @property string $name 连接名称
 * @property string $driver 数据库驱动(mysql/pgsql/sqlite)
 * @property string $host 主机地址
 * @property int $port 端口号
 * @property string $database 数据库名
 * @property string|null $username 用户名
 * @property string|null $password 密码
 * @property string|null $charset 字符集
 * @property string|null $collation 排序规则
 * @property array $options 额外选项
 * @property bool $is_active 是否启用
 * @property string|null $description 描述
 * @property \Carbon\Carbon|null $last_connected_at 最后连接时间
 * @property \Carbon\Carbon|null $created_at 创建时间
 * @property \Carbon\Carbon|null $updated_at 更新时间
 * @property \Carbon\Carbon|null $deleted_at 删除时间
 */
class Connection extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'feature_dbadmin_connections';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'created_by',
        'name',
        'driver',
        'host',
        'port',
        'database',
        'username',
        'password',
        'charset',
        'collation',
        'options',
        'is_active',
        'description',
        'last_connected_at',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [
        'port' => 'integer',
        'options' => 'array',
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    /**
     * 隐藏字段
     *
     * 注意：数据库管理工具中密码需要可见，用于编辑连接
     */
    protected $hidden = [];

    // ==================== 访问器和修改器 ====================

    /**
     * 设置密码（明文存储）
     */
    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value;
    }

    /**
     * 获取密码（明文）
     */
    public function getPasswordAttribute(?string $value): ?string
    {
        return $value;
    }

    // ==================== 模型关系 ====================

    /**
     * 获取创建者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'created_by');
    }

    // ==================== 业务方法 ====================

    /**
     * 测试数据库连接
     *
     * @return array{success: bool, message: string, version: string|null}
     */
    public function testConnection(): array
    {
        try {
            // 1. 获取配置数组
            $config = $this->toConfigArray();

            // 2. 创建临时连接
            config(['database.connections.temp_test' => $config]);

            // 3. 测试连接
            $pdo = DB::connection('temp_test')->getPdo();

            // 4. 获取数据库版本
            $version = DB::connection('temp_test')->selectOne('SELECT VERSION() as version')->version ?? null;

            // 5. 清理临时连接
            DB::purge('temp_test');

            // 6. 更新最后连接时间
            $this->last_connected_at = now();
            $this->save();

            return [
                'success' => true,
                'message' => '连接成功',
                'version' => $version,
            ];
        } catch (\Exception $e) {
            // 清理临时连接
            DB::purge('temp_test');

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'version' => null,
            ];
        }
    }

    /**
     * 转换为 Laravel 连接配置数组
     *
     * @return array<string, mixed>
     */
    public function toConfigArray(): array
    {
        $config = [
            'driver' => $this->driver,
            'database' => $this->database,
            'options' => $this->options ?? [],
        ];

        // 根据驱动类型添加对应配置
        if (in_array($this->driver, ['mysql', 'pgsql'], true)) {
            $config['host'] = $this->host;
            $config['port'] = $this->port ?: $this->getDefaultPort();
            $config['database'] = $this->database;
            $config['username'] = $this->username;
            $config['password'] = $this->password;

            if ($this->charset) {
                $config['charset'] = $this->charset;
            }

            if ($this->collation && $this->driver === 'mysql') {
                $config['collation'] = $this->collation;
            }
        }

        return $config;
    }

    /**
     * 获取默认端口（根据驱动）
     */
    public function getDefaultPort(): int
    {
        return match ($this->driver) {
            'mysql' => 3306,
            'pgsql' => 5432,
            default => 3306,
        };
    }

    /**
     * 获取动态连接名称
     */
    public function getDynamicConnectionName(): string
    {
        return 'feature_dbadmin_' . $this->id;
    }

    /**
     * 注册动态连接
     */
    public function registerDynamicConnection(): void
    {
        $connectionName = $this->getDynamicConnectionName();
        config(['database.connections.' . $connectionName => $this->toConfigArray()]);
    }

    /**
     * 获取启用的连接列表
     *
     * @return array<int, string>
     */
    public static function getActiveConnections(): array
    {
        return static::where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();
    }
}
