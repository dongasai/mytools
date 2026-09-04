# Debug模块开发记录

## 创建日期
2026-05-16

## 模块架构
- 入口形式：Commands + Api
- 分层架构：Models → Services → Logics
- 业务范围：系统调试、日志分析、状态监控

## 开发进度

### 2026-05-16 - 模块初始化
- ✅ 创建模块基础结构
- ✅ 配置目录架构（Commands + Api）
- ✅ 创建README.md和DEV.md
- ✅ 创建DebugServiceProvider
- ✅ 配置API路由

### 2026-05-16 - 调试认证API实现
- ✅ 实现uid登录接口（跳过密码验证）
  - 创建DebugAuthController
  - 实现loginByUid方法
  - 配置路由：POST /debug/auth/login-by-uid
  - 测试通过（uid=10000）
- ✅ Token机制（参考Account模块）
  - Token作为会话标识，始终不变
  - 登录仅在token数据中添加user_id
  - 使用AccountTokenService和SessionApp
  - 动态TTL：匿名1天，登录30天

### 待开发功能
- [ ] Artisan命令实现
  - [ ] LogCommand - 日志查看命令
  - [ ] StatusCommand - 系统状态检查
  - [ ] CacheCommand - 缓存调试命令

- [ ] API接口实现
  - [ ] LogController - 日志API
  - [ ] StatusController - 状态API

- [ ] 业务层开发
  - [ ] LogService - 日志处理服务
  - [ ] StatusService - 状态检查服务

- [ ] 枚举定义
  - [ ] LogLevel - 日志级别枚举
  - [ ] SystemStatus - 系统状态枚举

## 技术要点
- Services层禁止读取HTTP/session/cookie
- Logics层使用静态方法
- 参数显性传递

## 参考
- [模块化架构规范](../../docs/模块化.md)
- [单模块全栈架构](.claude/skills/module-structure)