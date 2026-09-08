# 数据库连接管理设计

## 问题分析

当前方案缺失**数据库连接配置管理**功能：
- 只能使用系统配置的固定连接
- 无法动态添加/删除/修改数据库连接
- 用户可能需要连接多个不同的数据库实例

## 解决方案

### 新增数据表：`feature_dbadmin_connections`

#### 表结构设计

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint unsigned | 主键 |
| name | varchar(100) | 连接名称（唯一） |
| driver | varchar(20) | 驱动类型（mysql/pgsql/sqlite） |
| host | varchar(100) nullable | 主机地址 |
| port | int unsigned nullable | 端口号 |
| database | varchar(100) | 数据库名 |
| username | varchar(100) nullable | 用户名 |
| password | text nullable | 密码（明文存储） |
| charset | varchar(20) nullable | 字符集 |
| collation | varchar(50) nullable | 排序规则 |
| prefix | varchar(50) nullable | 表前缀 |
| options | json nullable | 其他连接选项 |
| description | text nullable | 连接描述 |
| is_active | tinyint(1) | 是否激活（默认1） |
| last_connected_at | timestamp nullable | 最后连接时间 |
| created_by | int unsigned | 创建者ID |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp nullable | 软删除时间 |

#### 索引设计

- `unique_name` (name) - 连接名称唯一
- `idx_driver` (driver) - 按驱动类型查询
- `idx_is_active` (is_active) - 查询激活的连接
- `idx_created_by` (created_by) - 按创建者查询

---

## 连接管理功能

### 功能模块

#### 1. 连接 CRUD
- **创建连接**: 添加新的数据库连接配置
- **查看连接**: 查看连接列表和详情
- **编辑连接**: 修改连接配置
- **删除连接**: 软删除连接配置
- **激活/停用**: 控制连接是否可用

#### 2. 连接测试
- 测试连接是否可用
- 显示连接信息（数据库版本、字符集等）
- 记录最后连接时间

#### 3. 连接切换
- 在不同连接间切换
- 设置默认连接

#### 4. 权限控制
- 只有超级管理员可以管理连接
- 记录创建者信息

---

## 安全设计

### 密码明文存储

数据库密码直接存储（不加密）：

```php
// 保存时直接存储
$connection->password = $request->password;

// 读取时直接返回
$password = $connection->password;
```

### 连接验证

- 必须测试连接成功后才能保存
- 验证数据库权限（SELECT/INSERT/UPDATE/DELETE）
- 验证连接安全性

### 访问控制

- 只有超级管理员（admin_role_id = 1）可以管理连接
- 普通用户只能使用已激活的连接
- 所有操作记录审计日志

---

## Model 设计

### Connection.php

```php
<?php

namespace Modules\FeatureDbadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * 数据库连接配置模型
 *
 * @property int $id
 * @property string $name 连接名称
 * @property string $driver 驱动类型
 * @property string|null $host 主机地址
 * @property int|null $port 端口号
 * @property string $database 数据库名
 * @property string|null $username 用户名
 * @property string|null $password 密码（明文）
 * @property string|null $charset 字符集
 * @property string|null $collation 排序规则
 * @property string|null $prefix 表前缀
 * @property array|null $options 其他选项
 * @property string|null $description 描述
 * @property bool $is_active 是否激活
 * @property \Carbon\Carbon|null $last_connected_at 最后连接时间
 * @property int $created_by 创建者ID
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Connection extends Model
{
    use SoftDeletes;

    protected $table = 'feature_dbadmin_connections';

    protected $fillable = [
        'name', 'driver', 'host', 'port', 'database',
        'username', 'password', 'charset', 'collation',
        'prefix', 'options', 'description', 'is_active',
        'last_connected_at', 'created_by'
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'port' => 'integer',
        'last_connected_at' => 'datetime',
    ];

    protected $hidden = [
        'password', // 隐藏密码字段
    ];

    /**
     * 设置密码（明文存储）
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = $value;
    }

    /**
     * 获取密码（明文）
     */
    public function getPasswordAttribute($value): ?string
    {
        return $value;
    }

    /**
     * 创建者关联
     */
    public function creator()
    {
        return $this->belongsTo(\Dcat\Admin\Models\Administrator::class, 'created_by');
    }

    /**
     * 测试连接
     */
    public function testConnection(): array
    {
        try {
            $config = $this->toConfigArray();

            // 创建临时连接
            config(['database.connections.temp_test' => $config]);

            // 测试连接
            DB::connection('temp_test')->getPdo();

            // 获取数据库版本
            $version = DB::connection('temp_test')->select('SELECT VERSION() as version')[0]->version ?? 'unknown';

            // 清理临时连接
            DB::purge('temp_test');

            // 更新最后连接时间
            $this->update(['last_connected_at' => now()]);

            return [
                'success' => true,
                'message' => '连接成功',
                'version' => $version,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '连接失败: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 转换为 Laravel 连接配置数组
     */
    public function toConfigArray(): array
    {
        $config = [
            'driver' => $this->driver,
            'database' => $this->database,
        ];

        // 根据驱动类型添加不同配置
        if ($this->driver === 'sqlite') {
            $config['database'] = $this->database; // SQLite 文件路径
        } else {
            $config['host'] = $this->host ?? 'localhost';
            $config['port'] = $this->port ?? $this->getDefaultPort();
            $config['username'] = $this->username;
            $config['password'] = $this->password;
            $config['charset'] = $this->charset ?? 'utf8mb4';
            $config['collation'] = $this->collation ?? 'utf8mb4_unicode_ci';
        }

        if ($this->prefix) {
            $config['prefix'] = $this->prefix;
        }

        if ($this->options) {
            $config['options'] = $this->options;
        }

        return $config;
    }

    /**
     * 获取默认端口
     */
    protected function getDefaultPort(): int
    {
        return match ($this->driver) {
            'mysql' => 3306,
            'pgsql' => 5432,
            'sqlsrv' => 1433,
            default => 3306,
        };
    }

    /**
     * 获取动态连接名称
     */
    public function getDynamicConnectionName(): string
    {
        return 'dynamic_' . $this->id;
    }
}
```

---

## Service 设计更新

### DatabaseService 更新

```php
<?php

namespace Modules\FeatureDbadmin\Services;

use Modules\FeatureDbadmin\Models\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * 数据库连接管理服务
 */
class DatabaseService
{
    /**
     * 获取所有可用的数据库连接
     *
     * @param bool $activeOnly 是否只返回激活的连接
     * @return array
     */
    public static function getConnections(bool $activeOnly = true): array
    {
        $query = Connection::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get()->toArray();
    }

    /**
     * 获取连接配置
     *
     * @param int $connectionId 连接ID
     * @return array|null
     */
    public static function getConnectionConfig(int $connectionId): ?array
    {
        $connection = Connection::find($connectionId);

        if (!$connection || !$connection->is_active) {
            return null;
        }

        return $connection->toConfigArray();
    }

    /**
     * 测试连接
     *
     * @param int $connectionId 连接ID
     * @return array
     */
    public static function testConnection(int $connectionId): array
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return [
                'success' => false,
                'message' => '连接不存在',
            ];
        }

        return $connection->testConnection();
    }

    /**
     * 切换到指定连接
     *
     * @param int $connectionId 连接ID
     * @return bool
     */
    public static function switchConnection(int $connectionId): bool
    {
        $connection = Connection::find($connectionId);

        if (!$connection || !$connection->is_active) {
            return false;
        }

        // 创建动态连接配置
        $connectionName = $connection->getDynamicConnectionName();
        $config = $connection->toConfigArray();

        // 注册连接
        Config::set("database.connections.{$connectionName}", $config);

        // 清除旧连接
        DB::purge($connectionName);

        return true;
    }

    /**
     * 获取当前使用的连接名称
     *
     * @return string
     */
    public static function getCurrentConnection(): string
    {
        return Config::get('database.default');
    }

    /**
     * 获取连接的数据库版本
     *
     * @param int $connectionId 连接ID
     * @return string|null
     */
    public static function getDatabaseVersion(int $connectionId): ?string
    {
        $result = self::testConnection($connectionId);
        return $result['version'] ?? null;
    }
}
```

---

## Controller 设计

### ConnectionController.php

```php
<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Dcat\Admin\Form;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\DatabaseService;

/**
 * 数据库连接管理控制器
 */
class ConnectionController extends AdminController
{
    protected $title = '数据库连接管理';

    /**
     * 连接列表
     */
    protected function grid()
    {
        return Grid::make(new Connection, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '连接名称');
            $grid->column('driver', '驱动')->label();
            $grid->column('host', '主机');
            $grid->column('port', '端口');
            $grid->column('database', '数据库');
            $grid->column('is_active', '状态')->switch();
            $grid->column('last_connected_at', '最后连接时间');
            $grid->column('created_at', '创建时间');

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 测试连接按钮
                $actions->append(
                    '<a class="btn btn-sm btn-primary" href="javascript:void(0)" onclick="testConnection(' . $actions->row->id . ')">
                        <i class="fa fa-plug"></i> 测试连接
                    </a>'
                );
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('driver', '驱动类型')->select([
                    'mysql' => 'MySQL',
                    'pgsql' => 'PostgreSQL',
                    'sqlite' => 'SQLite',
                ]);
                $filter->equal('is_active', '状态')->select([0 => '停用', 1 => '激活']);
            });
        });
    }

    /**
     * 连接详情
     */
    protected function detail($id)
    {
        return Show::make($id, new Connection, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '连接名称');
            $show->field('driver', '驱动类型');
            $show->field('host', '主机地址');
            $show->field('port', '端口');
            $show->field('database', '数据库名');
            $show->field('username', '用户名');
            $show->field('charset', '字符集');
            $show->field('collation', '排序规则');
            $show->field('prefix', '表前缀');
            $show->field('description', '描述');
            $show->field('is_active', '状态')->using([0 => '停用', 1 => '激活']);
            $show->field('last_connected_at', '最后连接时间');
            $show->field('created_at', '创建时间');
        });
    }

    /**
     * 创建/编辑表单
     */
    protected function form()
    {
        return Form::make(new Connection, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('name', '连接名称')
                ->required()
                ->rules('unique:feature_dbadmin_connections,name,' . $form->model()->id);

            $form->select('driver', '驱动类型')
                ->options([
                    'mysql' => 'MySQL',
                    'pgsql' => 'PostgreSQL',
                    'sqlite' => 'SQLite',
                ])
                ->default('mysql')
                ->required()
                ->when('in', ['mysql', 'pgsql'], function (Form $form) {
                    $form->text('host', '主机地址')->default('localhost')->required();
                    $form->number('port', '端口')->default(3306);
                    $form->text('username', '用户名')->required();
                    $form->password('password', '密码')->required();
                    $form->text('charset', '字符集')->default('utf8mb4');
                    $form->text('collation', '排序规则')->default('utf8mb4_unicode_ci');
                })
                ->when('eq', 'sqlite', function (Form $form) {
                    $form->text('database', '数据库文件路径')->required()
                        ->help('SQLite 数据库文件绝对路径，如: /path/to/database.sqlite');
                });

            $form->text('database', '数据库名')->required();

            $form->text('prefix', '表前缀')
                ->help('可选，数据库表前缀');

            $form->textarea('description', '描述')
                ->rows(3);

            $form->switch('is_active', '是否激活')->default(1);

            $form->hidden('created_by')->value(admin_user()->id);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 保存前验证连接
            $form->saving(function (Form $form) {
                // 测试连接
                $testResult = $this->testConnectionBeforeSave($form);
                if (!$testResult['success']) {
                    return $form->response()->error($testResult['message']);
                }
            });
        });
    }

    /**
     * 保存前测试连接
     */
    protected function testConnectionBeforeSave(Form $form): array
    {
        // 构建临时配置
        $config = [
            'driver' => $form->driver,
            'database' => $form->database,
        ];

        if ($form->driver !== 'sqlite') {
            $config['host'] = $form->host ?? 'localhost';
            $config['port'] = $form->port ?? ($form->driver === 'mysql' ? 3306 : 5432);
            $config['username'] = $form->username;
            $config['password'] = $form->password;
            $config['charset'] = $form->charset ?? 'utf8mb4';
            $config['collation'] = $form->collation ?? 'utf8mb4_unicode_ci';
        }

        try {
            Config::set('database.connections.temp_test', $config);
            DB::connection('temp_test')->getPdo();
            DB::purge('temp_test');

            return ['success' => true, 'message' => '连接测试成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '连接测试失败: ' . $e->getMessage()];
        }
    }

    /**
     * 测试连接接口
     */
    public function test(int $id)
    {
        $result = DatabaseService::testConnection($id);

        if ($result['success']) {
            return $this->response()->success($result['message'])->data($result);
        } else {
            return $this->response()->error($result['message']);
        }
    }
}
```

---

## 更新后的数据表清单

### 表列表（共5张表）

1. ✅ `feature_dbadmin_connections` - 数据库连接配置表（**新增**）
2. ✅ `feature_dbadmin_query_histories` - SQL 查询历史表
3. ✅ `feature_dbadmin_saved_queries` - 保存的查询表
4. ✅ `feature_dbadmin_favorite_tables` - 收藏的表
5. ✅ `feature_dbadmin_table_snapshots` - 表结构快照表

---

**更新时间**: 2026-09-08
**维护者**: 开发团队