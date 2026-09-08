-- ******************************************************************
-- 表 cleanup_tasks 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupTask
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `task_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务名称',
  `plan_id` bigint(20) unsigned NOT NULL COMMENT '关联的清理计划ID',
  `backup_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联的备份ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '任务状态:1待执行,2备份中,3执行中,4已完成,5已失败,6已取消,7已暂停',
  `progress` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '执行进度百分比',
  `current_step` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '当前执行步骤',
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
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_plan_id` (`plan_id`) USING BTREE,
  KEY `idx_backup_id` (`backup_id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  CONSTRAINT `cleanup_tasks_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `cleanup_plans` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='清理任务表';
