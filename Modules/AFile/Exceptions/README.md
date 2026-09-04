# 异常处理

异常处理类用于定义和处理模块中的异常情况。

## 职责

- 定义模块特定的异常类型
- 提供异常的处理逻辑
- 支持错误信息的国际化

## 可能的异常

- `FileUploadException`: 文件上传异常
- `FileNotFoundException`: 文件未找到异常
- `InvalidFileTypeException`: 无效的文件类型异常

## 命名规范

异常类应以 `Exception` 结尾，例如 `FileUploadException`、`FileNotFoundException` 等。
