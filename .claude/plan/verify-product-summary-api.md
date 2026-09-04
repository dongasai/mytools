# 任务规划：验证 产品产量产值汇总 API 实现

## 任务目标
验证 `/api/proto/nt/production/product/summary` API 的实现正确性

## 阶段1：梳理现状

### 任务分析
- **验证对象**: 产品产量产值汇总API
- **旧API**: `GET /api/product/summaryProduct`
- **新API**: `POST /api/proto/nt/production/product/summary`
- **Handler**: `ProductionProductSummaryHandler`
- **验证维度**:
  - Handler实现正确性
  - Proto定义完整性
  - 数据查询逻辑准确性
  - 响应格式合规性

### 需要探索的内容
- Handler代码位置和实现细节
- Proto Message定义
- NtProductDataService实现
- 相关Model和数据表
- 测试数据和验证方法

### 现状收集

#### 已实现的核心组件

**1. Handler 类**: `ProductSummaryHandler.php`
- 文件：`Modules/NtProduction/ApiProto/Handlers/ProductSummaryHandler.php`
- 功能：处理时间参数、调用Service、构建响应
- 特点：时间范围扩展为整月，need_login=true

**2. Proto 定义**: `production.proto`
- 文件：`Modules/NtProduction/ApiProto/protos/production.proto`
- Message：`Nt_productionProductSummaryRequest/Response/Data`
- 字段：start_time, end_time, proNum, proVal, unit
- 符合过滤条件回传规范（start_time/end_time回传）

**3. Service 层**: `NtProductDataService.php`
- 方法：`getSummary()` - SQL聚合查询
- 逻辑：COALESCE(SUM()) 避免空值，单独查询unit
- 返回：`['proNum' => float, 'proVal' => float, 'unit' => string]`

**4. Model 层**: `NtProductData.php`
- 表名：`nt_production_product_data`
- 字段：company_id, data_time, name, unit, number, total
- Scope：byCompany, byDateRange, byYearMonth, byDept, byName

**5. 路由注册**: `pathlist.php`
- 路径：`/api/proto/nt_production/product/summary`
- Handler：`ProductSummaryHandler`
- 状态：已注册

**6. 数据库迁移**: 已创建
- 唯一约束：`uk_pd_company_time_product` (company_id, data_time, name)
- 索引：company_id, data_time, dept_id

#### 关键发现

✅ **架构合规**：Handler → Service → Model，符合三层架构
✅ **SQL优化**：使用COALESCE避免NULL值
✅ **时间处理**：Carbon扩展整月范围
✅ **过滤回传**：start_time/end_time已回传
⚠️ **潜在问题**：unit字段可能为空，需验证获取逻辑

---

## 阶段2：查询经验

### 相关经验摘要

#### ✅ 时间范围处理经验
- **验证规则**：YYYY-MM格式验证（正则`/^\d{4}-\d{2}$/`）
- **业务逻辑**：start_month <= end_month闭包验证
- **缓存策略**：Cache::remember()，键格式`{module}:{company_id}:{start_time}:{end_time}`

#### ✅ 数据汇总SQL经验
- **聚合优化**：selectRaw()单次查询聚合所有字段
- **静态方法**：Service方法必须静态，参数显性传入
- **空值保障**：SQL层COALESCE + PHP层`?? 0`双重保障

#### ✅ company_id隔离经验
- **重要教训**：必须核对数据库schema确认字段名
- **验证要点**：监听器/Observer中字段名不匹配会静默丢弃

#### ✅ Handler开发规范
- **流程**：定义Proto → composer proto → 创建Handler → 测试
- **方法签名**：`handle(Message $request): Message`
- **参数验证**：使用Validation类（继承ValidationCore）

### 需要注意的坑点

1. ⚠️ unit字段可能为空，需验证获取逻辑
2. ⚠️ 数据表可能没有测试数据，需准备测试数据
3. ⚠️ company_id隔离需要验证正确性

---

## 阶段3：梳理方案+拆分任务

### 发现的实现问题

通过代码审查，发现以下问题需要验证：

**1. Handler 参数验证缺失**
- 没有验证时间格式（应为 YYYY-MM）
- 没有验证 start_time <= end_time
- Carbon::parse() 异常未捕获

**2. Service unit 字段处理逻辑**
- 当所有 unit 为 null/空字符串时返回空字符串
- 可能不符合业务预期（应该有默认单位？）

**3. company_id 隔离验证**
- 使用 token_merchant_id 作为 company_id
- 需要验证 merchant 和 company 的关系

**4. 数据表测试数据**
- StatisTestDataSeeder 只提供 company_id=11 的数据
- 只有 2026年1-6月数据

### 任务清单

#### 第一阶段：代码审查（无需数据）

□ 任务1：审查 Handler 参数验证逻辑
  - 目标：验证 Handler 是否正确处理时间参数
  - 文件：ProductSummaryHandler.php, production.proto
  - 操作：检查时间格式验证、时间范围验证、异常处理
  - 验证：确认 Handler 缺少时间参数验证
  - 推荐Skill：无

□ 任务2：审查 Service SQL 聚合逻辑
  - 目标：验证 Service 层 SQL 查询正确性
  - 文件：NtProductDataService.php, NtProductData.php
  - 操作：检查 COALESCE 函数、unit 获取逻辑、Scope 正确性
  - 验证：确认 SQL 聚合逻辑正确，unit 获取逻辑需改进
  - 推荐Skill：无

□ 任务3：审查 Proto 定义完整性
  - 目标：验证 Proto Message 定义符合 API 规范
  - 文件：production.proto
  - 操作：检查字段类型、注释、命名规范
  - 验证：确认 Proto 定义完整，建议增加时间格式注释
  - 推荐Skill：proto-dev

#### 第二阶段：数据准备

□ 任务4：准备多场景测试数据（依赖任务1-3完成）
  - 目标：创建覆盖各种边界场景的测试数据
  - 文件：StatisTestDataSeeder.php
  - 操作：创建不同 company_id、NULL unit、空字符串 unit、跨时间范围、0值数据
  - 验证：使用数据库查询确认数据正确插入
  - 推荐Skill：dev-seeder

□ 任务5：验证数据插入正确性
  - 目标：确保测试数据符合预期
  - 文件：数据库表 nt_production_product_data
  - 操作：查询各场景数据量统计
  - 验证：数据统计符合预期
  - 推荐Skill：无

#### 第三阶段：API 功能测试

□ 任务6：测试正常场景（有数据）（依赖任务4、5完成）
  - 目标：验证 API 在正常数据场景下的正确性
  - 文件：ProductSummaryHandler.php
  - 操作：使用 company_id=11，时间范围 2026-01 到 2026-06，验证返回数据正确性
  - 验证：返回数据与手动计算一致
  - 推荐Skill：laravel-e2e-test

□ 任务7：测试边界场景（无数据/空值）（依赖任务4、5完成）
  - 目标：验证 API 在边界场景下的健壮性
  - 文件：ProductSummaryHandler.php
  - 操作：测试无数据时间范围、不存在 company_id、NULL unit、空字符串 unit
  - 验证：API 正确处理边界场景，不抛异常
  - 推荐Skill：laravel-e2e-test

□ 任务8：测试时间范围验证（依赖任务4、5完成）
  - 目标：验证时间参数验证逻辑
  - 文件：ProductSummaryHandler.php
  - 操作：测试 start_time > end_time、错误时间格式、跨年范围、单月范围
  - 验证：API 正确拒绝非法时间参数或正确处理
  - 推荐Skill：laravel-e2e-test

□ 任务9：测试 company_id 隔离正确性（依赖任务4、5完成）
  - 目标：验证企业数据隔离正确性
  - 文件：ProductSummaryHandler.php
  - 操作：使用不同 company_id 查询，验证数据不交叉
  - 验证：company_id 隔离正确，无数据泄露
  - 推荐Skill：laravel-e2e-test

#### 第四阶段：问题修复

□ 任务10：修复 Handler 参数验证（依赖任务1、6-9）
  - 目标：增加时间参数验证和异常处理
  - 文件：ProductSummaryHandler.php
  - 操作：验证时间格式、验证时间范围、捕获异常、返回友好错误信息
  - 验证：重新运行任务8，验证修复有效
  - 推荐Skill：handler-controller-guide

□ 任务11：修复 Service unit 获取逻辑（依赖任务2、6-9）
  - 目标：改进 unit 字段获取逻辑，增加默认值处理
  - 文件：NtProductDataService.php
  - 操作：分析业务需求，提供默认单位或改进获取逻辑
  - 验证：重新运行任务7，验证 unit 处理符合预期
  - 推荐Skill：无

□ 任务12：添加 Proto 时间格式注释（依赖任务3）
  - 目标：在 Proto 文件中明确时间格式要求
  - 文件：production.proto
  - 操作：添加时间格式注释，重新生成 Proto PHP 类
  - 验证：Proto 文件包含时间格式说明
  - 推荐Skill：proto-dev

#### 第五阶段：回归测试

□ 任务13：执行完整回归测试（依赖任务10-12）
  - 目标：验证所有修复后 API 的完整性
  - 文件：ProductSummaryHandler.php, NtProductDataService.php
  - 操作：重新运行所有测试场景，验证修复有效
  - 验证：所有测试通过，API 功能完整
  - 推荐Skill：laravel-e2e-test