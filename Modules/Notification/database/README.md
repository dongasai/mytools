# 通知模块数据库迁移文件

## 迁移文件说明
本目录包含通知模块所需的所有数据库迁移文件。

## 迁移文件列表

### 1. create_notification_templates_table.php
创建通知模板表

```php
Schema::create('notification_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100)->comment('模板名称');
    $table->string('type', 20)->comment('通知类型');
    $table->string('channel', 20)->comment('通知渠道');
    $table->string('title', 255)->comment('通知标题');
    $table->text('content')->comment('通知内容');
    $table->json('variables')->nullable()->comment('变量定义');
    $table->tinyInteger('status')->default(1)->comment('状态：0禁用 1启用');
    $table->timestamps();
});
```

### 2. create_notifications_table.php
创建通知记录表

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('template_id')->comment('模板ID');
    $table->string('type', 20)->comment('通知类型');
    $table->string('channel', 20)->comment('通知渠道');
    $table->string('title', 255)->comment('通知标题');
    $table->text('content')->comment('通知内容');
    $table->json('data')->nullable()->comment('通知数据');
    $table->tinyInteger('priority')->default(2)->comment('优先级');
    $table->string('status', 20)->default('pending')->comment('状态');
    $table->integer('retry_count')->default(0)->comment('重试次数');
    $table->timestamp('sent_at')->nullable()->comment('发送时间');
    $table->timestamps();
});
```

### 3. create_notification_recipients_table.php
创建通知接收者表

```php
Schema::create('notification_recipients', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('notification_id')->comment('通知ID');
    $table->unsignedBigInteger('user_id')->comment('用户ID');
    $table->timestamp('read_at')->nullable()->comment('阅读时间');
    $table->timestamps();
});
```

## 索引设计

### notification_templates 表索引
- type, channel 联合索引
- status 索引

### notifications 表索引
- template_id 外键索引
- type, channel 联合索引
- status 索引
- priority 索引
- sent_at 索引

### notification_recipients 表索引
- notification_id 外键索引
- user_id 索引
- read_at 索引

## 外键约束

### notifications 表
```php
$table->foreign('template_id')
      ->references('id')
      ->on('notification_templates')
      ->onDelete('cascade');
```

### notification_recipients 表
```php
$table->foreign('notification_id')
      ->references('id')
      ->on('notifications')
      ->onDelete('cascade');
```

## 数据填充
本模块包含以下数据填充文件：

### 1. NotificationTypeSeeder.php
填充通知类型数据

### 2. NotificationChannelSeeder.php
填充通知渠道数据

### 3. NotificationPrioritySeeder.php
填充通知优先级数据

### 4. NotificationStatusSeeder.php
填充通知状态数据

## 使用说明
1. 运行迁移：
```bash
php artisan migrate
```

2. 回滚迁移：
```bash
php artisan migrate:rollback
```

3. 重置迁移：
```bash
php artisan migrate:refresh
```

4. 运行数据填充：
```bash
php artisan db:seed --class=NotificationTypeSeeder
php artisan db:seed --class=NotificationChannelSeeder
php artisan db:seed --class=NotificationPrioritySeeder
php artisan db:seed --class=NotificationStatusSeeder
``` 
