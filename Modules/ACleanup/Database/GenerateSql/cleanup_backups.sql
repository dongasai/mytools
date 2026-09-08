-- ******************************************************************
-- 表 cleanup_backups 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupBackup
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_backups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_id` bigint(20) unsigned NOT NULL COMMENT '关联的清理计划ID',
  `task_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联的清理任务ID(如果是任务触发的备份)',
  `backup_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备份名称',
  `backup_type` tinyint(3) unsigned NOT NULL COMMENT '备份类型:1SQL,2JSON,3CSV',
  `compression_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '压缩类型:1none,2gzip,3zip',
  `backup_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备份文件路径',
  `backup_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '备份文件大小(字节)',
  `original_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '原始数据大小(字节)',
  `tables_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '备份表数量',
  `records_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '备份记录数量',
  `backup_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '备份状态:1进行中,2已完成,3已失败',
  `backup_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备份文件MD5哈希',
  `backup_config` json DEFAULT NULL COMMENT '备份配置信息',
  `started_at` timestamp NULL DEFAULT NULL COMMENT '备份开始时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '备份完成时间',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '备份过期时间',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_plan_id` (`plan_id`) USING BTREE,
  KEY `idx_task_id` (`task_id`) USING BTREE,
  KEY `idx_backup_status` (`backup_status`) USING BTREE,
  KEY `idx_expires_at` (`expires_at`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  CONSTRAINT `cleanup_backups_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `cleanup_plans` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='备份记录表';
