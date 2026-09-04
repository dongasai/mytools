# 检查列表

## 基础规范
- 非必要不能try-catch，会掩盖错误
- 避免自定义"服务容器/依赖注入/Facades",严重的过度设计
- 所有代码必须有PHPDoc注释（类、方法、属性）
- 禁止使用构造函数属性提升语法（PHP 8+ 语法糖）

## 模型/数据库表
- 模型内不能定义逻辑方法/查询方法/scope助手方法
- 模型内只能定义属性/关联/类型处理
- 模型不允许使用 boot 语法/工厂函数
激活 design-database 技能,来分析数据库相关修改


## 服务层
- 优先定义静态服务
- 所有参数必须显性传入
- 不能读取http数据/cookie数据/全局变量/session

## 逻辑层
- 必须定义静态类,不允许实例化
- 不能读取http数据/cookie数据/全局变量/session
- 所有参数必须显性传入

## 控制器层（入口层）
- Handler/Controller 只负责 HTTP 处理和 session 读取
- 主要做参数收集和返回结构组织
- 必须显性传参给 Services 层
- 不能直接调用 Models 层（必须通过 Services/Logics）
- 三种入口分离：Api/DcatAdmin/Web 各司其职

## API 规范
- Token 机制：客户端持久标识，获取后不变，登录仅添加 user_id
- Protobuf API：使用 proto-dev 技能开发 Handler
- Restful API：遵循 REST 规范
- 禁止在 API Controller 中直接操作数据库

## 事件驱动
- 模块内业务独立，模块间通过 Event 通信
- 跨模块调用只能调用 Service 层（禁止直接调用 Model）
- Event 必须有清晰的 PHPDoc 说明用途
- Listener 不能包含复杂业务逻辑（应委托给 Service）

## 队列任务
- 耗时操作必须使用队列任务
- Job 类必须有 PHPDoc 注释
- Queue 配置需检查超时设置

## HTML和CSS
- 不能使用cdn文件


## 模块化开发
- 不能跨模块调用Model层
- 模块通讯主要靠Event和Hook
- 跨模块调用只能调用Service层
- 严格禁止修改主应用，所有开发必须在模块内完成（如需修改，停止开发，让用户修改）
- 参考最佳实践：遇到问题先查看 Demo5 模块


## 安全规范
- 禁止 SQL 注入（使用 Eloquent ORM，避免原生 SQL）
- 禁止 XSS（输出必须转义）
- 禁止命令注入（避免 exec/system，使用队列）
- 禁止文件上传漏洞（验证文件类型/大小）
- API 必须有认证机制（Token 或 Session）


## 日志
- 不能随意产生文件日志
- 非必要不能扩充日志渠道
- 除核心业务入数据库日志外,禁止额外的/独立的日志
