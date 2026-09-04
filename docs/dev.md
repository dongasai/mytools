# 开发流程
1. 规划模块
2. 设计模块
    - 数据库
    - 枚举
3. 创建数据库表
4. Seeder设计开发
5. 设计Api

# api分析
先阅读 docs/greenbid/pages 下的页面列表
然后使用 subagent general-purpose 逐一分析每个页面,每个页面启动一个Task
页面分析任务:
```
阅读 docs/greenbid/pages/xx.md 页面文档,找出本页面涉及的Api.
更新到 docs/greenbid/api/ApiList.md 中(只记录列表,别重复),具体的每个Api信息在 docs/greenbid/api/目录创建单个Api的文档,用 Api的path路径关联命名

阅读 docs/碳能模块化开发.md

两个任务:
1. 分析Api,并更新ApiList.md 和 单Api的文档
2. 分析模块化开发,查看docs/tanneng/module-planning.md 模块规划文档,是否合理,并修复不合理的规划
```

# 数据库分析
先阅读 docs/greenbid/pages 下的页面列表
然后使用 subagent general-purpose 逐一分析每个页面,每个页面启动一个Task
页面分析任务:
```
阅读 docs/greenbid/pages/xx.md 页面文档,找出本页面涉及的Api和数据库(推测).
更新到 docs/greenbid/database/TableList.md 中(只记录列表,别重复),具体的每个表的信息在 docs/greenbid/database/目录创建单个表的文档,用table-{module}-{table}命名


核心任务:
1. 分析页面Api/页面结构推测表结构
2. 更新 docs/greenbid/database/TableList.md 中的表列表
3. 更新 docs/greenbid/database/ 目录下的单个表文档
```

# Api实现分析

然后使用 subagent general-purpose 逐一分析docs/greenbid/api/* 的每个Api
每个Api启动一个Agent Task任务,每个Api启动一个Agent Task任务,每个Api启动一个Agent Task任务
最多并发2个subagent,每批次任务间隔120s

Api处理任务:
```
阅读 docs/greenbid/api/xx.md Api文档
阅读仿制站的前端代码解析旧Api信息是否准确
看我们的Api实现情况,更新Api文档
同时更新 docs/greenbid/api/ApiList.md 


核心任务:
1. 确认旧Api信息准确
2. 检查我们的Api实现情况/比对新旧Api
2. 更新Api文档/ApiList.md 
```
ErrorCode  CommonErrorData


当前正在进行 Merchant2 模块的功能合并到 Enterprise模块
