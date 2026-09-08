-- ******************************************************************
-- 表 cleanup_plan_contents 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupPlanContent
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_plan_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_id` bigint(20) unsigned NOT NULL COMMENT '计划ID',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `model_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model类名',
  `cleanup_type` tinyint(3) unsigned NOT NULL COMMENT '清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `conditions` json DEFAULT NULL COMMENT '清理条件JSON配置',
  `priority` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '清理优先级',
  `batch_size` int(10) unsigned NOT NULL DEFAULT '1000' COMMENT '批处理大小',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `backup_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用备份',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '备注说明',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_plan_table` (`plan_id`,`table_name`) USING BTREE,
  KEY `idx_plan_id` (`plan_id`) USING BTREE,
  KEY `idx_table_name` (`table_name`) USING BTREE,
  KEY `idx_priority` (`priority`) USING BTREE,
  KEY `idx_model_class` (`model_class`) USING BTREE,
  CONSTRAINT `cleanup_plan_contents_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `cleanup_plans` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='计划内容表';
