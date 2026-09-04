# 碳资产概览API修复方案

## 审阅目标
对碳资产概览API（`/api/proto/nt_dashboard/carbon_asset/list`）的现有实现代码进行问题修复。

## 问题清单与修复方案

### P0-1: pathlist.php Handler 类名不匹配

**问题**: pathlist.php:570 注册 `CarbonAssetListHandler`，但实际文件是 `DashboardCarbonAssetHandler`，导致 Class not found 致命错误。

**修复**: 修改 pathlist.php 中 handler_class 为 `Modules\NtDashboard\ApiProto\Handlers\DashboardCarbonAssetHandler`。

**文件**: `Modules/ApiProto/config/pathlist.php:570`

---

### P0-2: API 路径文档不一致

**问题**: API文档写 `POST /api/proto/nt/dashboard/carbon/asset`，实际注册 `/api/proto/nt_dashboard/carbon_asset/list`，路径格式完全不同。

**修复**: 更新 API 文档（`docs/greenbid/api/api-oview-carbon_asset.md`）中的 NEW_API路径为实际注册的 `/api/proto/nt_dashboard/carbon_asset/list`。文档应反映代码实际状态，不改动已注册的路由。

**文件**: `docs/greenbid/api/api-oview-carbon_asset.md:199`

---

### P1-1: 新增碳资产 Hook + 实现 Service 真实数据逻辑

**问题**: `DashboardService::carbonAsset()` 返回全0/null占位数据，NtCarbon 模块没有碳资产专用 Hook。

**修复方案**:

1. **在 NtCarbon 模块新增 Hook 定义**:
   - `Modules/NtCarbon/Hooks/Definitions/GetCarbonAssetHook.php` — Hook 定义（单处理器）
   - `Modules/NtCarbon/Hooks/Parameters/GetCarbonAssetParameter.php` — 参数（company_id, year）
   - `Modules/NtCarbon/Hooks/Results/GetCarbonAssetResult.php` — 返回值（pv_power, biogas_power, clean_power, ccer, inclusive, capture, trading, quota, total_tco2e, offset）
   - `Modules/NtCarbon/Hooks/Handlers/GetCarbonAssetHandler.php` — Handler 实现

2. **GetCarbonAssetHandler 实现逻辑**:
   - 调用 `CarbonReductionService::getCompanyReductionSummary($companyId, $year)` 按 reduction_type 分类汇总：
     - `pv` → pv_power（光伏发电碳减排）
     - `biogas` → biogas_power（沼气发电碳减排）
     - `clean_energy` → clean_power（绿电/绿证碳减排）
     - `ccer` → 累加到 ccer（CCER减排项目）
     - `other` → trading（其他减排量）
     - `carbon_capture` → capture（碳捕集，需先新增枚举值）
   - 查询 `CarbonTrade` 按 company_id + year + trade_category 聚合：
     - `trade_category = 'ccer'` → ccer
     - `trade_category = 'inclusive'` → inclusive
     - `trade_category = 'other'` → trading
   - 查询 `CarbonQuota` 按 company_id + year 获取 total_quota → quota
   - 计算总减排量 total_tco2e = pv_power + biogas_power + clean_power + ccer + inclusive + capture + trading
   - 通过 `GetMonthlyEmissionHook` 获取总碳排放量，计算碳抵消比例 offset（除零防护）

3. **前置依赖**:
   - ReductionType 枚举新增 `CARBON_CAPTURE = 'carbon_capture'`
   - CarbonTrade 新增 `trade_category` 字段 + TradeCategory 枚举
   - CarbonReduction 添加 company_id 字段（迁移）

4. **注册 Hook**: 在 `Modules/NtCarbon/Providers/HookServiceProvider.php` 注册新 Hook

5. **修改 DashboardService::carbonAsset()**: 通过 HookManager 调用 GetCarbonAssetHook 获取数据，替代 TODO 占位

**涉及文件**:
- 新增: 4个 Hook 文件（Definition, Parameter, Result, Handler）
- 修改: `Modules/NtCarbon/Providers/HookServiceProvider.php`
- 修改: `Modules/NtDashboard/Services/DashboardService.php`

---

### P1-2: CarbonReduction 模型补充 company_id

**问题**: CarbonReduction 模型 $fillable 缺少 company_id，无法按企业隔离数据。

**修复**: 在 CarbonReduction 模型的 $fillable 和 $casts 中添加 company_id。

**注意**: 需要先确认数据库迁移中 nt_carbon_reduction 表是否已有 company_id 字段。如果没有，需要新增迁移（但按项目规范禁止自动执行迁移，仅创建迁移文件）。

**文件**: `Modules/NtCarbon/Models/CarbonReduction.php`

---

### P2-1: 统一碳抵消比例计算公式

**问题**: API 文档中存在两个不同的碳抵消比例公式：
- 公式A: `offset = total_tco2e / 总碳排放量 × 100`
- 公式B: `offset = total_tco2e / (总碳排放 - total_tco2e) × 100`

**修复**: 采用公式A（`offset = total_tco2e / 总碳排放量 × 100`），理由：
- 公式A 是行业标准定义（碳抵消量占总排放的比例）
- 公式B 的分母 `(总碳排放 - total_tco2e)` 没有明确业务含义
- 更新 API 文档，删除公式B，保留公式A

**文件**: `docs/greenbid/api/api-oview-carbon_asset.md:183`

---

### P2-2: CarbonAssetLogic 字段映射对齐

**问题**: `CarbonAssetLogic::calculateTotalReduction()` 的 key（pv/green_energy/biogas/ccer/other）与 API 字段（pv_power/biogas_power/clean_power/ccer/inclusive/capture/trading）不一致，缺少 inclusive 和 capture。

**修复**: 更新 `calculateTotalReduction()` 方法的 key 映射，对齐 API 字段定义：
```php
// 旧
['pv', 'green_energy', 'biogas', 'ccer', 'other']
// 新
['pv_power', 'biogas_power', 'clean_power', 'ccer', 'inclusive', 'capture', 'trading']
```

**文件**: `Modules/NtCarbon/Logics/CarbonAssetLogic.php:104`

---

### P2-3: CarbonTrade.quota() 关联键修正

**问题**: CarbonTrade::quota() 使用 year 作为 BelongsTo 关联键，但同一年可能有多条 CarbonQuota（免费+购买），导致关联不精确。

**修复**: CarbonTrade 和 CarbonQuota 之间没有直接的外键关系（CarbonTrade 没有 quota_id 字段），移除这个不精确的关联方法。如果需要查询配额，应在 Service 层通过 company_id + year 查询。

**文件**: `Modules/NtCarbon/Models/CarbonTrade.php:71-74`

---

### P2-4: CarbonAssetField 枚举单位修正

**问题**: getUnit() 对非 OFFSET 字段返回 `tCO₂`，但 API 文档单位是 `tCO₂e`（二氧化碳当量）。

**修复**: 将默认返回值从 `tCO₂` 改为 `tCO₂e`。

**文件**: `Modules/NtCarbon/Enums/CarbonAssetField.php:54`

---

## 修复顺序

1. P0-1: pathlist.php Handler 类名修复（1处改动）
2. P0-2: API 文档路径修正（1处改动）
3. P1-1: 新增碳资产 Hook + Service 实现（6处改动，含4个新文件）
4. P1-2: CarbonReduction 补充 company_id（1处改动 + 可能1个迁移文件）
5. P2-1 ~ P2-4: 次要修复（4处改动）

## 风险评估

- P0 修复零风险（仅纠正配置错误）
- P1-1 是核心功能实现，需注意：
  - CarbonReduction 的 reduction_type 枚举值需要确认（当前定义：energy_efficiency/renewable/process/carbon_capture）
  - CarbonTrade 没有 trade_category 字段区分 CCER/碳普惠/其他，可能需要通过 exchange 字段或新增分类字段来区分
  - GetMonthlyEmissionHook 递归调用需避免循环
- P1-2 需确认数据库字段存在
