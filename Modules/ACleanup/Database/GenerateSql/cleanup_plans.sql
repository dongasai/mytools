-- ******************************************************************
-- 表 cleanup_plans 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupPlan
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计划名称',
  `selected_tables` json DEFAULT NULL COMMENT '选择的Model类列表，格式：["Modules\\System\\Models\\AdminActionlog"]',
  `global_conditions` json DEFAULT NULL COMMENT '全局清理条件',
  `backup_config` json DEFAULT NULL COMMENT '备份配置',
  `is_template` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为模板',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '计划描述',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_plan_name` (`plan_name`) USING BTREE,
  KEY `idx_is_template` (`is_template`) USING BTREE,
  KEY `idx_is_enabled` (`is_enabled`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='清理计划表';
