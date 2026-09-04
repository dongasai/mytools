# 模型属性转换器

模型属性转换器用于在模型属性和数据库字段之间进行数据转换。

## 职责

- 定义模型属性的转换规则
- 处理复杂数据类型的序列化和反序列化
- 支持自定义数据格式

## 可能的转换器

- `FilePathCast`: 文件路径转换器
- `ImageDimensionsCast`: 图片尺寸转换器
- `FileMetadataCast`: 文件元数据转换器

## 命名规范

转换器类应以 `Cast` 结尾，例如 `FilePathCast`、`ImageDimensionsCast` 等。
