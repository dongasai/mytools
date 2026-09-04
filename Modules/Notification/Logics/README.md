# Logics 目录

逻辑层，存放静态方法的业务逻辑类。

## 职责
- 数据组装逻辑
- 单一业务逻辑处理
- 高性能无状态方法

## 特点
- **静态方法**
- **无状态**
- **禁止读取 HTTP/session**

## 命名规范
- PascalCase + Logic 后缀
- 示例: `NotificationLogic.php`, `TemplateLogic.php`