-- 数据库备份
-- 数据库: nengtan_laravel
-- 时间: 2026-07-25 11:56:34

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 表结构: admin_action_logs
DROP TABLE IF EXISTS `admin_action_logs`;
CREATE TABLE `admin_action_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作类型',
  `unid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一标识符',
  `admin_id` bigint unsigned NOT NULL COMMENT '操作的Admin ID',
  `object_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作对象类名',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作URL',
  `before` json DEFAULT NULL COMMENT '操作之前的数据 (JSON格式)',
  `after` json DEFAULT NULL COMMENT '操作之后的数据 (JSON格式)',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态：1-成功，0-失败',
  `p1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '参数1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_action_logs_unid_unique` (`unid`),
  KEY `admin_action_logs_admin_id_index` (`admin_id`),
  KEY `admin_action_logs_type1_index` (`type1`),
  KEY `admin_action_logs_unid_index` (`unid`),
  KEY `admin_action_logs_object_class_index` (`object_class`),
  KEY `admin_action_logs_status_index` (`status`),
  KEY `admin_action_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_extension_histories
DROP TABLE IF EXISTS `admin_extension_histories`;
CREATE TABLE `admin_extension_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint NOT NULL DEFAULT '1',
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `detail` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_extension_histories_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_extensions
DROP TABLE IF EXISTS `admin_extensions`;
CREATE TABLE `admin_extensions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_enabled` tinyint NOT NULL DEFAULT '0',
  `options` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_extensions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_grid_views
DROP TABLE IF EXISTS `admin_grid_views`;
CREATE TABLE `admin_grid_views` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint unsigned NOT NULL COMMENT '操作的Admin ID',
  `type1` enum('private','public') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private' COMMENT '视图类型：私有/公共',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '视图标题',
  `router_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '路由名字',
  `p1` json DEFAULT NULL COMMENT '参数1 (JSON格式)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_menu
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uri` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40707 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: admin_menu (119 行)
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('1', '0', '1', 'Index', 'feather icon-bar-chart-2', '/', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('2', '0', '2', 'Admin', 'feather icon-settings', '', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('3', '2', '3', 'Users', '', 'auth/users', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('4', '2', '4', 'Roles', '', 'auth/roles', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('5', '2', '5', 'Permission', '', 'auth/permissions', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('6', '2', '6', 'Menu', '', 'auth/menu', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('7', '2', '7', 'Extensions', '', 'auth/extensions', '', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('10001', '0', '10001', 'Admin模块', 'feather icon-settings', '', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('10003', '10001', '10003', '图表演示', 'feather icon-bar-chart-2', 'module_dcatadmin/metrics', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('10004', '10001', '10004', '高级图表', 'feather icon-activity', 'module_dcatadmin/metrics2', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('10005', '10001', '10005', '路由查看', 'feather icon-navigation', 'module_dcatadmin/routers', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('10006', '10001', '10006', '配置查看', 'feather icon-settings', 'module_dcatadmin/config', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('10007', '10001', '10007', '菜单同步', 'feather icon-refresh-cw', 'module_dcatadmin/menu-sync', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('12001', '0', '12001', 'DEMO5', 'feather icon-layers', '', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('12002', '12001', '12002', '仪表盘', 'feather icon-home', 'module_demo5/dashboard', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('12003', '12001', '12003', '文章管理', 'feather icon-file-text', 'module_demo5/posts', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('12004', '12001', '12004', '评论管理', 'feather icon-message-circle', 'module_demo5/comments', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13001', '2', '13001', '数据清理管理', 'feather icon-trash-2', '', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13002', '13001', '13002', '清理配置', 'feather icon-settings', 'cleanup-admin/configs', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13003', '13001', '13003', '清理计划', 'feather icon-calendar', 'cleanup-admin/plans', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13004', '13001', '13004', '清理任务', 'feather icon-play-circle', 'cleanup-admin/tasks', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13005', '13001', '13005', '备份管理', 'feather icon-hard-drive', 'cleanup-admin/backups', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13006', '13001', '13006', '清理日志', 'feather icon-file-text', 'cleanup-admin/logs', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('13007', '13001', '13007', '统计信息', 'feather icon-bar-chart-2', 'cleanup-admin/stats', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15001', '0', '15001', '应用管理', 'feather icon-settings', '', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15002', '15001', '15002', '系统配置', 'feather icon-sliders', 'module_application/config', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15003', '15001', '15003', '配置管理', 'feather icon-edit-3', 'module_application/config-admin', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15011', '15001', '15011', '日志管理', 'feather icon-file-text', '', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15012', '15011', '15012', '系统日志', 'feather icon-activity', 'module_application/system-log', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15013', '15011', '15013', '操作日志', 'feather icon-edit', 'module_application/action-log', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15021', '15001', '15021', '任务管理', 'feather icon-cpu', '', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15022', '15021', '15022', '队列任务', 'feather icon-list', 'module_application/jobs', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15023', '15021', '15023', '失败任务', 'feather icon-x-circle', 'module_application/failed-jobs', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15024', '15021', '15024', '任务批次', 'feather icon-package', 'module_application/job-batches', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15025', '15021', '15025', '任务运行', 'feather icon-play', 'module_application/job-runs', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15031', '15001', '15031', '系统工具', 'feather icon-tool', '', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15032', '15031', '15032', '管理视图', 'feather icon-eye', 'module_application/admin-views', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15041', '15001', '15041', '功能开关', 'feather icon-toggle-left', '', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15042', '15041', '15042', '功能管理', '', 'module_application/features', '', '1', '2026-07-16 03:29:56', '2026-07-16 03:29:56');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15051', '15001', '15051', '字典管理', 'feather icon-book', '', '', '1', '2026-07-24 21:47:58', '2026-07-24 21:47:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('15052', '15051', '15052', '字典数据', '', 'module_application/dict', '', '1', '2026-07-24 21:47:58', '2026-07-24 21:47:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17001', '0', '17001', '商户管理', 'feather icon-shopping-bag', '', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17002', '17001', '17002', '仪表盘', 'feather icon-home', 'module_merchant2/merchants/dashboard', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17003', '17001', '17003', '商户管理', 'feather icon-building', '', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17004', '17003', '17004', '商户列表', 'feather icon-list', 'module_merchant2/merchants', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17005', '17003', '17005', '创建商户', 'feather icon-plus', 'module_merchant2/merchants/create', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17006', '17001', '17006', '用户管理', 'feather icon-users', '', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17007', '17006', '17007', '用户列表', 'feather icon-list', 'module_merchant2/merchant-users', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17008', '17006', '17008', '创建用户', 'feather icon-user-plus', 'module_merchant2/merchant-users/create', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17009', '17006', '17009', '用户统计', 'feather icon-bar-chart-2', 'module_merchant2/merchant-users/dashboard', '', '1', '2026-07-16 03:29:59', '2026-07-16 03:29:59');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17010', '17001', '17010', '统计分析', 'feather icon-bar-chart', '', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17011', '17010', '17011', '商户统计', 'feather icon-pie-chart', 'module_merchant2/merchants/statistics', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17012', '17010', '17012', '用户统计', 'feather icon-trending-up', 'module_merchant2/merchant-users/statistics', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17013', '17001', '17013', '角色管理', 'feather icon-shield', '', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17014', '17013', '17014', '角色列表', 'feather icon-list', 'module_merchant2/roles', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17015', '17001', '17015', '权限管理', 'feather icon-lock', '', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17016', '17015', '17016', '权限列表', 'feather icon-list', 'module_merchant2/permissions', '', '1', '2026-07-16 03:30:00', '2026-07-16 03:30:00');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17017', '17001', '17017', '菜单管理', 'feather icon-menu', '', '', '1', '2026-07-16 03:30:01', '2026-07-16 03:30:01');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17018', '17017', '17018', '菜单列表', 'feather icon-list', 'module_merchant2/menus', '', '1', '2026-07-16 03:30:01', '2026-07-16 03:30:01');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17020', '0', '4', '套餐管理', 'feather icon-layers', '', 'merchant2', '1', NULL, NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17021', '17020', '1', '套餐列表', '', 'module_merchant2/plans', 'merchant2', '1', NULL, NULL);
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17022', '17020', '3', '过期日志', '', '', 'merchant2', '0', NULL, '2026-07-24 00:54:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17023', '17020', '2', '订阅管理', 'feather icon-clipboard', '', '', '1', '2026-07-24 00:54:32', '2026-07-24 00:54:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('17024', '17023', '0', '订阅记录', '', 'module_merchant2/merchant-plans', '', '1', '2026-07-24 00:54:32', '2026-07-24 00:54:32');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20001', '0', '20001', '积分管理', 'fa-bullseye', '', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20002', '20001', '20002', '仪表盘', 'feather icon-home', 'module_point/dashboard', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20003', '20001', '20003', '积分管理', 'feather icon-target', 'module_point/points', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20004', '20001', '20004', '积分配置', 'feather icon-settings', 'module_point/configs', '', '1', '2026-07-16 03:30:05', '2026-07-16 03:30:05');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20005', '20001', '20005', '积分货币', 'feather icon-dollar-sign', 'module_point/currencies', '', '1', '2026-07-16 03:30:05', '2026-07-16 03:30:05');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20006', '20001', '20006', '积分日志', 'feather icon-file-text', 'module_point/logs', '', '1', '2026-07-16 03:30:05', '2026-07-16 03:30:05');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20007', '20001', '20007', '积分流转', 'feather icon-refresh-cw', 'module_point/circulations', '', '1', '2026-07-16 03:30:05', '2026-07-16 03:30:05');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20008', '20001', '20008', '积分转账', 'feather icon-arrow-right', 'module_point/transfers', '', '1', '2026-07-16 03:30:05', '2026-07-16 03:30:05');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('20009', '20001', '20009', '积分订单', 'feather icon-shopping-bag', 'module_point/orders', '', '1', '2026-07-16 03:30:05', '2026-07-16 03:30:05');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('27001', '0', '27001', 'CMS管理', 'feather icon-book', '', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('27002', '27001', '27002', '文章管理', '', 'cms/articles', '', '1', '2026-07-16 03:29:57', '2026-07-16 03:29:57');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('27003', '27001', '27003', '分类管理', '', 'cms/categories', '', '1', '2026-07-16 03:29:58', '2026-07-16 03:29:58');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('31001', '0', '31001', '文件管理', 'feather icon-folder', '', '', '1', '2026-07-16 03:29:54', '2026-07-16 03:29:54');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('31002', '31001', '31002', '文件列表', '', 'module_afile/files', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('31003', '31001', '31003', '图片管理', '', 'module_afile/images', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('31004', '31001', '31004', '存储配置', '', 'module_afile/storage-configs', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('31005', '31001', '31005', '文件模板', '', 'module_afile/file-templates', '', '1', '2026-07-16 03:29:55', '2026-07-16 03:29:55');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('33001', '0', '33001', '通知管理', 'feather icon-bell', '', '', '1', '2026-07-16 03:30:01', '2026-07-16 03:30:01');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('33002', '33001', '33002', '通知日志', 'feather icon-file-text', 'module_notification/notification-logs', '', '1', '2026-07-16 03:30:01', '2026-07-16 03:30:01');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('33003', '33001', '33003', '通知模板', 'feather icon-file-plus', 'module_notification/notification-templates', '', '1', '2026-07-16 03:30:01', '2026-07-16 03:30:01');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40001', '0', '40001', '能源管理', 'feather icon-zap', '', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40002', '40001', '40002', '能源分类', '', 'module_ntenergy/categories', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40003', '40001', '40003', '能源数据', '', 'module_ntenergy/energy-data', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40004', '40001', '40004', '峰谷方案', '', 'module_ntenergy/peak-valley-schemes', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40005', '40001', '40005', '电价方案', '', 'module_ntenergy/tariffs', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40006', '40001', '40006', '公式管理', '', 'module_ntenergy/formulas', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40101', '0', '40101', '碳排放管理', 'feather icon-cloud', '', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40102', '40101', '40102', '碳排放因子', '', 'module_ntcarbon/factor', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40103', '40101', '40103', '碳排放记录', '', 'module_ntcarbon/emission', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40104', '40101', '40104', '碳足迹', '', 'module_ntcarbon/footprint', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40105', '40101', '40105', '碳配额', '', 'module_ntcarbon/quota', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40106', '40101', '40106', '碳交易', '', 'module_ntcarbon/trade', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40107', '40101', '40107', '碳报告', '', 'module_ntcarbon/report', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40201', '0', '40201', '原辅材料管理', 'feather icon-package', '', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40202', '40201', '40202', '排放因子', '', 'module_ntmaterial/factors', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40203', '40201', '40203', '原辅料记录', '', 'module_ntmaterial/records', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40204', '40201', '40204', '月度汇总', '', 'module_ntmaterial/monthlies', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40301', '0', '40301', '生产数据管理', 'feather icon-layers', '', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40302', '40301', '40302', '产品数据', '', 'module_ntproduction/product-data', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40303', '40301', '40303', 'CO3分解数据', '', 'module_ntproduction/co3-decomposition', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40304', '40301', '40304', '工序替煤量', '', 'module_ntproduction/coal-replace', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40401', '0', '40401', '排放物管理', 'feather icon-wind', '', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40402', '40401', '40402', '排放源', '', 'module_ntemission/source', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40403', '40401', '40403', '排放记录', '', 'module_ntemission/record', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40404', '40401', '40404', '排放汇总', '', 'module_ntemission/summary', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40501', '0', '40501', '数据分析', 'feather icon-trending-up', '', '', '1', '2026-07-16 03:30:01', '2026-07-16 03:30:01');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40502', '40501', '40502', '分析配置', '', 'module_ntanalysis/configs', '', '1', '2026-07-16 03:30:02', '2026-07-16 03:30:02');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40601', '0', '40601', '数据大屏', 'feather icon-monitor', '', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40602', '40601', '40602', '大屏配置', '', 'module_ntdashboard/config', '', '1', '2026-07-16 03:30:03', '2026-07-16 03:30:03');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40701', '0', '40701', '报表统计', 'feather icon-file-text', '', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40702', '40701', '40702', '报表模板', '', 'module_ntreport/template', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40703', '40701', '40703', '报表任务', '', 'module_ntreport/task', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40704', '40701', '40704', '报表文件', '', 'module_ntreport/file', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40705', '40701', '40705', '统计指标', '', 'module_ntreport/indicator', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`) VALUES ('40706', '40701', '40706', '统计配置', '', 'module_ntreport/config', '', '1', '2026-07-16 03:30:04', '2026-07-16 03:30:04');

-- 表结构: admin_permission_menu
DROP TABLE IF EXISTS `admin_permission_menu`;
CREATE TABLE `admin_permission_menu` (
  `permission_id` bigint NOT NULL,
  `menu_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_permission_menu_permission_id_menu_id_unique` (`permission_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_permissions
DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_path` text COLLATE utf8mb4_unicode_ci,
  `order` int NOT NULL DEFAULT '0',
  `parent_id` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_permissions_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: admin_permissions (6 行)
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `order`, `parent_id`, `created_at`, `updated_at`) VALUES ('1', 'Auth management', 'auth-management', '', '', '1', '0', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `order`, `parent_id`, `created_at`, `updated_at`) VALUES ('2', 'Users', 'users', '', '/auth/users*', '2', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `order`, `parent_id`, `created_at`, `updated_at`) VALUES ('3', 'Roles', 'roles', '', '/auth/roles*', '3', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `order`, `parent_id`, `created_at`, `updated_at`) VALUES ('4', 'Permissions', 'permissions', '', '/auth/permissions*', '4', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `order`, `parent_id`, `created_at`, `updated_at`) VALUES ('5', 'Menu', 'menu', '', '/auth/menu*', '5', '1', '2026-07-16 03:29:50', NULL);
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `order`, `parent_id`, `created_at`, `updated_at`) VALUES ('6', 'Extension', 'extension', '', '/auth/extensions*', '6', '1', '2026-07-16 03:29:50', NULL);

-- 表结构: admin_role_menu
DROP TABLE IF EXISTS `admin_role_menu`;
CREATE TABLE `admin_role_menu` (
  `role_id` bigint NOT NULL,
  `menu_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_menu_role_id_menu_id_unique` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_role_permissions
DROP TABLE IF EXISTS `admin_role_permissions`;
CREATE TABLE `admin_role_permissions` (
  `role_id` bigint NOT NULL,
  `permission_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_role_users
DROP TABLE IF EXISTS `admin_role_users`;
CREATE TABLE `admin_role_users` (
  `role_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_users_role_id_user_id_unique` (`role_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: admin_role_users (1 行)
INSERT INTO `admin_role_users` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '1', '2026-07-16 03:29:51', '2026-07-16 03:29:51');

-- 表结构: admin_roles
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: admin_roles (1 行)
INSERT INTO `admin_roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES ('1', 'Administrator', 'administrator', '2026-07-16 03:29:50', '2026-07-16 03:29:51');

-- 表结构: admin_settings
DROP TABLE IF EXISTS `admin_settings`;
CREATE TABLE `admin_settings` (
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: admin_users
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: admin_users (1 行)
INSERT INTO `admin_users` (`id`, `username`, `password`, `name`, `avatar`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'admin', '$2y$12$KGU0riKR9Hz6FiFJWtNvVeL5UCbVScboiVrZqrV6iQoRLjZDUufku', 'Administrator', NULL, NULL, '2026-07-16 03:29:50', '2026-07-16 03:29:50');

-- 表结构: ai_conversations
DROP TABLE IF EXISTS `ai_conversations`;
CREATE TABLE `ai_conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `provider_id` bigint unsigned NOT NULL COMMENT '提供商ID',
  `model_id` bigint unsigned NOT NULL COMMENT '模型ID',
  `user_id` bigint unsigned DEFAULT NULL COMMENT '用户ID(后台测试时可为NULL)',
  `conversation_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对话会话ID(多轮对话标识)',
  `prompt_text` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户输入的提示文本',
  `response_text` text COLLATE utf8mb4_unicode_ci COMMENT 'AI返回的响应文本',
  `input_tokens` int unsigned NOT NULL DEFAULT '0' COMMENT '输入tokens数量',
  `output_tokens` int unsigned NOT NULL DEFAULT '0' COMMENT '输出tokens数量',
  `total_cost` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '总成本(美元)',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:1进行中,2已完成,3失败',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT '错误信息(失败时记录)',
  `response_time_ms` int unsigned NOT NULL DEFAULT '0' COMMENT '响应时间(毫秒)',
  `context_json` json DEFAULT NULL COMMENT '对话上下文(JSON格式)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_model_id` (`model_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_conversation_id` (`conversation_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI对话记录表';

-- 表结构: ai_images
DROP TABLE IF EXISTS `ai_images`;
CREATE TABLE `ai_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `provider_id` bigint unsigned NOT NULL COMMENT '提供商ID',
  `model_id` bigint unsigned NOT NULL COMMENT '模型ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `prompt_text` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片生成提示文本',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '生成的图片URL',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片存储路径(本地或云端)',
  `image_size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片尺寸(如1024x1024)',
  `cost` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '生成成本(美元)',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:1待处理,2生成中,3成功,4失败',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `retry_count` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_model_id` (`model_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI图片生成记录表';

-- 表结构: ai_provider_models
DROP TABLE IF EXISTS `ai_provider_models`;
CREATE TABLE `ai_provider_models` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `provider_id` bigint unsigned NOT NULL COMMENT '提供商ID(关联ai_providers.id)',
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模型名称:gpt-4/claude-3-opus等',
  `model_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模型类型:chat/image/embedding',
  `max_tokens` int unsigned NOT NULL DEFAULT '4096' COMMENT '最大tokens限制',
  `cost_per_input_token` decimal(10,8) NOT NULL DEFAULT '0.00000000' COMMENT '输入tokens单价(美元)',
  `cost_per_output_token` decimal(10,8) NOT NULL DEFAULT '0.00000000' COMMENT '输出tokens单价(美元)',
  `is_active` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否启用:1启用,2禁用',
  `config_json` json DEFAULT NULL COMMENT '模型特定配置(JSON格式)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_model_name` (`model_name`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI模型配置表';

-- 表结构: ai_providers
DROP TABLE IF EXISTS `ai_providers`;
CREATE TABLE `ai_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `provider_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提供商类型:openai/claude/gemini/custom',
  `provider_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提供商名称',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'API密钥(加密存储)',
  `api_endpoint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'API端点URL',
  `is_active` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否启用:1启用,2禁用',
  `priority` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '优先级:数字越大优先级越高',
  `config_json` json DEFAULT NULL COMMENT '其他配置参数(JSON格式)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_provider_type` (`provider_type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI服务提供商配置表';

-- 表结构: ai_test_results
DROP TABLE IF EXISTS `ai_test_results`;
CREATE TABLE `ai_test_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `test_id` bigint unsigned NOT NULL COMMENT '测试记录ID',
  `test_sequence` int unsigned NOT NULL DEFAULT '1' COMMENT '测试序号',
  `is_success` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否成功:1成功,2失败',
  `response_time_ms` int unsigned NOT NULL DEFAULT '0' COMMENT '响应时间(毫秒)',
  `input_tokens` int unsigned NOT NULL DEFAULT '0' COMMENT '输入tokens',
  `output_tokens` int unsigned NOT NULL DEFAULT '0' COMMENT '输出tokens',
  `cost` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '成本(美元)',
  `response_text` text COLLATE utf8mb4_unicode_ci COMMENT '响应文本',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_test_id` (`test_id`),
  KEY `idx_is_success` (`is_success`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI测试结果详情表';

-- 表结构: ai_tests
DROP TABLE IF EXISTS `ai_tests`;
CREATE TABLE `ai_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `provider_id` bigint unsigned NOT NULL COMMENT '提供商ID',
  `model_id` bigint unsigned NOT NULL COMMENT '模型ID',
  `test_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '测试类型:connect/response/cost/image',
  `test_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '测试名称',
  `test_config_json` json DEFAULT NULL COMMENT '测试配置(JSON格式)',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:1待执行,2执行中,3成功,4失败',
  `total_tests` int unsigned NOT NULL DEFAULT '1' COMMENT '总测试次数',
  `success_count` int unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
  `fail_count` int unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `avg_response_time_ms` int unsigned NOT NULL DEFAULT '0' COMMENT '平均响应时间(毫秒)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_model_id` (`model_id`),
  KEY `idx_test_type` (`test_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI集成测试记录表';

-- 表结构: application_configs
DROP TABLE IF EXISTS `application_configs`;
CREATE TABLE `application_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `keyname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'key',
  `is_client` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '是否给客户端',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `type` tinyint unsigned NOT NULL COMMENT '类型 详情见枚举',
  `value` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '值',
  `group` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组',
  `group2` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '默认' COMMENT '分组2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `desc` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `options` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '其他配置',
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_configs_keyname_unique` (`keyname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: application_continuous_times
DROP TABLE IF EXISTS `application_continuous_times`;
CREATE TABLE `application_continuous_times` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL COMMENT '用户id',
  `stype` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '产品类型',
  `sid` bigint DEFAULT NULL COMMENT '产品id',
  `number` bigint DEFAULT NULL COMMENT '计数',
  `last_time` bigint DEFAULT NULL COMMENT '最后的时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `diff` int DEFAULT NULL COMMENT '差值',
  PRIMARY KEY (`id`),
  KEY `application_continuous_times_user_id_index` (`user_id`),
  KEY `application_continuous_times_stype_sid_index` (`stype`,`sid`),
  KEY `application_continuous_times_last_time_index` (`last_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 表结构: application_dict
DROP TABLE IF EXISTS `application_dict`;
CREATE TABLE `application_dict` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '字典ID',
  `dict_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字典类型',
  `dict_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字典标签(显示文本)',
  `dict_value` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字典值',
  `dict_sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态(1=启用 0=禁用)',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_value` (`dict_type`,`dict_value`),
  KEY `idx_dict_type` (`dict_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通用字典表';

-- 表数据: application_dict (21 行)
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('1', 'gender', '男', '1', '0', '1', '男性', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('2', 'gender', '女', '2', '1', '1', '女性', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('3', 'user_status', '在职', '1', '0', '1', '正常在职', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('4', 'user_status', '待培训', '2', '1', '1', '待培训', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('5', 'user_status', '待上岗', '3', '2', '1', '待上岗', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('6', 'user_status', '已离职', '4', '3', '1', '已离职', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('7', 'user_status', '已退休', '5', '4', '1', '已退休', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('8', 'enable_status', '启用', '1', '0', '1', '通用启用', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('9', 'enable_status', '禁用', '0', '1', '1', '通用禁用', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('10', 'yes_no', '是', '1', '0', '1', '通用是', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('11', 'yes_no', '否', '0', '1', '1', '通用否', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('12', 'sms_scene', '注册', '102', '0', '1', '短信注册场景', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('13', 'sms_scene', '找回密码', '103', '1', '1', '短信找回密码场景', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('14', 'company_energy_classify', '电力', 'electricity', '0', '1', '电力能源', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('15', 'company_energy_classify', '天然气', 'natural_gas', '1', '1', '天然气能源', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('16', 'company_energy_classify', '蒸汽', 'steam', '2', '1', '蒸汽能源', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('17', 'company_energy_classify', '其他', 'other', '3', '1', '其他能源', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('18', 'steam_pres', '低压(≤1.0MPa)', 'low', '0', '1', '低压蒸汽', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('19', 'steam_pres', '中压(1.0-4.0MPa)', 'medium', '1', '1', '中压蒸汽', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('20', 'steam_pres', '高压(>4.0MPa)', 'high', '2', '1', '高压蒸汽', '2026-07-24 21:42:06', '2026-07-24 21:42:06');
INSERT INTO `application_dict` (`id`, `dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES ('21', 'test_type', '测试项', 'test_value', '0', '1', NULL, '2026-07-24 21:47:21', '2026-07-24 21:47:21');

-- 表结构: application_system_logs
DROP TABLE IF EXISTS `application_system_logs`;
CREATE TABLE `application_system_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `level1` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '来源类型（fund, item, farm等）',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日志消息内容',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间（兼容字段，等同于collected_at）',
  `data1` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据',
  PRIMARY KEY (`id`),
  KEY `application_system_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: article_cates
DROP TABLE IF EXISTS `article_cates`;
CREATE TABLE `article_cates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `pid` bigint unsigned NOT NULL DEFAULT '0' COMMENT '父级分类ID',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类标题',
  `unid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分类标识',
  `img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分类首图',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0隐藏,1显示',
  `order` int unsigned NOT NULL DEFAULT '0' COMMENT '排序顺序',
  `desc1` text COLLATE utf8mb4_unicode_ci COMMENT '分类描述',
  `can_delete` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否可删除:0不可删,1可删',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_cates_unid_unique` (`unid`),
  KEY `idx_parent_status` (`pid`,`status`),
  KEY `article_cates_title_index` (`title`),
  KEY `article_cates_unid_index` (`unid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CMS文章分类表';

-- 表结构: articles
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章标题',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文章简述',
  `category_id` bigint unsigned NOT NULL COMMENT '分类ID',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `views_count` int unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0隐藏,1显示',
  `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '排序权重',
  `is_top` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶:0否,1是',
  `is_recommend` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐:0否,1是',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT '文章详情内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_creator` (`created_by`),
  KEY `idx_display_order` (`status`,`is_top`,`sort_order`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `articles_views_count_index` (`views_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CMS文章内容表';

-- 表结构: benchmark
DROP TABLE IF EXISTS `benchmark`;
CREATE TABLE `benchmark` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `benchmark_id` bigint NOT NULL COMMENT '标杆ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标杆名称',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID',
  `benchmark_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型:unit_product=单位产品,area=单位面积,process=工序',
  `benchmark_value` decimal(16,4) NOT NULL COMMENT '标杆值',
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单位',
  `standard_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标准编号（如GB/T 12345）',
  `valid_from` date NOT NULL COMMENT '生效开始',
  `valid_to` date DEFAULT NULL COMMENT '生效结束（NULL=长期有效）',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_bench_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标杆值表';

-- 表结构: cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: cache (6 行)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('b0dfd9dca3358a4aa7dea0a8c2c7e46a70c98fe5', 'i:4;', '1784904081');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('b0dfd9dca3358a4aa7dea0a8c2c7e46a70c98fe5:timer', 'i:1784904081;', '1784904081');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('file_storage_config:all:local', 'O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:38:\"Modules\\AFile\\Models\\FileStorageConfig\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:20:\"file_storage_configs\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:1;s:4:\"name\";s:5:\"local\";s:6:\"driver\";s:5:\"local\";s:6:\"config\";s:79:\"{\"root\":\"\\/data\\/project\\/tanneng\\/nengtan_laravel\\/storage\\/app\",\"throw\":true}\";s:11:\"description\";s:55:\"本地文件存储，文件保存在 storage/app 目录\";s:10:\"is_default\";i:1;s:7:\"is_temp\";i:1;s:6:\"status\";i:1;s:3:\"env\";s:5:\"local\";s:10:\"created_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"updated_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"created_by\";i:1;s:10:\"updated_by\";i:1;}s:11:\"\0*\0original\";a:13:{s:2:\"id\";i:1;s:4:\"name\";s:5:\"local\";s:6:\"driver\";s:5:\"local\";s:6:\"config\";s:79:\"{\"root\":\"\\/data\\/project\\/tanneng\\/nengtan_laravel\\/storage\\/app\",\"throw\":true}\";s:11:\"description\";s:55:\"本地文件存储，文件保存在 storage/app 目录\";s:10:\"is_default\";i:1;s:7:\"is_temp\";i:1;s:6:\"status\";i:1;s:3:\"env\";s:5:\"local\";s:10:\"created_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"updated_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"created_by\";i:1;s:10:\"updated_by\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"config\";s:5:\"array\";s:10:\"is_default\";s:7:\"boolean\";s:7:\"is_temp\";s:7:\"boolean\";s:6:\"status\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:10:{i:0;s:4:\"name\";i:1;s:6:\"driver\";i:2;s:6:\"config\";i:3;s:11:\"description\";i:4;s:10:\"is_default\";i:5;s:7:\"is_temp\";i:6;s:6:\"status\";i:7;s:3:\"env\";i:8;s:10:\"created_by\";i:9;s:10:\"updated_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', '1784953953');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('file_storage_config:default:local', 'O:38:\"Modules\\AFile\\Models\\FileStorageConfig\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:20:\"file_storage_configs\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:1;s:4:\"name\";s:5:\"local\";s:6:\"driver\";s:5:\"local\";s:6:\"config\";s:79:\"{\"root\":\"\\/data\\/project\\/tanneng\\/nengtan_laravel\\/storage\\/app\",\"throw\":true}\";s:11:\"description\";s:55:\"本地文件存储，文件保存在 storage/app 目录\";s:10:\"is_default\";i:1;s:7:\"is_temp\";i:1;s:6:\"status\";i:1;s:3:\"env\";s:5:\"local\";s:10:\"created_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"updated_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"created_by\";i:1;s:10:\"updated_by\";i:1;}s:11:\"\0*\0original\";a:13:{s:2:\"id\";i:1;s:4:\"name\";s:5:\"local\";s:6:\"driver\";s:5:\"local\";s:6:\"config\";s:79:\"{\"root\":\"\\/data\\/project\\/tanneng\\/nengtan_laravel\\/storage\\/app\",\"throw\":true}\";s:11:\"description\";s:55:\"本地文件存储，文件保存在 storage/app 目录\";s:10:\"is_default\";i:1;s:7:\"is_temp\";i:1;s:6:\"status\";i:1;s:3:\"env\";s:5:\"local\";s:10:\"created_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"updated_at\";s:19:\"2026-07-16 03:28:57\";s:10:\"created_by\";i:1;s:10:\"updated_by\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"config\";s:5:\"array\";s:10:\"is_default\";s:7:\"boolean\";s:7:\"is_temp\";s:7:\"boolean\";s:6:\"status\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:10:{i:0;s:4:\"name\";i:1;s:6:\"driver\";i:2;s:6:\"config\";i:3;s:11:\"description\";i:4;s:10:\"is_default\";i:5;s:7:\"is_temp\";i:6;s:6:\"status\";i:7;s:3:\"env\";i:8;s:10:\"created_by\";i:9;s:10:\"updated_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}', '1784953953');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('token_33d45d07caf4cb5b86d2bc1dbed7fde7', 'a:1:{s:10:\"token_data\";a:7:{s:16:\"merchant_user_id\";N;s:9:\"device_id\";s:0:\"\";s:11:\"client_type\";s:0:\"\";s:14:\"client_version\";s:0:\"\";s:10:\"ip_address\";s:13:\"192.168.4.107\";s:10:\"user_agent\";s:10:\"curl/8.5.0\";s:10:\"created_at\";i:1784903938;}}', '1784990338');
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('token_5a51c20460ed0e24ba35ccb4aadc0771', 'a:1:{s:10:\"token_data\";a:7:{s:16:\"merchant_user_id\";N;s:9:\"device_id\";s:0:\"\";s:11:\"client_type\";s:0:\"\";s:14:\"client_version\";s:0:\"\";s:10:\"ip_address\";s:13:\"192.168.4.107\";s:10:\"user_agent\";s:10:\"curl/8.5.0\";s:10:\"created_at\";i:1784904038;}}', '1784990438');

-- 表结构: cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: cleanup_backup_files
DROP TABLE IF EXISTS `cleanup_backup_files`;
CREATE TABLE `cleanup_backup_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `backup_id` bigint unsigned NOT NULL COMMENT '备份记录ID',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名',
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件路径',
  `file_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件SHA256哈希',
  `backup_type` tinyint unsigned NOT NULL COMMENT '备份类型:1SQL,2JSON,3CSV',
  `compression_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '压缩类型:1none,2gzip,3zip',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `cleanup_backup_files_backup_id_index` (`backup_id`),
  KEY `cleanup_backup_files_table_name_index` (`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='备份文件表';

-- 表结构: cleanup_backups
DROP TABLE IF EXISTS `cleanup_backups`;
CREATE TABLE `cleanup_backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_id` bigint unsigned NOT NULL COMMENT '关联的清理计划ID',
  `task_id` bigint unsigned DEFAULT NULL COMMENT '关联的清理任务ID(如果是任务触发的备份)',
  `backup_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备份名称',
  `backup_type` tinyint unsigned NOT NULL COMMENT '备份类型:1SQL,2JSON,3CSV',
  `compression_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '压缩类型:1none,2gzip,3zip',
  `backup_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备份文件路径',
  `backup_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '备份文件大小(字节)',
  `original_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '原始数据大小(字节)',
  `tables_count` int unsigned NOT NULL DEFAULT '0' COMMENT '备份表数量',
  `records_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '备份记录数量',
  `backup_status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '备份状态:1进行中,2已完成,3已失败',
  `backup_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备份文件MD5哈希',
  `backup_config` json DEFAULT NULL COMMENT '备份配置信息',
  `started_at` timestamp NULL DEFAULT NULL COMMENT '备份开始时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '备份完成时间',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '备份过期时间',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cleanup_backups_plan_id_index` (`plan_id`),
  KEY `cleanup_backups_task_id_index` (`task_id`),
  KEY `cleanup_backups_backup_status_index` (`backup_status`),
  KEY `cleanup_backups_expires_at_index` (`expires_at`),
  KEY `cleanup_backups_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='备份记录表';

-- 表结构: cleanup_configs
DROP TABLE IF EXISTS `cleanup_configs`;
CREATE TABLE `cleanup_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `model_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model类名',
  `model_info` json DEFAULT NULL COMMENT 'Model类信息',
  `module_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模块名称',
  `data_category` tinyint unsigned NOT NULL COMMENT '数据分类:1用户数据,2日志数据,3交易数据,4缓存数据,5配置数据',
  `default_cleanup_type` tinyint unsigned NOT NULL COMMENT '默认清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `default_conditions` json DEFAULT NULL COMMENT '默认清理条件JSON配置',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用清理',
  `priority` int unsigned NOT NULL DEFAULT '100' COMMENT '清理优先级(数字越小优先级越高)',
  `batch_size` int unsigned NOT NULL DEFAULT '1000' COMMENT '批处理大小',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '配置描述',
  `last_cleanup_at` timestamp NULL DEFAULT NULL COMMENT '最后清理时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cleanup_configs_table_name_unique` (`table_name`),
  KEY `cleanup_configs_module_name_data_category_index` (`module_name`,`data_category`),
  KEY `cleanup_configs_is_enabled_priority_index` (`is_enabled`,`priority`),
  KEY `cleanup_configs_last_cleanup_at_index` (`last_cleanup_at`),
  KEY `cleanup_configs_model_class_index` (`model_class`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理配置表';

-- 表数据: cleanup_configs (3 行)
INSERT INTO `cleanup_configs` (`id`, `table_name`, `model_class`, `model_info`, `module_name`, `data_category`, `default_cleanup_type`, `default_conditions`, `is_enabled`, `priority`, `batch_size`, `description`, `last_cleanup_at`, `created_at`, `updated_at`) VALUES ('1', 'admin_action_logs', 'Modules\\System\\Models\\AdminActionLog', NULL, 'System', '2', '3', '\"{\\\"days\\\":90,\\\"date_field\\\":\\\"created_at\\\"}\"', '1', '1', '1000', '管理员操作日志清理，保留90天内的记录', NULL, '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `cleanup_configs` (`id`, `table_name`, `model_class`, `model_info`, `module_name`, `data_category`, `default_cleanup_type`, `default_conditions`, `is_enabled`, `priority`, `batch_size`, `description`, `last_cleanup_at`, `created_at`, `updated_at`) VALUES ('2', 'user_profiles', 'Modules\\Account\\Models\\UserProfile', NULL, 'Account', '1', '4', '\"{\\\"user_status\\\":\\\"deleted\\\"}\"', '0', '10', '100', '已删除用户档案数据清理', NULL, '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `cleanup_configs` (`id`, `table_name`, `model_class`, `model_info`, `module_name`, `data_category`, `default_cleanup_type`, `default_conditions`, `is_enabled`, `priority`, `batch_size`, `description`, `last_cleanup_at`, `created_at`, `updated_at`) VALUES ('3', 'failed_jobs', NULL, NULL, 'System', '2', '2', '\"{\\\"older_than_hours\\\":24}\"', '1', '1', '500', '失败任务队列清理，删除24小时前的记录', NULL, '2026-07-16 03:28:57', '2026-07-16 03:28:57');

-- 表结构: cleanup_logs
DROP TABLE IF EXISTS `cleanup_logs`;
CREATE TABLE `cleanup_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `task_id` bigint unsigned NOT NULL COMMENT '任务ID',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `model_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model类名',
  `cleanup_type` tinyint unsigned NOT NULL COMMENT '清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `before_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '清理前记录数',
  `after_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '清理后记录数',
  `deleted_records` bigint unsigned NOT NULL DEFAULT '0' COMMENT '删除记录数',
  `execution_time` decimal(8,3) NOT NULL DEFAULT '0.000' COMMENT '执行时间(秒)',
  `conditions` json DEFAULT NULL COMMENT '使用的清理条件',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `cleanup_logs_task_id_index` (`task_id`),
  KEY `cleanup_logs_table_name_index` (`table_name`),
  KEY `cleanup_logs_cleanup_type_index` (`cleanup_type`),
  KEY `cleanup_logs_created_at_index` (`created_at`),
  KEY `cleanup_logs_model_class_index` (`model_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理日志表';

-- 表结构: cleanup_plan_contents
DROP TABLE IF EXISTS `cleanup_plan_contents`;
CREATE TABLE `cleanup_plan_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_id` bigint unsigned NOT NULL COMMENT '计划ID',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `model_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model类名',
  `cleanup_type` tinyint unsigned NOT NULL COMMENT '清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除',
  `conditions` json DEFAULT NULL COMMENT '清理条件JSON配置',
  `priority` int unsigned NOT NULL DEFAULT '100' COMMENT '清理优先级',
  `batch_size` int unsigned NOT NULL DEFAULT '1000' COMMENT '批处理大小',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `backup_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用备份',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT '备注说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cleanup_plan_contents_plan_id_table_name_unique` (`plan_id`,`table_name`),
  KEY `cleanup_plan_contents_plan_id_index` (`plan_id`),
  KEY `cleanup_plan_contents_table_name_index` (`table_name`),
  KEY `cleanup_plan_contents_priority_index` (`priority`),
  KEY `cleanup_plan_contents_model_class_index` (`model_class`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='计划内容表';

-- 表数据: cleanup_plan_contents (1 行)
INSERT INTO `cleanup_plan_contents` (`id`, `plan_id`, `table_name`, `model_class`, `cleanup_type`, `conditions`, `priority`, `batch_size`, `is_enabled`, `backup_enabled`, `notes`, `created_at`, `updated_at`) VALUES ('1', '1', 'admin_action_logs', 'Modules\\System\\Models\\AdminActionLog', '3', '\"{\\\"date_field\\\":\\\"created_at\\\",\\\"days\\\":30}\"', '1', '1000', '1', '1', '管理员操作日志，保留30天', '2026-07-16 03:28:57', '2026-07-16 03:28:57');

-- 表结构: cleanup_plans
DROP TABLE IF EXISTS `cleanup_plans`;
CREATE TABLE `cleanup_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计划名称',
  `plan_type` tinyint unsigned NOT NULL COMMENT '计划类型:1全量清理,2模块清理,3分类清理,4自定义清理,5混合清理',
  `target_selection` json DEFAULT NULL COMMENT '目标选择配置',
  `selected_tables` json DEFAULT NULL COMMENT '选择的Model类列表，格式：["Modules\\System\\Models\\AdminActionlog"]',
  `global_conditions` json DEFAULT NULL COMMENT '全局清理条件',
  `backup_config` json DEFAULT NULL COMMENT '备份配置',
  `is_template` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为模板',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '计划描述',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cleanup_plans_plan_name_unique` (`plan_name`),
  KEY `cleanup_plans_plan_type_index` (`plan_type`),
  KEY `cleanup_plans_is_template_index` (`is_template`),
  KEY `cleanup_plans_is_enabled_index` (`is_enabled`),
  KEY `cleanup_plans_created_by_index` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理计划表';

-- 表数据: cleanup_plans (2 行)
INSERT INTO `cleanup_plans` (`id`, `plan_name`, `plan_type`, `target_selection`, `selected_tables`, `global_conditions`, `backup_config`, `is_template`, `is_enabled`, `description`, `created_by`, `created_at`, `updated_at`) VALUES ('1', '日志清理计划', '3', '{\"data_category\": 2}', NULL, '\"{\\\"older_than_days\\\":30}\"', '\"{\\\"enabled\\\":true,\\\"backup_type\\\":1,\\\"compression_type\\\":2,\\\"retention_days\\\":7}\"', '1', '1', '定期清理系统日志数据，保留30天内的记录并备份', '1', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `cleanup_plans` (`id`, `plan_name`, `plan_type`, `target_selection`, `selected_tables`, `global_conditions`, `backup_config`, `is_template`, `is_enabled`, `description`, `created_by`, `created_at`, `updated_at`) VALUES ('2', '用户数据清理', '2', '{\"module_name\": \"Account\"}', NULL, '\"{\\\"user_status\\\":\\\"deleted\\\",\\\"deleted_days_ago\\\":180}\"', '\"{\\\"enabled\\\":true,\\\"backup_type\\\":2,\\\"compression_type\\\":3,\\\"retention_days\\\":30}\"', '0', '1', '清理已删除用户的相关数据，删除180天前的记录', '1', '2026-07-16 03:28:57', '2026-07-16 03:28:57');

-- 表结构: cleanup_sql_backups
DROP TABLE IF EXISTS `cleanup_sql_backups`;
CREATE TABLE `cleanup_sql_backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `backup_id` bigint unsigned NOT NULL COMMENT '备份记录ID',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `sql_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'INSERT语句内容',
  `records_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '记录数量',
  `content_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '内容大小(字节)',
  `content_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '内容SHA256哈希',
  `backup_conditions` json DEFAULT NULL COMMENT '备份条件',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `cleanup_sql_backups_backup_id_index` (`backup_id`),
  KEY `cleanup_sql_backups_table_name_index` (`table_name`),
  KEY `cleanup_sql_backups_records_count_index` (`records_count`),
  KEY `cleanup_sql_backups_content_size_index` (`content_size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SQL备份表';

-- 表结构: cleanup_table_stats
DROP TABLE IF EXISTS `cleanup_table_stats`;
CREATE TABLE `cleanup_table_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表名',
  `record_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '记录总数',
  `table_size_mb` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '表大小(MB)',
  `index_size_mb` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '索引大小(MB)',
  `data_free_mb` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '碎片空间(MB)',
  `avg_row_length` int unsigned NOT NULL DEFAULT '0' COMMENT '平均行长度',
  `auto_increment` bigint unsigned DEFAULT NULL COMMENT '自增值',
  `oldest_record_time` timestamp NULL DEFAULT NULL COMMENT '最早记录时间',
  `newest_record_time` timestamp NULL DEFAULT NULL COMMENT '最新记录时间',
  `scan_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '扫描时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cleanup_table_stats_table_name_scan_time_unique` (`table_name`,`scan_time`),
  KEY `cleanup_table_stats_table_name_index` (`table_name`),
  KEY `cleanup_table_stats_record_count_index` (`record_count`),
  KEY `cleanup_table_stats_table_size_mb_index` (`table_size_mb`),
  KEY `cleanup_table_stats_scan_time_index` (`scan_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='表统计信息表';

-- 表结构: cleanup_tasks
DROP TABLE IF EXISTS `cleanup_tasks`;
CREATE TABLE `cleanup_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `task_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务名称',
  `plan_id` bigint unsigned NOT NULL COMMENT '关联的清理计划ID',
  `backup_id` bigint unsigned DEFAULT NULL COMMENT '关联的备份ID',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '任务状态:1待执行,2备份中,3执行中,4已完成,5已失败,6已取消,7已暂停',
  `progress` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '执行进度百分比',
  `current_step` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '当前执行步骤',
  `total_tables` int unsigned NOT NULL DEFAULT '0' COMMENT '总表数',
  `processed_tables` int unsigned NOT NULL DEFAULT '0' COMMENT '已处理表数',
  `total_records` bigint unsigned NOT NULL DEFAULT '0' COMMENT '总记录数',
  `deleted_records` bigint unsigned NOT NULL DEFAULT '0' COMMENT '已删除记录数',
  `backup_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '备份文件大小(字节)',
  `execution_time` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '执行时间(秒)',
  `backup_time` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '备份时间(秒)',
  `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `backup_completed_at` timestamp NULL DEFAULT NULL COMMENT '备份完成时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cleanup_tasks_plan_id_index` (`plan_id`),
  KEY `cleanup_tasks_backup_id_index` (`backup_id`),
  KEY `cleanup_tasks_status_index` (`status`),
  KEY `cleanup_tasks_created_by_index` (`created_by`),
  KEY `cleanup_tasks_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='清理任务表';

-- 表结构: demo5_comments
DROP TABLE IF EXISTS `demo5_comments`;
CREATE TABLE `demo5_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评论内容',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '评论状态',
  `post_id` bigint unsigned NOT NULL COMMENT '文章ID',
  `user_id` bigint unsigned NOT NULL COMMENT '评论者ID',
  `parent_id` bigint unsigned DEFAULT NULL COMMENT '父评论ID',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP地址',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT '用户代理',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demo5_comments_status_index` (`status`),
  KEY `demo5_comments_post_id_index` (`post_id`),
  KEY `demo5_comments_user_id_index` (`user_id`),
  KEY `demo5_comments_parent_id_index` (`parent_id`),
  KEY `demo5_comments_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: demo5_comments (85 行)
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('1', '文章结构合理，但深度有待加强。', 'approved', '1', '4', NULL, '3.61.25.177', 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_1 like Mac OS X; en-US) AppleWebKit/535.25.6 (KHTML, like Gecko) Version/3.0.5 Mobile/8B112 Safari/6535.25.6', '2026-07-05 19:29:09', '2026-07-16 03:29:09');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('2', '这篇文章解决了我长期困惑的问题。', 'approved', '1', '2', NULL, '39.208.84.31', 'Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20221017 Firefox/37.0', '2026-07-08 04:29:09', '2026-07-16 03:29:09');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('3', '写得很详细，值得收藏学习。', 'approved', '1', '5', NULL, '5.213.128.186', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5362 (KHTML, like Gecko) Chrome/39.0.889.0 Mobile Safari/5362', '2026-06-26 16:29:09', '2026-07-16 03:29:09');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('4', '作者能分享一下相关的实践案例吗？', 'approved', '1', '5', NULL, '121.249.68.126', 'Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20211212 Firefox/36.0', '2026-07-02 02:29:09', '2026-07-16 03:29:09');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('5', '非常感谢分享，内容对我很有帮助。', 'approved', '1', '5', NULL, '208.36.20.7', 'Mozilla/5.0 (iPad; CPU OS 7_2_2 like Mac OS X; en-US) AppleWebKit/532.22.4 (KHTML, like Gecko) Version/4.0.5 Mobile/8B114 Safari/6532.22.4', '2026-06-24 07:29:09', '2026-07-16 03:29:09');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('6', '整体来说写得还可以，有参考价值。', 'approved', '1', '1', NULL, '2.242.176.171', 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_6) AppleWebKit/5330 (KHTML, like Gecko) Chrome/37.0.887.0 Mobile Safari/5330', '2026-06-21 00:29:09', '2026-07-16 03:29:09');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('7', '写得很详细，值得收藏学习。', 'approved', '1', '1', NULL, '69.60.181.244', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/537.2 (KHTML, like Gecko) Version/15.0 EdgiOS/80.01112.48 Mobile/15E148 Safari/537.2', '2026-06-30 00:29:10', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('8', '这个观点很新颖，值得思考。', 'approved', '1', '5', '7', '111.146.156.108', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/83.0.4205.97 Safari/532.0 EdgA/83.01004.36', '2026-06-30 00:51:10', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('9', '确实如此，深有同感。', 'approved', '1', '1', '3', '150.247.93.157', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5312 (KHTML, like Gecko) Chrome/36.0.843.0 Mobile Safari/5312', '2026-06-26 17:15:09', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('10', '确实如此，深有同感。', 'approved', '1', '5', '3', '221.123.96.213', 'Opera/8.81 (X11; Linux x86_64; sl-SI) Presto/2.8.253 Version/12.00', '2026-06-26 18:06:09', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('11', '补充一点我的看法...', 'approved', '1', '4', '3', '211.102.73.220', 'Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20130922 Firefox/37.0', '2026-06-26 18:07:09', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('12', '理论与实践结合得很好，受益匪浅。', 'approved', '2', '5', NULL, '195.67.229.143', 'Opera/8.74 (X11; Linux x86_64; en-US) Presto/2.11.299 Version/11.00', '2026-07-05 06:29:10', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('13', '非常感谢分享，内容对我很有帮助。', 'approved', '2', '2', NULL, '255.13.217.118', 'Opera/8.22 (Windows 98; nl-NL) Presto/2.10.189 Version/10.00', '2026-07-05 07:29:10', '2026-07-16 03:29:10');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('14', '关于文中的观点，我有一些不同的看法。', 'approved', '2', '4', NULL, '135.57.49.140', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5330 (KHTML, like Gecko) Chrome/36.0.899.0 Mobile Safari/5330', '2026-07-01 00:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('15', '赞同作者的观点，分析得很透彻。', 'approved', '2', '4', NULL, '216.143.252.215', 'Mozilla/5.0 (Windows; U; Windows NT 5.01) AppleWebKit/532.15.2 (KHTML, like Gecko) Version/5.0.4 Safari/532.15.2', '2026-06-28 19:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('16', '谢谢分享，确实如此。', 'approved', '2', '5', '13', '174.248.71.232', 'Mozilla/5.0 (Windows; U; Windows NT 5.1) AppleWebKit/532.45.6 (KHTML, like Gecko) Version/4.0 Safari/532.45.6', '2026-07-05 08:11:10', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('17', '同意楼主的看法。', 'approved', '2', '4', '13', '122.237.206.64', 'Mozilla/5.0 (Windows; U; Windows CE) AppleWebKit/531.18.5 (KHTML, like Gecko) Version/5.0 Safari/531.18.5', '2026-07-05 08:21:10', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('18', '这种方法在实际项目中应用效果如何？', 'pending', '3', '4', NULL, '107.182.23.152', 'Mozilla/5.0 (Windows; U; Windows 98; Win 9x 4.90) AppleWebKit/534.40.6 (KHTML, like Gecko) Version/4.1 Safari/534.40.6', '2026-07-04 13:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('19', '非常感谢分享，内容对我很有帮助。', 'approved', '3', '4', NULL, '173.226.175.36', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows CE; Trident/5.0)', '2026-06-15 19:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('20', '这篇文章解决了我长期困惑的问题。', 'approved', '3', '3', NULL, '186.172.41.110', 'Opera/9.31 (X11; Linux x86_64; en-US) Presto/2.11.268 Version/10.00', '2026-07-06 21:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('21', '期待作者更多的精彩分享！', 'approved', '3', '1', NULL, '241.149.98.173', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/5.0)', '2026-06-17 10:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('22', '整体来说写得还可以，有参考价值。', 'approved', '3', '2', NULL, '85.65.184.153', 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 6.0; Trident/4.0)', '2026-07-06 00:29:11', '2026-07-16 03:29:11');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('23', '感谢解答，明白了。', 'approved', '3', '5', '21', '60.254.97.210', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_8) AppleWebKit/531.0 (KHTML, like Gecko) Chrome/95.0.4629.40 Safari/531.0 Edg/95.01106.92', '2026-06-17 11:30:11', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('24', '确实如此，深有同感。', 'approved', '3', '4', '18', '252.83.182.80', 'Mozilla/5.0 (iPad; CPU OS 7_1_1 like Mac OS X; en-US) AppleWebKit/532.1.3 (KHTML, like Gecko) Version/4.0.5 Mobile/8B113 Safari/6532.1.3', '2026-07-04 14:01:11', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('25', '同意楼主的看法。', 'approved', '3', '3', '21', '91.46.237.155', 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_2_2 like Mac OS X; nl-NL) AppleWebKit/534.11.7 (KHTML, like Gecko) Version/4.0.5 Mobile/8B112 Safari/6534.11.7', '2026-06-17 11:52:11', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('26', '补充一点我的看法...', 'approved', '3', '3', '22', '167.253.209.123', 'Mozilla/5.0 (Windows 95; sl-SI; rv:1.9.1.20) Gecko/20141226 Firefox/35.0', '2026-07-06 02:20:11', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('27', '思路清晰，逻辑严谨，是一篇优质文章。', 'approved', '4', '1', NULL, '111.170.11.26', 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 4.0; Trident/3.1)', '2026-07-07 05:29:12', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('28', '观点值得参考，但需要更多的实例支撑。', 'approved', '4', '4', NULL, '6.237.210.130', 'Mozilla/5.0 (Windows CE) AppleWebKit/531.1 (KHTML, like Gecko) Chrome/95.0.4795.25 Safari/531.1 Edg/95.01141.83', '2026-07-14 11:29:12', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('29', '很好的文章！学到了很多新知识。', 'approved', '4', '3', NULL, '122.238.142.65', 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_1 like Mac OS X; sl-SI) AppleWebKit/533.24.6 (KHTML, like Gecko) Version/3.0.5 Mobile/8B115 Safari/6533.24.6', '2026-06-29 23:29:12', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('30', '这种方法在实际项目中应用效果如何？', 'pending', '4', '5', NULL, '54.58.19.10', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5320 (KHTML, like Gecko) Chrome/40.0.800.0 Mobile Safari/5320', '2026-06-15 16:29:12', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('31', '文章结构合理，但深度有待加强。', 'approved', '4', '3', NULL, '14.72.44.219', 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 4.0; Trident/5.0)', '2026-07-06 18:29:12', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('32', '期待作者更多的精彩分享！', 'approved', '4', '1', NULL, '41.23.249.102', 'Mozilla/5.0 (Windows; U; Windows NT 5.1) AppleWebKit/532.12.7 (KHTML, like Gecko) Version/5.0.2 Safari/532.12.7', '2026-06-16 22:29:12', '2026-07-16 03:29:12');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('33', '确实如此，深有同感。', 'approved', '4', '1', '30', '182.14.2.188', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5310 (KHTML, like Gecko) Chrome/39.0.852.0 Mobile Safari/5310', '2026-06-15 16:51:12', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('34', '补充一点我的看法...', 'approved', '4', '3', '29', '86.44.190.82', 'Opera/9.29 (Windows 95; nl-NL) Presto/2.9.182 Version/11.00', '2026-06-30 01:19:12', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('35', '感谢解答，明白了。', 'approved', '4', '5', '31', '125.249.181.132', 'Mozilla/5.0 (iPad; CPU OS 7_1_2 like Mac OS X; sl-SI) AppleWebKit/534.34.1 (KHTML, like Gecko) Version/4.0.5 Mobile/8B115 Safari/6534.34.1', '2026-07-06 20:03:12', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('36', '作者能分享一下相关的实践案例吗？', 'approved', '5', '3', NULL, '183.113.207.2', 'Opera/8.99 (X11; Linux i686; en-US) Presto/2.12.309 Version/12.00', '2026-07-14 08:29:13', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('37', '作者能分享一下相关的实践案例吗？', 'approved', '5', '3', NULL, '250.114.197.208', 'Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/38.0.850.0 Mobile Safari/5311', '2026-06-21 20:29:13', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('38', '关于文中的观点，我有一些不同的看法。', 'pending', '5', '1', NULL, '94.129.67.157', 'Opera/9.49 (Windows CE; en-US) Presto/2.9.318 Version/12.00', '2026-06-19 17:29:13', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('39', '非常感谢分享，内容对我很有帮助。', 'approved', '5', '2', NULL, '229.112.77.92', 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X; sl-SI) AppleWebKit/531.41.7 (KHTML, like Gecko) Version/3.0.5 Mobile/8B119 Safari/6531.41.7', '2026-06-24 23:29:13', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('40', '赞同作者的观点，分析得很透彻。', 'approved', '5', '5', NULL, '90.194.219.41', 'Mozilla/5.0 (Windows NT 6.2; sl-SI; rv:1.9.2.20) Gecko/20130729 Firefox/37.0', '2026-06-19 16:29:13', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('41', '很好的文章！学到了很多新知识。', 'approved', '5', '1', NULL, '61.183.207.165', 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_2_1 like Mac OS X; nl-NL) AppleWebKit/533.49.7 (KHTML, like Gecko) Version/3.0.5 Mobile/8B112 Safari/6533.49.7', '2026-07-04 19:29:13', '2026-07-16 03:29:13');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('42', '赞同作者的观点，分析得很透彻。', 'approved', '5', '5', NULL, '175.249.162.200', 'Opera/8.30 (X11; Linux x86_64; sl-SI) Presto/2.8.252 Version/12.00', '2026-06-28 21:29:14', '2026-07-16 03:29:14');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('43', '感谢解答，明白了。', 'approved', '5', '3', '38', '150.245.17.227', 'Opera/9.76 (Windows NT 6.1; en-US) Presto/2.11.302 Version/12.00', '2026-06-19 18:05:13', '2026-07-16 03:29:14');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('44', '同意楼主的看法。', 'approved', '5', '5', '37', '20.249.169.65', 'Opera/9.36 (Windows 95; en-US) Presto/2.9.228 Version/10.00', '2026-06-21 22:25:13', '2026-07-16 03:29:14');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('45', '确实如此，深有同感。', 'approved', '5', '5', '42', '22.111.23.253', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_2 like Mac OS X) AppleWebKit/537.1 (KHTML, like Gecko) Version/15.0 EdgiOS/87.01008.48 Mobile/15E148 Safari/537.1', '2026-06-28 22:45:14', '2026-07-16 03:29:14');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('46', '同意楼主的看法。', 'approved', '5', '1', '42', '192.189.219.8', 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_2) AppleWebKit/5321 (KHTML, like Gecko) Chrome/38.0.800.0 Mobile Safari/5321', '2026-06-28 21:56:14', '2026-07-16 03:29:14');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('47', '期待作者更多的精彩分享！', 'approved', '6', '2', NULL, '92.106.141.28', 'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X; en-US) AppleWebKit/531.49.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B112 Safari/6531.49.2', '2026-06-19 20:29:15', '2026-07-16 03:29:15');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('48', '文章中提到的某个概念能否进一步解释一下？', 'pending', '6', '5', NULL, '244.2.123.128', 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/40.0.822.0 Mobile Safari/5352', '2026-07-02 17:29:15', '2026-07-16 03:29:15');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('49', '理论与实践结合得很好，受益匪浅。', 'approved', '6', '4', NULL, '218.248.69.33', 'Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.1; Trident/3.1)', '2026-06-25 14:29:15', '2026-07-16 03:29:15');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('50', '写得很详细，值得收藏学习。', 'approved', '6', '5', NULL, '29.114.159.62', 'Mozilla/5.0 (Windows CE) AppleWebKit/5360 (KHTML, like Gecko) Chrome/36.0.815.0 Mobile Safari/5360', '2026-07-11 10:29:15', '2026-07-16 03:29:15');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('51', '这个观点很新颖，值得思考。', 'approved', '6', '1', '47', '41.219.85.107', 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_1 rv:4.0) Gecko/20100215 Firefox/36.0', '2026-06-19 22:29:15', '2026-07-16 03:29:15');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('52', '感谢解答，明白了。', 'approved', '6', '2', '48', '217.147.0.77', 'Mozilla/5.0 (X11; Linux i686; rv:5.0) Gecko/20111008 Firefox/36.0', '2026-07-02 18:35:15', '2026-07-16 03:29:15');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('53', '文章中提到的某个概念能否进一步解释一下？', 'approved', '7', '2', NULL, '122.145.129.201', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5311 (KHTML, like Gecko) Chrome/39.0.811.0 Mobile Safari/5311', '2026-06-16 13:29:16', '2026-07-16 03:29:16');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('54', '文章结构合理，但深度有待加强。', 'approved', '7', '5', NULL, '11.134.91.145', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_6 rv:4.0) Gecko/20110825 Firefox/36.0', '2026-07-05 13:29:16', '2026-07-16 03:29:16');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('55', '这篇文章解决了我长期困惑的问题。', 'approved', '7', '2', NULL, '223.88.95.234', 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_8 rv:4.0; en-US) AppleWebKit/531.11.7 (KHTML, like Gecko) Version/5.1 Safari/531.11.7', '2026-06-27 12:29:16', '2026-07-16 03:29:16');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('56', '感谢解答，明白了。', 'approved', '7', '4', '55', '171.211.5.137', 'Mozilla/5.0 (compatible; MSIE 11.0; Windows 98; Trident/5.1)', '2026-06-27 14:23:16', '2026-07-16 03:29:16');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('57', '同意楼主的看法。', 'approved', '7', '5', '55', '40.57.50.71', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_2 like Mac OS X) AppleWebKit/536.0 (KHTML, like Gecko) Version/15.0 EdgiOS/90.01066.25 Mobile/15E148 Safari/536.0', '2026-06-27 13:39:16', '2026-07-16 03:29:16');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('58', '文章内容不错，但有些地方可以进一步完善。', 'approved', '8', '2', NULL, '234.55.158.41', 'Mozilla/5.0 (compatible; MSIE 6.0; Windows CE; Trident/3.1)', '2026-07-05 02:29:17', '2026-07-16 03:29:17');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('59', '非常感谢分享，内容对我很有帮助。', 'approved', '8', '2', NULL, '121.32.2.212', 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_6 rv:6.0) Gecko/20140628 Firefox/36.0', '2026-06-16 22:29:17', '2026-07-16 03:29:17');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('60', '这种方法在实际项目中应用效果如何？', 'approved', '8', '1', NULL, '142.118.226.217', 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/4.1)', '2026-06-15 18:29:17', '2026-07-16 03:29:17');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('61', '整体来说写得还可以，有参考价值。', 'approved', '8', '4', NULL, '253.230.23.141', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/532.1 (KHTML, like Gecko) Chrome/98.0.4624.91 Safari/532.1 EdgA/98.01072.17', '2026-06-22 10:29:17', '2026-07-16 03:29:17');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('62', '赞同作者的观点，分析得很透彻。', 'approved', '8', '4', NULL, '176.0.215.180', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/3.0)', '2026-07-11 21:29:17', '2026-07-16 03:29:17');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('63', '观点值得参考，但需要更多的实例支撑。', 'approved', '8', '1', NULL, '255.236.249.166', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows CE; Trident/4.1)', '2026-06-17 10:29:18', '2026-07-16 03:29:18');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('64', '感谢解答，明白了。', 'approved', '8', '1', '60', '225.54.12.247', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 4.0; Trident/4.1)', '2026-06-15 20:13:17', '2026-07-16 03:29:18');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('65', '谢谢分享，确实如此。', 'approved', '8', '4', '61', '149.179.182.118', 'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/532.9.3 (KHTML, like Gecko) Version/4.0.3 Safari/532.9.3', '2026-06-22 12:22:17', '2026-07-16 03:29:18');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('66', '确实如此，深有同感。', 'approved', '8', '3', '60', '223.7.126.224', 'Opera/9.18 (Windows 95; sl-SI) Presto/2.12.275 Version/12.00', '2026-06-15 19:45:17', '2026-07-16 03:29:18');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('67', '谢谢分享，确实如此。', 'approved', '8', '3', '61', '191.4.36.235', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/4.0)', '2026-06-22 10:34:17', '2026-07-16 03:29:18');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('68', '说得很对，我也这么认为。', 'approved', '8', '2', '63', '189.122.6.232', 'Mozilla/5.0 (Windows CE) AppleWebKit/5330 (KHTML, like Gecko) Chrome/38.0.862.0 Mobile Safari/5330', '2026-06-17 11:05:18', '2026-07-16 03:29:18');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('69', '思路清晰，逻辑严谨，是一篇优质文章。', 'approved', '9', '1', NULL, '161.126.38.167', 'Opera/8.91 (X11; Linux i686; nl-NL) Presto/2.11.197 Version/11.00', '2026-07-07 18:29:19', '2026-07-16 03:29:19');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('70', '这种方法在实际项目中应用效果如何？', 'pending', '9', '5', NULL, '123.251.212.238', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_1 rv:3.0) Gecko/20180217 Firefox/36.0', '2026-07-04 00:29:19', '2026-07-16 03:29:19');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('71', '很好的文章！学到了很多新知识。', 'approved', '9', '4', NULL, '35.192.56.195', 'Opera/8.86 (Windows NT 6.2; sl-SI) Presto/2.9.279 Version/10.00', '2026-06-30 09:29:19', '2026-07-16 03:29:19');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('72', '谢谢分享，确实如此。', 'approved', '9', '5', '71', '78.184.157.202', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/79.0.4817.66 Safari/535.2 Edg/79.01073.64', '2026-06-30 09:59:19', '2026-07-16 03:29:19');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('73', '说得很对，我也这么认为。', 'approved', '9', '3', '70', '180.85.71.161', 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0)', '2026-07-04 00:34:19', '2026-07-16 03:29:19');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('74', '有道理，学习了。', 'approved', '9', '3', '69', '37.68.109.208', 'Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20190916 Firefox/35.0', '2026-07-07 18:47:19', '2026-07-16 03:29:19');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('75', '很好的文章！学到了很多新知识。', 'approved', '10', '4', NULL, '128.98.128.51', 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0; Trident/3.1)', '2026-06-19 22:29:20', '2026-07-16 03:29:20');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('76', '理论与实践结合得很好，受益匪浅。', 'approved', '10', '4', NULL, '190.124.102.209', 'Opera/8.62 (X11; Linux i686; nl-NL) Presto/2.11.353 Version/11.00', '2026-07-02 19:29:20', '2026-07-16 03:29:20');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('77', '文章结构合理，但深度有待加强。', 'approved', '10', '5', NULL, '161.139.10.56', 'Opera/8.91 (Windows 98; nl-NL) Presto/2.9.350 Version/10.00', '2026-07-09 20:29:20', '2026-07-16 03:29:20');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('78', '整体来说写得还可以，有参考价值。', 'approved', '10', '2', NULL, '67.246.41.6', 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_8) AppleWebKit/5321 (KHTML, like Gecko) Chrome/40.0.810.0 Mobile Safari/5321', '2026-07-12 06:29:20', '2026-07-16 03:29:20');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('79', '文章内容不错，但有些地方可以进一步完善。', 'approved', '10', '3', NULL, '158.213.131.90', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.0 (KHTML, like Gecko) Chrome/88.0.4054.16 Safari/535.0 EdgA/88.01035.15', '2026-06-16 16:29:20', '2026-07-16 03:29:20');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('80', '写得很详细，值得收藏学习。', 'approved', '10', '3', NULL, '202.117.134.82', 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_6) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/84.0.4744.81 Safari/532.0 Edg/84.01043.22', '2026-06-26 20:29:21', '2026-07-16 03:29:21');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('81', '写得很详细，值得收藏学习。', 'approved', '10', '2', NULL, '203.66.92.64', 'Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/3.1)', '2026-07-14 14:29:21', '2026-07-16 03:29:21');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('82', '确实如此，深有同感。', 'approved', '10', '5', '77', '209.244.247.168', 'Mozilla/5.0 (Windows; U; Windows NT 5.01) AppleWebKit/533.35.5 (KHTML, like Gecko) Version/4.1 Safari/533.35.5', '2026-07-09 22:18:20', '2026-07-16 03:29:21');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('83', '感谢解答，明白了。', 'approved', '10', '3', '78', '87.23.134.50', 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 6.0; Trident/3.0)', '2026-07-12 06:35:20', '2026-07-16 03:29:21');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('84', '说得很对，我也这么认为。', 'approved', '10', '5', '75', '29.135.239.5', 'Opera/9.42 (Windows 98; sl-SI) Presto/2.11.275 Version/11.00', '2026-06-19 22:47:20', '2026-07-16 03:29:21');
INSERT INTO `demo5_comments` (`id`, `content`, `status`, `post_id`, `user_id`, `parent_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('85', '同意楼主的看法。', 'approved', '10', '4', '78', '220.26.236.243', 'Mozilla/5.0 (Windows 98) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/92.0.4150.79 Safari/535.1 Edg/92.01132.76', '2026-07-12 08:22:20', '2026-07-16 03:29:21');

-- 表结构: demo5_posts
DROP TABLE IF EXISTS `demo5_posts`;
CREATE TABLE `demo5_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章标题',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章内容',
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT '文章状态',
  `user_id` bigint unsigned NOT NULL COMMENT '作者ID',
  `published_at` timestamp NULL DEFAULT NULL COMMENT '发布时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demo5_posts_status_index` (`status`),
  KEY `demo5_posts_user_id_index` (`user_id`),
  KEY `demo5_posts_published_at_index` (`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: demo5_posts (10 行)
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('1', 'Laravel 12 新特性详解', 'Laravel 12 带来了许多令人兴奋的新特性，包括改进的性能、新的语法糖、增强的类型安全性等。本文将详细介绍这些新特性，帮助开发者快速掌握最新版本的Laravel框架。', 'published', '2', '2026-07-06 03:29:08', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('2', '模块化开发最佳实践', '模块化开发是现代应用架构的重要组成部分。本文分享了在Laravel项目中实施模块化开发的最佳实践，包括模块设计原则、依赖管理、接口定义等关键概念。', 'published', '2', '2026-07-08 03:29:08', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('3', 'Dcat Admin 使用心得', 'Dcat Admin 是一个优秀的Laravel后台管理框架。本文总结了作者在使用Dcat Admin过程中的心得体会，包括配置技巧、自定义扩展、性能优化等方面的经验。', 'published', '4', '2026-07-10 03:29:08', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('4', 'Filament 后台开发技巧', 'Filament 是现代化的Laravel后台管理框架，采用TALL技术栈。本文介绍了Filament的核心概念、组件使用、自定义开发等实用技巧，帮助开发者快速构建优雅的后台界面。', 'published', '2', '2026-07-12 03:29:08', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('5', '多后台架构设计思考', '在企业级应用中，多后台架构是一种常见的设计模式。本文探讨了多后台系统的设计理念、数据隔离、权限管理、用户体验等关键问题，为复杂业务系统的架构设计提供参考。', 'published', '2', '2026-07-14 03:29:08', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('6', 'Dolore ex est alias quis aut.', 'Id dolor odio aut aspernatur corporis molestiae perspiciatis. Aperiam alias sequi numquam harum. Eligendi voluptatem et quis quaerat dolor unde ipsam.

Omnis rerum repellendus dolorem commodi culpa et. Enim qui praesentium deserunt sint dolores. Id enim asperiores et quisquam sint vitae. Quia distinctio rerum quidem occaecati temporibus alias mollitia.

Et aut aliquid nulla labore sit quis porro. Omnis voluptatem eos hic molestiae vitae odit et. Voluptatem nisi illum aliquam eum quas. Velit eos ab praesentium dolore est. A sed dolorem quos est.', 'published', '3', '2025-08-09 13:25:39', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('7', 'Fugit debitis ducimus nulla.', 'Fugit blanditiis temporibus et a. Laudantium in corporis inventore eum et. Sit excepturi dolores ullam accusamus vero ducimus saepe. Voluptas sed earum minus animi ut. Neque sit totam dolore quis fuga dolor.

Aut assumenda officiis numquam praesentium. Assumenda aliquam inventore dolore minima eaque commodi quas iure. Numquam est aut qui qui facere dolores deserunt. Voluptatem dolore dignissimos ut ut.

Veniam amet veniam sed a voluptatem ipsam reiciendis. Repudiandae quo et aspernatur recusandae. Numquam enim distinctio expedita rerum quod repellat harum fugiat. Cumque voluptatem sint quo quia consequuntur voluptatum.

Voluptatum voluptates officia est et quo. Optio expedita sunt eius culpa perferendis quia sit molestiae.

Modi et nemo corporis soluta soluta reprehenderit quisquam. Aut fugiat ea tempore. Aut minima eos pariatur ipsam dicta vitae quam.', 'published', '1', '2025-11-09 11:54:50', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('8', 'Odio debitis aspernatur corporis mollitia fugiat itaque quisquam.', 'Sed voluptatibus doloribus sequi sunt. Et vel hic rerum beatae vel. Sed mollitia eligendi quia in quo ut blanditiis. Earum sit perspiciatis et.

Est et eum eligendi quidem sint. Non quia vitae esse maiores accusantium. Asperiores blanditiis ut eveniet quod aperiam error.

Vel cum molestiae quod. Suscipit voluptates repudiandae hic minima consectetur consequatur nobis. Error quia molestias dignissimos illum qui tempore totam. Sed voluptatibus dolorem repellendus qui doloremque et voluptate ipsa.

Dolor consequatur reprehenderit optio tempora. Eveniet sequi distinctio minima. Officia quia et magnam tempora et sit neque.

Autem mollitia fugit fugiat nostrum dolor. Omnis soluta aliquid quia libero hic. Atque vitae ut aperiam laudantium inventore a aliquam rerum. Est nisi sed adipisci ea est sapiente ipsum.

Mollitia in officiis eum. Cupiditate sed adipisci sint. Iusto aspernatur quas et occaecati aperiam numquam rerum. Debitis ullam impedit qui nesciunt magni porro placeat.

Autem optio perspiciatis et voluptatem qui. Alias dicta ab enim sed rem. Ut numquam eos quisquam.

Quo illum debitis nemo sit sunt voluptas voluptas. Nesciunt eum voluptatum beatae architecto. Dolor omnis dignissimos quam ea. Enim omnis corrupti molestias quos.', 'published', '2', '2025-11-24 09:23:55', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('9', 'Incidunt facilis quasi temporibus aut unde iste fuga neque a.', 'Perferendis dolorum dolor earum voluptatem non consequatur aut. Voluptatem aliquam cumque labore rerum et. Modi quas sequi amet.

Dolorem eveniet voluptates aut facilis illo aut et qui. Nulla corporis officia autem amet cumque beatae. Quis est placeat et magni.

Nostrum inventore non quia necessitatibus nobis. Nostrum rerum ipsam deserunt rerum est id rerum.

Voluptatem animi omnis quia voluptatem enim. Amet voluptatum provident nam doloribus illo. Corporis necessitatibus eveniet facilis et. Eius dolor qui et amet non voluptatum.', 'published', '3', '2025-10-19 14:30:25', '2026-07-16 03:29:08', '2026-07-16 03:29:08');
INSERT INTO `demo5_posts` (`id`, `title`, `content`, `status`, `user_id`, `published_at`, `created_at`, `updated_at`) VALUES ('10', 'Similique aut officia quibusdam nostrum dolor et.', 'Soluta quaerat quod ipsam. Qui molestiae beatae corrupti incidunt sed deserunt voluptas et. Commodi unde harum ducimus molestias necessitatibus aut assumenda. Aperiam et cumque nisi dolores.

Aliquam dignissimos perferendis ut odio optio est totam. Praesentium aut sunt reiciendis sit magnam molestiae.

Voluptatem officia veritatis ipsam vel alias. Ea nobis voluptatem beatae quo exercitationem. Eum nulla dolores id eius qui a tempora. Nisi rerum ducimus totam excepturi sed et.', 'published', '4', '2026-04-28 10:12:44', '2026-07-16 03:29:08', '2026-07-16 03:29:08');

-- 表结构: demo5_users
DROP TABLE IF EXISTS `demo5_users`;
CREATE TABLE `demo5_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户姓名',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户邮箱',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户手机号',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户头像',
  `status` enum('active','inactive','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '用户状态',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `demo5_users_email_unique` (`email`),
  KEY `demo5_users_status_index` (`status`),
  KEY `demo5_users_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: demo5_users (5 行)
INSERT INTO `demo5_users` (`id`, `name`, `email`, `phone`, `avatar`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES ('1', '管理员', 'admin@demo5.com', NULL, NULL, 'active', '2026-07-15 03:29:07', '2026-07-16 03:29:07', '2026-07-16 03:29:07');
INSERT INTO `demo5_users` (`id`, `name`, `email`, `phone`, `avatar`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES ('2', '开发者张三', 'zhangsan@demo5.com', '13800138000', NULL, 'active', '2026-07-14 03:29:07', '2026-07-16 03:29:07', '2026-07-16 03:29:07');
INSERT INTO `demo5_users` (`id`, `name`, `email`, `phone`, `avatar`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES ('3', '开发者李四', 'lisi@demo5.com', '13900139000', NULL, 'active', '2026-07-13 03:29:07', '2026-07-16 03:29:07', '2026-07-16 03:29:07');
INSERT INTO `demo5_users` (`id`, `name`, `email`, `phone`, `avatar`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES ('4', '测试用户王五', 'wangwu@demo5.com', NULL, NULL, 'active', '2026-07-11 03:29:07', '2026-07-16 03:29:07', '2026-07-16 03:29:07');
INSERT INTO `demo5_users` (`id`, `name`, `email`, `phone`, `avatar`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES ('5', '临时用户赵六', 'zhaoliu@demo5.com', NULL, NULL, 'inactive', '2026-06-16 03:29:07', '2026-07-16 03:29:07', '2026-07-16 03:29:07');

-- 表结构: energy_data_monthly
DROP TABLE IF EXISTS `energy_data_monthly`;
CREATE TABLE `energy_data_monthly` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_id` bigint NOT NULL COMMENT '数据ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '表计ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表计类型',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '商户ID',
  `year` int NOT NULL COMMENT '年份',
  `month` int NOT NULL COMMENT '月份',
  `data_value` decimal(16,4) NOT NULL COMMENT '月累计值',
  `peak_value` decimal(16,4) DEFAULT NULL COMMENT '峰时段累计',
  `flat_value` decimal(16,4) DEFAULT NULL COMMENT '平时段累计',
  `valley_value` decimal(16,4) DEFAULT NULL COMMENT '谷时段累计',
  `yoy_rate` decimal(8,4) DEFAULT NULL COMMENT '同比增长率（%）',
  `mom_rate` decimal(8,4) DEFAULT NULL COMMENT '环比增长率（%）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_edm` (`meter_id`,`meter_type`,`year`,`month`),
  KEY `idx_edm_ym` (`year`,`month`),
  KEY `idx_edm_company_ym` (`company_id`,`year`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='月统计数据表';

-- 表结构: energy_data_peak_valley
DROP TABLE IF EXISTS `energy_data_peak_valley`;
CREATE TABLE `energy_data_peak_valley` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_id` bigint NOT NULL COMMENT '数据ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '表计ID',
  `data_date` date NOT NULL COMMENT '数据日期',
  `scheme_id` bigint unsigned DEFAULT NULL COMMENT '使用的尖峰平谷方案ID',
  `total_value` decimal(16,4) NOT NULL COMMENT '总电量',
  `sharp_value` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '尖峰电量',
  `peak_value` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '峰段电量',
  `flat_value` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '平段电量',
  `valley_value` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '谷段电量',
  `total_cost` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '总电费',
  `sharp_cost` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '尖峰电费',
  `peak_cost` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '峰段电费',
  `flat_cost` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '平段电费',
  `valley_cost` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '谷段电费',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_edpv` (`meter_id`,`data_date`),
  KEY `idx_edpv_date` (`data_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='尖峰平谷统计表';

-- 表结构: energy_data_yearly
DROP TABLE IF EXISTS `energy_data_yearly`;
CREATE TABLE `energy_data_yearly` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_id` bigint NOT NULL COMMENT '数据ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '表计ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表计类型',
  `year` int NOT NULL COMMENT '年份',
  `data_value` decimal(16,4) NOT NULL COMMENT '年累计值',
  `peak_value` decimal(16,4) DEFAULT NULL COMMENT '峰时段累计',
  `flat_value` decimal(16,4) DEFAULT NULL COMMENT '平时段累计',
  `valley_value` decimal(16,4) DEFAULT NULL COMMENT '谷时段累计',
  `yoy_rate` decimal(8,4) DEFAULT NULL COMMENT '同比增长率（%）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_edy` (`meter_id`,`meter_type`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='年统计数据表';

-- 表结构: energy_price_relevancy
DROP TABLE IF EXISTS `energy_price_relevancy`;
CREATE TABLE `energy_price_relevancy` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `relevancy_id` bigint NOT NULL COMMENT '关联ID',
  `tactics_id` bigint unsigned NOT NULL COMMENT '价格策略ID',
  `target_id` bigint unsigned NOT NULL COMMENT '目标ID（空间/表计/设备）',
  `target_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '目标类型:space,meter,equipment',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_price_rel` (`tactics_id`,`target_id`,`target_type`),
  KEY `idx_pr_target` (`target_id`,`target_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='价格关联表';

-- 表结构: energy_price_tactics
DROP TABLE IF EXISTS `energy_price_tactics`;
CREATE TABLE `energy_price_tactics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tactics_id` bigint NOT NULL COMMENT '策略ID',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '策略名称',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID',
  `tactics_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed' COMMENT '类型:fixed=固定,time_of_use=分时,peak_valley=尖峰平谷',
  `valid_from` date NOT NULL COMMENT '生效开始日期',
  `valid_to` date NOT NULL COMMENT '生效结束日期',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_tactics_cat` (`category_id`),
  KEY `idx_tactics_valid` (`valid_from`,`valid_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源价格策略表';

-- 表结构: energy_price_tactics_item
DROP TABLE IF EXISTS `energy_price_tactics_item`;
CREATE TABLE `energy_price_tactics_item` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `item_id` bigint NOT NULL COMMENT '明细ID',
  `tactics_id` bigint unsigned NOT NULL COMMENT '策略ID',
  `time_slot` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '时段:peak=尖,high=峰,flat=平,valley=谷',
  `start_time` time NOT NULL COMMENT '开始时间',
  `end_time` time NOT NULL COMMENT '结束时间',
  `price` decimal(16,4) NOT NULL COMMENT '单价',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_tactics_item_tid` (`tactics_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='策略明细表';

-- 表结构: energy_saving_project
DROP TABLE IF EXISTS `energy_saving_project`;
CREATE TABLE `energy_saving_project` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `project_id` bigint NOT NULL COMMENT '项目ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '项目名称',
  `project_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general' COMMENT '类型:general=综合,lighting=照明,hvac=暖通,process=工艺',
  `category_id` bigint unsigned DEFAULT NULL COMMENT '目标能源品种ID',
  `target_saving` decimal(16,4) DEFAULT NULL COMMENT '目标节能量',
  `actual_saving` decimal(16,4) DEFAULT NULL COMMENT '实际节能量',
  `investment` decimal(16,4) DEFAULT NULL COMMENT '投资金额',
  `start_date` date NOT NULL COMMENT '开始日期',
  `end_date` date DEFAULT NULL COMMENT '结束日期',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态:0=规划中,1=实施中,2=已完成,3=已终止',
  `responsible_user` bigint unsigned DEFAULT NULL COMMENT '负责人ID',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_esp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='节能项目表';

-- 表结构: enterprise_companies
DROP TABLE IF EXISTS `enterprise_companies`;
CREATE TABLE `enterprise_companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint unsigned NOT NULL COMMENT '归属租户ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '企业名称',
  `credit_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '统一社会信用代码',
  `legal_person` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '法定代表人',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '企业地址',
  `contact_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系人',
  `contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系电话',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `industry` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '行业类型',
  `standard` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '执行标准',
  `scale` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '企业规模',
  `status` enum('active','inactive','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '企业状态',
  `auth_status` enum('unauthenticated','authenticated','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unauthenticated' COMMENT '认证状态',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '服务到期时间',
  `report` tinyint NOT NULL DEFAULT '0' COMMENT '报告导出开关：0=关闭，1=开启',
  `is_disable` tinyint NOT NULL DEFAULT '0' COMMENT '禁用标记：0=正常，1=禁用',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '企业简介',
  `images` json DEFAULT NULL COMMENT '企业图片数组',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enterprise_companies_credit_code_unique` (`credit_code`),
  KEY `enterprise_companies_merchant_id_index` (`merchant_id`),
  KEY `enterprise_companies_status_index` (`status`),
  KEY `enterprise_companies_auth_status_index` (`auth_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: enterprise_companies (4 行)
INSERT INTO `enterprise_companies` (`id`, `merchant_id`, `name`, `credit_code`, `legal_person`, `address`, `contact_name`, `contact_phone`, `email`, `industry`, `standard`, `scale`, `status`, `auth_status`, `end_time`, `report`, `is_disable`, `content`, `images`, `created_at`, `updated_at`) VALUES ('1', '1', '测试企业API测试', '91110108MA01ABCD99', '张三', NULL, '李四', '13800138000', NULL, NULL, NULL, NULL, 'active', 'authenticated', NULL, '0', '0', NULL, NULL, '2026-07-16 01:41:00', '2026-07-16 01:41:00');
INSERT INTO `enterprise_companies` (`id`, `merchant_id`, `name`, `credit_code`, `legal_person`, `address`, `contact_name`, `contact_phone`, `email`, `industry`, `standard`, `scale`, `status`, `auth_status`, `end_time`, `report`, `is_disable`, `content`, `images`, `created_at`, `updated_at`) VALUES ('2', '1', '绿能科技有限公司', '91110000MA00ABCD123', '张明', '北京市朝阳区建国路88号', '李经理', '13800138001', NULL, '新能源', NULL, 'large', 'active', 'authenticated', NULL, '0', '0', NULL, NULL, '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `enterprise_companies` (`id`, `merchant_id`, `name`, `credit_code`, `legal_person`, `address`, `contact_name`, `contact_phone`, `email`, `industry`, `standard`, `scale`, `status`, `auth_status`, `end_time`, `report`, `is_disable`, `content`, `images`, `created_at`, `updated_at`) VALUES ('3', '1', '清洁能源集团', '91110000MA00EFGH456', '王强', '上海市浦东新区张江高科技园区', '赵总监', '13900139002', NULL, '清洁能源', NULL, 'medium', 'active', 'pending', NULL, '0', '0', NULL, NULL, '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `enterprise_companies` (`id`, `merchant_id`, `name`, `credit_code`, `legal_person`, `address`, `contact_name`, `contact_phone`, `email`, `industry`, `standard`, `scale`, `status`, `auth_status`, `end_time`, `report`, `is_disable`, `content`, `images`, `created_at`, `updated_at`) VALUES ('4', '11', '西藏红墙烧结砖有限公司22', '9154000071111419182738', '张三', '西藏自治区拉萨市堆龙德庆区', '系统管理员', '13989014404', '1514582970@qq.com', '建材制造', '1111', 'medium', 'active', 'authenticated', NULL, '0', '0', '这是一家专业生产烧结砖的企业', '[]', '2026-07-16 03:29:22', '2026-07-20 02:48:29');

-- 表结构: enterprise_customers
DROP TABLE IF EXISTS `enterprise_customers`;
CREATE TABLE `enterprise_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '归属企业ID',
  `customer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '客户名称',
  `material` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '采购产品名称',
  `number` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '供货量',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `transport` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '运输方式',
  `distance` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '运输距离(km)',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注/应用场景',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enterprise_customers_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: enterprise_depts
DROP TABLE IF EXISTS `enterprise_depts`;
CREATE TABLE `enterprise_depts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '归属企业ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '部门名称',
  `pid` bigint unsigned NOT NULL DEFAULT '0' COMMENT '父级部门ID',
  `level` int unsigned NOT NULL DEFAULT '1' COMMENT '部门层级',
  `is_disable` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用 0否1是',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enterprise_depts_company_id_index` (`company_id`),
  KEY `enterprise_depts_pid_index` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: enterprise_depts (6 行)
INSERT INTO `enterprise_depts` (`id`, `company_id`, `name`, `pid`, `level`, `is_disable`, `sort`, `created_at`, `updated_at`) VALUES ('1', '4', '生产部', '0', '1', '0', '1', '2026-07-16 03:29:22', '2026-07-16 03:29:22');
INSERT INTO `enterprise_depts` (`id`, `company_id`, `name`, `pid`, `level`, `is_disable`, `sort`, `created_at`, `updated_at`) VALUES ('2', '4', '技术部', '0', '1', '0', '2', '2026-07-16 03:29:22', '2026-07-16 03:29:22');
INSERT INTO `enterprise_depts` (`id`, `company_id`, `name`, `pid`, `level`, `is_disable`, `sort`, `created_at`, `updated_at`) VALUES ('3', '4', '管理部', '0', '1', '0', '3', '2026-07-16 03:29:22', '2026-07-16 03:29:22');
INSERT INTO `enterprise_depts` (`id`, `company_id`, `name`, `pid`, `level`, `is_disable`, `sort`, `created_at`, `updated_at`) VALUES ('4', '11', '锅炉车间', '0', '1', '0', '1', '2026-07-16 03:41:57', '2026-07-16 03:41:57');
INSERT INTO `enterprise_depts` (`id`, `company_id`, `name`, `pid`, `level`, `is_disable`, `sort`, `created_at`, `updated_at`) VALUES ('5', '11', '热力站', '0', '1', '0', '2', '2026-07-16 03:41:57', '2026-07-16 03:41:57');
INSERT INTO `enterprise_depts` (`id`, `company_id`, `name`, `pid`, `level`, `is_disable`, `sort`, `created_at`, `updated_at`) VALUES ('6', '11', '余热回收车间', '0', '1', '0', '3', '2026-07-16 03:41:57', '2026-07-16 03:41:57');

-- 表结构: enterprise_standards
DROP TABLE IF EXISTS `enterprise_standards`;
CREATE TABLE `enterprise_standards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `data_time` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '年份（YYYY格式）',
  `type` enum('energy','carbon') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标准类型',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '有无对标标准（0=无，1=有）',
  `yoy` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '同比降低百分比(%)',
  `product_target` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '单位产品目标值',
  `value_target` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '单位产值目标值',
  `standard_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标准名称',
  `standard_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标准号',
  `limit_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '能耗限值单位',
  `level_1` decimal(10,2) DEFAULT NULL COMMENT 'Ⅰ级限值',
  `level_2` decimal(10,2) DEFAULT NULL COMMENT 'Ⅱ级限值',
  `level_3` decimal(10,2) DEFAULT NULL COMMENT 'Ⅲ级限值',
  `baseline_value` decimal(10,2) DEFAULT NULL COMMENT '基准水平',
  `benchmark_value` decimal(10,2) DEFAULT NULL COMMENT '标杆水平',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_company_data_type` (`company_id`,`data_time`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: enterprise_standards (2 行)
INSERT INTO `enterprise_standards` (`id`, `company_id`, `data_time`, `type`, `status`, `yoy`, `product_target`, `value_target`, `standard_name`, `standard_code`, `limit_unit`, `level_1`, `level_2`, `level_3`, `baseline_value`, `benchmark_value`, `created_at`, `updated_at`) VALUES ('1', '1', '2026', 'energy', '0', '15.50', '0.0012', '0.1687', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-16 01:41:00', '2026-07-16 01:41:00');
INSERT INTO `enterprise_standards` (`id`, `company_id`, `data_time`, `type`, `status`, `yoy`, `product_target`, `value_target`, `standard_name`, `standard_code`, `limit_unit`, `level_1`, `level_2`, `level_3`, `baseline_value`, `benchmark_value`, `created_at`, `updated_at`) VALUES ('2', '1', '2026', 'carbon', '1', '20.00', '0.0005', '0.7283', '单位产品碳排放限额', 'GB 21252-2014', 'tCO₂/块', '120.00', '150.00', '180.00', '150.00', '120.00', '2026-07-16 01:41:00', '2026-07-16 01:41:00');

-- 表结构: enterprise_suppliers
DROP TABLE IF EXISTS `enterprise_suppliers`;
CREATE TABLE `enterprise_suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '归属企业ID',
  `supplier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '供应商名称',
  `material` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '采购物料名称',
  `number` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '供应量',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `transport` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '运输方式',
  `distance` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '运输距离(km)',
  `energy` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '能耗强度',
  `carbon` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '碳排放强度(tCO2e/t)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enterprise_suppliers_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: feature_blacklist
DROP TABLE IF EXISTS `feature_blacklist`;
CREATE TABLE `feature_blacklist` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `feature_id` bigint unsigned NOT NULL COMMENT '功能ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '加入黑名单原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feature_user` (`feature_id`,`user_id`),
  KEY `feature_blacklist_feature_id_index` (`feature_id`),
  KEY `feature_blacklist_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: feature_whitelist
DROP TABLE IF EXISTS `feature_whitelist`;
CREATE TABLE `feature_whitelist` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `feature_id` bigint unsigned NOT NULL COMMENT '功能ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '加入白名单原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feature_user` (`feature_id`,`user_id`),
  KEY `feature_whitelist_feature_id_index` (`feature_id`),
  KEY `feature_whitelist_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: features
DROP TABLE IF EXISTS `features`;
CREATE TABLE `features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '功能ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '功能名称',
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '功能标识(唯一)',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '功能描述',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认状态:0关闭 1开启',
  `percentage` int NOT NULL DEFAULT '0' COMMENT '灰度百分比(0-100)',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '功能分组(创作类/社交类/AI类)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `features_key_unique` (`key`),
  KEY `features_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: file_files
DROP TABLE IF EXISTS `file_files`;
CREATE TABLE `file_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `storage_disk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '储存disk',
  `user_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '储存目录',
  `re_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联类型',
  `re_id` int unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `o_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原名',
  `fsize` int unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `type1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件类型',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal正常,linked已关联,dangling悬空',
  `used_at` timestamp NULL DEFAULT NULL COMMENT '使用时间',
  `dangling_at` timestamp NULL DEFAULT NULL COMMENT '悬空时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file_files_user_id_index` (`user_id`),
  KEY `file_files_status_index` (`status`),
  KEY `file_files_re_type_re_id_index` (`re_type`,`re_id`),
  KEY `idx_status_created_at` (`status`,`created_at`),
  KEY `idx_status_dangling_at` (`status`,`dangling_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: file_imgs
DROP TABLE IF EXISTS `file_imgs`;
CREATE TABLE `file_imgs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `storage_disk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '储存disk',
  `user_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '储存目录',
  `re_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联类型',
  `re_id` int unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `o_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原名',
  `fsize` int unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `width` int unsigned NOT NULL DEFAULT '0' COMMENT '图片宽度',
  `height` int unsigned NOT NULL DEFAULT '0' COMMENT '图片高度',
  `type1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片类型',
  `private` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '私人:0公共,1私人',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal正常,linked已关联,dangling悬空',
  `used_at` timestamp NULL DEFAULT NULL COMMENT '使用时间',
  `dangling_at` timestamp NULL DEFAULT NULL COMMENT '悬空时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file_imgs_user_id_index` (`user_id`),
  KEY `file_imgs_status_index` (`status`),
  KEY `file_imgs_re_type_re_id_index` (`re_type`,`re_id`),
  KEY `file_imgs_private_index` (`private`),
  KEY `idx_status_created_at` (`status`,`created_at`),
  KEY `idx_status_dangling_at` (`status`,`dangling_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: file_storage_config_histories
DROP TABLE IF EXISTS `file_storage_config_histories`;
CREATE TABLE `file_storage_config_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `config_id` bigint unsigned NOT NULL COMMENT '关联的存储配置ID',
  `old_driver` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '旧存储驱动',
  `new_driver` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '新存储驱动',
  `old_config` text COLLATE utf8mb4_unicode_ci COMMENT '旧配置值',
  `new_config` text COLLATE utf8mb4_unicode_ci COMMENT '新配置值',
  `old_status` tinyint DEFAULT NULL COMMENT '旧状态',
  `new_status` tinyint DEFAULT NULL COMMENT '新状态',
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '变更时间',
  `changed_by` int unsigned NOT NULL DEFAULT '0' COMMENT '变更人ID',
  `change_reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变更原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_config_id` (`config_id`),
  KEY `idx_changed_at` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: file_storage_configs
DROP TABLE IF EXISTS `file_storage_configs`;
CREATE TABLE `file_storage_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '存储磁盘名称，唯一',
  `driver` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '存储驱动（local, s3, oss等）',
  `config` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置值，JSON格式',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置描述',
  `is_default` tinyint NOT NULL DEFAULT '0' COMMENT '是否默认存储，1表示是，0表示否',
  `is_temp` tinyint NOT NULL DEFAULT '0' COMMENT '是否用于临时存储，1表示是，0表示否',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态：1-启用，0-禁用',
  `env` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'production' COMMENT '环境（development, testing, production）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `updated_by` int unsigned NOT NULL DEFAULT '0' COMMENT '更新人ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name_env` (`name`,`env`),
  KEY `idx_status` (`status`),
  KEY `idx_env` (`env`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: file_storage_configs (2 行)
INSERT INTO `file_storage_configs` (`id`, `name`, `driver`, `config`, `description`, `is_default`, `is_temp`, `status`, `env`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES ('1', 'local', 'local', '{\"root\":\"\\/data\\/project\\/tanneng\\/nengtan_laravel\\/storage\\/app\",\"throw\":true}', '本地文件存储，文件保存在 storage/app 目录', '1', '1', '1', 'local', '2026-07-16 03:28:57', '2026-07-16 03:28:57', '1', '1');
INSERT INTO `file_storage_configs` (`id`, `name`, `driver`, `config`, `description`, `is_default`, `is_temp`, `status`, `env`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES ('2', 'oss', 'oss', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"bucket\":\"\",\"endpoint\":\"oss-cn-hangzhou.aliyuncs.com\",\"is_cname\":false,\"use_ssl\":true,\"signatureVersion\":\"v1\",\"region\":\"\",\"options\":[],\"macros\":[]}', '阿里云对象存储服务', '0', '0', '0', 'local', '2026-07-16 03:28:57', '2026-07-16 03:28:57', '1', '1');

-- 表结构: file_template
DROP TABLE IF EXISTS `file_template`;
CREATE TABLE `file_template` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unid` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标识',
  `file_id` int unsigned DEFAULT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板标题',
  `desc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `status` tinyint unsigned DEFAULT '1',
  `group` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分组',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: job_runs
DROP TABLE IF EXISTS `job_runs`;
CREATE TABLE `job_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `runclass` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '运行类',
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '运行状态',
  `desc` text COLLATE utf8mb4_unicode_ci COMMENT '描述信息',
  `runtime` decimal(12,5) NOT NULL DEFAULT '0.00000' COMMENT '运行时间',
  PRIMARY KEY (`id`),
  KEY `job_runs_queue_index` (`queue`),
  KEY `job_runs_created_at_status_index` (`created_at`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=523 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: job_runs (522 行)
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('1', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135411', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('2', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.02719783782959;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135413', 'run-end', '', '2027.19784');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('3', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135413', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('4', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.7481539249420166;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135416', 'run-end', '', '2748.15392');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('5', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135417', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('6', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:6.064957141876221;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135423', 'run-end', '', '6064.95714');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('7', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784135423', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('8', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02061295509338379;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784135423', 'run-end', '', '20.61296');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('9', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135628', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('10', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007416009902954;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135630', 'run-end', '', '2007.41601');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('11', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135631', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('12', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.190821886062622;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135633', 'run-end', '', '2190.82189');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('13', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135633', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('14', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.078827142715454;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135636', 'run-end', '', '3078.82714');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('15', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784135637', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('16', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017998933792114258;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784135637', 'run-end', '', '17.99893');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('17', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135824', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('18', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.008884906768799;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135826', 'run-end', '', '2008.88491');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('19', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135826', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('20', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1988089084625244;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135829', 'run-end', '', '2198.80891');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('21', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135829', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('22', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.0123748779296875;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135832', 'run-end', '', '3012.37488');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('23', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784135832', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('24', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016057968139648438;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784135832', 'run-end', '', '16.05797');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('25', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135991', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('26', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0078439712524414;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784135993', 'run-end', '', '2007.84397');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('27', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135993', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('28', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.4427120685577393;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784135996', 'run-end', '', '2442.71207');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('29', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784135996', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('30', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.1051130294799805;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136000', 'run-end', '', '3105.11303');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('31', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136000', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('32', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016659975051879883;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136000', 'run-end', '', '16.65998');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('33', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784136021', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('34', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007807970046997;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784136023', 'run-end', '', '2007.80797');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('35', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784136024', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('36', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.191920042037964;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784136026', 'run-end', '', '2191.92004');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('37', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136026', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('38', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.086794137954712;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136030', 'run-end', '', '3086.79414');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('39', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136030', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('40', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017210006713867188;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136030', 'run-end', '', '17.21001');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('41', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784136298', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('42', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784136300', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('43', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0077829360961914;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784136300', 'run-end', '', '2007.78294');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('44', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784136300', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('45', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0098211765289307;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784136302', 'run-end', '', '2009.82118');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('46', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784136302', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('47', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2283401489257812;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784136303', 'run-end', '', '2228.34015');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('48', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136303', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('49', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.186803102493286;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784136304', 'run-end', '', '2186.80310');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('50', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136305', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('51', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.120115041732788;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136306', 'run-end', '', '3120.11504');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('52', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136306', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('53', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016630172729492188;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136306', 'run-end', '', '16.63017');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('54', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.1795241832733154;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784136308', 'run-end', '', '3179.52418');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('55', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136308', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('56', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01864790916442871;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784136308', 'run-end', '', '18.64791');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('57', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784137785', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('58', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0081279277801514;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784137787', 'run-end', '', '2008.12793');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('59', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784137788', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('60', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.179872989654541;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784137790', 'run-end', '', '2179.87299');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('61', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784137791', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('62', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.1716530323028564;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784137794', 'run-end', '', '3171.65303');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('63', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784137795', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('64', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01765584945678711;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784137795', 'run-end', '', '17.65585');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('65', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784138737', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('66', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007704973220825;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784138739', 'run-end', '', '2007.70497');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('67', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784138739', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('68', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.176500082015991;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784138741', 'run-end', '', '2176.50008');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('69', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784138741', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('70', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.081166982650757;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784138745', 'run-end', '', '3081.16698');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('71', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784138745', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('72', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.020524978637695312;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784138745', 'run-end', '', '20.52498');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('73', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784140719', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('74', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0115950107574463;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784140721', 'run-end', '', '2011.59501');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('75', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784140721', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('76', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.3948988914489746;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784140724', 'run-end', '', '2394.89889');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('77', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784140724', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('78', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.7834951877593994;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784140728', 'run-end', '', '3783.49519');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('79', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784140729', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('80', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01686882972717285;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784140729', 'run-end', '', '16.86883');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('81', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784140850', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('82', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.006988763809204;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784140852', 'run-end', '', '2006.98876');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('83', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784140853', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('84', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1814661026000977;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784140855', 'run-end', '', '2181.46610');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('85', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784140856', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('86', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.5463008880615234;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784140859', 'run-end', '', '3546.30089');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('87', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784140859', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('88', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01592397689819336;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784140859', 'run-end', '', '15.92398');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('89', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784142584', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('90', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0076918601989746;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784142587', 'run-end', '', '2007.69186');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('91', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784142587', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('92', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2353718280792236;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784142589', 'run-end', '', '2235.37183');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('93', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784142590', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('94', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.185281991958618;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784142593', 'run-end', '', '3185.28199');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('95', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784142593', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('96', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01828908920288086;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784142593', 'run-end', '', '18.28909');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('97', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784142873', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('98', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007920026779175;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784142875', 'run-end', '', '2007.92003');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('99', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784142875', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('100', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.171754837036133;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784142877', 'run-end', '', '2171.75484');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('101', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784142878', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('102', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.464958906173706;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784142881', 'run-end', '', '3464.95891');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('103', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784142881', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('104', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02441692352294922;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784142881', 'run-end', '', '24.41692');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('105', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784143643', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('106', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0081019401550293;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784143645', 'run-end', '', '2008.10194');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('107', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784143645', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('108', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.181351900100708;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784143648', 'run-end', '', '2181.35190');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('109', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784143648', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('110', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.1205568313598633;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784143651', 'run-end', '', '3120.55683');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('111', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784143651', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('112', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.0163421630859375;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784143651', 'run-end', '', '16.34216');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('113', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784143683', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('114', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007319927215576;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784143685', 'run-end', '', '2007.31993');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('115', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784143686', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('116', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1793229579925537;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784143688', 'run-end', '', '2179.32296');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('117', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784143688', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('118', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.1044058799743652;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784143691', 'run-end', '', '3104.40588');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('119', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784143692', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('120', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.039520978927612305;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784143692', 'run-end', '', '39.52098');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('121', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784147038', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('122', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.030714988708496;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784147040', 'run-end', '', '2030.71499');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('123', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784147040', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('124', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.6681020259857178;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784147043', 'run-end', '', '2668.10203');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('125', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784147043', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('126', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.658997058868408;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784147047', 'run-end', '', '3658.99706');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('127', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784147047', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('128', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017283201217651367;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784147047', 'run-end', '', '17.28320');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('129', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784147980', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('130', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007622003555298;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784147982', 'run-end', '', '2007.62200');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('131', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784147982', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('132', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.176539897918701;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784147984', 'run-end', '', '2176.53990');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('133', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784147985', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('134', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.111909866333008;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784147988', 'run-end', '', '3111.90987');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('135', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784147988', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('136', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02435898780822754;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784147988', 'run-end', '', '24.35899');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('137', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784175309', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('138', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.014526128768921;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784175311', 'run-end', '', '2014.52613');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('139', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784175311', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('140', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.4429030418395996;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784175314', 'run-end', '', '2442.90304');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('141', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784175314', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('142', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.1698660850524902;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784175317', 'run-end', '', '3169.86609');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('143', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784175318', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('144', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017564058303833008;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784175318', 'run-end', '', '17.56406');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('145', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784175329', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('146', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007554054260254;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784175331', 'run-end', '', '2007.55405');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('147', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784175331', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('148', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2011029720306396;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784175333', 'run-end', '', '2201.10297');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('149', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784175333', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('150', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.048788070678711;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784175337', 'run-end', '', '4048.78807');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('151', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784175338', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('152', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.018039941787719727;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784175338', 'run-end', '', '18.03994');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('153', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784177753', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('154', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.597404956817627;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784177756', 'run-end', '', '2597.40496');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('155', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784177783', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('156', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1946840286254883;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784177785', 'run-end', '', '2194.68403');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('157', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784177813', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('158', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0157508850097656;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784177815', 'run-end', '', '2015.75089');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('159', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784177818', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('160', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.188271999359131;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784177820', 'run-end', '', '2188.27200');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('161', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784178129', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('162', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0079288482666016;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784178131', 'run-end', '', '2007.92885');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('163', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784178131', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('164', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1854758262634277;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784178133', 'run-end', '', '2185.47583');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('165', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784178133', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('166', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.2243850231170654;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784178137', 'run-end', '', '3224.38502');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('167', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784178137', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('168', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017635822296142578;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784178137', 'run-end', '', '17.63582');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('169', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784185908', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('170', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0152440071105957;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784185910', 'run-end', '', '2015.24401');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('171', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784185910', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('172', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.4704861640930176;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784185912', 'run-end', '', '2470.48616');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('173', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784185913', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('174', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.644545078277588;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784185916', 'run-end', '', '3644.54508');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('175', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784185917', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('176', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01918506622314453;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784185917', 'run-end', '', '19.18507');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('177', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784189198', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('178', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.426584005355835;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784189201', 'run-end', '', '2426.58401');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('179', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784189353', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('180', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0539917945861816;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784189355', 'run-end', '', '2053.99179');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('181', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784189376', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('182', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.679861068725586;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784189379', 'run-end', '', '2679.86107');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('183', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784190595', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('184', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0137369632720947;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784190598', 'run-end', '', '2013.73696');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('185', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784190598', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('186', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.657378911972046;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784190601', 'run-end', '', '2657.37891');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('187', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784190601', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('188', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.338371992111206;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784190606', 'run-end', '', '5338.37199');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('189', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784190607', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('190', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02850198745727539;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784190607', 'run-end', '', '28.50199');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('191', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784191613', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('192', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.009155035018921;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784191615', 'run-end', '', '2009.15504');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('193', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784191615', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('194', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.180651903152466;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784191618', 'run-end', '', '2180.65190');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('195', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784191618', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('196', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.0329179763793945;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784191622', 'run-end', '', '4032.91798');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('197', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784191622', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('198', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.03578901290893555;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784191623', 'run-end', '', '35.78901');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('199', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\CheckProtoCommand', '0', NULL, '0', '1784192234', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('200', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.00705409049987793;}\"', 'Modules\\ApiProto\\Console\\CheckProtoCommand', '0', NULL, '0', '1784192234', 'run-end', '', '7.05409');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('201', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\PathToMessageCommand', '0', NULL, '0', '1784209698', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('202', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.003459930419921875;}\"', 'Modules\\ApiProto\\Console\\PathToMessageCommand', '0', NULL, '0', '1784209698', 'run-end', '', '3.45993');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('203', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\MessageToPathCommand', '0', NULL, '0', '1784209715', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('204', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.00061798095703125;}\"', 'Modules\\ApiProto\\Console\\MessageToPathCommand', '0', NULL, '0', '1784209715', 'run-end', '', '0.61798');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('205', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210289', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('206', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.020189046859741;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210291', 'run-end', '', '2020.18905');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('207', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210291', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('208', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2057809829711914;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210294', 'run-end', '', '2205.78098');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('209', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210294', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('210', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.657309055328369;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210300', 'run-end', '', '5657.30906');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('211', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210300', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('212', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.04441380500793457;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210300', 'run-end', '', '44.41381');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('213', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210348', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('214', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0082199573516846;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210350', 'run-end', '', '2008.21996');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('215', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210350', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('216', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2153518199920654;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210353', 'run-end', '', '2215.35182');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('217', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210353', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('218', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.555130958557129;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210357', 'run-end', '', '3555.13096');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('219', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210357', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('220', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.019707918167114258;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210357', 'run-end', '', '19.70792');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('221', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210491', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('222', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.00944185256958;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210493', 'run-end', '', '2009.44185');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('223', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210494', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('224', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2772700786590576;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210496', 'run-end', '', '2277.27008');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('225', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210496', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('226', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.6746668815612793;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210500', 'run-end', '', '3674.66688');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('227', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210501', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('228', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.022110939025878906;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210501', 'run-end', '', '22.11094');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('229', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210625', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('230', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.010549783706665;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784210627', 'run-end', '', '2010.54978');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('231', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210628', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('232', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.3912041187286377;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784210630', 'run-end', '', '2391.20412');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('233', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210631', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('234', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.254771947860718;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784210635', 'run-end', '', '4254.77195');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('235', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210635', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('236', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017998933792114258;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784210635', 'run-end', '', '17.99893');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('237', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784218480', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('238', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0134270191192627;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784218482', 'run-end', '', '2013.42702');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('239', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784218482', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('240', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.7571640014648438;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784218485', 'run-end', '', '2757.16400');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('241', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784218485', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('242', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.013828992843628;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784218490', 'run-end', '', '5013.82899');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('243', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784218491', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('244', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01774907112121582;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784218491', 'run-end', '', '17.74907');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('245', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232178', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('246', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0154600143432617;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232180', 'run-end', '', '2015.46001');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('247', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784232180', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('248', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.428708076477051;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784232182', 'run-end', '', '2428.70808');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('249', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784232183', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('250', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.3701300621032715;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784232188', 'run-end', '', '5370.13006');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('251', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784232188', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('252', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017700910568237305;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784232188', 'run-end', '', '17.70091');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('253', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232452', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('254', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007999897003174;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232454', 'run-end', '', '2007.99990');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('255', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784232454', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('256', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.194898843765259;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784232456', 'run-end', '', '2194.89884');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('257', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784232456', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('258', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.395118236541748;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784232460', 'run-end', '', '3395.11824');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('259', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784232460', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('260', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01642608642578125;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784232460', 'run-end', '', '16.42609');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('261', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232819', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('262', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0056350231170654;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232821', 'run-end', '', '2005.63502');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('263', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232927', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('264', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0055079460144043;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232929', 'run-end', '', '2005.50795');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('265', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232956', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('266', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.009474992752075;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232958', 'run-end', '', '2009.47499');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('267', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232982', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('268', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0078439712524414;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784232984', 'run-end', '', '2007.84397');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('269', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784232984', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('270', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1925439834594727;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784232986', 'run-end', '', '2192.54398');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('271', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784232986', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('272', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.9883360862731934;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784232989', 'run-end', '', '2988.33609');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('273', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784232990', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('274', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01885390281677246;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784232990', 'run-end', '', '18.85390');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('275', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784255023', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('276', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.009876012802124;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784255025', 'run-end', '', '2009.87601');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('277', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784255026', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('278', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1870410442352295;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784255028', 'run-end', '', '2187.04104');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('279', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784255028', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('280', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.464231014251709;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784255032', 'run-end', '', '4464.23101');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('281', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784255033', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('282', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02269601821899414;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784255033', 'run-end', '', '22.69602');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('283', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784275802', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('284', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0077810287475586;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784275804', 'run-end', '', '2007.78103');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('285', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784275804', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('286', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.89300799369812;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784275807', 'run-end', '', '2893.00799');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('287', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784275807', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('288', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.6597468852996826;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784275811', 'run-end', '', '3659.74689');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('289', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784275811', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('290', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01645684242248535;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784275811', 'run-end', '', '16.45684');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('291', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784283797', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('292', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.011075973510742;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784283799', 'run-end', '', '2011.07597');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('293', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784283799', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('294', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.597759962081909;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784283802', 'run-end', '', '2597.75996');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('295', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784283802', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('296', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.085944890975952;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784283805', 'run-end', '', '3085.94489');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('297', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784283806', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('298', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01919698715209961;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784283806', 'run-end', '', '19.19699');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('299', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784285169', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('300', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.02138090133667;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784285171', 'run-end', '', '2021.38090');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('301', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784285171', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('302', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.7303810119628906;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784285175', 'run-end', '', '3730.38101');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('303', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784285175', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('304', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:15.742944955825806;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784285191', 'run-end', '', '15742.94496');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('305', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784285191', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('306', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.035041093826293945;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784285191', 'run-end', '', '35.04109');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('307', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784287515', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('308', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017894983291625977;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784287515', 'run-end', '', '17.89498');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('309', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784287644', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('310', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.018886089324951172;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784287644', 'run-end', '', '18.88609');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('311', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784288422', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('312', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0102741718292236;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784288424', 'run-end', '', '2010.27417');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('313', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784288424', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('314', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.6545889377593994;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784288427', 'run-end', '', '2654.58894');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('315', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784288428', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('316', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.442360877990723;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784288432', 'run-end', '', '4442.36088');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('317', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784288440', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('318', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.09470701217651367;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784288440', 'run-end', '', '94.70701');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('319', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784289265', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('320', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.1970219612121582;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784289265', 'run-end', '', '197.02196');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('321', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784289270', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('322', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.1987171173095703;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784289270', 'run-end', '', '198.71712');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('323', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784290475', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('324', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.1392519474029541;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784290475', 'run-end', '', '139.25195');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('325', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784290502', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('326', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.14354991912841797;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784290502', 'run-end', '', '143.54992');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('327', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784290531', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('328', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.008064985275268555;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784290532', 'run-end', '', '8.06499');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('329', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784291205', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('330', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.008141040802001953;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784291205', 'run-end', '', '8.14104');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('331', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784291388', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('332', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.012950897216797;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784291390', 'run-end', '', '2012.95090');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('333', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784291391', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('334', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.495652914047241;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784291393', 'run-end', '', '2495.65291');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('335', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784291394', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('336', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.5272698402404785;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784291398', 'run-end', '', '4527.26984');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('337', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784291398', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('338', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017010927200317383;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784291399', 'run-end', '', '17.01093');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('339', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\CheckProtoCommand', '0', NULL, '0', '1784291442', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('340', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.003838062286376953;}\"', 'Modules\\ApiProto\\Console\\CheckProtoCommand', '0', NULL, '0', '1784291442', 'run-end', '', '3.83806');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('341', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784292208', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('342', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0082550048828125;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784292210', 'run-end', '', '2008.25500');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('343', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784292210', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('344', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.194556951522827;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784292212', 'run-end', '', '2194.55695');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('345', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784292212', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('346', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.541203022003174;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784292217', 'run-end', '', '4541.20302');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('347', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784292217', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('348', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016932964324951172;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784292217', 'run-end', '', '16.93296');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('349', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784292219', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('350', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.008840084075927734;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784292219', 'run-end', '', '8.84008');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('351', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784293037', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('352', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0103561878204346;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784293039', 'run-end', '', '2010.35619');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('353', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784293039', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('354', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.766231060028076;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784293042', 'run-end', '', '2766.23106');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('355', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784293042', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('356', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.41124701499939;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784293048', 'run-end', '', '5411.24701');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('357', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784293048', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('358', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016869068145751953;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784293048', 'run-end', '', '16.86907');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('359', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784293220', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('360', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.009192943572998047;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784293220', 'run-end', '', '9.19294');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('361', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784303615', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('362', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.13242506980895996;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784303615', 'run-end', '', '132.42507');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('363', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784309132', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('364', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.1433238983154297;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784309132', 'run-end', '', '143.32390');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('365', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784439541', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('366', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.019845962524414062;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784439541', 'run-end', '', '19.84596');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('367', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784484471', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('368', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0142171382904053;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784484473', 'run-end', '', '2014.21714');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('369', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784484473', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('370', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.530884027481079;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784484475', 'run-end', '', '2530.88403');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('371', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784484476', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('372', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:6.155824184417725;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784484482', 'run-end', '', '6155.82418');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('373', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784484482', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('374', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.03166007995605469;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784484482', 'run-end', '', '31.66008');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('375', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784484878', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('376', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.056633949279785;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784484880', 'run-end', '', '2056.63395');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('377', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784484881', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('378', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.519343852996826;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784484885', 'run-end', '', '3519.34385');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('379', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784484886', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('380', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:7.966855049133301;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784484894', 'run-end', '', '7966.85505');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('381', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784484894', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('382', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02794194221496582;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784484894', 'run-end', '', '27.94194');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('383', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784485097', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('384', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.016111135482788;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784485099', 'run-end', '', '2016.11114');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('385', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784485099', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('386', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.6675209999084473;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784485103', 'run-end', '', '3667.52100');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('387', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784485104', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('388', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:8.223944902420044;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784485112', 'run-end', '', '8223.94490');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('389', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784485112', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('390', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.031588077545166016;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784485112', 'run-end', '', '31.58808');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('391', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784485487', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('392', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.1842958927154541;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784485487', 'run-end', '', '184.29589');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('393', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784485635', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('394', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0157859325408936;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784485638', 'run-end', '', '2015.78593');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('395', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784485638', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('396', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.472764015197754;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784485641', 'run-end', '', '2472.76402');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('397', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784485641', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('398', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:6.077589988708496;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784485647', 'run-end', '', '6077.58999');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('399', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784485647', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('400', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017162084579467773;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784485647', 'run-end', '', '17.16208');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('401', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784488336', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('402', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0076990127563477;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784488338', 'run-end', '', '2007.69901');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('403', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784488338', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('404', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.3975579738616943;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784488340', 'run-end', '', '2397.55797');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('405', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784488341', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('406', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.1059160232543945;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784488346', 'run-end', '', '5105.91602');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('407', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784488346', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('408', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.03310894966125488;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784488346', 'run-end', '', '33.10895');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('409', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784488530', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('410', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.00716090202331543;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784488530', 'run-end', '', '7.16090');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('411', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784648246', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('412', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.01397705078125;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784648249', 'run-end', '', '2013.97705');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('413', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784648249', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('414', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.4148411750793457;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784648251', 'run-end', '', '2414.84118');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('415', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784648252', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('416', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.994050979614258;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784648255', 'run-end', '', '2994.05098');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('417', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784648255', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('418', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017007112503051758;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784648255', 'run-end', '', '17.00711');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('419', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784648645', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('420', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.5760130882263184;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784648648', 'run-end', '', '2576.01309');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('421', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784711466', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('422', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0156989097595215;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784711468', 'run-end', '', '2015.69891');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('423', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784711468', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('424', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.5534958839416504;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784711471', 'run-end', '', '2553.49588');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('425', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784711471', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('426', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.743088006973267;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784711477', 'run-end', '', '5743.08801');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('427', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784711477', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('428', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.034304141998291016;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784711477', 'run-end', '', '34.30414');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('429', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784711487', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('430', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.007647991180419922;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784711487', 'run-end', '', '7.64799');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('431', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827046', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('432', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.015695095062256;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827049', 'run-end', '', '2015.69510');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('433', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827049', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('434', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.349332094192505;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827051', 'run-end', '', '2349.33209');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('435', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827052', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('436', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:5.196760892868042;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827057', 'run-end', '', '5196.76089');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('437', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827057', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('438', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016932964324951172;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827057', 'run-end', '', '16.93296');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('439', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827110', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('440', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007951021194458;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827112', 'run-end', '', '2007.95102');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('441', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827112', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('442', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.172437906265259;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827114', 'run-end', '', '2172.43791');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('443', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827114', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('444', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.0198380947113037;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827118', 'run-end', '', '3019.83809');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('445', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827118', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('446', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016489028930664062;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827118', 'run-end', '', '16.48903');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('447', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827146', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('448', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0100631713867188;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827148', 'run-end', '', '2010.06317');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('449', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827148', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('450', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.170234203338623;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827150', 'run-end', '', '2170.23420');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('451', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827150', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('452', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.052058219909668;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827153', 'run-end', '', '3052.05822');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('453', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827154', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('454', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.017258882522583008;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827154', 'run-end', '', '17.25888');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('455', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827199', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('456', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0080008506774902;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827202', 'run-end', '', '2008.00085');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('457', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827202', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('458', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1766438484191895;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827204', 'run-end', '', '2176.64385');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('459', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827204', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('460', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.01685094833374;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827208', 'run-end', '', '4016.85095');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('461', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827209', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('462', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01658797264099121;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827209', 'run-end', '', '16.58797');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('463', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827303', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('464', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0088319778442383;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827305', 'run-end', '', '2008.83198');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('465', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827306', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('466', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.2247679233551025;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827308', 'run-end', '', '2224.76792');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('467', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827308', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('468', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.057732105255127;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827311', 'run-end', '', '3057.73211');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('469', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827312', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('470', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.019572019577026367;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827312', 'run-end', '', '19.57202');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('471', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827366', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('472', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0076749324798584;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827368', 'run-end', '', '2007.67493');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('473', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827368', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('474', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.197949171066284;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827370', 'run-end', '', '2197.94917');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('475', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827371', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('476', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.0550730228424072;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827374', 'run-end', '', '3055.07302');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('477', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827374', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('478', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.016089916229248047;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827374', 'run-end', '', '16.08992');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('479', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827421', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('480', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.007382869720459;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827423', 'run-end', '', '2007.38287');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('481', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827492', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('482', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0105810165405273;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784827494', 'run-end', '', '2010.58102');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('483', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827494', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('484', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.1776020526885986;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784827496', 'run-end', '', '2177.60205');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('485', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827497', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('486', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:4.633605003356934;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784827501', 'run-end', '', '4633.60500');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('487', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827502', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('488', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.03383922576904297;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784827502', 'run-end', '', '33.83923');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('489', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827557', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('490', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.1483769416809082;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827557', 'run-end', '', '148.37694');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('491', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827766', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('492', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.0075609683990478516;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827766', 'run-end', '', '7.56097');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('493', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827769', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('494', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.007209062576293945;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827769', 'run-end', '', '7.20906');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('495', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827986', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('496', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.007528781890869141;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784827986', 'run-end', '', '7.52878');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('497', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784868572', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('498', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.579862117767334;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784868573', 'run-end', '', '579.86212');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('499', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784891302', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('500', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.0300538539886475;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784891304', 'run-end', '', '2030.05385');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('501', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784891305', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('502', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:3.349135160446167;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784891308', 'run-end', '', '3349.13516');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('503', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784891308', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('504', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:7.756752967834473;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784891316', 'run-end', '', '7756.75297');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('505', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784891317', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('506', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.03399181365966797;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784891317', 'run-end', '', '33.99181');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('507', 'Console', '\"a:0:{}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784900549', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('508', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.011242866516113281;}\"', 'Modules\\Merchant2\\Console\\GetTokenCommand', '0', NULL, '0', '1784900549', 'run-end', '', '11.24287');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('509', 'Console', '\"a:0:{}\"', 'Modules\\DcatAdmin\\Console\\SyncAdminMenuCommand', '0', NULL, '0', '1784900878', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('510', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:1.0421321392059326;}\"', 'Modules\\DcatAdmin\\Console\\SyncAdminMenuCommand', '0', NULL, '0', '1784900879', 'run-end', '', '1042.13214');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('511', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784903578', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('512', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.024180889129638672;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784903578', 'run-end', '', '24.18089');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('513', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784903662', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('514', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.02054286003112793;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784903662', 'run-end', '', '20.54286');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('515', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784909054', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('516', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.013400077819824;}\"', 'Modules\\ApiProto\\Console\\ProtoGatherCommand', '0', NULL, '0', '1784909056', 'run-end', '', '2013.40008');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('517', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784909056', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('518', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:2.4176290035247803;}\"', 'Modules\\ApiProto\\Console\\ProtoGenerateCommand', '0', NULL, '0', '1784909059', 'run-end', '', '2417.62900');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('519', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784909059', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('520', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:7.565741062164307;}\"', 'Modules\\ApiProto\\Console\\ProtoFixNamespaceCommand', '0', NULL, '0', '1784909066', 'run-end', '', '7565.74106');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('521', 'Console', '\"a:0:{}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784909067', 'run', '', '0.10000');
INSERT INTO `job_runs` (`id`, `queue`, `payload`, `runclass`, `attempts`, `reserved_at`, `available_at`, `created_at`, `status`, `desc`, `runtime`) VALUES ('522', 'Console', '\"a:1:{s:7:\\\"runtime\\\";d:0.01968693733215332;}\"', 'Modules\\ApiProto\\Console\\GeneratePathlistCommand', '0', NULL, '0', '1784909067', 'run-end', '', '19.68694');

-- 表结构: jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: merchant2_expiration_logs
DROP TABLE IF EXISTS `merchant2_expiration_logs`;
CREATE TABLE `merchant2_expiration_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `log_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日志类型：expired/expiring_soon/renewed/plan_changed/status_changed',
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info' COMMENT '级别：info/notice/warning/critical',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消息内容',
  `extra` json DEFAULT NULL COMMENT '附加信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_merchant_log_type` (`merchant_id`,`log_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='过期/预警日志表';

-- 表数据: merchant2_expiration_logs (2 行)
INSERT INTO `merchant2_expiration_logs` (`id`, `merchant_id`, `log_type`, `level`, `message`, `extra`, `created_at`, `updated_at`) VALUES ('1', '11', 'plan_changed', 'info', '开通套餐：测试套餐', '{\"remark\": \"\", \"plan_id\": 11, \"plan_code\": \"test_plan\", \"plan_name\": \"测试套餐\", \"expired_at\": \"2026-08-23 01:33:33\", \"previous_plan_name\": null}', '2026-07-24 01:33:33', '2026-07-24 01:33:33');
INSERT INTO `merchant2_expiration_logs` (`id`, `merchant_id`, `log_type`, `level`, `message`, `extra`, `created_at`, `updated_at`) VALUES ('2', '11', 'renewed', 'info', '续费套餐：测试套餐', '{\"remark\": \"\", \"plan_id\": 11, \"plan_code\": \"test_plan\", \"plan_name\": \"测试套餐\", \"duration_days\": 30, \"new_expired_at\": \"2026-09-22 01:33:33\", \"previous_plan_name\": \"测试套餐\", \"previous_expired_at\": \"2026-08-23 01:33:33\"}', '2026-07-24 01:33:45', '2026-07-24 01:33:45');

-- 表结构: merchant2_menu_diy
DROP TABLE IF EXISTS `merchant2_menu_diy`;
CREATE TABLE `merchant2_menu_diy` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `menu_id` bigint unsigned NOT NULL COMMENT '关联系统菜单ID',
  `custom_sort` int DEFAULT NULL COMMENT '商户自定义排序（NULL=使用系统默认）',
  `custom_pid` bigint unsigned DEFAULT NULL COMMENT '商户自定义父菜单ID（NULL=使用系统默认）',
  `is_hide` tinyint(1) DEFAULT NULL COMMENT '商户是否隐藏（NULL=使用系统默认）',
  `is_full` tinyint(1) DEFAULT NULL COMMENT '商户是否全屏（NULL=使用系统默认）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_merchant_id` (`merchant_id`),
  KEY `idx_menu_id` (`menu_id`),
  KEY `idx_merchant_menu_unique` (`merchant_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商户菜单自定义配置表（调整排序、层级、显示状态）';

-- 表结构: merchant2_menus
DROP TABLE IF EXISTS `merchant2_menus`;
CREATE TABLE `merchant2_menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `pid` bigint unsigned NOT NULL DEFAULT '0' COMMENT '父菜单ID（0=顶级菜单）',
  `type` enum('page','button') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'page' COMMENT '菜单类型: page=页面菜单, button=按钮权限',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单标识名（路由name）',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '路由路径',
  `component` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '组件路径',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '显示标题',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图标名称（Element Plus图标）',
  `api_paths` json DEFAULT NULL COMMENT '关联的API路径列表（JSON数组）',
  `permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '权限标识（如: user.create, order.delete）',
  `sort` int NOT NULL DEFAULT '0' COMMENT '默认排序值',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '菜单状态: active=启用, inactive=禁用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permission` (`permission`),
  KEY `idx_pid` (`pid`),
  KEY `idx_name` (`name`),
  KEY `idx_status` (`status`),
  KEY `idx_pid_sort` (`pid`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商户菜单表（支持页面菜单和按钮权限）';

-- 表数据: merchant2_menus (81 行)
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', '0', 'page', 'data', '/data/fill/consumeEnergy', '/data/fill/consumeEnergy/index', '数据采集', 'SetUp', NULL, NULL, '2', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', '2', 'page', 'fill', '/data/fill/consumeEnergy', '/data/fill/consumeEnergy/index', '数据填报', '', NULL, NULL, '3', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', '4', 'page', 'consumeEnergy', '/data/fill/consumeEnergy', '/data/fill/consumeEnergy/index', '能耗数据', '', NULL, NULL, '4', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6', '4', 'page', 'production', '/data/fill/production', '/data/fill/production/index', '产量产值', '', NULL, NULL, '8', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7', '4', 'page', 'material', '/data/fill/material', '/data/fill/material/index', '原辅材料', '', NULL, NULL, '12', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', '4', 'page', 'process', '/data/fill/process', '/data/fill/process/index', '生产过程', '', NULL, NULL, '16', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', '0', 'page', 'user', '/user/index', '/user/index', '用户管理', 'UserFilled', NULL, NULL, '70', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', '0', 'page', 'dept', '/user/dept', '/user/dept', '能源计量管理', 'OfficeBuilding', NULL, NULL, '74', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', '4', 'page', 'carbonStandard', '/data/fill/carbonStandard', '/data/fill/carbonStandard/index', '能碳标准', '', NULL, NULL, '19', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13', '35', 'page', 'supplierList', '/data/fill/supplierList', '/data/fill/supplierList/index', '供应商管理', '', NULL, NULL, '25', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14', '35', 'page', 'customerList', '/data/fill/customerList', '/data/fill/customerList/index', '客户管理', '', NULL, NULL, '30', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('15', '4', 'page', 'carbonAsset', '/data/fill/carbonAsset', '/data/fill/carbonAsset/index', '碳资产', '', NULL, NULL, '21', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('16', '36', 'page', 'exhaustEmission', '/data/fill/emission/exhaustEmission', '/data/fill/emission/exhaustEmission', '废气排放', '', NULL, NULL, '36', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('17', '36', 'page', 'wastewaterDischarge', '/data/fill/emission/wastewaterDischarge', '/data/fill/emission/wastewaterDischarge', '废水排放', '', NULL, NULL, '39', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('18', '36', 'page', 'solidWaste', '/data/fill/emission/solidWaste', '/data/fill/emission/solidWaste', '工业固体废物', '', NULL, NULL, '42', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('19', '0', 'page', 'function', '/data/function/energyConsumption', '/data/function/energyConsumption/index', '功能导航', 'DataAnalysis', NULL, NULL, '45', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('20', '19', 'page', 'energyConsumption', '/data/function/energyConsumption', '/data/function/energyConsumption/index', '能源消耗查询', '', NULL, NULL, '46', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('21', '19', 'page', 'energy', '/data/function/energy/TotalEnergyAnalysis', '/data/function/energy/TotalEnergyAnalysis', '能源消耗分析', '', NULL, NULL, '47', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('22', '21', 'page', 'TotalEnergyAnalysis', '/data/function/energy/TotalEnergyAnalysis', '/data/function/energy/TotalEnergyAnalysis', '综合能耗', '', NULL, NULL, '48', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('23', '21', 'page', 'EnergyTypeAnalysis', '/data/function/energy/EnergyTypeAnalysis', '/data/function/energy/EnergyTypeAnalysis', '用能类型', '', NULL, NULL, '49', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('24', '21', 'page', 'YoYAnalysis', '/data/function/energy/YoYAnalysis', '/data/function/energy/YoYAnalysis', '同比分析', '', NULL, NULL, '50', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('25', '21', 'page', 'MoMAnalysis', '/data/function/energy/MoMAnalysis', '/data/function/energy/MoMAnalysis', '环比分析', '', NULL, NULL, '51', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('28', '19', 'page', 'duibiao', '/data/function/duibiao/energyStandard', '/data/function/duibiao/energyStandard', '能碳对标', '', NULL, NULL, '52', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('29', '28', 'page', 'energyStandard', '/data/function/duibiao/energyStandard', '/data/function/duibiao/energyStandard', '能耗对标', '', NULL, NULL, '53', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('30', '28', 'page', 'duibiaoCarbon', '/data/function/duibiao/carbonStandard', '/data/function/duibiao/carbonStandard', '碳排放对标', '', NULL, NULL, '54', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('31', '19', 'page', 'nxphyh', '/data/function/nxphyh/energyFlow', '/data/function/nxphyh/energyFlow', '能效平衡及优化', '', NULL, NULL, '55', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('32', '31', 'page', 'energyFlow', '/data/function/nxphyh/energyFlow', '/data/function/nxphyh/energyFlow', '能流分析', '', NULL, NULL, '56', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('33', '31', 'page', 'energyBalance', '/data/function/nxphyh/energyBalance', '/data/function/nxphyh/energyBalance', '能源平衡', '', NULL, NULL, '57', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('34', '19', 'page', 'tpfgl', '/data/function/tpfgl/index', '/data/function/tpfgl/index', '碳排放管理', '', NULL, NULL, '58', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('35', '4', 'page', 'supplierList', '/data/fill/supplierList', '/data/fill/supplierList/index', '供应链管理', '', NULL, NULL, '24', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('36', '4', 'page', 'emission', '/data/fill/emission/exhaustEmission', '/data/fill/emission/exhaustEmission', '环境排放', '', NULL, NULL, '35', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('37', '41', 'page', 'supplier', '/data/function/supplierList', '/data/function/supplierList/index', '供应商', '', NULL, NULL, '62', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('38', '41', 'page', 'customer', '/data/function/customerList', '/data/function/customerList/index', '客户', '', NULL, NULL, '63', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('39', '19', 'page', 'carbonAsset_total', '/data/function/carbonAsset/index', '/data/function/carbonAsset/index', '碳资产', '', NULL, NULL, '59', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('40', '19', 'page', 'carbonFoot', '/data/function/carbonFoot/index', '/data/function/carbonFoot/index', '碳足迹', '', NULL, NULL, '60', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('41', '19', 'page', 'supplier', '/data/function/supplierList', '/data/function/supplierList/index', '供应链管理', '', NULL, NULL, '61', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('42', '47', 'page', 'dataExport', '/data/function/dataExport', '/data/function/dataExport/index', '碳核查报告', '', NULL, NULL, '68', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('43', '19', 'page', 'carbonBudeget', '/data/function/carbonBudeget', '/data/function/carbonBudeget/index', '能碳预算管理', '', NULL, NULL, '64', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('44', '43', 'page', 'ntys', '/data/function/carbonBudeget/index', '/data/function/carbonBudeget/index', '能耗预算', '', NULL, NULL, '65', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('45', '43', 'page', 'tpfys', '/data/function/carbonBudeget/tpfBudeget', '/data/function/carbonBudeget/tpfBudeget', '碳排放预算', '', NULL, NULL, '66', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('47', '19', 'page', 'export', '/data/function/dataExport', '/data/function/dataExport/index', '报告导出', '', NULL, NULL, '67', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('48', '47', 'page', 'transportExport', '/data/function/dataExport/transportExport', '/data/function/dataExport/transportExport', '碳足迹报告', '', NULL, NULL, '69', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('49', '5', 'page', 'add', '', '', '添加', '', NULL, NULL, '5', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('50', '5', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '6', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('51', '5', 'page', 'import', '', '', '导入', '', NULL, NULL, '7', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('52', '6', 'page', 'add', '', '', '添加', '', NULL, NULL, '9', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('53', '6', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '10', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('54', '6', 'page', 'import', '', '', '导入', '', NULL, NULL, '11', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('55', '7', 'page', 'add', '', '', '添加', '', NULL, NULL, '13', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('56', '7', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '14', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('57', '7', 'page', 'import', '', '', '导入', '', NULL, NULL, '15', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('58', '8', 'page', 'add', '', '', '添加', '', NULL, NULL, '17', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('59', '8', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '18', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('60', '12', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '20', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('61', '15', 'page', 'add', '', '', '添加', '', NULL, NULL, '22', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('62', '15', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '23', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('63', '13', 'page', 'add', '', '', '添加', '', NULL, NULL, '26', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('64', '13', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '27', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('65', '13', 'page', 'del', '', '', '删除', '', NULL, NULL, '28', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('66', '13', 'page', 'import', '', '', '导入', '', NULL, NULL, '29', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('67', '14', 'page', 'add', '', '', '添加', '', NULL, NULL, '31', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('68', '14', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '32', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('69', '14', 'page', 'del', '', '', '删除', '', NULL, NULL, '33', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('70', '14', 'page', 'import', '', '', '导入', '', NULL, NULL, '34', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('71', '16', 'page', 'add', '', '', '添加', '', NULL, NULL, '37', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('72', '16', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '38', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('73', '17', 'page', 'add', '', '', '添加', '', NULL, NULL, '40', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('74', '17', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '41', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('75', '18', 'page', 'add', '', '', '添加', '', NULL, NULL, '43', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('76', '18', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '44', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('77', '10', 'page', 'add', '', '', '添加', '', NULL, NULL, '71', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('78', '10', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '72', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('79', '10', 'page', 'del', '', '', '删除', '', NULL, NULL, '73', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('80', '11', 'page', 'add', '', '', '添加', '', NULL, NULL, '75', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('81', '11', 'page', 'edit', '', '', '编辑', '', NULL, NULL, '76', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('82', '11', 'page', 'del', '', '', '删除', '', NULL, NULL, '77', 'inactive', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('87', '0', 'page', 'dataScreen', '/dataScreen', '/dataScreen/index', '首页', 'DataAnalysis', NULL, NULL, '1', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('88', '0', 'page', 'statis', '/data/fill/statis', '/data/fill/statis/index', '生产情况管理', 'Operation', NULL, NULL, '78', 'active', '2026-07-16 03:29:22', '2026-07-16 03:36:33', NULL);
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('89', '0', 'button', 'energy:add', NULL, NULL, '添加能耗', NULL, NULL, 'energy:add', '1', 'active', '2026-07-16 15:24:49', '2026-07-16 15:25:44', '2026-07-16 15:25:44');
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('90', '0', 'button', 'energy:edit', NULL, NULL, '编辑能耗', NULL, NULL, 'energy:edit', '2', 'active', '2026-07-16 15:24:49', '2026-07-16 15:25:44', '2026-07-16 15:25:44');
INSERT INTO `merchant2_menus` (`id`, `pid`, `type`, `name`, `path`, `component`, `title`, `icon`, `api_paths`, `permission`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES ('91', '0', 'button', 'user:add', NULL, NULL, '添加用户', NULL, NULL, 'user:add', '3', 'active', '2026-07-16 15:24:49', '2026-07-16 15:25:44', '2026-07-16 15:25:44');

-- 表结构: merchant2_merchant_plans
DROP TABLE IF EXISTS `merchant2_merchant_plans`;
CREATE TABLE `merchant2_merchant_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchant_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `plan_id` bigint unsigned NOT NULL COMMENT '套餐ID',
  `plan_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐名称快照',
  `plan_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐代码快照',
  `price_snapshot` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格快照',
  `duration_days` int unsigned NOT NULL DEFAULT '0' COMMENT '有效天数快照',
  `started_at` timestamp NOT NULL COMMENT '开始时间',
  `expired_at` timestamp NULL DEFAULT NULL COMMENT '过期时间，null=永久',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态：active/expired/cancelled',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `quotas_snapshot` json DEFAULT NULL COMMENT '配额快照',
  `features_snapshot` json DEFAULT NULL COMMENT '功能快照',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_merchant_status` (`merchant_id`,`status`),
  KEY `idx_expired_at` (`expired_at`),
  KEY `idx_plan_id` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商户套餐订阅记录表';

-- 表数据: merchant2_merchant_plans (2 行)
INSERT INTO `merchant2_merchant_plans` (`id`, `merchant_id`, `plan_id`, `plan_name`, `plan_code`, `price_snapshot`, `duration_days`, `started_at`, `expired_at`, `status`, `remark`, `quotas_snapshot`, `features_snapshot`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', '11', '11', '测试套餐', 'test_plan', '99.00', '30', '2026-07-24 01:33:33', '2026-08-23 01:33:33', 'expired', '', '{\"max_users\": 10}', '{\"feature_a\": true}', '2026-07-24 01:33:33', '2026-07-24 01:33:45', NULL);
INSERT INTO `merchant2_merchant_plans` (`id`, `merchant_id`, `plan_id`, `plan_name`, `plan_code`, `price_snapshot`, `duration_days`, `started_at`, `expired_at`, `status`, `remark`, `quotas_snapshot`, `features_snapshot`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', '11', '11', '测试套餐', 'test_plan', '99.00', '30', '2026-07-24 01:33:45', '2026-09-22 01:33:33', 'active', '', '{\"max_users\": 10}', '{\"feature_a\": true}', '2026-07-24 01:33:45', '2026-07-24 01:33:45', NULL);

-- 表结构: merchant2_merchants
DROP TABLE IF EXISTS `merchant2_merchants`;
CREATE TABLE `merchant2_merchants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户名称',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户代码',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '商户描述',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商户Logo',
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商户横幅',
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系邮箱',
  `contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系电话',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT '商户地址',
  `status` enum('active','inactive','suspended','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive' COMMENT '商户状态',
  `settings` json DEFAULT NULL COMMENT '商户设置',
  `expired_at` timestamp NULL DEFAULT NULL COMMENT '过期时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant2_merchants_code_unique` (`code`),
  KEY `merchant2_merchants_status_index` (`status`),
  KEY `merchant2_merchants_expired_at_index` (`expired_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: merchant2_merchants (4 行)
INSERT INTO `merchant2_merchants` (`id`, `name`, `code`, `description`, `logo`, `banner`, `contact_email`, `contact_phone`, `address`, `status`, `settings`, `expired_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', '测试企业', 'TEST001', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-16 01:22:19', '2026-07-24 00:41:57', NULL);
INSERT INTO `merchant2_merchants` (`id`, `name`, `code`, `description`, `logo`, `banner`, `contact_email`, `contact_phone`, `address`, `status`, `settings`, `expired_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', '示例文具店', 'demo_stationery', '专门销售文具用品的零售店铺', NULL, NULL, 'contact@stationery.com', '13800138001', '北京市朝阳区文具街123号', 'active', '\"{\\\"business_hours\\\":\\\"09:00-18:00\\\",\\\"delivery\\\":true,\\\"payment_methods\\\":[\\\"cash\\\",\\\"alipay\\\",\\\"wechat\\\"]}\"', '2027-07-16 03:28:58', '2026-07-16 03:28:58', '2026-07-16 03:28:58', NULL);
INSERT INTO `merchant2_merchants` (`id`, `name`, `code`, `description`, `logo`, `banner`, `contact_email`, `contact_phone`, `address`, `status`, `settings`, `expired_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', '创意设计有限公司', 'creative_design', '专业的品牌设计和创意策划公司', NULL, NULL, 'info@creative.com', '13800138002', '上海市浦东新区创意园区456号', 'active', '\"{\\\"services\\\":[\\\"branding\\\",\\\"web_design\\\",\\\"marketing\\\"],\\\"team_size\\\":15,\\\"established_year\\\":2020}\"', '2027-07-16 03:29:00', '2026-07-16 03:29:00', '2026-07-16 03:29:00', NULL);
INSERT INTO `merchant2_merchants` (`id`, `name`, `code`, `description`, `logo`, `banner`, `contact_email`, `contact_phone`, `address`, `status`, `settings`, `expired_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', '西藏红墙烧结砖有限公司', 'XZHQ', '西藏红墙烧结砖有限公司', NULL, NULL, NULL, '13989014404', '西藏自治区拉萨市', 'active', NULL, '2026-09-22 01:33:33', '2026-07-16 03:29:22', '2026-07-24 01:33:45', NULL);

-- 表结构: merchant2_operation_logs
DROP TABLE IF EXISTS `merchant2_operation_logs`;
CREATE TABLE `merchant2_operation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_user_id` bigint unsigned DEFAULT NULL COMMENT '操作用户ID',
  `merchant_id` bigint unsigned DEFAULT NULL COMMENT '商户ID',
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作类型',
  `action_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作描述',
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作模型类型',
  `model_id` bigint unsigned DEFAULT NULL COMMENT '操作模型ID',
  `old_values` json DEFAULT NULL COMMENT '旧值',
  `new_values` json DEFAULT NULL COMMENT '新值',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户代理',
  `result` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success' COMMENT '操作结果',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `merchant2_operation_logs_merchant_user_id_index` (`merchant_user_id`),
  KEY `merchant2_operation_logs_merchant_id_index` (`merchant_id`),
  KEY `merchant2_operation_logs_action_type_index` (`action_type`),
  KEY `merchant2_operation_logs_result_index` (`result`),
  KEY `merchant2_operation_logs_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `merchant2_operation_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: merchant2_plans
DROP TABLE IF EXISTS `merchant2_plans`;
CREATE TABLE `merchant2_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐名称',
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐代码',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `duration_days` int unsigned NOT NULL DEFAULT '0' COMMENT '有效天数，0=永久',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态：active/inactive',
  `quotas` json DEFAULT NULL COMMENT '配额定义',
  `features` json DEFAULT NULL COMMENT '功能开关',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plan_code` (`code`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐定义表';

-- 表数据: merchant2_plans (5 行)
INSERT INTO `merchant2_plans` (`id`, `name`, `code`, `description`, `duration_days`, `price`, `sort`, `status`, `quotas`, `features`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', '年度套餐', 'yearly_plan', NULL, '365', '100.00', '0', 'active', '{\"max_users\": 50}', '{\"api_access\": true}', '2026-07-24 00:41:57', '2026-07-24 00:41:57', '2026-07-24 00:41:57');
INSERT INTO `merchant2_plans` (`id`, `name`, `code`, `description`, `duration_days`, `price`, `sort`, `status`, `quotas`, `features`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', '测试套餐', 'test_plan', '测试用套餐', '30', '99.00', '0', 'active', '{\"max_users\": 10}', '{\"feature_a\": true}', '2026-07-24 01:33:27', '2026-07-24 01:33:27', NULL);
INSERT INTO `merchant2_plans` (`id`, `name`, `code`, `description`, `duration_days`, `price`, `sort`, `status`, `quotas`, `features`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', '基础版', 'basic', '适合小型企业，提供基础能碳管理功能', '365', '0.00', '1', 'active', '{\"max_users\": 5, \"max_enterprises\": 1, \"max_energy_types\": 3, \"data_retention_days\": 90}', '{\"api_access\": false, \"basic_report\": true, \"custom_dashboard\": false, \"advanced_analysis\": false, \"energy_monitoring\": true, \"carbon_calculation\": true}', '2026-07-24 01:45:14', '2026-07-24 01:45:14', NULL);
INSERT INTO `merchant2_plans` (`id`, `name`, `code`, `description`, `duration_days`, `price`, `sort`, `status`, `quotas`, `features`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13', '专业版', 'professional', '适合中型企业，提供专业能碳管理与分析功能', '365', '9999.00', '2', 'active', '{\"max_users\": 50, \"max_enterprises\": 10, \"max_energy_types\": 20, \"data_retention_days\": 365}', '{\"api_access\": true, \"basic_report\": true, \"custom_dashboard\": true, \"advanced_analysis\": true, \"energy_monitoring\": true, \"carbon_calculation\": true}', '2026-07-24 01:45:16', '2026-07-24 01:45:16', NULL);
INSERT INTO `merchant2_plans` (`id`, `name`, `code`, `description`, `duration_days`, `price`, `sort`, `status`, `quotas`, `features`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14', '旗舰版', 'flagship', '适合大型集团，全功能无限制永久使用', '0', '29999.00', '3', 'active', '{\"max_users\": -1, \"max_enterprises\": -1, \"max_energy_types\": -1, \"data_retention_days\": -1}', '{\"api_access\": true, \"basic_report\": true, \"custom_dashboard\": true, \"priority_support\": true, \"advanced_analysis\": true, \"energy_monitoring\": true, \"carbon_calculation\": true, \"custom_integration\": true}', '2026-07-24 01:45:17', '2026-07-24 01:45:17', NULL);

-- 表结构: merchant2_role_menus
DROP TABLE IF EXISTS `merchant2_role_menus`;
CREATE TABLE `merchant2_role_menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `role_id` bigint unsigned NOT NULL COMMENT '角色ID',
  `menu_id` bigint unsigned NOT NULL COMMENT '菜单ID（仅关联type=button）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_menu` (`role_id`,`menu_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_menu_id` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色菜单按钮权限关联表';

-- 表结构: merchant2_roles
DROP TABLE IF EXISTS `merchant2_roles`;
CREATE TABLE `merchant2_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint unsigned NOT NULL COMMENT '所属商户',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色显示名称',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '角色描述',
  `level` tinyint NOT NULL DEFAULT '1' COMMENT '角色等级，数字越大权限越高',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否激活',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant2_roles_merchant_id_name_unique` (`merchant_id`,`name`),
  KEY `merchant2_roles_merchant_id_index` (`merchant_id`),
  KEY `merchant2_roles_level_index` (`level`),
  KEY `merchant2_roles_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: merchant2_roles (1 行)
INSERT INTO `merchant2_roles` (`id`, `merchant_id`, `name`, `display_name`, `description`, `level`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', '0', 'merchant_admin', '商户管理员', '拥有商户管理、用户管理、角色管理的全部权限', '10', '1', '2026-07-16 03:28:58', '2026-07-16 03:28:58', NULL);

-- 表结构: merchant2_user_roles
DROP TABLE IF EXISTS `merchant2_user_roles`;
CREATE TABLE `merchant2_user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_user_id` bigint unsigned NOT NULL COMMENT '商户用户ID',
  `role_id` bigint unsigned NOT NULL COMMENT '角色ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant2_user_roles_merchant_user_id_role_id_unique` (`merchant_user_id`,`role_id`),
  KEY `merchant2_user_roles_merchant_user_id_index` (`merchant_user_id`),
  KEY `merchant2_user_roles_role_id_index` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: merchant2_user_roles (4 行)
INSERT INTO `merchant2_user_roles` (`id`, `merchant_user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('1', '2', '1', '2026-07-16 03:28:59', '2026-07-16 03:28:59');
INSERT INTO `merchant2_user_roles` (`id`, `merchant_user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('2', '5', '1', '2026-07-16 03:29:00', '2026-07-16 03:29:00');
INSERT INTO `merchant2_user_roles` (`id`, `merchant_user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('3', '8', '1', '2026-07-16 03:29:01', '2026-07-16 03:29:01');
INSERT INTO `merchant2_user_roles` (`id`, `merchant_user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('4', '1', '2', '2026-07-16 15:25:05', '2026-07-16 15:25:05');

-- 表结构: merchant2_users
DROP TABLE IF EXISTS `merchant2_users`;
CREATE TABLE `merchant2_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户姓名',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱地址',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '手机号',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `merchant_id` bigint unsigned NOT NULL COMMENT '所属商户',
  `user_id` bigint unsigned DEFAULT NULL COMMENT '关联用户ID（User1模块）',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive' COMMENT '用户状态',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '最后登录IP',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant2_users_username_unique` (`username`),
  KEY `merchant2_users_merchant_id_index` (`merchant_id`),
  KEY `merchant2_users_status_index` (`status`),
  KEY `merchant2_users_last_login_at_index` (`last_login_at`),
  KEY `merchant2_users_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: merchant2_users (9 行)
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', '测试管理员', 'testadmin', NULL, NULL, '$2y$12$bJFWpoKRayC7bVeXoSiCFuU.TslD520isZeBuAEFONysSYzTS/Wwm', '1', NULL, 'active', NULL, NULL, NULL, '2026-07-16 01:22:20', '2026-07-16 15:19:35', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', '张三', '91110000MA01G1K42X', NULL, NULL, '$2y$12$6I3lRYfHH4GlPUu91NHn.u155QqdordspLDdBnVD7IOnCS7QVIL0a', '2', NULL, 'active', '2026-07-16 02:28:59', NULL, NULL, '2026-07-15 03:28:59', '2026-07-15 05:28:59', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', '李四', '91110000MA01G1K42A', NULL, NULL, '$2y$12$Tl2AA2z.xGtxPnhUWAolvec4Bt9DSgMnhZrAsBwoHiEhpILVJm2oq', '2', NULL, 'active', '2026-07-16 02:28:59', NULL, NULL, '2026-06-21 03:28:59', '2026-07-15 07:28:59', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', '王五', '91110000MA01G1K42B', NULL, NULL, '$2y$12$.6MOavnrFRcrwIHBcmsP.uVZenp7..s/hRFegZ71fyYY/mNMBjqye', '2', NULL, 'active', '2026-07-15 15:28:59', NULL, NULL, '2026-06-30 03:28:59', '2026-07-15 17:28:59', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', '赵设计', '91310000MA1G2KJ39D', NULL, NULL, '$2y$12$86cteU86/NEzcxOP0i/bZu6uxrcKHRrf7.65nHt1HlmmJAtXK1VAK', '3', NULL, 'active', '2026-07-15 14:29:00', NULL, NULL, '2026-07-03 03:29:00', '2026-07-15 22:29:00', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6', '孙设计师1', '91310000MA1G2KJ39A', NULL, NULL, '$2y$12$ESGmjU0oa5k3Ve9MWio/7.CGck.LDBW.yP7W1Lavk5LBSovYdxOfu', '3', NULL, 'active', '2026-07-16 02:29:00', NULL, NULL, '2026-06-26 03:29:00', '2026-07-16 02:29:00', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7', '周设计师2', '91310000MA1G2KJ39B', NULL, NULL, '$2y$12$IMaeWvG2WtsOq4xuLOKfVePRf5M1nZxQai/.Mi0HQ9khhQlpAXiIe', '3', NULL, 'active', '2026-07-15 07:29:01', NULL, NULL, '2026-06-22 03:29:01', '2026-07-15 23:29:01', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', '钱人事', '91310000MA1G2KJ39C', NULL, NULL, '$2y$12$nUKaFlICS.dKRw4XRDsqJOrox9ooaVeGV6wPm3geckHJKUffr8vQi', '3', NULL, 'active', '2026-07-15 10:29:01', NULL, NULL, '2026-06-19 03:29:01', '2026-07-15 12:29:01', NULL);
INSERT INTO `merchant2_users` (`id`, `name`, `username`, `phone`, `email`, `password`, `merchant_id`, `user_id`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', '系统管1', '915400007419182738', '13034632222', '15148848@qq.com', '$2y$12$qg02JYZCX4dsfxvBJTWlvuEeuOu8RHiSRDw8H3lQTPdMfb/M2yZf2', '11', NULL, 'active', '2026-07-24 15:37:46', '192.168.4.163', NULL, '2026-07-16 03:29:22', '2026-07-24 15:37:46', NULL);

-- 表结构: migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: migrations (160 行)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '2016_01_04_173148_create_admin_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '2020_09_07_090635_create_admin_settings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2020_09_22_015815_create_admin_extensions_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2020_11_01_083237_update_admin_menu_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2024_12_07_143400_create_cleanup_plans_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2024_12_07_143500_create_cleanup_backup_files_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2024_12_07_143600_create_cleanup_backups_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2024_12_07_143700_create_cleanup_configs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2024_12_07_143800_create_cleanup_logs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2024_12_07_143900_create_cleanup_plan_contents_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2024_12_07_144000_create_cleanup_tasks_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2024_12_07_144100_create_cleanup_sql_backups_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2024_12_07_144200_create_cleanup_table_stats_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2025_11_05_002721_create_demo5_posts_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2025_11_05_002740_create_demo5_comments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2025_11_08_174943_create_merchants_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2025_11_08_174945_create_merchant_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('19', '2025_11_08_180001_create_merchant_roles_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('20', '2025_11_08_180004_create_merchant_user_roles_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('21', '2025_11_08_180006_create_merchant_operation_logs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('22', '2025_11_09_010320_create_notifications_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('23', '2025_11_29_135213_create_application_configs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('24', '2025_11_29_135955_create_application_continuous_times_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('25', '2025_11_29_140402_create_failed_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('26', '2025_11_29_140843_create_job_runs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('27', '2025_11_29_182457_create_admin_grid_views_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('28', '2025_11_29_182458_create_admin_action_logs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('29', '2025_11_29_221500_create_application_system_logs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('30', '2025_12_07_000000_create_point_currency_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('31', '2025_12_07_000001_create_point_config_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('32', '2025_12_07_000002_create_point_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('33', '2025_12_07_000003_create_point_logs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('34', '2025_12_07_000004_create_point_admin_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('35', '2025_12_07_000005_create_point_circulation_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('36', '2025_12_07_000006_create_point_transfer_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('37', '2025_12_07_000007_create_point_order_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('38', '2025_12_10_110000_add_account_id_to_merchant_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('39', '2026_04_19_153837_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('40', '2026_04_26_000001_create_demo5_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('41', '2026_04_26_091900_remove_foreign_key_from_admin_grid_views_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('42', '2026_04_29_000001_create_file_files_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('43', '2026_04_29_000002_create_file_imgs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('44', '2026_05_01_113627_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('45', '2026_05_01_114038_create_sessions_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('46', '2026_05_04_234019_create_file_storage_configs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('47', '2026_05_04_234020_create_file_storage_config_histories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('48', '2026_05_04_234022_create_file_template_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('49', '2026_05_05_000001_add_dangling_tracking_fields', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('50', '2026_05_05_143419_create_ai_providers_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('51', '2026_05_05_143441_create_ai_provider_models_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('52', '2026_05_05_143500_create_ai_conversations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('53', '2026_05_05_143500_create_ai_images_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('54', '2026_05_05_143500_create_ai_tests_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('55', '2026_05_05_143501_create_ai_test_results_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('56', '2026_05_19_093532_create_features_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('57', '2026_05_21_100001_create_article_cates_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('58', '2026_05_21_100002_create_articles_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('59', '2026_06_29_100000_rename_account_id_to_user_id_in_merchant_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('60', '2026_07_03_000001_create_energy_category_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('61', '2026_07_03_000002_create_energy_item_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('62', '2026_07_03_000003_create_energy_price_tactics_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('63', '2026_07_03_000004_create_energy_price_tactics_item_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('64', '2026_07_03_000005_create_energy_price_relevancy_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('65', '2026_07_03_000006_create_peak_valley_scheme_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('66', '2026_07_03_000007_create_peak_valley_item_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('67', '2026_07_03_000008_create_tariff_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('68', '2026_07_03_000009_create_tariff_time_of_use_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('69', '2026_07_03_000010_create_energy_data_hourly_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('70', '2026_07_03_000011_create_energy_data_daily_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('71', '2026_07_03_000012_create_energy_data_monthly_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('72', '2026_07_03_000013_create_energy_data_yearly_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('73', '2026_07_03_000014_create_energy_data_peak_valley_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('74', '2026_07_03_000015_create_formula_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('75', '2026_07_03_000016_create_formula_param_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('76', '2026_07_03_000017_create_benchmark_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('77', '2026_07_03_000018_create_energy_saving_project_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('78', '2026_07_03_000019_create_product_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('79', '2026_07_03_000020_create_product_output_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('80', '2026_07_03_000021_create_energy_cost_records_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('81', '2026_07_03_000022_create_energy_bills_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('82', '2026_07_03_000023_create_energy_cost_statistics_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('83', '2026_07_03_000024_create_energy_budgets_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('84', '2026_07_04_000001_create_carbon_factor_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('85', '2026_07_04_000002_create_carbon_emission_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('86', '2026_07_04_000003_create_carbon_scheme_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('87', '2026_07_04_000004_create_carbon_scheme_item_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('88', '2026_07_04_000005_create_carbon_quota_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('89', '2026_07_04_000006_create_carbon_trade_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('90', '2026_07_04_000007_create_carbon_footprint_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('91', '2026_07_04_000008_create_carbon_footprint_detail_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('92', '2026_07_04_000009_create_carbon_reduction_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('93', '2026_07_04_000010_create_carbon_report_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('94', '2026_07_04_000011_add_company_id_to_carbon_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('95', '2026_07_05_085644_create_enterprise_companies_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('96', '2026_07_05_090953_create_nt_process_categories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('97', '2026_07_05_090953_create_nt_processes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('98', '2026_07_05_090953_create_nt_product_categories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('99', '2026_07_05_090953_create_nt_products_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('100', '2026_07_05_091951_create_nt_material_categories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('101', '2026_07_05_091952_create_nt_factors_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('102', '2026_07_05_091953_create_nt_materials_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('103', '2026_07_05_091954_create_nt_material_records_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('104', '2026_07_05_091955_create_nt_material_monthlies_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('105', '2026_07_05_091956_create_enterprise_depts_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('106', '2026_07_05_092458_create_nt_emission_records_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('107', '2026_07_05_092458_create_nt_emission_sources_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('108', '2026_07_05_092458_create_nt_emission_summaries_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('109', '2026_07_05_100000_create_nt_product_data_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('110', '2026_07_05_100001_create_nt_process_co3_decomposition_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('111', '2026_07_05_100002_create_nt_process_coal_replace_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('112', '2026_07_06_000001_create_report_template_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('113', '2026_07_06_000002_create_report_task_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('114', '2026_07_06_000003_create_report_file_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('115', '2026_07_06_000004_create_stat_indicator_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('116', '2026_07_06_000005_create_stat_data_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('117', '2026_07_06_000006_create_stat_config_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('118', '2026_07_09_000001_create_carbon_monthly_aggregate_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('119', '2026_07_09_000002_create_carbon_daily_aggregate_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('120', '2026_07_09_100004_add_deleted_at_to_merchant2_roles_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('121', '2026_07_09_100005_add_deleted_at_to_merchant2_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('122', '2026_07_12_100000_rename_merchant1_to_merchant2_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('123', '2026_07_13_100000_rename_email_to_username_in_merchant2_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('124', '2026_07_13_164729_create_merchant2_menus_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('125', '2026_07_13_164757_create_merchant2_menu_diy_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('126', '2026_07_14_100000_create_merchant2_role_menus_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('127', '2026_07_14_100001_migrate_role_permissions_to_menus', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('128', '2026_07_14_100002_drop_old_permission_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('129', '2026_07_15_000001_add_output_emission_to_carbon_monthly_aggregate_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('130', '2026_07_15_040824_add_missing_fields_to_enterprise_companies_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('131', '2026_07_16_000001_create_enterprise_customers_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('132', '2026_07_16_000002_create_enterprise_suppliers_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('133', '2026_07_16_000003_create_enterprise_standards_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('134', '2026_07_15_124900_add_company_id_to_energy_data_daily', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('135', '2026_07_15_124901_add_company_id_to_energy_data_monthly', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('136', '2026_07_15_125000_add_category_type_to_energy_categories', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('137', '2026_07_16_160500_create_energy_cm_heat_table', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('138', '2026_07_16_170000_create_energy_sy_heat_table', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('139', '2026_07_15_100001_add_company_id_to_nt_report_tables', '4');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('140', '2026_07_16_042044_create_energy_cm_ele_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('141', '2026_07_16_000001_add_company_id_to_carbon_reduction', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('142', '2026_07_16_000002_add_trade_category_to_carbon_trade', '7');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('143', '2026_07_16_215517_AddPhoneAndEmailToMerchant2UsersTable', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('144', '2026_07_17_100000_create_energy_sy_ele_table', '9');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('145', '2026_07_17_100000_create_energy_cm_fuel_table', '10');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('146', '2026_07_17_120000_create_energy_cm_medium_table', '11');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('147', '2026_07_17_100000_create_nt_emission_fluids_table', '12');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('148', '2026_07_17_130000_create_energy_sy_energy_table', '13');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('150', '2026_07_17_195500_create_energy_receipt_table', '14');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('151', '2026_07_20_020424_add_content_and_images_to_enterprise_companies_table', '15');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('152', '2026_07_21_100001_alter_nt_production_process_categories_table', '16');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('153', '2026_07_21_100011_alter_nt_production_processes_table', '16');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('154', '2026_07_21_100021_create_nt_production_process_data_table', '16');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('155', '2026_07_22_100001_alter_nt_production_processes_add_sort_table', '16');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('156', '2026_07_22_170722_drop_nt_production_process_co3_decomposition_table', '17');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('157', '2026_07_22_170809_drop_nt_production_process_coal_replace_table', '17');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('158', '2026_07_24_100000_create_merchant2_plans_table', '18');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('159', '2026_07_24_110000_create_merchant2_merchant_plans_table', '19');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('160', '2026_07_24_120000_create_merchant2_expiration_logs_table', '20');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('161', '2026_07_24_100000_create_application_dict_table', '21');

-- 表结构: notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_carbon_daily_aggregate
DROP TABLE IF EXISTS `nt_carbon_daily_aggregate`;
CREATE TABLE `nt_carbon_daily_aggregate` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL COMMENT '企业ID',
  `data_date` date NOT NULL COMMENT '数据日期 YYYY-MM-DD',
  `fossil_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '化石能源排放 tCO₂e',
  `electricity_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '电力排放 tCO₂e',
  `heat_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '热力排放 tCO₂e',
  `output` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '产量 吨',
  `total_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '总排放 tCO₂e',
  `created_by` int DEFAULT NULL COMMENT '创建人',
  `updated_by` int DEFAULT NULL COMMENT '更新人',
  `is_deleted` int NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_date` (`company_id`,`data_date`,`is_deleted`),
  KEY `idx_company_date` (`company_id`,`data_date`),
  KEY `idx_date` (`data_date`),
  KEY `idx_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_carbon_emission
DROP TABLE IF EXISTS `nt_carbon_emission`;
CREATE TABLE `nt_carbon_emission` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `emission_id` bigint unsigned NOT NULL COMMENT '排放ID',
  `space_id` bigint unsigned NOT NULL COMMENT '空间ID',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID',
  `scope_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '排放范围：Scope1, Scope2, Scope3',
  `data_date` date NOT NULL COMMENT '数据日期',
  `period_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '周期：hourly, daily, monthly, yearly',
  `energy_value` decimal(16,4) NOT NULL COMMENT '能源消耗量',
  `emission_value` decimal(16,4) NOT NULL COMMENT '碳排放量（kgCO₂e）',
  `factor_id` bigint unsigned DEFAULT NULL COMMENT '使用的排放因子ID',
  `factor_value` decimal(16,4) DEFAULT NULL COMMENT '因子快照值（记录时的因子）',
  `source_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'auto' COMMENT '数据来源：auto=自动计算, manual=手动录入',
  `remark` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`,`data_date`),
  KEY `idx_ce_space_date` (`space_id`,`data_date`),
  KEY `idx_ce_cat` (`category_id`),
  KEY `idx_ce_scope` (`scope_type`),
  KEY `idx_ce_date` (`data_date`),
  KEY `idx_nt_carbon_emission_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳排放记录表 - 支持 Scope 1/2/3，按月分区'
/*!50500 PARTITION BY RANGE  COLUMNS(data_date)
(PARTITION p202607 VALUES LESS THAN ('2026-08-01') ENGINE = InnoDB,
 PARTITION p_future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- 表结构: nt_carbon_factor
DROP TABLE IF EXISTS `nt_carbon_factor`;
CREATE TABLE `nt_carbon_factor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `factor_id` bigint unsigned NOT NULL COMMENT '因子ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '因子名称',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID',
  `factor_value` decimal(16,4) NOT NULL COMMENT '因子值（kgCO₂e/单位）',
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单位',
  `version` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '版本号',
  `source` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '数据来源（如"IPCC 2023"、"生态环境部2024"）',
  `scope_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '排放范围：Scope1, Scope2, Scope3',
  `is_default` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否默认版本: 0=否, 1=是',
  `valid_from` date NOT NULL COMMENT '生效开始',
  `valid_to` date DEFAULT NULL COMMENT '生效结束',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cf_cat` (`category_id`),
  KEY `idx_cf_scope` (`scope_type`),
  KEY `idx_cf_default` (`category_id`,`is_default`),
  KEY `idx_cf_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='排放因子表 - 支持版本管理，多版本并存';

-- 表结构: nt_carbon_footprint
DROP TABLE IF EXISTS `nt_carbon_footprint`;
CREATE TABLE `nt_carbon_footprint` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `footprint_id` bigint unsigned NOT NULL COMMENT '碳足迹ID',
  `product_id` bigint unsigned NOT NULL COMMENT '产品ID',
  `year` int unsigned NOT NULL COMMENT '年份',
  `functional_unit` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '功能单位（如"1吨钢材"）',
  `total_emission` decimal(16,4) NOT NULL COMMENT '总碳排放（kgCO₂e/功能单位）',
  `scope1_emission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'Scope1排放',
  `scope2_emission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'Scope2排放',
  `scope3_emission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'Scope3排放',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=计算中, 1=已完成, 2=已认证',
  `certification_body` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '认证机构',
  `certification_date` date DEFAULT NULL COMMENT '认证日期',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cf_product` (`product_id`),
  KEY `idx_cf_year` (`year`),
  KEY `idx_cf_status` (`status`),
  KEY `idx_cf_product_year` (`product_id`,`year`),
  KEY `idx_nt_carbon_footprint_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品碳足迹表 - 生命周期分析';

-- 表结构: nt_carbon_footprint_detail
DROP TABLE IF EXISTS `nt_carbon_footprint_detail`;
CREATE TABLE `nt_carbon_footprint_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `detail_id` bigint unsigned NOT NULL COMMENT '明细ID',
  `footprint_id` bigint unsigned NOT NULL COMMENT '碳足迹ID',
  `stage` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '生命周期阶段：raw_material=原材料, production=生产, transportation=运输, usage=使用, disposal=处置',
  `category_id` bigint unsigned DEFAULT NULL COMMENT '能源品种ID',
  `emission_value` decimal(16,4) NOT NULL COMMENT '排放量（kgCO₂e）',
  `ratio` decimal(8,4) DEFAULT NULL COMMENT '占比（%）',
  `description` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cfd_footprint` (`footprint_id`),
  KEY `idx_cfd_stage` (`stage`),
  KEY `idx_cfd_cat` (`category_id`),
  KEY `idx_cfd_footprint_stage` (`footprint_id`,`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳足迹明细表 - 生命周期五阶段追踪';

-- 表结构: nt_carbon_monthly_aggregate
DROP TABLE IF EXISTS `nt_carbon_monthly_aggregate`;
CREATE TABLE `nt_carbon_monthly_aggregate` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL COMMENT '企业ID',
  `month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '月份 YYYY-MM',
  `fossil_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '化石能源排放 tCO₂e',
  `electricity_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '电力排放 tCO₂e',
  `heat_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '热力排放 tCO₂e',
  `output_electricity_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '输出电力排放 tCO₂e',
  `output_heat_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '输出热力排放 tCO₂e',
  `output` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '产量 吨',
  `material_acquisition_tco2` decimal(12,4) DEFAULT '0.0000' COMMENT '原料获取排放 tCO₂e',
  `material_transport_tco2` decimal(12,4) DEFAULT '0.0000' COMMENT '物料运输排放 tCO₂e',
  `process_co3_tco2` decimal(12,4) DEFAULT '0.0000' COMMENT '生产过程CO3排放 tCO₂e',
  `process_coal_tco2` decimal(12,4) DEFAULT '0.0000' COMMENT '生产过程燃煤排放 tCO₂e',
  `total_emission` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '总排放 tCO₂e',
  `created_by` int DEFAULT NULL COMMENT '创建人',
  `updated_by` int DEFAULT NULL COMMENT '更新人',
  `is_deleted` int NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_month` (`company_id`,`month`,`is_deleted`),
  KEY `idx_company_month` (`company_id`,`month`),
  KEY `idx_month` (`month`),
  KEY `idx_is_deleted` (`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_carbon_quota
DROP TABLE IF EXISTS `nt_carbon_quota`;
CREATE TABLE `nt_carbon_quota` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `quota_id` bigint unsigned NOT NULL COMMENT '配额ID',
  `year` int unsigned NOT NULL COMMENT '年份',
  `total_quota` decimal(16,4) NOT NULL COMMENT '总配额（tCO₂e）',
  `used_quota` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '已用配额',
  `remaining_quota` decimal(16,4) DEFAULT NULL COMMENT '剩余配额（计算字段，应用层维护）',
  `quota_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free' COMMENT '类型：free=免费, purchased=购买',
  `source` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '配额来源',
  `valid_from` date NOT NULL COMMENT '生效开始',
  `valid_to` date NOT NULL COMMENT '生效结束',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cq_year` (`year`),
  KEY `idx_cq_type` (`quota_type`),
  KEY `idx_cq_year_type` (`year`,`quota_type`),
  KEY `idx_nt_carbon_quota_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳配额表 - 年度配额管理';

-- 表结构: nt_carbon_reduction
DROP TABLE IF EXISTS `nt_carbon_reduction`;
CREATE TABLE `nt_carbon_reduction` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `reduction_id` bigint unsigned NOT NULL COMMENT '减排ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '项目名称',
  `reduction_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型：energy_efficiency=能效提升, renewable=可再生能源, process=工艺改进, carbon_capture=碳捕获',
  `target_reduction` decimal(16,4) DEFAULT NULL COMMENT '目标减排量（tCO₂e）',
  `actual_reduction` decimal(16,4) DEFAULT NULL COMMENT '实际减排量',
  `investment` decimal(16,4) DEFAULT NULL COMMENT '投资金额（元）',
  `start_date` date NOT NULL COMMENT '开始日期',
  `end_date` date DEFAULT NULL COMMENT '结束日期',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=规划, 1=实施中, 2=已完成',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cr_type` (`reduction_type`),
  KEY `idx_cr_status` (`status`),
  KEY `idx_cr_start` (`start_date`),
  KEY `idx_cr_type_status` (`reduction_type`,`status`),
  KEY `idx_cr_company` (`company_id`),
  KEY `idx_cr_company_type` (`company_id`,`reduction_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳减排项目表 - 减排项目跟踪';

-- 表结构: nt_carbon_report
DROP TABLE IF EXISTS `nt_carbon_report`;
CREATE TABLE `nt_carbon_report` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `report_id` bigint unsigned NOT NULL COMMENT '报告ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '报告名称',
  `year` int unsigned NOT NULL COMMENT '年份',
  `report_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型：annual=年度, quarterly=季度, monthly=月度',
  `total_emission` decimal(16,4) DEFAULT NULL COMMENT '总排放量（tCO₂e）',
  `scope1_total` decimal(16,4) DEFAULT NULL COMMENT 'Scope1合计',
  `scope2_total` decimal(16,4) DEFAULT NULL COMMENT 'Scope2合计',
  `scope3_total` decimal(16,4) DEFAULT NULL COMMENT 'Scope3合计',
  `yoy_change` decimal(8,4) DEFAULT NULL COMMENT '同比变化（%）',
  `file_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '报告文件路径',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=草稿, 1=已审核, 2=已发布',
  `published_at` timestamp NULL DEFAULT NULL COMMENT '发布时间',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_creport_year` (`year`),
  KEY `idx_creport_type` (`report_type`),
  KEY `idx_creport_status` (`status`),
  KEY `idx_creport_year_type` (`year`,`report_type`),
  KEY `idx_nt_carbon_report_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳报告表 - 碳排放报告生成';

-- 表结构: nt_carbon_scheme
DROP TABLE IF EXISTS `nt_carbon_scheme`;
CREATE TABLE `nt_carbon_scheme` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `scheme_id` bigint unsigned NOT NULL COMMENT '方案ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '方案名称',
  `year` int unsigned NOT NULL COMMENT '年份',
  `scheme_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard' COMMENT '类型：standard=标准, custom=自定义',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=草稿, 1=已审核, 2=已发布',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cs_year` (`year`),
  KEY `idx_cs_status` (`status`),
  KEY `idx_cs_type` (`scheme_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='核算方案表';

-- 表结构: nt_carbon_scheme_item
DROP TABLE IF EXISTS `nt_carbon_scheme_item`;
CREATE TABLE `nt_carbon_scheme_item` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `item_id` bigint unsigned NOT NULL COMMENT '明细ID',
  `scheme_id` bigint unsigned NOT NULL COMMENT '方案ID',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID',
  `scope_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '排放范围',
  `factor_id` bigint unsigned DEFAULT NULL COMMENT '排放因子ID',
  `factor_override` decimal(16,4) DEFAULT NULL COMMENT '因子覆盖值（优先于factor默认值）',
  `description` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_csi_scheme` (`scheme_id`),
  KEY `idx_csi_cat` (`category_id`),
  KEY `idx_csi_scope` (`scope_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='核算方案明细表';

-- 表结构: nt_carbon_trade
DROP TABLE IF EXISTS `nt_carbon_trade`;
CREATE TABLE `nt_carbon_trade` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `trade_id` bigint unsigned NOT NULL COMMENT '交易ID',
  `trade_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易类型：buy=买入, sell=卖出',
  `trade_category` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易类别：ccer=CCER, inclusive=碳普惠, other=其他',
  `trade_date` date NOT NULL COMMENT '交易日期',
  `volume` decimal(16,4) NOT NULL COMMENT '交易量（tCO₂e）',
  `unit_price` decimal(16,4) NOT NULL COMMENT '单价（元/tCO₂e）',
  `total_amount` decimal(16,4) NOT NULL COMMENT '总金额',
  `counterparty` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易对手',
  `exchange` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易所（如全国碳交易所）',
  `certificate_no` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易凭证号',
  `year` int unsigned NOT NULL COMMENT '对应配额年份',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态：0=待确认, 1=已确认, 2=已取消',
  `remark` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '软删除: 0=正常, 1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_ct_date` (`trade_date`),
  KEY `idx_ct_type` (`trade_type`),
  KEY `idx_ct_year` (`year`),
  KEY `idx_ct_status` (`status`),
  KEY `idx_ct_date_type` (`trade_date`,`trade_type`),
  KEY `idx_nt_carbon_trade_company` (`company_id`),
  KEY `idx_ct_company_category` (`company_id`,`trade_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳交易记录表 - 买入/卖出两种交易类型';

-- 表结构: nt_emission_fluids
DROP TABLE IF EXISTS `nt_emission_fluids`;
CREATE TABLE `nt_emission_fluids` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `data_time` year NOT NULL COMMENT '年份(YYYY)',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态',
  `annual_emis` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '年度排放量',
  `nhn_emis` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '氨氮排放量',
  `cod_emis` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'COD排放量',
  `nhn_rate` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '氨氮去除率',
  `cod_rate` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'COD去除率',
  `nhn_vol` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '氨氮处理量',
  `cod_vol` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'COD处理量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_year` (`company_id`,`data_time`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='废水排放数据表';

-- 表数据: nt_emission_fluids (1 行)
INSERT INTO `nt_emission_fluids` (`id`, `company_id`, `data_time`, `status`, `annual_emis`, `nhn_emis`, `cod_emis`, `nhn_rate`, `cod_rate`, `nhn_vol`, `cod_vol`, `created_at`, `updated_at`) VALUES ('1', '11', '2026', '1', '150.25', '12.50', '25.75', '85.50', '92.30', '60.25', '280.50', '2026-07-17 19:33:09', '2026-07-17 19:33:09');

-- 表结构: nt_emission_records
DROP TABLE IF EXISTS `nt_emission_records`;
CREATE TABLE `nt_emission_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `source_id` bigint unsigned NOT NULL COMMENT '排放源ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '月份YYYY-MM',
  `emission_volume` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '排放量',
  `treatment_volume` decimal(16,4) DEFAULT NULL COMMENT '处理量',
  `utilization_volume` decimal(16,4) DEFAULT NULL COMMENT '利用量（固废）',
  `disposal_volume` decimal(16,4) DEFAULT NULL COMMENT '处置量（固废）',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `status` enum('draft','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT '记录状态',
  `remarks` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_emission_records_company_id_index` (`company_id`),
  KEY `nt_emission_records_source_id_index` (`source_id`),
  KEY `nt_emission_records_data_time_index` (`data_time`),
  KEY `nt_emission_records_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_emission_sources
DROP TABLE IF EXISTS `nt_emission_sources`;
CREATE TABLE `nt_emission_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '排放源名称',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '排放源编码',
  `emission_type` enum('gas','fluid','solid','noise') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gas' COMMENT '排放类型',
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '排放源位置',
  `device_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '处理设备信息',
  `treatment_efficiency` decimal(10,2) DEFAULT NULL COMMENT '处理效率%',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '排放源描述',
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '排放源状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_emission_sources_company_id_index` (`company_id`),
  KEY `nt_emission_sources_emission_type_index` (`emission_type`),
  KEY `nt_emission_sources_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_emission_summaries
DROP TABLE IF EXISTS `nt_emission_summaries`;
CREATE TABLE `nt_emission_summaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '月份YYYY-MM',
  `emission_type` enum('gas','fluid','solid','noise') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gas' COMMENT '排放类型',
  `total_emission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '总排放量',
  `total_treatment` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '总处理量',
  `total_utilization` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '总利用量（固废）',
  `total_disposal` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '总处置量（固废）',
  `record_count` int NOT NULL DEFAULT '0' COMMENT '记录数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nt_emission_summaries_company_id_data_time_emission_type_unique` (`company_id`,`data_time`,`emission_type`),
  KEY `nt_emission_summaries_company_id_index` (`company_id`),
  KEY `nt_emission_summaries_data_time_index` (`data_time`),
  KEY `nt_emission_summaries_emission_type_index` (`emission_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_energy_bills
DROP TABLE IF EXISTS `nt_energy_bills`;
CREATE TABLE `nt_energy_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `bill_id` bigint NOT NULL COMMENT '账单ID',
  `bill_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '账单编号',
  `tariff_id` bigint unsigned NOT NULL COMMENT '费率ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '计量器ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计量器类型',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品类ID',
  `energy_amount` decimal(16,4) NOT NULL COMMENT '用能量',
  `total_cost` decimal(12,2) NOT NULL COMMENT '总成本',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已支付金额',
  `bill_date` date NOT NULL COMMENT '账单日期',
  `due_date` date DEFAULT NULL COMMENT '支付截止日期',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:1=未支付,2=部分支付,3=已支付,4=已取消',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `time_range` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '时间范围JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nt_energy_bills_bill_no_unique` (`bill_no`),
  KEY `idx_bill_tariff` (`tariff_id`),
  KEY `idx_bill_meter` (`meter_id`),
  KEY `idx_bill_cat` (`category_id`),
  KEY `idx_bill_date` (`bill_date`),
  KEY `idx_bill_status` (`status`),
  KEY `idx_bill_meter_date` (`meter_id`,`meter_type`,`bill_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源账单表';

-- 表结构: nt_energy_budgets
DROP TABLE IF EXISTS `nt_energy_budgets`;
CREATE TABLE `nt_energy_budgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `budget_id` bigint NOT NULL COMMENT '预算ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '计量器ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计量器类型',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品类ID',
  `budget_amount` decimal(12,2) NOT NULL COMMENT '预算金额',
  `used_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已使用金额',
  `period_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '预算周期:daily,monthly,quarterly,yearly',
  `period_start` date NOT NULL COMMENT '周期起始日期',
  `period_end` date NOT NULL COMMENT '周期结束日期',
  `alert_threshold` decimal(5,2) NOT NULL DEFAULT '80.00' COMMENT '告警阈值百分比',
  `alert_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '告警状态:0=正常,1=预警,2=超预算',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_budget` (`meter_id`,`meter_type`,`period_type`,`period_start`),
  KEY `idx_budget_meter` (`meter_id`),
  KEY `idx_budget_cat` (`category_id`),
  KEY `idx_budget_period` (`period_start`,`period_end`),
  KEY `idx_budget_alert` (`alert_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源预算表';

-- 表结构: nt_energy_categories
DROP TABLE IF EXISTS `nt_energy_categories`;
CREATE TABLE `nt_energy_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `category_id` bigint NOT NULL COMMENT '品种ID',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '品种名称（电/天然气/水/蒸汽等）',
  `unit_of_measure` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计量单位（kWh/m³/t/GJ等）',
  `category_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'energy' COMMENT '能源分类: electric/heat/energy/medium/sup_electric/sup_heat/sup_energy',
  `kgce` decimal(16,4) NOT NULL COMMENT '千克标准煤当量系数',
  `kgco2e` decimal(16,4) NOT NULL COMMENT '碳排放因子（kgCO₂e/单位）',
  `icon` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图标',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '排序',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ecat_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源品种表';

-- 表结构: nt_energy_cm_ele
DROP TABLE IF EXISTS `nt_energy_cm_ele`;
CREATE TABLE `nt_energy_cm_ele` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据月份YYYY-MM',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID（工序）',
  `number` decimal(16,4) NOT NULL COMMENT '电力消耗量(kWh)',
  `factor` decimal(10,4) NOT NULL DEFAULT '0.1229' COMMENT '折标煤系数(kgce/kWh)',
  `tce` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '折算标煤量(tce)',
  `co2_factor` decimal(10,4) NOT NULL DEFAULT '0.5306' COMMENT 'CO₂排放因子(tCO₂/MWh)',
  `tco2e` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '温室气体排放量(tCO₂e)',
  `html` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '预留字段',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID（商户ID）',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cm_ele` (`company_id`,`data_time`,`dept_id`),
  KEY `idx_cm_ele_time` (`data_time`),
  KEY `idx_cm_ele_company` (`company_id`),
  KEY `idx_cm_ele_dept` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='电力消耗明细表';

-- 表结构: nt_energy_cm_fuel
DROP TABLE IF EXISTS `nt_energy_cm_fuel`;
CREATE TABLE `nt_energy_cm_fuel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `company_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID（关联enterprise_company_dept）',
  `energy_id` bigint unsigned NOT NULL COMMENT '能源品类ID（关联nt_energy_categories）',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '月份（YYYY-MM格式）',
  `number` decimal(16,4) NOT NULL COMMENT '消耗量',
  `unit` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单位（吨/千克等）',
  `factor` decimal(10,4) NOT NULL COMMENT '折标煤系数（kgce/kg或kgce/m³）',
  `tce` decimal(16,4) NOT NULL COMMENT '折标煤量（tce）= number × factor / 1000',
  `power` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '低位发热量（GJ/t或MJ/kg）',
  `unit2` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '热值单位',
  `carbon` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '单位热值含碳量（tC/GJ）',
  `ox_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '碳氧化率（%）',
  `tco2e` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'CO₂排放量（tCO₂e）= number × power × carbon × (ox_rate/100) × (44/12) / 1000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cm_fuel_unique` (`company_id`,`dept_id`,`energy_id`,`data_time`),
  KEY `idx_cm_fuel_company_time` (`company_id`,`data_time`),
  KEY `idx_cm_fuel_dept_time` (`dept_id`,`data_time`),
  KEY `idx_cm_fuel_energy_time` (`energy_id`,`data_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='化石燃料消耗明细表（按部门分组）';

-- 表数据: nt_energy_cm_fuel (3 行)
INSERT INTO `nt_energy_cm_fuel` (`id`, `company_id`, `dept_id`, `energy_id`, `data_time`, `number`, `unit`, `factor`, `tce`, `power`, `unit2`, `carbon`, `ox_rate`, `tco2e`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES ('1', '1', '1', '1', '2025-12', '100.5000', '吨', '0.7143', '0.0718', '26.4000', 't', '0.0280', '98.00', '0.0003', '2026-07-17 16:14:04', '2026-07-17 16:14:04', '1', NULL);
INSERT INTO `nt_energy_cm_fuel` (`id`, `company_id`, `dept_id`, `energy_id`, `data_time`, `number`, `unit`, `factor`, `tce`, `power`, `unit2`, `carbon`, `ox_rate`, `tco2e`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES ('2', '1', '1', '21', '2025-12', '0.5661', '吨', '1.4571', '0.0008', '42.6520', 't', '0.0202', '98.00', '0.0000', '2026-07-17 16:14:04', '2026-07-17 16:14:04', '1', NULL);
INSERT INTO `nt_energy_cm_fuel` (`id`, `company_id`, `dept_id`, `energy_id`, `data_time`, `number`, `unit`, `factor`, `tce`, `power`, `unit2`, `carbon`, `ox_rate`, `tco2e`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES ('3', '1', '2', '1', '2025-12', '50.2500', '吨', '0.7143', '0.0359', '26.4000', 't', '0.0280', '98.00', '0.0001', '2026-07-17 16:14:04', '2026-07-17 16:14:04', '1', NULL);

-- 表结构: nt_energy_cm_heat
DROP TABLE IF EXISTS `nt_energy_cm_heat`;
CREATE TABLE `nt_energy_cm_heat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据月份YYYY-MM',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID',
  `pres` decimal(16,4) NOT NULL COMMENT '蒸汽压力(MPa)',
  `heat` decimal(16,4) NOT NULL COMMENT '蒸汽温度(℃)',
  `enthalpy` decimal(16,4) DEFAULT NULL COMMENT '蒸汽热焓值(kJ/kg)',
  `number` decimal(16,4) NOT NULL COMMENT '蒸汽消耗量(吨)',
  `calorific` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '热力转换热值(MJ)',
  `factor` decimal(16,4) NOT NULL DEFAULT '0.1229' COMMENT '折标系数',
  `tce` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '折算标煤量(tce)',
  `co2_factor` decimal(16,4) NOT NULL DEFAULT '0.1100' COMMENT '热力CO₂排放因子',
  `tco2e` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '温室气体排放量(tCO₂e)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID（商户ID）',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cm_heat` (`company_id`,`data_time`,`dept_id`),
  KEY `idx_cm_heat_time` (`data_time`),
  KEY `idx_cm_heat_company` (`company_id`),
  KEY `idx_cm_heat_dept` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='热力消耗明细表';

-- 表结构: nt_energy_cm_medium
DROP TABLE IF EXISTS `nt_energy_cm_medium`;
CREATE TABLE `nt_energy_cm_medium` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `company_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID（关联enterprise_company_dept）',
  `energy_id` bigint unsigned NOT NULL COMMENT '能源品类ID（关联nt_energy_categories）',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '月份（YYYY-MM格式）',
  `number` decimal(16,4) NOT NULL COMMENT '消耗量',
  `unit` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单位（吨/千克等）',
  `factor` decimal(10,4) NOT NULL COMMENT '折标煤系数（kgce/kg或kgce/m³）',
  `tce` decimal(16,4) NOT NULL COMMENT '折标煤量（tce）= number × factor / 1000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cm_medium` (`company_id`,`dept_id`,`energy_id`,`data_time`),
  KEY `idx_cm_medium_company_time` (`company_id`,`data_time`),
  KEY `idx_cm_medium_dept_time` (`dept_id`,`data_time`),
  KEY `idx_cm_medium_energy_time` (`energy_id`,`data_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='耗能工质消耗明细表（按部门分组）';

-- 表结构: nt_energy_cost_records
DROP TABLE IF EXISTS `nt_energy_cost_records`;
CREATE TABLE `nt_energy_cost_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `record_id` bigint NOT NULL COMMENT '成本记录ID',
  `tariff_id` bigint unsigned NOT NULL COMMENT '费率ID',
  `tariff_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '费率名称',
  `meter_id` bigint unsigned NOT NULL COMMENT '计量器ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计量器类型:meter,virtual_meter,offline_meter',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品类ID',
  `energy_amount` decimal(16,4) NOT NULL COMMENT '用能量',
  `unit_price` decimal(10,4) NOT NULL COMMENT '单价',
  `cost` decimal(12,2) NOT NULL COMMENT '成本金额',
  `time_range` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '应用时间范围JSON',
  `record_date` date NOT NULL COMMENT '记录日期',
  `time_slot` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '时段类型:sharp,peak,flat,valley',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_cost_record_tariff` (`tariff_id`),
  KEY `idx_cost_record_meter` (`meter_id`),
  KEY `idx_cost_record_cat` (`category_id`),
  KEY `idx_cost_record_date` (`record_date`),
  KEY `idx_cost_record_meter_date` (`meter_id`,`meter_type`,`record_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源成本记录表';

-- 表结构: nt_energy_cost_statistics
DROP TABLE IF EXISTS `nt_energy_cost_statistics`;
CREATE TABLE `nt_energy_cost_statistics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `stat_id` bigint NOT NULL COMMENT '统计ID',
  `dimension_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '统计维度:meter,category,time',
  `dimension_id` bigint unsigned NOT NULL COMMENT '维度对象ID',
  `dimension_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '维度名称',
  `total_cost` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '总成本',
  `avg_cost` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '平均成本',
  `max_cost` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '最大成本',
  `min_cost` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '最小成本',
  `record_count` int NOT NULL DEFAULT '0' COMMENT '记录数量',
  `total_energy` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '总用能量',
  `stat_date` date NOT NULL COMMENT '统计日期',
  `period_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '统计周期:daily,weekly,monthly,yearly',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cost_stat` (`dimension_type`,`dimension_id`,`stat_date`,`period_type`),
  KEY `idx_cost_stat_dim` (`dimension_type`,`dimension_id`),
  KEY `idx_cost_stat_date` (`stat_date`),
  KEY `idx_cost_stat_period` (`period_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源成本统计表';

-- 表结构: nt_energy_data_daily
DROP TABLE IF EXISTS `nt_energy_data_daily`;
CREATE TABLE `nt_energy_data_daily` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `data_id` bigint NOT NULL COMMENT '数据ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '表计ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表计类型',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '商户ID',
  `data_date` date NOT NULL COMMENT '数据日期',
  `data_value` decimal(16,4) NOT NULL COMMENT '日累计值',
  `peak_value` decimal(16,4) DEFAULT NULL COMMENT '峰时段值',
  `flat_value` decimal(16,4) DEFAULT NULL COMMENT '平时段值',
  `valley_value` decimal(16,4) DEFAULT NULL COMMENT '谷时段值',
  `max_value` decimal(16,4) DEFAULT NULL COMMENT '日最大值',
  `min_value` decimal(16,4) DEFAULT NULL COMMENT '日最小值',
  `avg_value` decimal(16,4) DEFAULT NULL COMMENT '日均值',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  PRIMARY KEY (`id`,`data_date`),
  UNIQUE KEY `uk_edd` (`meter_id`,`meter_type`,`data_date`),
  KEY `idx_edd_date` (`data_date`),
  KEY `idx_edd_company_date` (`company_id`,`data_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日统计数据表（按月分区）'
/*!50500 PARTITION BY RANGE  COLUMNS(data_date)
(PARTITION p202607 VALUES LESS THAN ('2026-08-01') ENGINE = InnoDB,
 PARTITION p202608 VALUES LESS THAN ('2026-09-01') ENGINE = InnoDB,
 PARTITION p202609 VALUES LESS THAN ('2026-10-01') ENGINE = InnoDB,
 PARTITION p202610 VALUES LESS THAN ('2026-11-01') ENGINE = InnoDB,
 PARTITION p202611 VALUES LESS THAN ('2026-12-01') ENGINE = InnoDB,
 PARTITION p202612 VALUES LESS THAN ('2027-01-01') ENGINE = InnoDB,
 PARTITION p202701 VALUES LESS THAN ('2027-02-01') ENGINE = InnoDB,
 PARTITION p_future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- 表结构: nt_energy_data_hourly
DROP TABLE IF EXISTS `nt_energy_data_hourly`;
CREATE TABLE `nt_energy_data_hourly` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `data_id` bigint NOT NULL COMMENT '数据ID',
  `meter_id` bigint unsigned NOT NULL COMMENT '表计ID',
  `meter_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表计类型:meter,virtual_meter,offline_meter',
  `data_time` datetime NOT NULL COMMENT '数据时间（UTC，整点）',
  `data_value` decimal(16,4) NOT NULL COMMENT '能耗值',
  `is_flagged` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否异常标记',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  PRIMARY KEY (`id`,`data_time`),
  UNIQUE KEY `uk_edh` (`meter_id`,`meter_type`,`data_time`),
  KEY `idx_edh_time` (`data_time`),
  KEY `idx_edh_meter` (`meter_id`,`meter_type`,`data_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='小时能耗数据表（按月分区）'
/*!50500 PARTITION BY RANGE  COLUMNS(data_time)
(PARTITION p202607 VALUES LESS THAN ('2026-08-01') ENGINE = InnoDB,
 PARTITION p202608 VALUES LESS THAN ('2026-09-01') ENGINE = InnoDB,
 PARTITION p202609 VALUES LESS THAN ('2026-10-01') ENGINE = InnoDB,
 PARTITION p202610 VALUES LESS THAN ('2026-11-01') ENGINE = InnoDB,
 PARTITION p202611 VALUES LESS THAN ('2026-12-01') ENGINE = InnoDB,
 PARTITION p202612 VALUES LESS THAN ('2027-01-01') ENGINE = InnoDB,
 PARTITION p202701 VALUES LESS THAN ('2027-02-01') ENGINE = InnoDB,
 PARTITION p_future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- 表结构: nt_energy_formula_params
DROP TABLE IF EXISTS `nt_energy_formula_params`;
CREATE TABLE `nt_energy_formula_params` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `param_id` bigint NOT NULL COMMENT '参数ID',
  `formula_id` bigint unsigned NOT NULL COMMENT '公式ID',
  `param_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '参数名称',
  `param_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '参数编码（与表达式中变量对应，如A、B、C）',
  `source_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据来源:meter=表计,point=点位,constant=常量',
  `source_id` bigint unsigned DEFAULT NULL COMMENT '来源ID',
  `constant_value` decimal(16,4) DEFAULT NULL COMMENT '常量值（source_type=constant时）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_fparam_fid` (`formula_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公式参数表';

-- 表结构: nt_energy_formulas
DROP TABLE IF EXISTS `nt_energy_formulas`;
CREATE TABLE `nt_energy_formulas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `formula_id` bigint NOT NULL COMMENT '公式ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公式名称',
  `expression` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公式表达式，如 A * B + C',
  `formula_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general' COMMENT '类型:general=通用,virtual_meter=虚拟表计,statistic=统计',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_formula_type` (`formula_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='计算公式表';

-- 表结构: nt_energy_items
DROP TABLE IF EXISTS `nt_energy_items`;
CREATE TABLE `nt_energy_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `item_id` bigint NOT NULL COMMENT '指标ID',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `category_id` bigint unsigned NOT NULL COMMENT '所属能源品种ID',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_eitem_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源细分指标表';

-- 表结构: nt_energy_peak_valley_items
DROP TABLE IF EXISTS `nt_energy_peak_valley_items`;
CREATE TABLE `nt_energy_peak_valley_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `item_id` bigint NOT NULL COMMENT '明细ID',
  `scheme_id` bigint unsigned NOT NULL COMMENT '方案ID',
  `time_slot` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '时段:sharp=尖,peak=峰,flat=平,valley=谷',
  `start_time` time NOT NULL COMMENT '开始时间',
  `end_time` time NOT NULL COMMENT '结束时间',
  `price` decimal(16,4) NOT NULL COMMENT '单价（元/kWh）',
  `applies_to` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all' COMMENT '适用范围:all=全周,weekday=工作日,weekend=周末',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_pv_item_sid` (`scheme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='尖峰平谷明细表';

-- 表结构: nt_energy_peak_valley_schemes
DROP TABLE IF EXISTS `nt_energy_peak_valley_schemes`;
CREATE TABLE `nt_energy_peak_valley_schemes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `scheme_id` bigint NOT NULL COMMENT '方案ID',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '方案名称',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID（通常为电）',
  `year` int NOT NULL COMMENT '适用年份',
  `is_active` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否启用',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pv_scheme` (`category_id`,`year`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='尖峰平谷方案表';

-- 表结构: nt_energy_receipts
DROP TABLE IF EXISTS `nt_energy_receipts`;
CREATE TABLE `nt_energy_receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '商户ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据月份（YYYY-MM格式）',
  `receipt_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '凭证类型',
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '凭证名称',
  `receipt_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '凭证编号',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `file_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件URL',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_company_datatime_created` (`company_id`,`data_time`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能耗凭证表';

-- 表数据: nt_energy_receipts (7 行)
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('1', '1', '2025-12', '电费单', '2025年12月电费单', 'ELEC-202512-001', '15680.50', '/uploads/receipts/202512-elec-001.pdf', NULL, NULL, '2026-07-17 21:01:28', '2026-07-17 21:01:28');
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('2', '1', '2025-12', '天然气采购发票', '2025年12月天然气采购发票', 'GAS-202512-001', '8750.00', '/uploads/receipts/202512-gas-001.pdf', NULL, NULL, '2026-07-17 21:01:28', '2026-07-17 21:01:28');
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('3', '1', '2025-11', '水费单', '2025年11月水费单', 'WATER-202511-001', '3200.00', '/uploads/receipts/202511-water-001.pdf', NULL, NULL, '2026-07-17 21:01:28', '2026-07-17 21:01:28');
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('4', '1', '2025-11', '热力费单', '2025年11月热力费单', 'HEAT-202511-001', '12500.00', '/uploads/receipts/202511-heat-001.pdf', NULL, NULL, '2026-07-17 21:01:28', '2026-07-17 21:01:28');
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('5', '11', '2025-12', '电费单', '2025年12月电费单', 'ELEC-2025-001', '15680.50', '/uploads/receipts/202512_ele.pdf', NULL, NULL, '2026-07-17 21:02:12', '2026-07-17 21:02:12');
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('6', '11', '2025-12', '天然气采购发票', '2025年12月天然气采购发票', 'GAS-2025-015', '8750.00', '/uploads/receipts/202512_gas.pdf', NULL, NULL, '2026-07-17 21:02:12', '2026-07-17 21:02:12');
INSERT INTO `nt_energy_receipts` (`id`, `company_id`, `data_time`, `receipt_type`, `name`, `receipt_no`, `amount`, `file_url`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ('7', '11', '2025-11', '水费单', '2025年11月水费单', 'WATER-2025-008', '3200.00', '/uploads/receipts/202511_water.pdf', NULL, NULL, '2026-07-17 21:02:12', '2026-07-17 21:02:12');

-- 表结构: nt_energy_sy_ele
DROP TABLE IF EXISTS `nt_energy_sy_ele`;
CREATE TABLE `nt_energy_sy_ele` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据月份YYYY-MM',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID',
  `supply_stage` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '供电环节',
  `grid_number` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '上网电量(kWh)',
  `tce` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '折算标煤量(tce)',
  `co2_factor` decimal(16,4) NOT NULL DEFAULT '0.1229' COMMENT 'CO₂排放因子',
  `tco2e` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'CO₂排放量(tCO₂e)',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID（商户ID）',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sy_ele` (`company_id`,`data_time`,`dept_id`),
  KEY `idx_sy_ele_time` (`data_time`),
  KEY `idx_sy_ele_company` (`company_id`),
  KEY `idx_sy_ele_dept` (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='外供电力明细表';

-- 表数据: nt_energy_sy_ele (1 行)
INSERT INTO `nt_energy_sy_ele` (`id`, `data_time`, `dept_id`, `supply_stage`, `grid_number`, `tce`, `co2_factor`, `tco2e`, `company_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', '2026-07', '1', '发电车间', '50000.0000', '6.1450', '0.5306', '26.5300', '1', '1', NULL, '2026-07-17 12:59:40', '2026-07-17 12:59:58', '2026-07-17 12:59:58');

-- 表结构: nt_energy_sy_energy
DROP TABLE IF EXISTS `nt_energy_sy_energy`;
CREATE TABLE `nt_energy_sy_energy` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID（商户ID）',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据月份YYYY-MM',
  `number` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '数量(t)',
  `tce` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '折算标煤量(tce)',
  `power` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '低位发热量(GJ/t)',
  `carbon` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '单位热值含碳量(tC/GJ)',
  `ox_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '碳氧化率(%)',
  `tco2e` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'CO₂排放量(tCO₂e)',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sy_energy` (`company_id`,`dept_id`,`data_time`),
  KEY `idx_sy_energy_company_time` (`company_id`,`data_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='外输焦碳明细表';

-- 表结构: nt_energy_sy_heat
DROP TABLE IF EXISTS `nt_energy_sy_heat`;
CREATE TABLE `nt_energy_sy_heat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `data_time` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据月份YYYY-MM',
  `dept_id` bigint unsigned NOT NULL COMMENT '部门ID（产汽工序）',
  `pres` decimal(16,4) NOT NULL COMMENT '蒸汽压力(MPa)',
  `heat` decimal(16,4) NOT NULL COMMENT '蒸汽温度(℃)',
  `enthalpy` decimal(16,4) DEFAULT NULL COMMENT '蒸汽热焓值(kJ/kg)',
  `supply_number` decimal(16,4) NOT NULL COMMENT '外供蒸汽量(吨)',
  `calorific` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '热力转换热值(MJ)',
  `factor` decimal(16,4) NOT NULL DEFAULT '0.1229' COMMENT '折标系数',
  `tce` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '折算标煤量(tce)',
  `co2_factor` decimal(16,4) NOT NULL DEFAULT '0.1100' COMMENT '热力CO₂排放因子',
  `tco2e` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '温室气体排放量(tCO₂e)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID（商户ID）',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sy_heat` (`company_id`,`data_time`,`dept_id`),
  KEY `idx_sy_heat_time` (`data_time`),
  KEY `idx_sy_heat_company` (`company_id`),
  KEY `idx_sy_heat_dept` (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='外供热力明细表';

-- 表数据: nt_energy_sy_heat (18 行)
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('1', '2025-07', '4', '1.4400', '205.7400', '2855.6400', '490.1900', '1358757.6610', '0.1229', '46.3608', '0.1100', '149.4633', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('2', '2025-07', '5', '0.8200', '183.5700', '2727.2800', '108.0500', '285634.4970', '0.1229', '9.7458', '0.1100', '31.4198', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('3', '2025-07', '6', '0.5100', '139.0100', '2637.0300', '152.4300', '389197.9947', '0.1229', '13.2794', '0.1100', '42.8118', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('4', '2025-08', '4', '1.5300', '246.0200', '2960.9500', '445.3500', '1281365.4735', '0.1229', '43.7202', '0.1100', '140.9502', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('5', '2025-08', '5', '0.8800', '195.9300', '2847.8400', '146.2800', '404332.5480', '0.1229', '13.7958', '0.1100', '44.4766', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('6', '2025-08', '6', '0.5500', '123.2800', '2665.9800', '126.3700', '326317.6688', '0.1229', '11.1340', '0.1100', '35.8949', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('7', '2025-09', '4', '1.0500', '211.1600', '2810.3600', '257.1600', '701177.5992', '0.1229', '23.9242', '0.1100', '77.1295', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('8', '2025-09', '5', '0.7200', '190.7500', '2812.3500', '246.9000', '673693.8090', '0.1229', '22.9864', '0.1100', '74.1063', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('9', '2025-09', '6', '0.3100', '135.0600', '2628.1200', '164.6600', '418957.6108', '0.1229', '14.2948', '0.1100', '46.0853', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('10', '2025-10', '4', '1.2200', '205.1300', '2801.7700', '279.9100', '760803.7773', '0.1229', '25.9586', '0.1100', '83.6884', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('11', '2025-10', '5', '0.8400', '197.1900', '2841.7600', '228.1700', '629297.4234', '0.1229', '21.4716', '0.1100', '69.2227', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('12', '2025-10', '6', '0.4400', '126.5100', '2673.1400', '67.0700', '173671.0580', '0.1229', '5.9257', '0.1100', '19.1038', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('13', '2025-11', '4', '1.3700', '218.1000', '2910.5600', '290.2900', '820597.5778', '0.1229', '27.9988', '0.1100', '90.2657', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('14', '2025-11', '5', '0.9000', '196.8500', '2804.3600', '109.1600', '296982.8792', '0.1229', '10.1331', '0.1100', '32.6681', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('15', '2025-11', '6', '0.4600', '156.3200', '2651.5900', '93.1000', '239066.8350', '0.1229', '8.1570', '0.1100', '26.2974', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('16', '2025-12', '4', '1.0200', '263.8600', '2902.0500', '229.4000', '646520.3140', '0.1229', '22.0593', '0.1100', '71.1172', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('17', '2025-12', '5', '1.0000', '167.4500', '2803.7800', '272.5300', '741292.5012', '0.1229', '25.2929', '0.1100', '81.5422', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);
INSERT INTO `nt_energy_sy_heat` (`id`, `data_time`, `dept_id`, `pres`, `heat`, `enthalpy`, `supply_number`, `calorific`, `factor`, `tce`, `co2_factor`, `tco2e`, `created_at`, `updated_at`, `company_id`, `created_by`, `updated_by`, `deleted_at`) VALUES ('18', '2025-12', '6', '0.3200', '141.1500', '2605.8600', '70.7000', '178313.8840', '0.1229', '6.0841', '0.1100', '19.6145', '2026-07-16 03:41:57', '2026-07-16 03:41:57', '11', NULL, NULL, NULL);

-- 表结构: nt_energy_tariff_time_of_uses
DROP TABLE IF EXISTS `nt_energy_tariff_time_of_uses`;
CREATE TABLE `nt_energy_tariff_time_of_uses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `detail_id` bigint NOT NULL COMMENT '明细ID',
  `tariff_id` bigint unsigned NOT NULL COMMENT '费率ID',
  `time_slot` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '时段:sharp=尖,peak=峰,flat=平,valley=谷',
  `start_time` time NOT NULL COMMENT '开始时间',
  `end_time` time NOT NULL COMMENT '结束时间',
  `price` decimal(16,4) NOT NULL COMMENT '单价',
  `applies_to` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all' COMMENT '适用:all,weekday,weekend',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_tou_tariff` (`tariff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分时费率明细表';

-- 表结构: nt_energy_tariffs
DROP TABLE IF EXISTS `nt_energy_tariffs`;
CREATE TABLE `nt_energy_tariffs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tariff_id` bigint NOT NULL COMMENT '费率ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '费率名称',
  `category_id` bigint unsigned NOT NULL COMMENT '能源品种ID',
  `tariff_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '费率类型:flat=统一,time_of_use=分时,tiered=阶梯',
  `unit_of_price` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '价格单位（元/kWh, 元/m³等）',
  `valid_from` date NOT NULL COMMENT '生效起始日期',
  `valid_to` date NOT NULL COMMENT '生效结束日期',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_tariff_cat` (`category_id`),
  KEY `idx_tariff_valid` (`valid_from`,`valid_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='费率表';

-- 表结构: nt_factors
DROP TABLE IF EXISTS `nt_factors`;
CREATE TABLE `nt_factors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '因子名称',
  `factor` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '排放因子值',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '排放因子单位',
  `type` enum('material','transport','energy') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'material' COMMENT '因子类型',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '因子描述',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_factors_type_index` (`type`),
  KEY `nt_factors_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: nt_factors (4 行)
INSERT INTO `nt_factors` (`id`, `name`, `factor`, `unit`, `type`, `description`, `status`, `created_at`, `updated_at`) VALUES ('1', '电力排放因子', '0.5839', 'kgCO₂e/kWh', 'energy', '电网电力排放因子，0.5839 kgCO₂e/kWh（国家电网平均排放因子）', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `nt_factors` (`id`, `name`, `factor`, `unit`, `type`, `description`, `status`, `created_at`, `updated_at`) VALUES ('2', '煤炭排放因子', '2.6600', 'kgCO₂e/kg', 'energy', '原煤燃烧排放因子，2.66 kgCO₂e/kg（煤炭燃烧平均排放因子）', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `nt_factors` (`id`, `name`, `factor`, `unit`, `type`, `description`, `status`, `created_at`, `updated_at`) VALUES ('3', '天然气排放因子', '2.1622', 'kgCO₂e/m³', 'energy', '天然气燃烧排放因子，2.1622 kgCO₂e/m³（天然气燃烧平均排放因子）', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `nt_factors` (`id`, `name`, `factor`, `unit`, `type`, `description`, `status`, `created_at`, `updated_at`) VALUES ('4', '柴油排放因子', '3.0960', 'kgCO₂e/kg', 'energy', '柴油燃烧排放因子，3.096 kgCO₂e/kg（柴油燃烧平均排放因子）', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');

-- 表结构: nt_material_categories
DROP TABLE IF EXISTS `nt_material_categories`;
CREATE TABLE `nt_material_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '分类描述',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_material_categories_company_id_index` (`company_id`),
  KEY `nt_material_categories_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: nt_material_categories (4 行)
INSERT INTO `nt_material_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', '金属材料', '钢材、铝材、铜材等金属材料分类', '1', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `nt_material_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('2', '1', '化工原料', '石油化工原料、化学试剂、溶剂等材料分类', '2', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `nt_material_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('3', '1', '电子元件', '芯片、电容、电阻、二极管等电子元件分类', '3', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');
INSERT INTO `nt_material_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('4', '1', '包装材料', '纸箱、塑料包装、木质包装等包装材料分类', '4', 'active', '2026-07-16 03:28:57', '2026-07-16 03:28:57');

-- 表结构: nt_material_monthlies
DROP TABLE IF EXISTS `nt_material_monthlies`;
CREATE TABLE `nt_material_monthlies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `year` smallint unsigned NOT NULL COMMENT '年份',
  `month` tinyint unsigned NOT NULL COMMENT '月份',
  `material_id` bigint unsigned NOT NULL COMMENT '原辅料ID',
  `total_quantity` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '月度使用总数量',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `avg_material_factor` decimal(16,4) DEFAULT NULL COMMENT '平均材料排放因子',
  `total_transport_distance` decimal(10,2) DEFAULT NULL COMMENT '月度运输总距离(千米)',
  `avg_transport_factor` decimal(16,4) DEFAULT NULL COMMENT '平均运输排放因子',
  `total_material_emission` decimal(15,4) DEFAULT NULL COMMENT '月度原料获取总排放量(tCO2e)',
  `total_transport_emission` decimal(15,4) DEFAULT NULL COMMENT '月度运输总排放量(tCO2e)',
  `total_emission` decimal(15,4) DEFAULT NULL COMMENT '月度总排放量(tCO2e)',
  `record_count` int unsigned NOT NULL DEFAULT '0' COMMENT '记录数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mm_company_year_month_material` (`company_id`,`year`,`month`,`material_id`),
  KEY `idx_mm_company_id` (`company_id`),
  KEY `idx_mm_year_month` (`year`,`month`),
  KEY `idx_mm_material_id` (`material_id`),
  KEY `idx_mm_company_year_month` (`company_id`,`year`,`month`),
  KEY `idx_mm_company_material` (`company_id`,`material_id`),
  CONSTRAINT `nt_material_monthlies_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `nt_materials` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_material_records
DROP TABLE IF EXISTS `nt_material_records`;
CREATE TABLE `nt_material_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `data_time` date NOT NULL COMMENT '数据时间',
  `material_id` bigint unsigned NOT NULL COMMENT '原辅料ID',
  `quantity` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '使用数量',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `factor_id` bigint unsigned DEFAULT NULL COMMENT '排放因子ID',
  `material_factor` decimal(16,4) DEFAULT NULL COMMENT '材料排放因子',
  `transport_distance` decimal(10,2) DEFAULT NULL COMMENT '运输距离(千米)',
  `transport_factor_id` bigint unsigned DEFAULT NULL COMMENT '运输排放因子ID',
  `transport_factor` decimal(16,4) DEFAULT NULL COMMENT '运输排放因子',
  `material_emission` decimal(15,4) DEFAULT NULL COMMENT '原料获取排放量(tCO2e)',
  `transport_emission` decimal(15,4) DEFAULT NULL COMMENT '运输排放量(tCO2e)',
  `total_emission` decimal(15,4) DEFAULT NULL COMMENT '总排放量(tCO2e)',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mr_company_time_material` (`company_id`,`data_time`,`material_id`),
  KEY `idx_mr_company_id` (`company_id`),
  KEY `idx_mr_data_time` (`data_time`),
  KEY `idx_mr_material_id` (`material_id`),
  KEY `idx_mr_factor_id` (`factor_id`),
  KEY `idx_mr_transport_factor_id` (`transport_factor_id`),
  KEY `idx_mr_company_data_time` (`company_id`,`data_time`),
  KEY `idx_mr_company_material` (`company_id`,`material_id`),
  CONSTRAINT `nt_material_records_factor_id_foreign` FOREIGN KEY (`factor_id`) REFERENCES `nt_factors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nt_material_records_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `nt_materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `nt_material_records_transport_factor_id_foreign` FOREIGN KEY (`transport_factor_id`) REFERENCES `nt_factors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_materials
DROP TABLE IF EXISTS `nt_materials`;
CREATE TABLE `nt_materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `category_id` bigint unsigned DEFAULT NULL COMMENT '分类ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '材料名称',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '材料编码',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `specification` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '规格型号',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '材料描述',
  `factor_id` bigint unsigned DEFAULT NULL COMMENT '排放因子ID',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_materials_company_id_index` (`company_id`),
  KEY `nt_materials_category_id_index` (`category_id`),
  KEY `nt_materials_factor_id_index` (`factor_id`),
  KEY `nt_materials_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: nt_production_process_categories
DROP TABLE IF EXISTS `nt_production_process_categories`;
CREATE TABLE `nt_production_process_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '企业ID(0=公共)',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分类编码',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '分类描述',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_category_code` (`company_id`,`code`),
  KEY `nt_production_process_categories_company_id_index` (`company_id`),
  KEY `nt_production_process_categories_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工艺分类表(公共company_id=0 + 企业自定义)';

-- 表数据: nt_production_process_categories (12 行)
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('1', '0', '碳酸盐/CO2类', 'carbonate_co2', NULL, '1', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('2', '0', '化石燃料类', 'fossil_fuel', NULL, '2', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('3', '0', '煅烧/焙烧/玻璃/水泥/陶瓷类', 'calcination_ceramic', NULL, '3', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('4', '0', '金属冶炼类', 'metal_smelting', NULL, '4', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('5', '0', '化工类', 'chemical', NULL, '5', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('6', '0', '气体/火炬类', 'gas_flare', NULL, '6', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('7', '0', 'HFC/氟化物类', 'hfc_fluoride', NULL, '7', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('8', '0', '固废/废液/其他', 'waste_other', NULL, '8', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('9', '0', '运输类', 'transport', NULL, '9', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('10', '0', '农业类', 'agriculture', NULL, '10', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('11', '0', '能源/电力/氢能类', 'energy_power', NULL, '11', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');
INSERT INTO `nt_production_process_categories` (`id`, `company_id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('12', '0', '焦化/CRU/RFCC类', 'coking_cru', NULL, '12', 'active', '2026-07-22 17:05:57', '2026-07-22 17:05:57');

-- 表结构: nt_production_process_data
DROP TABLE IF EXISTS `nt_production_process_data`;
CREATE TABLE `nt_production_process_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `definition_id` bigint unsigned NOT NULL COMMENT '工艺定义ID',
  `data_time` date NOT NULL COMMENT '数据时间',
  `payload` json NOT NULL COMMENT '工艺表单数据(完整JSON)',
  `tco2` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT 'CO2排放量(冗余列)',
  `tch4` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT 'CH4排放量(冗余)',
  `tn2o` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT 'N2O排放量(冗余)',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_process_data` (`company_id`,`definition_id`,`data_time`),
  KEY `idx_company_time` (`company_id`,`data_time`),
  KEY `idx_definition` (`definition_id`),
  KEY `idx_tco2` (`tco2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工艺过程数据表(企业数据,通用JSON模式)';

-- 表结构: nt_production_processes
DROP TABLE IF EXISTS `nt_production_processes`;
CREATE TABLE `nt_production_processes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '企业ID(0=公共)',
  `category_id` bigint unsigned DEFAULT NULL COMMENT '分类ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '工序名称',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '工序编码',
  `route` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '前端路由标识',
  `formula` text COLLATE utf8mb4_unicode_ci COMMENT '计算公式表达式',
  `formula_params` json DEFAULT NULL COMMENT '参数定义(JSON)',
  `default_factors` json DEFAULT NULL COMMENT '默认因子配置(JSON)',
  `formula_desc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '公式描述(中文)',
  `has_children` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有子表',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序号',
  `parameters` json DEFAULT NULL COMMENT '工艺参数（JSON格式）',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_process_code` (`company_id`,`code`),
  KEY `nt_production_processes_company_id_index` (`company_id`),
  KEY `nt_production_processes_category_id_index` (`category_id`),
  KEY `nt_production_processes_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工艺类型定义表(公共company_id=0 + 企业自定义)';

-- 表数据: nt_production_processes (78 行)
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, '碳酸原料分解', 'co3Decomposition', NULL, NULL, NULL, NULL, NULL, '0', '0', '{\"route\": \"Co3DecompositionForm\", \"title\": \"碳酸原料分解\", \"method\": \"co3Decomposition\", \"trade_process_id\": 91}', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('2', '1', NULL, '煤矸石替代原燃料燃烧', 'coalReplace', NULL, NULL, NULL, NULL, NULL, '0', '0', '{\"route\": \"CoalReplaceForm\", \"title\": \"煤矸石替代原燃料燃烧\", \"method\": \"coalReplace\", \"trade_process_id\": 92}', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('3', '0', '1', '碳酸盐分解', 'carbonate', 'carbonate', 'number * (ratio / 100) * factor * (use_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"原料用量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例(%)\", \"default\": 100}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"利用率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"carbonate_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 原料用量 × 比例 × 排放因子 × 利用率', '1', '1', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('4', '0', '1', '铝生产(炭阳极)', 'al', 'al', 'number * factor * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"原铝产量(tAl)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(tCO₂/tAl)\", \"default\": 0.42}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CO2\", \"source\": \"factor_gwp\", \"default\": 1}, \"factor\": {\"key\": \"al_factor\", \"source\": \"factor_process\", \"default\": 0.42}}', 'CO2 = 原铝产量 × 排放因子 × GWP', '0', '2', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('5', '0', '1', 'CO3分解', 'co3_decomposition', 'co3Decomposition', 'consumption_net * (cao_ratio * 0.785 + mgo_ratio * 1.092)', '{\"fields\": [{\"key\": \"consumption_net\", \"min\": 0, \"type\": \"number\", \"label\": \"净消耗量(吨)\", \"required\": true}, {\"key\": \"cao_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"CaO含量占比(%)\"}, {\"key\": \"mgo_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"MgO含量占比(%)\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 净耗量 × (CaO% × 0.785 + MgO% × 1.092)', '0', '3', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('6', '0', '1', '碳化矿排放', 'carbonate_ore', 'carbonateOre', 'number * (ratio / 100) * factor * (use_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"矿石量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"碳酸盐含量(%)\"}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"煅烧比例(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"carbonate_ore_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 矿石量 × 碳酸盐含量 × 排放因子 × 煅烧比例', '0', '4', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('7', '0', '1', '碳化原料', 'carbonize', 'carbonize', 'number * (ratio / 100) * factor * (use_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"原料量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例(%)\", \"default\": 100}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"利用率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"carbonize_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 原料量 × 比例 × 排放因子 × 利用率', '1', '5', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('8', '0', '1', 'CO2保护气', 'co2_protect', 'co2Protect', 'number * (ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"CO2用量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"CO2体积占比(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 用量 × CO2体积占比', '1', '6', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('9', '0', '1', '外购CO2', 'buy_co2', 'buyCo2', 'number * (ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"外购量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"纯度(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 外购量 × 纯度', '0', '7', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('10', '0', '1', 'CO2回收', 'co2_recovery', 'co2Recovery', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 各路径合计(前端汇总)', '1', '8', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('11', '0', '1', 'CO2铝工艺', 'co2_al', 'co2Al', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 各路径合计(前端汇总)', '1', '9', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('12', '0', '1', '草酸分解', 'oxalic', 'oxalicDecomposition', 'number * factor * (ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\", \"default\": 0.698}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"纯度(%)\", \"default\": 99.6}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"oxalic_factor\", \"source\": \"factor_process\", \"default\": 0.698}}', 'CO2 = 消耗量 × 因子(0.698) × 纯度', '0', '10', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('13', '0', '1', '尿素脱硫', 'urea_desulfur', 'ureaAsDesulfurization', 'number * (ratio / 100) * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例(%)\", \"default\": 100}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\", \"default\": 0.733}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"urea_desulfur_factor\", \"source\": \"factor_process\", \"default\": 0.733}}', 'CO2 = 消耗量 × 比例 × 因子(0.733)', '0', '11', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('14', '0', '1', '磷矿石', 'phosphate', 'phosphate', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"phosphate_factor\", \"source\": \"factor_process\", \"default\": 0.24}}', 'CO2 = 消耗量 × 排放因子', '0', '12', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('15', '0', '3', '水泥熟料', 'clinker', 'clinker', 'number * (cao_ratio * 0.785 + mgo_ratio * 1.092)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"熟料量(吨)\", \"required\": true}, {\"key\": \"cao_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"CaO含量占比(%)\"}, {\"key\": \"mgo_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"MgO含量占比(%)\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 熟料量 × (CaO% × 0.785 + MgO% × 1.092)', '1', '13', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('16', '0', '3', '陶瓷', 'ceramics', 'ceramics', 'number * (ratio / 100) * (cao_ratio * 0.785 + mgo_ratio * 1.092)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"原料消耗量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"利用率(%)\", \"default\": 100}, {\"key\": \"cao_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"CaO含量占比(%)\"}, {\"key\": \"mgo_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"MgO含量占比(%)\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 原料消耗 × 利用率 × (CaO% × 0.785 + MgO% × 1.092)', '1', '14', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('17', '0', '3', '玻璃碳酸盐', 'glass_co3', 'glassCo3', 'number * (ratio / 100) * factor * (use_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"矿石量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"碳酸盐含量(%)\"}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"煅烧比例(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"glass_co3_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 矿石量 × 碳酸盐含量 × 排放因子 × 煅烧比例', '1', '15', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('18', '0', '3', '烟气脱硫', 'fgd', 'fgd', 'number * (ratio / 100) * factor * (use_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"脱硫剂量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例(%)\", \"default\": 100}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"利用率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"fgd_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 脱硫剂量 × 比例 × 排放因子 × 利用率', '1', '16', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('19', '0', '3', '煅烧', 'calcination', 'calcination', 'total_carbon_in - total_carbon_out + vdaf_co2', '{\"fields\": [{\"key\": \"total_carbon_in\", \"min\": 0, \"type\": \"number\", \"label\": \"碳入(吨)\", \"required\": true}, {\"key\": \"total_carbon_out\", \"min\": 0, \"type\": \"number\", \"label\": \"碳出(吨)\", \"required\": true}, {\"key\": \"vdaf_co2\", \"type\": \"number\", \"label\": \"挥发分CH4折算CO2(吨)\", \"default\": 0}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 碳入 - 碳出 + 挥发分CH4折算CO2', '1', '17', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('20', '0', '3', '焙烧', 'roasting', 'roasting', 'total_carbon_in - total_carbon_out', '{\"fields\": [{\"key\": \"total_carbon_in\", \"min\": 0, \"type\": \"number\", \"label\": \"碳入(填充料+待焙烧品)(吨)\", \"required\": true}, {\"key\": \"total_carbon_out\", \"min\": 0, \"type\": \"number\", \"label\": \"碳出(碳输出+焙烧品)(吨)\", \"required\": true}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 碳入(填充料+待焙烧品) - 碳出(碳输出+焙烧品)', '1', '18', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('21', '0', '3', '石墨化', 'graphiting', 'graphiting', 'total_carbon_in - total_carbon_out + vdaf_co2', '{\"fields\": [{\"key\": \"total_carbon_in\", \"min\": 0, \"type\": \"number\", \"label\": \"碳入(保温料+待石墨化品)(吨)\", \"required\": true}, {\"key\": \"total_carbon_out\", \"min\": 0, \"type\": \"number\", \"label\": \"碳出(粉尘+碎屑+石墨化品)(吨)\", \"required\": true}, {\"key\": \"vdaf_co2\", \"type\": \"number\", \"label\": \"挥发分CH4折算CO2(吨)\", \"default\": 0}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 碳入 - 碳出 + 挥发分CH4折算CO2', '1', '19', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('22', '0', '3', '石油焦煅烧', 'petcoke', 'shiYouJiaoDuanShao', '(before_number * before_content - (after_number + dust) * after_content) * 44 / 12', '{\"fields\": [{\"key\": \"before_number\", \"min\": 0, \"type\": \"number\", \"label\": \"煅前量(吨)\", \"required\": true}, {\"key\": \"before_content\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"煅前含碳量(%)\"}, {\"key\": \"after_number\", \"min\": 0, \"type\": \"number\", \"label\": \"煅后量(吨)\"}, {\"key\": \"dust\", \"min\": 0, \"type\": \"number\", \"label\": \"粉尘量(吨)\"}, {\"key\": \"after_content\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"煅后含碳量(%)\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = (煅前量×煅前含碳 - (煅后量+粉尘)×煅后含碳) × 44/12', '0', '20', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('23', '0', '4', '硅铁', 'fesi', 'feSi', 'number * factor * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(tCO₂/t)\", \"default\": 2.79}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CO2\", \"source\": \"factor_gwp\", \"default\": 1}, \"factor\": {\"key\": \"fesi_factor\", \"source\": \"factor_process\", \"default\": 2.79}}', 'CO2 = 产量 × 排放因子 × GWP', '0', '21', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('24', '0', '4', '白云石', 'dolomite', 'dolomite', 'number * ratio * 0.477', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 消耗量 × 比例 × 0.477', '0', '22', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('25', '0', '4', '纯碱', 'sodaash', 'sodaash', 'number * factor * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CO2\", \"source\": \"factor_gwp\", \"default\": 1}, \"factor\": {\"key\": \"sodaash_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 消耗量 × 排放因子 × GWP', '0', '23', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('26', '0', '4', '电极消耗', 'electrode', 'electrode', 'number * factor * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\", \"default\": 3.663}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CO2\", \"source\": \"factor_gwp\", \"default\": 1}, \"factor\": {\"key\": \"electrode_factor\", \"source\": \"factor_process\", \"default\": 3.663}}', 'CO2 = 消耗量 × 排放因子(3.663) × GWP', '0', '24', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('27', '0', '4', '阳极效应', 'anode_effect', 'anodeEffect', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = CF4排放 + C2F6排放(双因子计算,前端汇总)', '0', '25', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('28', '0', '4', '含碳原料(钢铁)', 'steel', 'steel', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"steel_factor\", \"source\": \"factor_process\", \"default\": 3.667}}', 'CO2 = 消耗量 × 排放因子', '0', '26', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('29', '0', '4', '残渣', 'residue', 'residue', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"residue_factor\", \"source\": \"factor_process\", \"default\": 3.667}}', 'CO2 = 消耗量 × 排放因子', '0', '27', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('30', '0', '4', '焦油', 'tar', 'tar', 'number * content', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"content\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"含碳量\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 消耗量 × 含碳量', '0', '28', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('31', '0', '5', '溶剂消耗', 'solvent', 'solventConsumption', 'number * (ratio / 100) * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"纯度(%)\", \"default\": 100}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"solvent_factor\", \"source\": \"factor_process\", \"default\": 0.5}}', 'CO2 = 消耗量 × 纯度 × 排放因子', '0', '29', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('32', '0', '5', '化工原料', 'chemical', 'chemical', 'material_carbon - product_carbon - other_carbon', '{\"fields\": [{\"key\": \"material_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"原料总碳(吨)\", \"required\": true}, {\"key\": \"product_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"产品总碳(吨)\", \"required\": true}, {\"key\": \"other_carbon\", \"type\": \"number\", \"label\": \"其他总碳(吨)\", \"default\": 0}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 原料总碳 - 产品总碳 - 其他总碳', '1', '30', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('33', '0', '5', '环氧乙烷', 'epoxy', 'huanYangYiXi', 'material_carbon - product_eo_carbon - product_eg_carbon', '{\"fields\": [{\"key\": \"material_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"原料碳(吨)\", \"required\": true}, {\"key\": \"product_eo_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"环氧乙烷碳(吨)\", \"required\": true}, {\"key\": \"product_eg_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"乙二醇碳(吨)\", \"required\": true}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 原料碳 - 环氧乙烷碳 - 乙二醇碳', '1', '31', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('34', '0', '5', '硝酸', 'hno3', 'hno3', '(number * factor * (1 - remove_ratio / 100) * (running_ratio / 100)) / 1000', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(kgN₂O/t)\"}, {\"key\": \"remove_ratio\", \"type\": \"number\", \"label\": \"N2O去除率(%)\", \"default\": 0}, {\"key\": \"running_ratio\", \"type\": \"number\", \"label\": \"运转率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tn2o\", \"type\": \"number\", \"label\": \"N₂O排放量(tN₂O)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"hno3_factor\", \"source\": \"factor_process\", \"default\": 6}}', 'N2O = 产量 × 因子 × (1-去除率) × 运转率 / 1000', '0', '32', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('35', '0', '5', '己二酸', 'adipic_acid', 'adipicAcid', '(number * factor * (1 - remove_ratio / 100) * (running_ratio / 100)) / 1000', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(kgN₂O/t)\"}, {\"key\": \"remove_ratio\", \"type\": \"number\", \"label\": \"N2O去除率(%)\", \"default\": 0}, {\"key\": \"running_ratio\", \"type\": \"number\", \"label\": \"运转率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tn2o\", \"type\": \"number\", \"label\": \"N₂O排放量(tN₂O)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"adipic_acid_factor\", \"source\": \"factor_process\", \"default\": 300}}', 'N2O = 产量 × 因子 × (1-去除率) × 运转率 / 1000', '0', '33', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('36', '0', '5', '钛白粉', 'tio2', 'tiO2', 'number * factor * (1 - remove_ratio / 100) * (running_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"remove_ratio\", \"type\": \"number\", \"label\": \"去除率(%)\", \"default\": 0}, {\"key\": \"running_ratio\", \"type\": \"number\", \"label\": \"运转率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"tio2_factor\", \"source\": \"factor_process\", \"default\": 0.5}}', 'CO2 = 产量 × 因子 × (1-去除率) × 运转率', '0', '34', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('37', '0', '5', '乙烯裂解', 'vinyl', 'yiXiLieJie', 'velocity * duration * (mol_co2 + mol_co) * 19.77 / 10000', '{\"fields\": [{\"key\": \"velocity\", \"min\": 0, \"type\": \"number\", \"label\": \"流速(Nm³/h)\", \"required\": true}, {\"key\": \"duration\", \"min\": 0, \"type\": \"number\", \"label\": \"时长(h)\", \"required\": true}, {\"key\": \"mol_co2\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"CO2摩尔分数(%)\"}, {\"key\": \"mol_co\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"CO摩尔分数(%)\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 流速 × 时长 × (CO2+CO摩尔分数) × 19.77 / 10000', '0', '35', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('38', '0', '5', '制氢装置', 'h2', 'zhiQingZhuangZhi', 'velocity * duration * (mol + by_mol) * 19.77 / 10000', '{\"fields\": [{\"key\": \"velocity\", \"min\": 0, \"type\": \"number\", \"label\": \"流速(Nm³/h)\", \"required\": true}, {\"key\": \"duration\", \"min\": 0, \"type\": \"number\", \"label\": \"时长(h)\", \"required\": true}, {\"key\": \"mol\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"主摩尔分数(%)\"}, {\"key\": \"by_mol\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"副摩尔分数(%)\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 流速 × 时长 × (主+副摩尔分数) × 19.77 / 10000', '0', '36', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('39', '0', '5', '其他产品生产', 'other_product', 'qiTaChanPinShengChan', '(material_carbon - product_carbon - waste_carbon) * 44 / 12', '{\"fields\": [{\"key\": \"material_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"原料总碳(吨)\", \"required\": true}, {\"key\": \"product_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"产品总碳(吨)\", \"required\": true}, {\"key\": \"waste_carbon\", \"type\": \"number\", \"label\": \"废弃物总碳(吨)\", \"default\": 0}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = (原料碳 - 产品碳 - 废弃物碳) × 44/12', '0', '37', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('40', '0', '6', '正常火炬', 'normal_flare', 'zhengChanghuoju', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 含碳组分CO2 + 碳氢燃烧CO2(前端汇总)', '1', '38', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('41', '0', '6', '油气异常火炬', 'oil_gas_abnormal_flare', 'oilGasAbnormalFlare', 'velocity * duration * mol * efficiency * 44 / 22.4', '{\"fields\": [{\"key\": \"velocity\", \"min\": 0, \"type\": \"number\", \"label\": \"流速(Nm³/h)\", \"required\": true}, {\"key\": \"duration\", \"min\": 0, \"type\": \"number\", \"label\": \"时长(h)\", \"required\": true}, {\"key\": \"mol\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"摩尔分数(%)\"}, {\"key\": \"efficiency\", \"type\": \"number\", \"label\": \"燃烧效率(%)\", \"default\": 98}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 流速 × 时长 × 摩尔分数 × 燃烧效率 × 44/22.4', '0', '39', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('42', '0', '6', '油气正常火炬', 'oil_gas_normal_flare', 'oilGasNormalFlare', 'velocity * mol * efficiency * gwp', '{\"fields\": [{\"key\": \"velocity\", \"min\": 0, \"type\": \"number\", \"label\": \"流速(Nm³/h)\", \"required\": true}, {\"key\": \"mol\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"摩尔分数(%)\"}, {\"key\": \"efficiency\", \"type\": \"number\", \"label\": \"燃烧效率(%)\", \"default\": 98}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CO2\", \"source\": \"factor_gwp\", \"default\": 1}}', 'CO2 = 流速 × 摩尔分数 × 燃烧效率 × GWP', '0', '40', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('43', '0', '6', '逸散排放', 'methane_escape', 'methaneEscape', 'number * 0.67 * 10 * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"填充量(吨)\", \"required\": true}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 21}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂e)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CH4\", \"source\": \"factor_gwp\", \"default\": 21}}', 'CO2e = 填充量 × 0.67 × 10 × GWP(CH4)', '0', '41', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('44', '0', '6', '废气处理', 'waste_gas', 'wasteGas', 'number * density * ratio * 44 / 12', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"废气量(万Nm³)\", \"required\": true}, {\"key\": \"density\", \"min\": 0, \"type\": \"number\", \"label\": \"总烃浓度(mg/Nm³)\"}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"碳氧化率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 废气量 × 总烃浓度 × 碳氧化率 × 44/12', '0', '42', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('45', '0', '6', '燃气脱硫', 'fuel_gas_desulfur', 'fuelGasDesulfurization', 'number * (ratio / 100) * factor * (use_ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"脱硫剂量(吨)\", \"required\": true}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例(%)\", \"default\": 100}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"利用率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"fuel_gas_desulfur_factor\", \"source\": \"factor_process\", \"default\": 0.44}}', 'CO2 = 脱硫剂量 × 比例 × 排放因子 × 利用率', '0', '43', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('46', '0', '6', '烟气焚烧', 'fuel_gas_burn', 'fuelGasBurn', 'discharge * tar_ratio * heating * carbon_ratio * (burn_ratio / 100) * 44 / 12', '{\"fields\": [{\"key\": \"discharge\", \"min\": 0, \"type\": \"number\", \"label\": \"排放量(吨)\", \"required\": true}, {\"key\": \"tar_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"焦油含量(%)\"}, {\"key\": \"heating\", \"min\": 0, \"type\": \"number\", \"label\": \"低位发热量(GJ/t)\"}, {\"key\": \"carbon_ratio\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"单位热值含碳量(tc/GJ)\"}, {\"key\": \"burn_ratio\", \"type\": \"number\", \"label\": \"碳氧化率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 排放量 × 焦油含量 × 低位发热量 × 含碳量 × 碳氧化率 × 44/12', '0', '44', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('47', '0', '6', 'CH4回收', 'ch4_recovery', 'ch4Recovery', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 各路径合计(前端汇总)', '1', '45', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('48', '0', '6', '甲烷回收', 'methane_recovery', 'methaneRecovery', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 各路径合计(前端汇总)', '1', '46', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('49', '0', '7', 'SF6', 'sf6', 'sf6', 'capacity_diff * gwp / 1000', '{\"fields\": [{\"key\": \"capacity_diff\", \"type\": \"number\", \"label\": \"容量差(kg)\", \"required\": true}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 22800}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"SF6\", \"source\": \"factor_gwp\", \"default\": 22800}}', 'CO2 = 容量差 × GWP(SF6) / 1000', '0', '47', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('50', '0', '7', 'HCFC-22', 'hcfc22', 'hcfc22', 'actual_destroy * gwp', '{\"fields\": [{\"key\": \"actual_destroy\", \"type\": \"number\", \"label\": \"实际销毁量(吨)\", \"required\": true}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 11700}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"HFC23\", \"source\": \"factor_gwp\", \"default\": 11700}}', 'CO2 = 实际销毁量 × GWP(11700)', '0', '48', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('51', '0', '7', 'HFC-23', 'hfc23', 'hfc23', 'actual_destroy * 44 / 70 * gwp', '{\"fields\": [{\"key\": \"actual_destroy\", \"type\": \"number\", \"label\": \"实际销毁量(吨)\", \"required\": true}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 11700}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"HFC23\", \"source\": \"factor_gwp\", \"default\": 11700}}', 'CO2 = 实际销毁量 × 44/70 × GWP(11700)', '0', '49', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('52', '0', '7', 'HFC副产', 'hfc_by', 'hfcBy', 'number * (factor / 100) * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产量(吨)\", \"required\": true}, {\"key\": \"factor\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"排放因子(%)\"}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"HFC_by\", \"source\": \"factor_gwp\", \"default\": 1}}', 'CO2 = 产量 × 排放因子% × GWP', '0', '50', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('53', '0', '7', '制冷设备', 'refeq', 'refeq', 'capacity_diff * gwp', '{\"fields\": [{\"key\": \"capacity_diff\", \"type\": \"number\", \"label\": \"容量差(期初+购入-期末-产品用量)(kg)\", \"required\": true}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"refeq_gwp\", \"source\": \"factor_gwp\", \"default\": 1}}', 'CO2 = 容量差 × GWP', '0', '51', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('54', '0', '7', '氟聚合物', 'fluoropolymer', 'fluoropolymer', 'number * density * gwp / 1000000000', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产生量(m³)\", \"required\": true}, {\"key\": \"density\", \"min\": 0, \"type\": \"number\", \"label\": \"密度(mg/m³)\"}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"fluoropolymer_gwp\", \"source\": \"factor_gwp\", \"default\": 1}}', 'CO2 = 产生量 × 密度 × GWP / 10⁹', '0', '52', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('55', '0', '8', '固体废物', 'solid_waste', 'solidWaste', 'number * content * (mineral_ratio / 100) * (burn_ratio / 100) * 44 / 12', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"焚烧量(吨)\", \"required\": true}, {\"key\": \"content\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"碳含量(%)\"}, {\"key\": \"mineral_ratio\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"矿物碳比例(%)\"}, {\"key\": \"burn_ratio\", \"type\": \"number\", \"label\": \"燃烧效率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 焚烧量 × 碳含量 × 矿物碳比例 × 燃烧效率 × 44/12', '0', '53', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('56', '0', '8', '含碳物料', 'carbon_material', 'carbonMaterialUsage', 'number * factor * (ratio / 100)', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"数量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"比例(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"carbon_material_factor\", \"source\": \"factor_process\", \"default\": 3.667}}', 'CO2 = 数量 × 排放因子 × 比例', '1', '54', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('57', '0', '8', '含碳辅料', 'carbon_auxiliary', 'carbonMaterialConsumption', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"数量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"carbon_auxiliary_factor\", \"source\": \"factor_process\", \"default\": 3.667}}', 'CO2 = 数量 × 排放因子', '0', '55', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('58', '0', '8', '沥青氧化', 'asphalt_oxidation', 'yangHuaLiQing', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"流量相关值\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\", \"default\": 0.03}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"asphalt_oxidation_factor\", \"source\": \"factor_process\", \"default\": 0.03}}', 'CO2 = 流量 × 排放因子(0.03)', '0', '56', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('59', '0', '8', 'CBS固碳产品', 'cbs_product', 'cbsProduct', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"产量(吨)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子\"}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"cbs_product_factor\", \"source\": \"factor_process\", \"default\": 0.5}}', 'CO2 = 产量 × 排放因子', '0', '57', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('60', '0', '9', '天然气长输', 'transport_ch4', 'transportCh4', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"输送量(万Nm³)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(tCH₄/万Nm³)\"}], \"outputs\": [{\"key\": \"tch4\", \"type\": \"number\", \"label\": \"CH₄排放量(tCH₄)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"transport_ch4_factor\", \"source\": \"factor_process\", \"default\": 0.01}}', 'CH4 = 输送量 × 排放因子', '0', '58', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('61', '0', '9', '道路车辆', 'road_vehicle', 'roadVehicle', 'factor * number', '{\"fields\": [{\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(kgCO₂/km)\"}, {\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"行驶里程(km)\", \"required\": true}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"road_vehicle_factor\", \"source\": \"factor_process\", \"default\": 0.2}}', 'CO2 = 排放因子 × 行驶里程', '0', '59', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('62', '0', '9', '船舶燃料', 'ship_fuel', 'shipFuel', 'factor * number', '{\"fields\": [{\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(tCO₂/t)\"}, {\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"消耗量(吨)\", \"required\": true}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"ship_fuel_factor\", \"source\": \"factor_process\", \"default\": 3.1}}', 'CO2 = 排放因子 × 消耗量', '0', '60', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('63', '0', '10', '农田', 'farmland', 'farmland', '(tn2o + volt_tn2o + melt_tn2o) * gwp', '{\"fields\": [{\"key\": \"tn2o\", \"min\": 0, \"type\": \"number\", \"label\": \"直接排放N2O(tN₂O)\", \"required\": true}, {\"key\": \"volt_tn2o\", \"type\": \"number\", \"label\": \"挥发N2O(tN₂O)\", \"default\": 0}, {\"key\": \"melt_tn2o\", \"type\": \"number\", \"label\": \"淋溶N2O(tN₂O)\", \"default\": 0}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP(N2O)\", \"default\": 265}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂e)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"N2O\", \"source\": \"factor_gwp\", \"default\": 265}}', 'CO2e = (直接N2O + 挥发N2O + 淋溶N2O) × GWP(265)', '0', '61', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('64', '0', '10', '稻田', 'paddy', 'paddy', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"面积(公顷)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(kgCH₄/hm²)\"}], \"outputs\": [{\"key\": \"tch4\", \"type\": \"number\", \"label\": \"CH₄排放量(tCH₄)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"paddy_factor\", \"source\": \"factor_process\", \"default\": 300}}', 'CH4 = 面积 × 排放因子', '1', '62', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('65', '0', '10', '畜牧', 'animal', 'animal', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tch4\", \"type\": \"number\", \"label\": \"CH₄排放量(tCH₄)\", \"precision\": 6}, {\"key\": \"tn2o\", \"type\": \"number\", \"label\": \"N₂O排放量(tN₂O)\", \"precision\": 6}]}', '[]', '肠道发酵CH4 + 粪便管理CH4 + 粪便管理N2O(前端分步计算汇总)', '0', '63', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('66', '0', '10', '沼气', 'biogas', 'biogas', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CH4_biogas\", \"source\": \"factor_gwp\", \"default\": 27.9}}', 'CO2 = (自用+外供-销毁) × 燃烧效率 × 燃烧因子(前端汇总)', '0', '64', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('67', '0', '12', '焦化过程', 'coking_process', 'cokingProcess', 'total_carbon_in - total_carbon_out', '{\"fields\": [{\"key\": \"total_carbon_in\", \"min\": 0, \"type\": \"number\", \"label\": \"碳入(炼焦原料总碳)(吨)\", \"required\": true}, {\"key\": \"total_carbon_out\", \"min\": 0, \"type\": \"number\", \"label\": \"碳出(焦炭+煤气+副产品总碳)(吨)\", \"required\": true}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 碳入(炼焦原料) - 碳出(焦炭+煤气+副产品)', '1', '65', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('68', '0', '12', '流化焦化', 'coking', 'coking', 'surplus * content * ratio * 44 / 12', '{\"fields\": [{\"key\": \"surplus\", \"min\": 0, \"type\": \"number\", \"label\": \"多余焦炭量(吨)\", \"required\": true}, {\"key\": \"content\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"平均含碳量(%)\"}, {\"key\": \"ratio\", \"type\": \"number\", \"label\": \"氧化率(%)\", \"default\": 100}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 多余焦炭量 × 含碳量 × 氧化率 × 44/12', '0', '66', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('69', '0', '12', 'CRU非连续催化烧焦', 'cru_non_continuous', 'qiTaFeiLianXuCuiHuaShaoJiao', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 烧焦量 × 碳含量 × 氧化率 × 44/12(前端复杂计算汇总)', '0', '67', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('70', '0', '12', 'RFCC连续催化烧焦', 'rfcc_continuous', 'qiTaLianXuCuiHuaShaoJiao', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 连续烧焦量 × 碳含量 × 氧化率 × 44/12(前端复杂计算汇总)', '0', '68', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('71', '0', '12', '原料气', 'raw_gas', 'rawGasList', '(1 - res_ratio) * number * (1 - use_ratio) * (1 - ctn_ratio * rmv_ratio) * gwp', '{\"fields\": [{\"key\": \"res_ratio\", \"type\": \"number\", \"label\": \"残留比例\", \"default\": 0}, {\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"原料量(吨)\", \"required\": true}, {\"key\": \"use_ratio\", \"type\": \"number\", \"label\": \"利用率\", \"default\": 0}, {\"key\": \"ctn_ratio\", \"type\": \"number\", \"label\": \"收集率(%)\", \"default\": 0}, {\"key\": \"rmv_ratio\", \"type\": \"number\", \"label\": \"去除率(%)\", \"default\": 0}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP\", \"default\": 1}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CO2\", \"source\": \"factor_gwp\", \"default\": 1}}', 'CO2 = (1-残留) × 原料量 × (1-利用率) × (1-收集×去除) × GWP', '0', '69', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('72', '0', '12', '原料气副产', 'raw_gas_by', 'rawGasBy', 'total', '{\"fields\": [], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 各副产物汇总(前端子表汇总)', '1', '70', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('73', '0', '12', '原油碳含量', 'raw_material_carbon', 'rawMaterialCarbon', '(material_carbon - product_carbon - waste_carbon) * 44 / 12', '{\"fields\": [{\"key\": \"material_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"原料总碳(吨)\", \"required\": true}, {\"key\": \"product_carbon\", \"min\": 0, \"type\": \"number\", \"label\": \"产品总碳(吨)\", \"required\": true}, {\"key\": \"waste_carbon\", \"type\": \"number\", \"label\": \"废弃物总碳(吨)\", \"default\": 0}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = (原料总碳 - 产品总碳 - 废弃物总碳) × 44/12', '1', '71', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('74', '0', '12', '原油产品', 'crude_product', 'crudeProduct', 'total_a - total_b - total_c', '{\"fields\": [{\"key\": \"total_a\", \"min\": 0, \"type\": \"number\", \"label\": \"原料总碳(吨)\", \"required\": true}, {\"key\": \"total_b\", \"min\": 0, \"type\": \"number\", \"label\": \"产品总碳(吨)\", \"required\": true}, {\"key\": \"total_c\", \"type\": \"number\", \"label\": \"废弃物总碳(吨)\", \"default\": 0}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂)\", \"precision\": 6}]}', '[]', 'CO2 = 原料总碳 - 产品总碳 - 废弃物总碳', '1', '72', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('75', '0', '6', '气体开采', 'gas_mining', 'gasMiningVentilation', 'number * factor', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"设施数(个)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(tCH₄/设施·年)\"}], \"outputs\": [{\"key\": \"tch4\", \"type\": \"number\", \"label\": \"CH₄排放量(tCH₄)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"gas_mining_factor\", \"source\": \"factor_process\", \"default\": 0.5}}', 'CH4 = 设施数 × 排放因子', '0', '73', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('76', '0', '6', '气体损失', 'gas_loss', 'gasLossEmission', 'number * factor / 1000', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"设施数(个)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"排放因子(kgCH₄/设施·年)\"}], \"outputs\": [{\"key\": \"tch4\", \"type\": \"number\", \"label\": \"CH₄排放量(tCH₄)\", \"precision\": 6}]}', '{\"factor\": {\"key\": \"gas_loss_factor\", \"source\": \"factor_process\", \"default\": 0.5}}', 'CH4 = 设施数 × 排放因子 / 1000', '0', '74', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('77', '0', '6', '气体处理损失', 'gas_handl_loss', 'gasHandlLoss', 'number * factor * gwp', '{\"fields\": [{\"key\": \"number\", \"min\": 0, \"type\": \"number\", \"label\": \"天然气处理量(亿Nm³)\", \"required\": true}, {\"key\": \"factor\", \"step\": 0.0001, \"type\": \"number\", \"label\": \"逸散因子(tCH₄/亿Nm³)\"}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP(CH4)\", \"default\": 28}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂e)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CH4\", \"source\": \"factor_gwp\", \"default\": 28}, \"factor\": {\"key\": \"gas_handl_loss_factor\", \"source\": \"factor_process\", \"default\": 0.5}}', 'CO2e = 天然气处理量 × 逸散因子 × GWP(28)', '0', '75', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');
INSERT INTO `nt_production_processes` (`id`, `company_id`, `category_id`, `name`, `code`, `route`, `formula`, `formula_params`, `default_factors`, `formula_desc`, `has_children`, `sort`, `parameters`, `status`, `created_at`, `updated_at`) VALUES ('78', '0', '6', '气体勘探', 'gas_exploration', 'gasExploration', 'velocity * duration * (mol / 100) * 7.17 / 10000 * gwp', '{\"fields\": [{\"key\": \"velocity\", \"min\": 0, \"type\": \"number\", \"label\": \"流速(Nm³/h)\", \"required\": true}, {\"key\": \"duration\", \"min\": 0, \"type\": \"number\", \"label\": \"时长(h)\", \"required\": true}, {\"key\": \"mol\", \"max\": 100, \"min\": 0, \"type\": \"number\", \"label\": \"CH4摩尔分数(%)\"}, {\"key\": \"gwp\", \"type\": \"number\", \"label\": \"GWP(CH4)\", \"default\": 28}], \"outputs\": [{\"key\": \"tco2\", \"type\": \"number\", \"label\": \"CO₂排放量(tCO₂e)\", \"precision\": 6}]}', '{\"gwp\": {\"key\": \"CH4\", \"source\": \"factor_gwp\", \"default\": 28}}', 'CO2e = 流速 × 时长 × CH4摩尔分数 × 7.17 / 10000 × GWP', '0', '76', NULL, 'active', '2026-07-22 17:07:01', '2026-07-22 17:07:01');

-- 表结构: nt_production_product_categories
DROP TABLE IF EXISTS `nt_production_product_categories`;
CREATE TABLE `nt_production_product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '分类描述',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_production_product_categories_company_id_index` (`company_id`),
  KEY `nt_production_product_categories_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: nt_production_product_categories (4 行)
INSERT INTO `nt_production_product_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', '电子产品', '电子元器件、电路板、电子设备等产品分类', '1', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `nt_production_product_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('2', '1', '机械设备', '工业机械设备、自动化设备、传动装置等产品分类', '2', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `nt_production_product_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('3', '1', '化工产品', '化工原料、化学制剂、精细化工品等产品分类', '3', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `nt_production_product_categories` (`id`, `company_id`, `name`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES ('4', '1', '消费品', '日用消费品、食品、纺织品等产品分类', '4', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');

-- 表结构: nt_production_product_data
DROP TABLE IF EXISTS `nt_production_product_data`;
CREATE TABLE `nt_production_product_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `data_time` date NOT NULL COMMENT '数据时间',
  `dept_id` bigint unsigned DEFAULT NULL COMMENT '部门ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '产品名称',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `number` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '产品数量',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '产品总值',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pd_company_time_product` (`company_id`,`data_time`,`name`),
  KEY `idx_pd_company_id` (`company_id`),
  KEY `idx_pd_data_time` (`data_time`),
  KEY `idx_pd_company_data_time` (`company_id`,`data_time`),
  KEY `idx_pd_company_dept` (`company_id`,`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: nt_production_product_data (26 行)
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('1', '11', '2026-01-01', NULL, '标准砖', '块', '1070.00', '44940.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('2', '11', '2026-01-01', NULL, '空心砖', '块', '1067.00', '46948.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('3', '11', '2026-02-01', NULL, '标准砖', '块', '867.00', '40749.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('4', '11', '2026-02-01', NULL, '空心砖', '块', '988.00', '47424.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('5', '11', '2026-03-01', NULL, '标准砖', '块', '1183.00', '47320.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('6', '11', '2026-03-01', NULL, '空心砖', '块', '947.00', '53979.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('7', '11', '2026-04-01', NULL, '标准砖', '块', '1182.00', '47280.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('8', '11', '2026-04-01', NULL, '空心砖', '块', '1149.00', '60897.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('9', '11', '2026-05-01', NULL, '标准砖', '块', '891.00', '46332.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('10', '11', '2026-05-01', NULL, '空心砖', '块', '1010.00', '56560.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('11', '11', '2026-06-01', NULL, '标准砖', '块', '1036.00', '53872.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('12', '11', '2026-06-01', NULL, '空心砖', '块', '802.00', '36090.00', '2026-07-16 04:16:49', '2026-07-16 04:16:49');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('13', '1', '2026-01-01', NULL, '企业1产品A', '件', '966.00', '28570.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('14', '1', '2026-01-01', NULL, '企业1产品B', '件', '505.00', '40949.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('15', '1', '2026-02-01', NULL, '企业1产品A', '件', '967.00', '38568.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('16', '1', '2026-02-01', NULL, '企业1产品B', '件', '527.00', '41086.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('17', '1', '2026-03-01', NULL, '企业1产品A', '件', '582.00', '45851.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('18', '1', '2026-03-01', NULL, '企业1产品B', '件', '695.00', '45201.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('19', '11', '2026-04-01', NULL, 'NULL单位产品A', NULL, '800.00', '32000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('20', '11', '2026-04-01', NULL, 'NULL单位产品B', NULL, '600.00', '24000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('21', '11', '2026-05-01', NULL, '空单位产品A', '', '700.00', '28000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('22', '11', '2026-05-01', NULL, '空单位产品B', '', '900.00', '36000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('23', '11', '2025-12-01', NULL, '跨年产品', '吨', '1200.00', '60000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('24', '11', '2026-03-01', NULL, '跨年产品', '吨', '1500.00', '75000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('25', '11', '2026-06-01', NULL, '零产量产品', '块', '0.00', '0.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');
INSERT INTO `nt_production_product_data` (`id`, `company_id`, `data_time`, `dept_id`, `name`, `unit`, `number`, `total`, `created_at`, `updated_at`) VALUES ('26', '11', '2026-06-01', NULL, '正常产量产品', '块', '1000.00', '50000.00', '2026-07-16 16:22:33', '2026-07-16 16:22:33');

-- 表结构: nt_production_products
DROP TABLE IF EXISTS `nt_production_products`;
CREATE TABLE `nt_production_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL COMMENT '企业ID',
  `category_id` bigint unsigned DEFAULT NULL COMMENT '分类ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '产品名称',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '产品编码',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '计量单位',
  `specification` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '规格型号',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '产品描述',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nt_production_products_company_id_index` (`company_id`),
  KEY `nt_production_products_category_id_index` (`category_id`),
  KEY `nt_production_products_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: nt_production_products (2 行)
INSERT INTO `nt_production_products` (`id`, `company_id`, `category_id`, `name`, `code`, `unit`, `specification`, `description`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, '标准砖', 'STD_BRICK', '块', '标准规格烧结砖', '标准烧结砖产品，主要用于建筑墙体砌筑', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');
INSERT INTO `nt_production_products` (`id`, `company_id`, `category_id`, `name`, `code`, `unit`, `specification`, `description`, `status`, `created_at`, `updated_at`) VALUES ('2', '1', NULL, '空心砖', 'HOLLOW_BRICK', '块', '空心烧结砖', '空心烧结砖产品，具有保温隔热性能，用于建筑墙体砌筑', 'active', '2026-07-16 03:28:58', '2026-07-16 03:28:58');

-- 表结构: nt_report_file
DROP TABLE IF EXISTS `nt_report_file`;
CREATE TABLE `nt_report_file` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `task_id` bigint unsigned DEFAULT NULL COMMENT '来源任务ID(NULL=手动生成)',
  `template_id` bigint unsigned NOT NULL COMMENT '模板ID',
  `file_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名',
  `file_path` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件路径',
  `file_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
  `file_format` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件格式',
  `report_period_start` date DEFAULT NULL COMMENT '报表期间开始',
  `report_period_end` date DEFAULT NULL COMMENT '报表期间结束',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态:0生成中,1已完成,2失败',
  `error_message` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '失败原因',
  `generated_at` datetime DEFAULT NULL COMMENT '生成完成时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint NOT NULL DEFAULT '0' COMMENT '逻辑删除:0正常,1已删除',
  PRIMARY KEY (`id`),
  KEY `idx_rf_task` (`task_id`),
  KEY `idx_rf_period` (`report_period_start`,`report_period_end`),
  KEY `idx_rf_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='报表文件表';

-- 表结构: nt_report_task
DROP TABLE IF EXISTS `nt_report_task`;
CREATE TABLE `nt_report_task` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `template_id` bigint unsigned NOT NULL COMMENT '模板ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务名称',
  `cron_expression` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '定时表达式(NULL=手动执行)',
  `params` json DEFAULT NULL COMMENT '任务参数(时间范围、空间范围等)',
  `notify_channel` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '完成通知渠道',
  `notify_user_ids` json DEFAULT NULL COMMENT '通知人ID列表',
  `last_run_at` datetime DEFAULT NULL COMMENT '最后执行时间',
  `next_run_at` datetime DEFAULT NULL COMMENT '下次执行时间',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态:0暂停,1正常',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint NOT NULL DEFAULT '0' COMMENT '逻辑删除:0正常,1已删除',
  PRIMARY KEY (`id`),
  KEY `idx_rtask_tpl` (`template_id`),
  KEY `idx_rtask_status` (`status`),
  KEY `idx_rtk_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='报表任务表';

-- 表结构: nt_report_template
DROP TABLE IF EXISTS `nt_report_template`;
CREATE TABLE `nt_report_template` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板名称',
  `template_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型:energy=能耗,carbon=碳排放,cost=成本,custom=自定义',
  `file_format` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'xlsx' COMMENT '文件格式:xlsx,pdf,html',
  `template_content` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '模板内容(JSON或HTML模板)',
  `data_config` json DEFAULT NULL COMMENT '数据源配置',
  `is_system` tinyint NOT NULL DEFAULT '0' COMMENT '是否系统模板:0否,1是',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint NOT NULL DEFAULT '0' COMMENT '逻辑删除:0正常,1已删除',
  PRIMARY KEY (`id`),
  KEY `idx_rt_type` (`template_type`),
  KEY `idx_rt_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='报表模板表';

-- 表结构: nt_stat_config
DROP TABLE IF EXISTS `nt_stat_config`;
CREATE TABLE `nt_stat_config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `indicator_id` bigint unsigned NOT NULL COMMENT '指标ID',
  `cron_expression` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '统计频率(cron表达式)',
  `lookback_days` int NOT NULL DEFAULT '0' COMMENT '回溯天数',
  `auto_repair` tinyint NOT NULL DEFAULT '0' COMMENT '缺失数据是否自动修复:0否,1是',
  `repair_method` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '修复方式:interpolation=插值,previous=前值,zero=置零',
  `is_enabled` tinyint NOT NULL DEFAULT '1' COMMENT '是否启用:0否,1是',
  `last_run_at` datetime DEFAULT NULL COMMENT '最后执行时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint NOT NULL DEFAULT '0' COMMENT '逻辑删除:0正常,1已删除',
  PRIMARY KEY (`id`),
  KEY `idx_sc_indicator` (`indicator_id`),
  KEY `idx_sc_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='统计配置表';

-- 表结构: nt_stat_data
DROP TABLE IF EXISTS `nt_stat_data`;
CREATE TABLE `nt_stat_data` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT '统计ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `indicator_id` bigint unsigned NOT NULL COMMENT '指标ID',
  `space_id` bigint unsigned DEFAULT NULL COMMENT '空间ID',
  `data_date` date NOT NULL COMMENT '数据日期',
  `period_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '周期:daily=日,monthly=月,yearly=年',
  `data_value` decimal(16,4) NOT NULL COMMENT '统计值',
  `previous_value` decimal(16,4) DEFAULT NULL COMMENT '上期值(用于计算同比/环比)',
  `change_rate` decimal(8,4) DEFAULT NULL COMMENT '变化率(%)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint NOT NULL DEFAULT '0' COMMENT '逻辑删除:0正常,1已删除',
  PRIMARY KEY (`id`,`data_date`),
  KEY `idx_sd_indicator` (`indicator_id`,`data_date`),
  KEY `idx_sd_space` (`space_id`,`data_date`),
  KEY `idx_sd_date` (`data_date`),
  KEY `idx_sd_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='统计数据表'
/*!50500 PARTITION BY RANGE  COLUMNS(data_date)
(PARTITION p202607 VALUES LESS THAN ('2026-08-01') ENGINE = InnoDB,
 PARTITION p202608 VALUES LESS THAN ('2026-09-01') ENGINE = InnoDB,
 PARTITION p202609 VALUES LESS THAN ('2026-10-01') ENGINE = InnoDB,
 PARTITION p202610 VALUES LESS THAN ('2026-11-01') ENGINE = InnoDB,
 PARTITION p202611 VALUES LESS THAN ('2026-12-01') ENGINE = InnoDB,
 PARTITION p202612 VALUES LESS THAN ('2027-01-01') ENGINE = InnoDB,
 PARTITION p_future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- 表结构: nt_stat_indicator
DROP TABLE IF EXISTS `nt_stat_indicator`;
CREATE TABLE `nt_stat_indicator` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '指标ID',
  `company_id` bigint unsigned DEFAULT NULL COMMENT '企业ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指标名称',
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指标编码',
  `category` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类:energy=能耗,carbon=碳排放,cost=成本,efficiency=效率',
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '单位',
  `calculation_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '计算方式:sum=累计,avg=平均,max=最大,min=最小,ratio=比率',
  `data_source` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据来源:meter=表计,formula=公式,manual=手动',
  `source_id` bigint unsigned DEFAULT NULL COMMENT '来源ID',
  `formula_id` bigint unsigned DEFAULT NULL COMMENT '公式ID(calculation_type=formula时)',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_by` bigint unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint NOT NULL DEFAULT '0' COMMENT '逻辑删除:0正常,1已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_si_code` (`code`),
  KEY `idx_si_cat` (`category`),
  KEY `idx_si_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='统计指标定义表';

-- 表结构: personal_access_tokens
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: point
DROP TABLE IF EXISTS `point`;
CREATE TABLE `point` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `point_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '积分类型ID',
  `balance` bigint NOT NULL DEFAULT '0' COMMENT '积分余额（整数）',
  `total_earned` bigint NOT NULL DEFAULT '0' COMMENT '累计获得积分',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `point_user_id_point_id_unique` (`user_id`,`point_id`),
  KEY `point_user_id_index` (`user_id`),
  KEY `point_point_id_index` (`point_id`),
  KEY `point_balance_index` (`balance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: point_admin
DROP TABLE IF EXISTS `point_admin`;
CREATE TABLE `point_admin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `point_id` int NOT NULL DEFAULT '0' COMMENT '积分类型ID',
  `admin_id` int NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `total_points` bigint NOT NULL DEFAULT '0' COMMENT '操作积分数量',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态：0-待处理，1-已完成，2-已失败',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_admin_user_id_index` (`user_id`),
  KEY `point_admin_point_id_index` (`point_id`),
  KEY `point_admin_admin_id_index` (`admin_id`),
  KEY `point_admin_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: point_circulation
DROP TABLE IF EXISTS `point_circulation`;
CREATE TABLE `point_circulation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `from_point_id` int NOT NULL DEFAULT '0' COMMENT '源积分类型ID',
  `to_point_id` int NOT NULL DEFAULT '0' COMMENT '目标积分类型ID',
  `amount` bigint NOT NULL DEFAULT '0' COMMENT '流转积分数量',
  `re_id` int NOT NULL DEFAULT '0' COMMENT '关联ID',
  `re_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联类型',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态：0-待处理，1-已完成，2-已失败',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_circulation_user_id_index` (`user_id`),
  KEY `point_circulation_from_point_id_index` (`from_point_id`),
  KEY `point_circulation_to_point_id_index` (`to_point_id`),
  KEY `point_circulation_status_index` (`status`),
  KEY `point_circulation_re_id_re_type_index` (`re_id`,`re_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: point_config
DROP TABLE IF EXISTS `point_config`;
CREATE TABLE `point_config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '积分名称',
  `currency_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '关联的积分类型ID，外键关联point_currency表',
  `type` int NOT NULL DEFAULT '0' COMMENT '积分账户类型，关联POINT_TYPE枚举',
  `display_attributes` text COLLATE utf8mb4_unicode_ci COMMENT '显示属性，如图标、颜色等',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `point_config_type_unique` (`type`),
  KEY `point_config_currency_id_index` (`currency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: point_config (2 行)
INSERT INTO `point_config` (`id`, `name`, `currency_id`, `type`, `display_attributes`, `created_at`, `updated_at`) VALUES ('1', '种植点数', '1', '1', '{\"icon\":\"\\u2b50\",\"color\":\"#4CAF50\",\"background\":\"#f0f9ff\",\"border_color\":\"#91d5ff\",\"text_color\":\"#003a8c\",\"show_in_list\":true,\"show_in_detail\":true,\"sort_order\":0,\"description\":\"\\u7528\\u6237\\u79cd\\u690d\\u6d3b\\u52a8\\u83b7\\u5f97\\u7684\\u70b9\\u6570\\uff0c\\u6bcf\\u79cd\\u4e0b\\u4e00\\u4e2a\\u79cd\\u5b50\\u589e\\u957f1\\u70b9\"}', '2026-07-16 03:29:02', '2026-07-16 03:29:02');
INSERT INTO `point_config` (`id`, `name`, `currency_id`, `type`, `display_attributes`, `created_at`, `updated_at`) VALUES ('2', '疲劳值', '2', '4', '{\"icon\":\"\\u2b50\",\"color\":\"#FF6B6B\",\"background\":\"#f0f9ff\",\"border_color\":\"#91d5ff\",\"text_color\":\"#003a8c\",\"show_in_list\":true,\"show_in_detail\":true,\"sort_order\":0,\"description\":\"\\u7528\\u6237\\u6d3b\\u52a8\\u6d88\\u8017\\u7684\\u75b2\\u52b3\\u503c\\uff0c\\u7528\\u4e8e\\u9650\\u5236\\u6d3b\\u52a8\\u9891\\u7387\"}', '2026-07-16 03:29:02', '2026-07-16 03:29:02');

-- 表结构: point_currency
DROP TABLE IF EXISTS `point_currency`;
CREATE TABLE `point_currency` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `identification` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '积分标识',
  `type` int NOT NULL DEFAULT '0' COMMENT '积分类型，关联POINT_CURRENCY_TYPE枚举',
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '积分图标',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '积分名称',
  `display_attributes` text COLLATE utf8mb4_unicode_ci COMMENT '显示属性，如图标、颜色等',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `point_currency_identification_unique` (`identification`),
  UNIQUE KEY `point_currency_type_unique` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: point_currency (2 行)
INSERT INTO `point_currency` (`id`, `identification`, `type`, `icon`, `name`, `display_attributes`, `created_at`, `updated_at`) VALUES ('1', 'EXP', '1', '⭐', '经验积分', '{\"icon\":\"\\ud83c\\udfc6\",\"color\":\"#FFD700\",\"background\":\"#f6ffed\",\"border_color\":\"#b7eb8f\",\"text_color\":\"#135200\",\"unit\":\"\\u79ef\\u5206\",\"show_in_header\":true,\"show_in_sidebar\":true,\"enable_transfer\":true,\"enable_exchange\":true,\"min_transfer\":1,\"max_transfer\":999999,\"sort_order\":0,\"description\":\"\\u7528\\u6237\\u901a\\u8fc7\\u5404\\u79cd\\u6d3b\\u52a8\\u548c\\u4efb\\u52a1\\u83b7\\u5f97\\u7684\\u7ecf\\u9a8c\\u79ef\\u5206\"}', '2026-07-16 03:29:01', '2026-07-16 03:29:01');
INSERT INTO `point_currency` (`id`, `identification`, `type`, `icon`, `name`, `display_attributes`, `created_at`, `updated_at`) VALUES ('2', 'FATIGUE', '2', '⚡', '疲劳值', '{\"icon\":\"\\ud83c\\udfc6\",\"color\":\"#FF6B6B\",\"background\":\"#f6ffed\",\"border_color\":\"#b7eb8f\",\"text_color\":\"#135200\",\"unit\":\"\\u79ef\\u5206\",\"show_in_header\":true,\"show_in_sidebar\":true,\"enable_transfer\":true,\"enable_exchange\":true,\"min_transfer\":1,\"max_transfer\":999999,\"sort_order\":0,\"description\":\"\\u7528\\u6237\\u6d3b\\u52a8\\u6d88\\u8017\\u7684\\u75b2\\u52b3\\u503c\\uff0c\\u7528\\u4e8e\\u9650\\u5236\\u6d3b\\u52a8\\u9891\\u7387\"}', '2026-07-16 03:29:01', '2026-07-16 03:29:01');

-- 表结构: point_logs
DROP TABLE IF EXISTS `point_logs`;
CREATE TABLE `point_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `point_id` int NOT NULL DEFAULT '0' COMMENT '积分类型ID',
  `amount` bigint NOT NULL DEFAULT '0' COMMENT '操作积分数量,正值为收入,负值为支出',
  `operate_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上游操作ID',
  `operate_type` int NOT NULL DEFAULT '0' COMMENT '上游操作类型',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建IP',
  `later_balance` bigint NOT NULL DEFAULT '0' COMMENT '操作后余额',
  `before_balance` bigint NOT NULL DEFAULT '0' COMMENT '操作前余额',
  `date_key` int NOT NULL DEFAULT '0' COMMENT '日期key（用于分表）',
  `hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '防篡改哈希值',
  `prev_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上一条记录的哈希值',
  PRIMARY KEY (`id`),
  KEY `point_logs_user_id_index` (`user_id`),
  KEY `point_logs_point_id_index` (`point_id`),
  KEY `point_logs_operate_type_index` (`operate_type`),
  KEY `point_logs_create_time_index` (`create_time`),
  KEY `point_logs_date_key_index` (`date_key`),
  KEY `point_logs_user_id_point_id_create_time_index` (`user_id`,`point_id`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: point_order
DROP TABLE IF EXISTS `point_order`;
CREATE TABLE `point_order` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `point_id` int NOT NULL DEFAULT '0' COMMENT '积分类型ID',
  `amount` bigint NOT NULL DEFAULT '0' COMMENT '积分数量',
  `order_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单类型',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单标题',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单描述',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '订单状态',
  `extra_data` text COLLATE utf8mb4_unicode_ci COMMENT '额外数据（JSON格式）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `point_order_order_no_unique` (`order_no`),
  KEY `point_order_user_id_index` (`user_id`),
  KEY `point_order_point_id_index` (`point_id`),
  KEY `point_order_order_type_index` (`order_type`),
  KEY `point_order_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: point_transfer
DROP TABLE IF EXISTS `point_transfer`;
CREATE TABLE `point_transfer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `from_user_id` int NOT NULL DEFAULT '0' COMMENT '转出用户ID',
  `to_user_id` int NOT NULL DEFAULT '0' COMMENT '转入用户ID',
  `point_id` int NOT NULL DEFAULT '0' COMMENT '积分类型ID',
  `amount` bigint NOT NULL DEFAULT '0' COMMENT '转账积分数量',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态：0-待处理，1-已完成，2-已失败',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_transfer_from_user_id_index` (`from_user_id`),
  KEY `point_transfer_to_user_id_index` (`to_user_id`),
  KEY `point_transfer_point_id_index` (`point_id`),
  KEY `point_transfer_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表结构: product
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `product_id` bigint NOT NULL COMMENT '产品ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '产品名称',
  `product_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '产品编码',
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单位（件、吨、台等）',
  `category_id` bigint unsigned DEFAULT NULL COMMENT '关联能源品种ID',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品表';

-- 表结构: product_output
DROP TABLE IF EXISTS `product_output`;
CREATE TABLE `product_output` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `output_id` bigint NOT NULL COMMENT '产量ID',
  `product_id` bigint unsigned NOT NULL COMMENT '产品ID',
  `output_date` date NOT NULL COMMENT '生产日期',
  `output_value` decimal(16,4) NOT NULL COMMENT '产量值',
  `space_id` bigint unsigned DEFAULT NULL COMMENT '生产空间ID',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint DEFAULT NULL COMMENT '更新人ID',
  `is_deleted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '逻辑删除:0=正常,1=已删除',
  PRIMARY KEY (`id`),
  KEY `idx_po_product` (`product_id`),
  KEY `idx_po_date` (`output_date`),
  KEY `idx_po_space` (`space_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品产量表';

-- 表结构: sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 表数据: sessions (8 行)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('5HnMyU6LZ2aY5VWtysunH0A0MBNnuk7bmEsl8kvV', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiMVc1amJRVWg3UlhXSFZXZGN6aFppSkcxVzRzRXRIR1d0ZUpoeGhjYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784185654');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('5Vju5KNx47olHb8Tk3EHhINVO2zP4sZ27z2lHrZ7', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTUZranBLOUJTaFJTZklqMk1qRnpJUFk4VXVPbDFBa3ZxNkxTR3RlZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784185654');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('93icmcOGOOOYruIpyUtwAqOAu4DR1fqJrEWSUxGH', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiV2ZaRWJTeEg5THlFU1F4d3ZKUnFJUHhEU2hobnlpcVFpSTY5VmZwSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784369225');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('a8wu5F7Axm1E1I5vSxs8P1HN2FsUZImCRbNo8uI8', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoib2lnSWc0SDJyb3FQVkdYNXE1NXAwQVprdmUwNG5DWGxhQklTT3lWViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784185654');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('gud7z1f64D1cWszPon4lUTSXk5orJhMbD3Gr2HB4', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUkJ3N1h6ZUlIYllYMDZwQnJmSHY5dEFOWmFOT1RRMlliODFXUTRRMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784369225');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('HD4BwfpH6TqiPQGXIwWSx9dozoqrRBnMsz16GhkH', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiU1V6S1BSRkYxc0h6SzI3V2tjRWUxOU0zUTFSWHhwVHZ2MWhNaDhyUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784369225');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('N0cCXryMQCdEI0nz4hqRwpKRxW5aIp5zVAC4jXp0', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiS0RMcTlkaGo2NkVVcTJoNnJFUFU3ZDNlZjBKTHFoMTRRVzRuZUpPMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784369225');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('OWDzng8k7ansf24aTAqffL8NEsB9Wb18vUDkeX4Y', NULL, '10.42.0.59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoicXlia0s2QWNGNnQyOWpaNFVEdUJncHpxUmVlNjhJbmNlTkE4cDZoRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1784185654');

-- 表结构: user_feature_settings
DROP TABLE IF EXISTS `user_feature_settings`;
CREATE TABLE `user_feature_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `feature_id` bigint unsigned NOT NULL COMMENT '功能ID',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开关状态:0关闭 1开启',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_feature` (`user_id`,`feature_id`),
  KEY `user_feature_settings_user_id_index` (`user_id`),
  KEY `user_feature_settings_feature_id_index` (`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
