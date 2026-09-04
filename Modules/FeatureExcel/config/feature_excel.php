<?php

/**
 * Excel导入导出引擎模块配置
 *
 * 定义导入导出的默认参数、限制和格式选项
 */
return [
    // 默认导出格式（excel / csv）
    'default_format' => 'excel',

    // 最大上传文件大小（字节），默认10MB
    'max_upload_size' => 10 * 1024 * 1024,

    // 单次导入最大行数
    'max_rows' => 100000,

    // 分块处理阈值（超过此行数启用分块处理）
    'chunk_threshold' => 5000,

    // 分块大小（每批处理的行数）
    'chunk_size' => 1000,

    // 默认缓存TTL（秒）
    'default_cache_ttl' => 86400,

    // 时区转换 - 源时区
    'default_timezone_from' => 'Asia/Shanghai',

    // 时区转换 - 目标时区
    'default_timezone_to' => 'UTC',

    // CSV编码格式
    'csv_encoding' => 'UTF-8',

    // CSV是否包含BOM头
    'csv_include_bom' => true,
];
