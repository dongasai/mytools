# 开发经验分析记录/进度

> 本文件记录每次经验总结的时间、范围和结果，便于追踪和延续。

---

## 📍 当前进度

| 项目 | 状态 |
|------|------|
| **7月分析** | ✅ 已完成 |
| **8月分析** | 🔄 部分完成（8月1日-26日） |
| **下次分析** | 8月27日-31日（8月底或9月初） |

---

## 经验总结历史

### 2026-08-26 | AiWork/202607 + 202608（部分）分析

**分析时间**: 2026-08-26
**分析范围**: AiWork/202607（7月）、AiWork/202608/01-26（8月1日-26日）
**分析截止**: 2026-08-26 20:00
**下次分析起点**: AiWork/202608/27（8月27日起的工作记录）

> ⚠️ **注意**: 8月尚未结束，后续会继续分析 8月27日-31日的工作记录

**文档统计**:
- 7月文档: ~170 个文件
- 8月文档: ~291 个文件（仅 8月1日-26日）
- 总计: ~461 个文件
- 后续待分析: 8月27日-31日的工作记录

**产出经验文档**: 25 篇

**经验分类**:

| 类别 | 数量 | 核心经验 |
|------|------|----------|
| 开发规范 | 9 | Handler签名、try-catch禁用、模块间通信、Model scope、枚举命名、数据隔离、迁移目录、字段命名、事件驱动 |
| 架构设计 | 7 | FeatureExcel模板、备份四层架构、队列优化、API迁移、模块重构、商户合并、RBAC权限 |
| API开发 | 4 | Proto精度、Excel导入导出、Token认证、过滤参数回传 |
| 常见错误 | 4 | Dcat字段方法、DataValidator bug、并发唯一约束、安全漏洞 |
| 测试经验 | 2 | E2E测试、代码审阅自动化 |

**关键发现**:

1. **Handler签名问题**（8月审阅发现）
   - 70+ 个 Handler 使用错误签名（具体Proto类型而非Message）
   - 影响: NtEnergy 18个、NtEmission 16个、NtReport 24个
   
2. **try-catch泛滥**（8月审阅统计）
   - 416 处违规分布在 213 个文件
   - 违反"禁止try"核心规范

3. **跨模块调用违规**（8月审阅发现）
   - NtEnergy: 5个Model直接调用Enterprise
   - NtMaterial: 4个Model跨模块调用
   - 应通过Hook/Event通信

4. **Enterprise重构**（7月完成）
   - merchant → enterprise 全局重命名
   - 11张表重命名、7张表字段重命名
   - 113+ 文件修改，550+ 处引用替换
   - TaskFlow自动化：46分钟（效率提升40倍）

5. **事件架构完善**（7月完成）
   - 11个模块EventServiceProvider配置统一
   - 15个监听器绑定
   - 跨模块监听使用完整类名路径

6. **安全漏洞修复**（7月完成）
   - 路径遍历: realpath + 前缀检查
   - 时序攻击: hash_equals()
   - 生产环境配置警告

**经验文件位置**:
```
/home/dongasai/.claude/agent-memory/dev-experience/
├── MEMORY.md                           # 经验索引
├── proto-handler-signature-pattern.md  # Handler签名规范
├── try-catch-anti-pattern.md           # try-catch禁用
├── cross-module-communication-pattern.md # 模块间通信
├── model-scope-method-rules.md         # Model scope规范
├── enum-naming-consistency.md          # 枚举命名
├── data-isolation-enterprise-id.md     # 数据隔离
├── migration-directory-naming.md       # 迁移目录命名
├── company-id-to-enterprise-id-migration.md # 字段迁移
├── event-driven-architecture-pattern.md # 事件驱动
├── feature-excel-template-pattern.md   # FeatureExcel模板
├── backup-module-architecture.md       # 备份架构
├── queue-worker-performance.md         # 队列优化
├── api-migration-strategy.md           # API迁移
├── module-refactoring-process.md       # 模块重构
├── enterprise-tenant-merge.md          # 商户合并
├── rbac-permission-button-design.md    # RBAC权限
├── proto-field-precision-design.md     # Proto精度
├── module-excel-import-export-pattern.md # Excel导入导出
├── token-authentication-mechanism.md   # Token认证
├── filter-parameter-return-pattern.md  # 过滤参数回传
├── dcat-admin-field-methods.md         # Dcat字段方法
├── data-validator-engine-bug.md        # DataValidator bug
├── concurrent-unique-constraint.md     # 并发唯一约束
├── security-vulnerability-fixes.md     # 安全漏洞
├── e2e-testing-with-playwright.md      # E2E测试
└── code-review-automation.md           # 代码审阅
```

**下次分析计划**:
- **分析时间**: 8月底或9月初
- **分析范围**: AiWork/202608/27-31（8月27日-31日的工作记录）
- **关注重点**:
  - 新架构模式
  - 新错误类型
  - 性能优化经验
  - P1阶段遗留问题
- **更新内容**: 本文件历史记录 + MEMORY.md 索引

---

### 2026-08-28 | CarbonMonthlyAggregate 移除任务经验

**任务**: 移除 CarbonMonthlyAggregate 月度聚合表，改为从原表统计
**产出记忆文件**: 4 篇

| 记忆文件 | 类型 | 核心内容 |
|----------|------|----------|
| `technical_aggregate_table_removal.md` | 技术模式 | 分阶段实施：索引→停写→改写→清理；时间计算/CONCAT索引失效/软删除陷阱 |
| `feedback_cross_module_direct_model_access.md` | 架构规范 | 跨模块禁止直接调用Model，必须通过Hook/Event通信 |
| `feedback_query_rewrite_completeness.md` | 流程规范 | 数据源变更必须全局搜索覆盖所有消费端（Handler/Report/Command） |
| `feedback_performance_first_index_before_rewrite.md` | 流程规范 | 先加索引再改写查询，避免二次修改 |

**关键踩坑**:
- GetTransportDetailHandler 遗漏改写（全局搜索不彻底）
- 固定31日导致月份边界计算错误（应用 Carbon::endOfMonth()）
- CONCAT 导致索引失效（改用字段分别比较）
- 直接调用其他模块Model（违反架构规范）

---

## 经验提取流程

### 1. 文档扫描

```bash
# 列出所有工作记录
find AiWork/202608 -type f -name "*.md" | sort
```

### 2. 关键文档识别

优先阅读以下类型文档：
- 审阅报告（代码问题汇总）
- 修复报告（问题和解决方案）
- 重构文档（架构改进）
- 完成报告（任务总结）
- 测试报告（问题和验证）

### 3. 经验提取

从文档中提取：
- 技术问题及解决方案
- 常见错误及避免方法
- 架构设计决策及原因
- 最佳实践及代码示例

### 4. 经验分类

按以下类别整理：
- 开发规范（必须遵守）
- 架构设计（设计模式）
- API开发（接口设计）
- 常见错误（踩坑经验）
- 测试经验（验证方法）

### 5. 文档保存

经验文件保存到：
```
/home/dongasai/.claude/agent-memory/dev-experience/{经验名}.md
```

格式要求：
```markdown
---
name: {经验名}
description: {一句话描述}
metadata:
  type: {feedback/project/technical}
  tech_stack: {技术栈}
  problem: {问题描述}
---

## 问题
...

## 解决方案
...

## 最佳实践
...

## 注意事项
...
```

### 6. 索引更新

更新 MEMORY.md 索引文件，添加新经验条目。

---

## 经验使用指南

### 查询经验

```bash
# 查看所有经验
cat /home/dongasai/.claude/agent-memory/dev-experience/MEMORY.md

# 查看具体经验
cat /home/dongasai/.claude/agent-memory/dev-experience/proto-handler-signature-pattern.md
```

### 应用经验

开发新功能时：
1. 查阅相关经验文档
2. 遵循最佳实践
3. 避免已知错误
4. 参考代码示例

### 更新经验

发现新经验时：
1. 创建新经验文件
2. 更新 MEMORY.md 索引
3. 更新本文件记录

---

**最后更新**: 2026-08-26 20:29
**分析状态**: 已完成 8月1日-26日分析，后续将继续分析 8月27日-31日
**维护者**: AI开发团队
