# 配置文件

配置文件用于存储模块的配置信息。

## 职责

- 存储模块的配置参数
- 提供配置的默认值
- 支持配置的覆盖和扩展

## 可能的配置文件

- `file.php`: 文件模块的基本配置
- `storage.php`: 存储相关的配置
- `image.php`: 图片处理相关的配置

## 配置项示例

```php
// file.php
return [
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
    'max_size' => 10 * 1024 * 1024, // 10MB
    'path' => 'uploads',
];
```
