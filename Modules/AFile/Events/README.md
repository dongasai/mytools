# 事件

事件用于在特定操作发生时通知系统的其他部分。

## 职责

- 定义系统中的事件
- 提供事件的数据结构
- 支持系统解耦和扩展

## 可能的事件

- `FileUploaded`: 文件上传完成事件
- `FileDeleted`: 文件删除事件
- `ImageProcessed`: 图片处理完成事件

## 命名规范

事件类应以具体的事件名称命名，通常使用过去时态，例如 `FileUploaded`、`ImageProcessed` 等。
