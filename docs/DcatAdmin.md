# Dcat Admin 笔记本

# 问题
- 缺少登陆用户信息 
```bash
php artisan   admin:install
```

 - 自定义表单
SuperAdmin - 超级管理员后台



Modules (模块目录)

## 常用
/adminmodule_create 在 Modules/TaskaAdmin 创建 DcatAdmin 模块

从 Modules/Point 中拆分出 Modules/PointAdmin 

## Admin模块拆分指南
- 最佳实践Demo5Admin

1. 按照Demo5Admin的目录结构创建目录
2. 从原模块复制Admin下的内容到新的模块
3. 复制 admin_menu.php 到新模块
4. 复制 路由admin.php 到新模块
5. 修复命名空间
6. 严格按照Demo5Admin的Provider创建Provider
7. 检查
  - admin_menu 是否和Demo5Admin的格式一致
  - 路由admin.php 是否和Demo5Admin的格式一致
  - 检查 Provider和 Demo5Admin 的 Provider 格式一致
8. 移除原模块的 admin_menu.php 路由admin.php
9. 激活模块
