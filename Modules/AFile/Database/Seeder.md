# AFile模块数据创建流程(Seeder规划设计)

## 概述

AFile文件管理模块的基础数据创建流程，初始化文件存储配置，为系统提供动态存储驱动支持。

## Seeder文件

- `AFileDatabaseSeeder` - 主Seeder，负责调用其他Seeder并展示统计信息
- `FileStorageConfigSeeder` - 创建存储配置数据，初始化local驱动

## 数据总体规划

- **存储配置表 (file_storage_configs)**: 1条local驱动配置
- **其他表**: 暂无初始数据（运行时动态创建）

## 创建流程

**执行依赖顺序**: 存储配置 → 其他表（运行时）

### 1. 存储配置数据创建

**依赖**: 无（第一个执行，其他表依赖此配置）
**Seeder**: `FileStorageConfigSeeder.php`

#### 1.1 存储配置结构

**配置字段**:
- `name`: 存储磁盘名称（必填，唯一）
- `driver`: 存储驱动类型（local/s3/oss等）
- `config`: 配置值（JSON格式，自动转换）
- `description`: 配置描述信息
- `is_default`: 是否默认存储（布尔值）
- `is_temp`: 是否临时存储（布尔值）
- `status`: 启用状态（布尔值）
- `env`: 环境标识（development/testing/production）

#### 1.2 预设存储配置

**1. Local本地存储**
```php
[
    'name' => 'local',
    'driver' => 'local',
    'config' => [
        'root' => storage_path('app'),
        'throw' => true,
    ],
    'description' => '本地文件存储（默认）',
    'is_default' => true,
    'is_temp' => true,
    'status' => true,
    'env' => app()->environment(),
]
```

**配置说明**:
- `root`: 存储根目录，指向storage/app
- `throw`: 异常抛出标志，文件操作失败时抛出异常
- `is_default`: 标记为默认存储磁盘
- `is_temp`: 同时用于临时文件存储

#### 1.3 配置JSON结构

**Local驱动配置**:
```json
{
  "root": "/www/devroot/moyuan/laravel_php/storage/app",
  "throw": true
}
```

**OSS驱动配置示例**（未来扩展）:
```json
{
  "access_id": "使用 env('ALIYUN_ACCESS_KEY_ID')",
  "access_key": "使用 env('ALIYUN_ACCESS_KEY_SECRET')",
  "bucket": "使用 env('ALIYUN_PROJECT_NAME')",
  "endpoint": "oss-cn-hangzhou.aliyuncs.com",
  "cdnDomain": "",
  "ssl": true,
  "isCName": false,
  "debug": false
}
```

⚠️ **安全提示**: OSS配置应通过环境变量注入，禁止硬编码敏感信息

## 数据产出统计

- **存储配置**: 1条
  - 1条 local驱动（默认+临时）
  - is_default: true
  - is_temp: true
  - status: true

## 配置使用流程

### 1. 系统启动时注册配置

**FileServiceProvider注册逻辑**:
```php
public function boot()
{
    // 从数据库读取配置
    $disks = FileStorageConfig::where('status', true)
        ->where('env', app()->environment())
        ->get();

    // 动态注册到Laravel文件系统
    foreach ($disks as $disk) {
        config(["filesystems.disks.{$disk->name}" => array_merge(
            ['driver' => $disk->driver],
            $disk->config
        )]);
    }

    // 设置默认磁盘
    $defaultDisk = FileStorageConfig::where('is_default', true)
        ->where('env', app()->environment())
        ->first();

    if ($defaultDisk) {
        config(['filesystems.default' => $defaultDisk->name]);
    }
}
```

### 2. 业务模块使用配置

**上传文件时选择存储磁盘**:
```php
// 使用默认存储
$disk = StorageConfig::getStorage();

// 使用指定存储
$ossDisk = StorageConfig::getDisk('oss');

// 文件上传
Storage::disk($disk)->put($path, $content);
```

### 3. 配置变更管理

**变更时自动记录历史**:
```php
// 更新配置（StorageConfigService自动记录历史）
$service = new StorageConfigService();
$service->updateDisk($id, 'oss', $ossConfig, '阿里云OSS存储', false, false);

// 历史自动记录到file_storage_config_histories表
```

## 执行方式

### 完整执行

```bash
# 清理现有数据（如果需要）
php artisan tinker --execute="(new \Modules\AFile\Database\Seeders\AFileDatabaseSeeder)->cleanup()"

# 执行完整的数据填充
php artisan db:seed --class=Modules\\AFile\\Database\\Seeders\\AFileDatabaseSeeder
```

### 单独执行

```bash
# 仅创建存储配置数据
php artisan db:seed --class=Modules\\AFile\\Database\\Seeders\\FileStorageConfigSeeder
```

### 验证配置

```bash
# 查询配置数据
php artisan tinker --execute="
\$config = \Modules\AFile\Models\FileStorageConfig::first();
print_r([
    'name' => \$config->name,
    'driver' => \$config->driver,
    'is_default' => \$config->is_default,
    'env' => \$config->env,
]);
"

# 测试存储功能
php artisan tinker --execute="
Storage::disk('local')->put('test.txt', 'Hello World');
echo Storage::disk('local')->get('test.txt');
Storage::disk('local')->delete('test.txt');
"
```

## 后续扩展

### 添加OSS驱动配置

**通过DcatAdmin后台添加**:
1. 访问: /admin/storage-configs/create
2. 填写配置信息:
   - name: oss
   - driver: oss
   - config: JSON格式的OSS配置
   - is_default: false（可选）
   - is_temp: false
   - status: true
3. 测试连接: /admin/storage-configs/{id}/test

**或通过Seeder添加**:
```php
// 在FileStorageConfigSeeder中添加
FileStorageConfig::create([
    'name' => 'oss',
    'driver' => 'oss',
    'config' => [
        'access_id' => env('ALIYUN_ACCESS_KEY_ID'),
        'access_key' => env('ALIYUN_ACCESS_KEY_SECRET'),
        'bucket' => env('ALIYUN_PROJECT_NAME'),
        'endpoint' => 'oss-cn-hangzhou.aliyuncs.com',
        'ssl' => true,
    ],
    'description' => '阿里云OSS存储',
    'is_default' => false,
    'is_temp' => false,
    'status' => true,
    'env' => $env,
]);
```

## 数据清理说明

**清理顺序**（逆向依赖）:
1. file_template（模板数据）
2. file_storage_config_histories（配置历史）
3. file_storage_configs（存储配置）
4. file_imgs（图片文件）
5. file_files（普通文件）

**注意事项**:
- ⚠️ 清理存储配置前应确保无文件数据依赖
- ⚠️ 生产环境谨慎执行清理操作
- ✅ 建议先备份重要数据再清理

---

**最后更新**: 2026-05-05
**版本**: v1.0