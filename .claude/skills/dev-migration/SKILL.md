---
name: dev-migration
description: 编写数据库迁移文件、处理迁移依赖关系、执行迁移操作、处理迁移错误时激活。触发场景：(1) 创建迁移文件 (2) 执行迁移命令 (3) 迁移失败报错（字段已存在/不存在、外键冲突、表冲突等）(4) 分析迁移状态 (5) 调整迁移时间戳。当遇到 Duplicate column、Can't DROP、Table already exists 等错误时必须立即激活此技能，禁止标记已执行来绕过问题。
---

# 数据库迁移文件开发规范

> **⚠️ 重要提醒**：遇到迁移错误（Duplicate column、Can't DROP、Table already exists 等）时，必须立即激活此技能！
> ❌ 禁止标记已执行来绕过问题 | ✅ 必须分析根源修复迁移文件 | ✅ 添加存在性判断确保幂等性

## 核心原则

### Skill协作原则（强制）

**迁移开发必须与 `design-database` Skill 协作**：

```
协作流程：
design-database（设计表结构）→ dev-migration（创建迁移+执行）→ dev-model（创建Model）→ dev-seeder（填充数据）
```

**强制要求**：创建迁移前必须先激活 `design-database` 设计表结构，避免盲目创建导致反复修改。

---

### 迁移文件修改策略

**核心原则**：开发阶段直接修改原迁移文件，不创建增量迁移文件

**执行策略（优先级排序）**：

| 方案 | 适用场景 | 操作步骤 | 优势/风险 |
|------|---------|---------|----------|
| **🌟 方案1（最佳）** | 修改已执行的迁移 | 修改迁移文件 → 手动SQL同步数据库 | 保留数据+一致性 |
| **✅ 方案2** | 修改未执行的迁移 | 修改文件 → `migrate` | 安全快速 |
| **✅ 方案3** | 修改已执行的迁移 | `rollback --step=1` → 修改 → `migrate` | 保留其他表数据 |
| **⚠️ 方案4** | 特殊场景（初始化/重构） | `db:wipe --force` → 修改 → `migrate` | 删除所有数据 |

---

## 一、迁移文件创建

### 创建命令（强制使用模块化命令）

```bash
# ✅ 正确：使用 module:make-migration
php artisan module:make-migration create_users_table User
php artisan module:make-migration add_status_to_users_table User

# ❌ 错误：不要使用 make:migration（会创建到错误位置）
```

**命令参数**：
- `{迁移名称}`：操作类型 + 表名（如 `create_users_table`）
- `{模块名}`：模块目录名（如 `User`、`Novel`）

**存储位置**：`Modules/{模块}/Database/Migrations/`

---

### 命名规范

**格式**：`{时间戳}_{操作类型}_{表名}_table.php`

**时间戳**：`YYYY_MM_DD_HHMMSS`

**操作类型**：
| 类型 | 格式 | 示例 |
|------|------|------|
| 创建表 | `create_{表名}_table` | `create_user_users_table.php` |
| 新增字段 | `add_{字段}_to_{表名}_table` | `add_status_to_users_table.php` |
| 修改字段 | `alter_{描述}_in_{表名}_table` | `alter_status_in_users_table.php` |

---

## 二、时间戳顺序规则（关键）

### 依赖关系原则

**核心规则**：被依赖的表必须先创建（时间戳更早）

**依赖检测**：
- 外键引用 → 被引用表时间戳必须更早
- 模块依赖 → User模块基础表最先创建

**时间戳规划分段**：
| 优先级 | 时间戳范围 | 模块类型 |
|--------|----------|---------|
| 最高 | `2024_12_xx` | Cleanup等工具模块 |
| 基础 | `2025_10_xx ~ 2025_11_30` | User、Account、Application |
| 业务 | `2025_12_xx ~ 2026_04_xx` | Point、FeatureSms、Demo5 |
| 扩展 | `2026_05_xx` | Novel、NovelReader、AFile |

**错误案例**：
```
❌ 2025_04_30_100003_create_novel_novels_table.php（引用user_users）
   2025_11_30_173434_create_user_users_table.php（Novel比User早7个月）

✅ 2025_11_30_173434_create_user_users_table.php（User先创建）
   2026_05_01_100003_create_novel_novels_table.php（Novel后创建）
```

---

## 三、迁移文件编写

### 基础结构模板

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 外键字段（确保被引用表已存在）
            $table->unsignedBigInteger('user_id')->comment('用户ID');

            // 业务字段
            $table->string('name', 100)->comment('名称');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态:1正常,2禁用');

            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('user_id');
            $table->index(['user_id', 'status']);

            // 外键约束
            $table->foreign('user_id')
                  ->references('id')
                  ->on('user_users')
                  ->onDelete('cascade');

            $table->comment('表用途说明');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

---

### 字段定义规范

| 类型 | 定义方式 | 注释规范 |
|------|---------|---------|
| 主键 | `$table->id()->comment('主键ID')` | 必须有注释 |
| 外键 | `$table->unsignedBigInteger('user_id')->comment('用户ID')` | 先定义字段，后添加外键 |
| 字符串 | `$table->string('name', 100)->comment('名称')` | 指定长度 |
| 整数 | `$table->integer('count')->default(0)->comment('数量')` | 默认值 |
| 枚举 | `$table->unsignedTinyInteger('status')->default(1)->comment('状态:1正常,2禁用')` | 枚举值注释 |
| 时间戳 | `$table->timestamp('created_at')->useCurrent()->comment('创建时间')` | useCurrent |

---

### 外键约束规范

**必须遵循**：
1. 被引用表必须先创建（时间戳更早）
2. 外键字段类型必须匹配（都是unsignedBigInteger）
3. 开发阶段删除外键要添加try-catch处理

**正确示例**：
```php
// 步骤1：定义字段
$table->unsignedBigInteger('user_id')->comment('用户ID');

// 步骤2：添加索引
$table->index('user_id');

// 步骤3：添加外键
$table->foreign('user_id')
      ->references('id')
      ->on('user_users')
      ->onDelete('cascade');
```

**删除外键示例（开发阶段）**：
```php
try {
    Schema::table('admin_grid_views', function (Blueprint $table): void {
        $table->dropForeign(['admin_id']);
    });
} catch (\Exception $e) {
    if (!str_contains($e->getMessage(), "Can't DROP")) {
        throw $e;
    }
}
```

---

## 四、手动同步数据库（最佳实践）

### 核心思路

**目的**：修改已执行的迁移文件后，通过手动SQL调整数据库结构，使两者保持一致

**优势**：保留测试数据 + 迁移文件记录准确 + migrate:status状态一致

### 操作流程

```
修改迁移文件 → 分析改动 → 编写SQL → 执行SQL → 验证结果
```

---

### SQL对照表（常见场景）

| 场景 | 迁移文件修改 | 手动SQL执行 |
|------|-------------|-------------|
| **新增字段** | `$table->string('phone', 20)->nullable()->comment('手机号')` | `ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL COMMENT '手机号'` |
| **修改字段** | `$table->string('name', 100)->comment('用户名')` | `ALTER TABLE users MODIFY COLUMN name VARCHAR(100) COMMENT '用户名'` |
| **删除字段** | 删除字段定义行 | `ALTER TABLE users DROP COLUMN temp_field` |
| **新增索引** | `$table->index('phone')` | `ALTER TABLE users ADD INDEX idx_phone (phone)` |
| **删除索引** | 删除索引定义行 | `ALTER TABLE users DROP INDEX idx_old_field` |
| **新增外键** | `$table->foreign('user_id')->references('id')->on('users')` | `ALTER TABLE users ADD CONSTRAINT fk_users_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` |

---

### SQL执行工具

| 工具 | 适用场景 |
|------|---------|
| MCP laravel-boost | 快速查询验证 |
| artisan tinker | 复杂SQL操作 |
| MySQL客户端 | 批量修改 |

**示例**：
```bash
# MCP laravel-boost
mcp__laravel-boost__database-query "ALTER TABLE users ADD COLUMN phone VARCHAR(20)"

# artisan tinker
php artisan tinker
>>> DB::statement("ALTER TABLE users ADD COLUMN phone VARCHAR(20) COMMENT '手机号'");
```

---

### 注意事项

**⚠️ 重要提醒**：
1. SQL语法必须与迁移文件一致（字段类型、长度、默认值）
2. 先修改迁移文件，再执行SQL
3. 外键操作需确保被引用表存在
4. 执行后验证结构一致性

**常见错误**：
```
❌ 迁移文件：$table->string('name', 100)
   SQL执行：ALTER TABLE users ADD COLUMN name VARCHAR(50)  # 长度不一致

✅ 正确：完全匹配迁移文件定义
```

---

## 五、迁移执行操作

### 开发阶段命令

| 场景 | 命令 | 说明 |
|------|------|------|
| 执行新迁移 | `php artisan migrate` | ✅ 首选：安全执行 |
| 查看状态 | `php artisan migrate:status` | 查看已执行和待执行 |
| 修改未执行的迁移 | 修改文件 → `migrate` | ✅ 推荐：直接执行 |
| 修改已执行的迁移 | 方案1（手动SQL同步）或方案3（rollback） | ⚠️ 根据场景选择 |
| 完全重置（谨慎） | `php artisan db:wipe --force && php artisan migrate` | ⚠️ 删除所有数据 |

**重要提醒**：
- ✅ 优先使用 `migrate` 执行新迁移
- ⚠️ 谨慎使用 `db:wipe` / `migrate:fresh`
- ❌ 生产环境禁止破坏性命令

---

## 六、迁移依赖检测

### 新迁移检查清单

**步骤1：检查外键依赖**
```bash
grep -r "references.*user_users" Modules/*/Database/Migrations/*.php
```

**步骤2：确定时间戳**
```
新迁移时间戳 > 被依赖表迁移时间戳
```

**步骤3：验证模块依赖顺序**
```
基础模块（User、Account） → 业务模块（Novel、Order） → 扩展模块
```

---

### 迁移失败排查与处理策略（核心）

#### 🚨 处理原则（强制遵守）

**核心铁律**：
1. ❌ **绝不能标记已执行来绕过问题** - 这是掩盖错误，不是解决问题
2. ✅ **必须分析失败根源** - 找出为什么失败，针对性修复
3. ✅ **修复迁移文件本身** - 让迁移文件具备容错能力

---

#### 常见错误类型与正确处理

| 错误类型 | 错误信息示例 | ❌ 错误处理 | ✅ 正确处理 |
|---------|------------|-----------|-----------|
| **字段已存在** | `Duplicate column name 'xxx'` | ❌ 标记迁移为已执行 | ✅ 添加字段存在性判断 |
| **字段不存在** | `Can't DROP 'xxx'; check that column/key exists` | ❌ 跳过该迁移 | ✅ 添加字段存在性判断 |
| **表已存在** | `Table 'xxx' already exists` | ❌ 标记已执行 | ✅ 添加表存在性判断或 db:wipe |
| **外键引用表不存在** | `Failed to open the referenced table 'yyy'` | ❌ 删除外键定义 | ✅ 调整时间戳顺序，确保引用表先创建 |
| **索引已存在** | `Duplicate key name 'idx_xxx'` | ❌ 手动删除索引 | ✅ 添加索引存在性判断 |

---

#### 幂等性改造模板（必须掌握）

**原则**：迁移文件必须具备可重复执行能力（幂等性）

##### 1. 字段添加迁移（必须判断存在）

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // ✅ 正确：先判断字段是否存在
        if (!Schema::hasColumn('users', 'status')) {
            $table->unsignedTinyInteger('status')
                ->default(1)
                ->comment('状态:1正常,2禁用')
                ->after('name');

            $table->index('status', 'idx_status');
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        // ✅ 正确：先判断字段是否存在
        if (Schema::hasColumn('users', 'status')) {
            $table->dropIndex('idx_status');
            $table->dropColumn('status');
        }
    });
}
```

##### 2. 字段删除迁移（必须判断存在）

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // ✅ 正确：先判断字段是否存在
        if (Schema::hasColumn('users', 'temp_field')) {
            $table->dropIndex('idx_temp_field');
            $table->dropColumn('temp_field');
        }
    });
}
```

##### 3. 表创建迁移（必须判断存在）

```php
public function up(): void
{
    // ✅ 正确：先判断表是否存在
    if (!Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('name', 100)->comment('用户名');
            $table->timestamps();
        });
    }
}

public function down(): void
{
    // ✅ 正确：先判断表是否存在
    if (Schema::hasTable('users')) {
        Schema::dropIfExists('users');
    }
}
```

---

#### 迁移失败排查流程图

```
迁移执行失败
   ↓
分析错误信息（ERROR MESSAGE）
   ↓
判断错误类型
   ├─ 字段已存在 → 添加存在性判断 → 重新执行迁移
   ├─ 字段不存在 → 添加存在性判断 → 重新执行迁移
   ├─ 表已存在 → 添加表存在性判断 / db:wipe
   ├─ 外键引用失败 → 调整时间戳顺序 → 重新执行迁移
   └─ 索引冲突 → 添加索引存在性判断 → 重新执行迁移
   ↓
修改迁移文件（修复根源）
   ↓
重新执行迁移
   ↓
验证结果（migrate:status + 数据库查询）
   ↓
问题解决 ✅
```

---

#### 实战案例：字段已存在的正确处理

**错误场景**：
```bash
$ php artisan migrate

SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'user_type'
```

**❌ 错误处理（禁止）**：
```bash
# 错误做法：标记迁移为已执行，绕过问题
php artisan tinker
>>> DB::table('migrations')->insert(['migration' => 'xxx_add_user_type', 'batch' => 11]);
# 这只是掩盖问题，迁移文件本身的缺陷没有修复
```

**✅ 正确处理**：
```php
// 修改迁移文件，添加存在性判断
public function up(): void
{
    Schema::table('user_infos', function (Blueprint $table) {
        // 判断字段是否存在，不存在才添加
        if (!Schema::hasColumn('user_infos', 'user_type')) {
            $table->tinyInteger('user_type')
                ->default(2)
                ->comment('用户类型: 1=平台方, 2=服务人员')
                ->after('merchant_id');
        }
    });
}
```

```bash
# 重新执行迁移
php artisan migrate
# ✅ 迁移成功执行，问题从根本上解决
```

---

#### 验证修复效果

**必须验证**：
```bash
# 1. 检查迁移状态
php artisan migrate:status

# 2. 查询数据库结构
# MCP laravel-boost
mcp__laravel-boost__database-query "SHOW COLUMNS FROM table_name LIKE 'field_name'"

# 3. 验证迁移文件与数据库一致性
# 迁移状态：Ran
# 数据库状态：字段存在/不存在（符合预期）
```

---

---

### 批量调整时间戳脚本

```bash
#!/bin/bash
MODULE_NAME="NovelReader"
OLD_TIMESTAMP="2025_05_01"
NEW_TIMESTAMP="2026_05_02"

for file in Modules/${MODULE_NAME}/Database/Migrations/*.php; do
  old_name=$(basename "$file")
  new_name=$(echo "$old_name" | sed "s/${OLD_TIMESTAMP}/${NEW_TIMESTAMP}/")
  mv "$file" "Modules/${MODULE_NAME}/Database/Migrations/$new_name"
  echo "调整: $old_name -> $new_name"
done
```

---

## 七、最佳实践

### 迁移文件合并原则

**何时合并（开发阶段）**：
| 场景 | 操作 |
|------|------|
| 同一表的多个增量修改 | 合并到原迁移文件 |
| 开发阶段频繁调整字段 | 修改原迁移文件 |
| 发现字段设计错误 | 修改原迁移文件 |
| 修改未执行的迁移 | 修改原迁移文件 |
| 上线后新增字段 | 创建新迁移文件 |

**合并示例**：
```
❌ 不推荐（开发阶段）：
   2026_05_01_100001_create_users_table.php
   2026_05_02_100002_add_email_to_users_table.php
   2026_05_03_100003_add_phone_to_users_table.php

✅ 推荐（开发阶段）：
   修改原迁移文件（2026_05_01_100001_create_users_table.php）
   包含所有字段：email、phone、status
```

---

### 模块间迁移协调

| 依赖类型 | 时间戳策略 |
|---------|-----------|
| User模块 | 所有模块基础，时间戳最早 |
| Account模块 | 依赖User，时间戳次早 |
| 业务模块 | 根据实际依赖确定 |
| 无依赖模块 | 可自由安排 |

**跨模块外键检查**：
```bash
grep -r "->on\('.*'\)" Modules/*/Database/Migrations/*.php
```

---

## 八、Skill协作关系

**处理迁移时必须同时激活的Skills**：

| Skill | 激活时机 | 协作内容 |
|-------|---------|---------|
| **design-database** | ✅ **强制同时激活** | 表结构设计、字段定义、索引规划 |
| dev-model | 迁移执行后 | 创建Eloquent Model |
| dev-seeder | 迁移执行后 | 创建测试数据 |

---

## 九、迁移工作流程总览

```
设计表结构（design-database）
  ↓
创建迁移文件（检查依赖、确定时间戳）
  ↓
执行迁移测试（php artisan migrate）
  ↓
发现问题？
  ├─ YES → 修改原迁移文件
  │        ├─ 方案1：手动SQL同步（最佳）
  │        └─ 方案3：rollback → 修改 → migrate
  │        ↓
  │        验证结果
  └─ NO → 创建Model（dev-model）
          ↓
          创建Seeder（dev-seeder）
          ↓
          完成 ✅
```

---

**关键原则总结**：
1. ✅ Skill协作：创建迁移前必须激活 `design-database`
2. ✅ 开发阶段：直接修改原迁移文件，不创建增量迁移
3. 🌟 最佳方案：修改迁移文件 + 手动SQL同步数据库（保留数据）
4. ✅ 时间戳顺序：被依赖的表必须先创建
5. ✅ 外键检查：确保被引用表时间戳更早
6. 🚨 **迁移失败处理**：❌ 禁止标记已执行绕过问题 | ✅ 必须分析根源修复迁移文件
7. ✅ **幂等性要求**：所有字段添加/删除/表创建必须添加存在性判断
8. ❌ 生产环境：禁止修改已执行的迁移文件

---

**最后更新**：2026-05-05
**版本**：v2.0（精简版）