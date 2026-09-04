# 事件监听器

事件监听器负责响应系统中的事件并执行相应的操作。

## 职责

- 监听特定的事件
- 执行事件触发后的操作
- 处理事件数据

## 可能的监听器

- `ProcessUploadedFile`: 处理上传文件的监听器
- `CleanupDeletedFile`: 清理已删除文件的监听器
- `GenerateThumbnail`: 生成缩略图的监听器

## 命名规范

监听器类应以具体的操作命名，通常使用动词开头，例如 `ProcessUploadedFile`、`GenerateThumbnail` 等。
