-- ******************************************************************
-- 表 cleanup_backup_files 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupBackupFile
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_backup_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `backup_id` bigint(20) unsigned NOT NULL COMMENT '备份记录ID',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件路径',
  `file_size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
  `file_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件SHA256哈希',
  `backup_type` tinyint(3) unsigned NOT NULL COMMENT '备份类型:1SQL,2JSON,3CSV',
  `compression_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '压缩类型:1none,2gzip,3zip',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_backup_id` (`backup_id`) USING BTREE,
  KEY `idx_table_name` (`table_name`) USING BTREE,
  CONSTRAINT `cleanup_backup_files_ibfk_1` FOREIGN KEY (`backup_id`) REFERENCES `cleanup_backups` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='备份文件表';
