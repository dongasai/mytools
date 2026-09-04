<?php

/**
 * 文件类型配置
 *
 * 定义允许上传的文件和图片类型及其大小限制
 * 修改此文件即可调整允许的类型，无需改控制器代码
 */
return [
    // 文件上传（非图片）
    'file' => [
        'mimes' => 'pdf,doc,docx,txt,xlsx,xls,ppt,pptx,jpeg,png,gif,webp,bmp',
        'max_size' => 10240, // KB，默认 10MB
    ],

    // 图片上传
    'image' => [
        'mimes' => 'jpeg,png,gif,webp,bmp',
        'max_size' => 5120, // KB，默认 5MB
    ],
];
