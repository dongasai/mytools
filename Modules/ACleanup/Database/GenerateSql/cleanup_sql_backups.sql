-- ******************************************************************
-- 表 cleanup_sql_backups 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupSqlBackup
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_sql_backups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `backup_id` bigint(20) unsigned NOT NULL COMMENT '备份记录ID',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `sql_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'INSERT语句内容',
  `records_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '记录数量',
  `content_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '内容大小(字节)',
  `content_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '内容SHA256哈希',
  `backup_conditions` json DEFAULT NULL COMMENT '备份条件',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_backup_id` (`backup_id`) USING BTREE,
  KEY `idx_table_name` (`table_name`) USING BTREE,
  KEY `idx_records_count` (`records_count`) USING BTREE,
  KEY `idx_content_size` (`content_size`) USING BTREE,
  CONSTRAINT `cleanup_sql_backups_ibfk_1` FOREIGN KEY (`backup_id`) REFERENCES `cleanup_backups` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='SQL备份表';
