-- ******************************************************************
-- 表 cleanup_configs 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupConfig
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_configs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `model_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model类名',
  `model_info` json DEFAULT NULL COMMENT 'Model类信息',
  `module_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模块名称',
  `data_category` tinyint(3) unsigned NOT NULL COMMENT '数据分类:1用户数据,2日志数据,3交易数据,4缓存数据,5配置数据',
  `default_cleanup_type` tinyint(3) unsigned NOT NULL COMMENT '默认清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `default_conditions` json DEFAULT NULL COMMENT '默认清理条件JSON配置',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用清理',
  `priority` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '清理优先级(数字越小优先级越高)',
  `batch_size` int(10) unsigned NOT NULL DEFAULT '1000' COMMENT '批处理大小',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '配置描述',
  `last_cleanup_at` timestamp NULL DEFAULT NULL COMMENT '最后清理时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_table_name` (`table_name`) USING BTREE,
  KEY `idx_module_category` (`module_name`,`data_category`) USING BTREE,
  KEY `idx_enabled_priority` (`is_enabled`,`priority`) USING BTREE,
  KEY `idx_last_cleanup` (`last_cleanup_at`) USING BTREE,
  KEY `idx_model_class` (`model_class`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='清理配置表';
