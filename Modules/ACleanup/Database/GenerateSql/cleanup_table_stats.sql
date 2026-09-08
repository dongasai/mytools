-- ******************************************************************
-- 表 cleanup_table_stats 的创建SQL
-- 对应的Model: App\Module\Cleanup\Models\CleanupTableStats
-- 警告: 此文件由系统自动生成，禁止修改！
-- ******************************************************************

CREATE TABLE `cleanup_table_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_table_scan` (`table_name`,`scan_time`) USING BTREE,
  KEY `idx_table_name` (`table_name`) USING BTREE,
  KEY `idx_record_count` (`record_count`) USING BTREE,
  KEY `idx_table_size` (`table_size_mb`) USING BTREE,
  KEY `idx_scan_time` (`scan_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='表统计信息表';
