# Debug 模块

## 模块概述
Debug模块提供系统调试、日志分析、状态监控等功能，支持CLI命令和API接口两种调用方式。

## 功能特性
- 🔧 Artisan命令：CLI调试工具
- 🌐 API接口：RESTful调试API

## 入口形式
- **Commands/** - Artisan命令入口
- **Api/** - RESTful API入口

## 核心功能
- 日志查看和分析
- 系统状态监控
- 缓存状态检查
- 性能分析

## 目录结构
```
Debug/
├── Api/                    # API入口
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Commands/               # Artisan命令
├── Models/                 # 数据模型
├── Services/               # 服务层
├── Logics/                 # 逻辑层
├── Enums/                  # 状态枚举
├── Dtos/                   # 数据传输对象
├── Events/                 # 事件定义
├── Listeners/              # 事件监听器
├── config/                 # 模块配置
├── routes/                 # 路由配置
│   ├── api.php
│   └── console.php
└── Tests/                  # 测试文件
```

## 开发指南
详见 [DEV.md](DEV.md)