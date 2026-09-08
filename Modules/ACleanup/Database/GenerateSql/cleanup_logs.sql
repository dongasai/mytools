-- ******************************************************************
-- 表 cleanup_logs 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupLog
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `task_id` bigint(20) unsigned NOT NULL COMMENT '任务ID',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `model_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model类名',
  `cleanup_type` tinyint(3) unsigned NOT NULL COMMENT '清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `before_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '清理前记录数',
  `after_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '清理后记录数',
  `deleted_records` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除记录数',
  `execution_time` decimal(8,3) NOT NULL DEFAULT '0.000' COMMENT '执行时间(秒)',
  `conditions` json DEFAULT NULL COMMENT '使用的清理条件',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_task_id` (`task_id`) USING BTREE,
  KEY `idx_table_name` (`table_name`) USING BTREE,
  KEY `idx_cleanup_type` (`cleanup_type`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  KEY `idx_model_class` (`model_class`) USING BTREE,
  CONSTRAINT `cleanup_logs_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `cleanup_tasks` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='清理日志表';
