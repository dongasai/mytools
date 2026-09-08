-- Cleanup 模块数据库表结构
-- 创建时间: 2024-12-16
-- 版本: v1.0.0

-- 1. 清理配置表 (cleanup_configs)
-- 存储每个数据表的基础清理配置信息
CREATE TABLE `cleanup_configs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(100) NOT NULL COMMENT '表名',
  `module_name` varchar(50) NOT NULL COMMENT '模块名称',
  `data_category` tinyint(3) unsigned NOT NULL COMMENT '数据分类:1用户数据,2日志数据,3交易数据,4缓存数据,5配置数据',
  `default_cleanup_type` tinyint(3) unsigned NOT NULL COMMENT '默认清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `default_conditions` json DEFAULT NULL COMMENT '默认清理条件JSON配置',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用清理',
  `priority` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '清理优先级(数字越小优先级越高)',
  `batch_size` int(10) unsigned NOT NULL DEFAULT '1000' COMMENT '批处理大小',
  `description` text COMMENT '配置描述',
  `last_cleanup_at` timestamp NULL DEFAULT NULL COMMENT '最后清理时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_table_name` (`table_name`),
  KEY `idx_module_category` (`module_name`, `data_category`),
  KEY `idx_enabled_priority` (`is_enabled`, `priority`),
  KEY `idx_last_cleanup` (`last_cleanup_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理配置表';

-- 2. 清理计划表 (cleanup_plans)
-- 存储清理计划信息，如"农场模块清理"
CREATE TABLE `cleanup_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_name` varchar(100) NOT NULL COMMENT '计划名称',
  `plan_type` tinyint(3) unsigned NOT NULL COMMENT '计划类型:1全量清理,2模块清理,3分类清理,4自定义清理,5混合清理',
  `target_selection` json DEFAULT NULL COMMENT '目标选择配置',
  `global_conditions` json DEFAULT NULL COMMENT '全局清理条件',
  `backup_config` json DEFAULT NULL COMMENT '备份配置',
  `is_template` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为模板',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `description` text COMMENT '计划描述',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_plan_name` (`plan_name`),
  KEY `idx_plan_type` (`plan_type`),
  KEY `idx_is_template` (`is_template`),
  KEY `idx_is_enabled` (`is_enabled`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理计划表';

-- 3. 计划内容表 (cleanup_plan_contents)
-- 存储计划的具体内容，即计划具体处理哪些表，怎么清理
CREATE TABLE `cleanup_plan_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_id` bigint(20) unsigned NOT NULL COMMENT '计划ID',
  `table_name` varchar(100) NOT NULL COMMENT '表名',
  `cleanup_type` tinyint(3) unsigned NOT NULL COMMENT '清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `conditions` json DEFAULT NULL COMMENT '清理条件JSON配置',
  `priority` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '清理优先级',
  `batch_size` int(10) unsigned NOT NULL DEFAULT '1000' COMMENT '批处理大小',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `backup_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用备份',
  `notes` text COMMENT '备注说明',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_plan_table` (`plan_id`, `table_name`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_priority` (`priority`),
  FOREIGN KEY (`plan_id`) REFERENCES `cleanup_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='计划内容表';

-- 4. 清理任务表 (cleanup_tasks)
-- 存储清理任务的执行信息和状态，即执行某个计划的具体实例
CREATE TABLE `cleanup_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `task_name` varchar(100) NOT NULL COMMENT '任务名称',
  `plan_id` bigint(20) unsigned NOT NULL COMMENT '关联的清理计划ID',
  `backup_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联的备份ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '任务状态:1待执行,2备份中,3执行中,4已完成,5已失败,6已取消,7已暂停',
  `progress` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '执行进度百分比',
  `current_step` varchar(50) DEFAULT NULL COMMENT '当前执行步骤',
  `total_tables` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总表数',
  `processed_tables` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已处理表数',
  `total_records` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总记录数',
  `deleted_records` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已删除记录数',
  `backup_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '备份文件大小(字节)',
  `execution_time` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '执行时间(秒)',
  `backup_time` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '备份时间(秒)',
  `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `backup_completed_at` timestamp NULL DEFAULT NULL COMMENT '备份完成时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `error_message` text COMMENT '错误信息',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_backup_id` (`backup_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`plan_id`) REFERENCES `cleanup_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理任务表';

-- 5. 备份记录表 (cleanup_backups)
-- 存储计划的备份方案和备份内容
CREATE TABLE `cleanup_backups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_id` bigint(20) unsigned NOT NULL COMMENT '关联的清理计划ID',
  `task_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联的清理任务ID(如果是任务触发的备份)',
  `backup_name` varchar(100) NOT NULL COMMENT '备份名称',
  `backup_type` tinyint(3) unsigned NOT NULL COMMENT '备份类型:1SQL,2JSON,3CSV',
  `compression_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '压缩类型:1none,2gzip,3zip',
  `backup_path` varchar(500) NOT NULL COMMENT '备份文件路径',
  `backup_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '备份文件大小(字节)',
  `original_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '原始数据大小(字节)',
  `tables_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '备份表数量',
  `records_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '备份记录数量',
  `backup_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '备份状态:1进行中,2已完成,3已失败',
  `backup_hash` varchar(64) DEFAULT NULL COMMENT '备份文件MD5哈希',
  `backup_config` json DEFAULT NULL COMMENT '备份配置信息',
  `started_at` timestamp NULL DEFAULT NULL COMMENT '备份开始时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '备份完成时间',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '备份过期时间',
  `error_message` text COMMENT '错误信息',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_backup_status` (`backup_status`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`plan_id`) REFERENCES `cleanup_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='备份记录表';

-- 6. 备份文件表 (cleanup_backup_files)
-- 存储备份的具体文件信息
CREATE TABLE `cleanup_backup_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `backup_id` bigint(20) unsigned NOT NULL COMMENT '备份记录ID',
  `table_name` varchar(100) NOT NULL COMMENT '表名',
  `file_path` varchar(500) NOT NULL COMMENT '文件路径',
  `file_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
  `records_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '记录数量',
  `file_hash` varchar(64) DEFAULT NULL COMMENT '文件MD5哈希',
  `backup_conditions` json DEFAULT NULL COMMENT '备份条件',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_backup_id` (`backup_id`),
  KEY `idx_table_name` (`table_name`),
  FOREIGN KEY (`backup_id`) REFERENCES `cleanup_backups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='备份文件表';

-- 7. 清理日志表 (cleanup_logs)
-- 记录任务执行的详细日志
CREATE TABLE `cleanup_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `task_id` bigint(20) unsigned NOT NULL COMMENT '任务ID',
  `table_name` varchar(100) NOT NULL COMMENT '表名',
  `operation_type` varchar(20) NOT NULL COMMENT '操作类型:BACKUP,TRUNCATE,DELETE,COUNT',
  `records_before` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '操作前记录数',
  `records_after` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '操作后记录数',
  `records_affected` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '影响记录数',
  `execution_time` decimal(8,3) NOT NULL DEFAULT '0.000' COMMENT '执行时间(秒)',
  `conditions_used` json DEFAULT NULL COMMENT '使用的清理条件',
  `sql_statement` text COMMENT '执行的SQL语句',
  `status` tinyint(3) unsigned NOT NULL COMMENT '执行状态:1成功,2失败,3跳过',
  `error_message` text COMMENT '错误信息',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_operation_type` (`operation_type`),
  FOREIGN KEY (`task_id`) REFERENCES `cleanup_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理日志表';

-- 8. 表统计信息表 (cleanup_table_stats)
-- 存储数据表的统计信息，用于清理决策
CREATE TABLE `cleanup_table_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(100) NOT NULL COMMENT '表名',
  `record_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '记录总数',
  `table_size_mb` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '表大小(MB)',
  `index_size_mb` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '索引大小(MB)',
  `data_free_mb` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '碎片空间(MB)',
  `avg_row_length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '平均行长度',
  `auto_increment` bigint(20) unsigned DEFAULT NULL COMMENT '自增值',
  `oldest_record_time` timestamp NULL DEFAULT NULL COMMENT '最早记录时间',
  `newest_record_time` timestamp NULL DEFAULT NULL COMMENT '最新记录时间',
  `scan_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '扫描时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_table_scan` (`table_name`, `scan_time`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_record_count` (`record_count`),
  KEY `idx_table_size` (`table_size_mb`),
  KEY `idx_scan_time` (`scan_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='表统计信息表';
