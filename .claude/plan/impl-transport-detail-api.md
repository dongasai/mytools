# 任务规划：实现 TransportDetail Proto API

## 任务目标
实现 `/api/proto/nt_report/transport_detail/list` API 的业务逻辑，当前 Handler 仅有骨架代码(TODO)，需补充数据查询和计算逻辑。

## 现状

### API信息
- **旧API**: `/api/transport/TransportDetail` (POST)
- **新API**: `/api/proto/nt_report/transport_detail/list` (POST)
- **功能**: 获取选定时间段产品碳足迹详情(按阶段)

### 已完成
- [x] Proto定义: `Modules/NtReport/ApiProto/protos/report.proto` (474-526行)
- [x] 路由注册: `Modules/ApiProto/config/pathlist.php` (1089行)
- [x] Handler骨架: `Modules/NtReport/ApiProto/Handlers/TransportDetailListHandler.php`
- [x] 底层数据模型和事件驱动链路

### 待实现
- [ ] Handler中的业务逻辑(TODO标记处)
- [ ] Service层封装查询和计算逻辑
- [ ] Hook机制(NtReport→NtCarbon跨模块调用)

### 数据来源
优先使用 `CarbonMonthlyAggregate` 月度聚合表(已有预聚合字段):
- `material_acquisition_tco2` → tco2e(原料获取)
- `material_transport_tco2` → tran_tco2e(运输)
- `process_co3_tco2 + process_coal_tco2` → process(生产)
- `output` → summary.proNum(产量)

当有product筛选时，回退到 `CarbonFootprint` + `CarbonFootprintDetail` 路径。

### Proto Message结构
```proto
message Nt_reportTransportDetailData {
  Nt_reportTransportStageValue tco2e = 1;       // 原料获取阶段
  Nt_reportTransportStageValue tran_tco2e = 2;  // 原料运输阶段
  Nt_reportTransportStageValue process = 3;     // 产品生产阶段
  Nt_reportTransportSummary summary = 4;        // 汇总信息
  string start_time = 5;
  string end_time = 6;
}
message Nt_reportTransportStageValue { float value = 1; float ratio = 2; }
message Nt_reportTransportSummary { float proNum = 1; float proVal = 2; string unit = 3; }
```

### 约束
- Handler禁止直接操作数据库，需通过Service层
- 跨模块调用必须通过Hook机制
- 需使用company_id进行数据隔离($this->token_merchant_id)
- Service层必须使用静态方法
- 过滤条件必须回传给前端
- 除零保护: ratio计算需处理分母为零

## 经验要点

| 坑点 | 说明 |
|------|------|
| company_id获取 | 必须用`$this->token_merchant_id`，禁止`$request->getCompanyId()` |
| handle()签名 | 必须用`handle(Message $request): Message` |
| Hook注册 | Hook文件存在不等于可用，必须通过ServiceProvider注册 |
| Result类 | 必须实现`jsonSerialize()`方法 |
| 过滤条件回传 | 返回时必须将过滤条件回传给前端 |
| 除零保护 | ratio计算需处理分母为零 |

## 方案

### 架构: Hook机制跨模块调用
```
NtReport Handler → NtReport Service → HookManager::apply() → NtCarbon Hook Handler → CarbonMonthlyAggregate
```

### Hook设计
- **名称**: `GetTransportDetailHook`
- **参数**: company_id, start_time, end_time, product
- **结果**: raw_material_value, transport_value, process_value, total_value, product_output, product_unit
- **占比计算**: 在Handler层计算(复用StatAnalysisLogic)，Hook只返回原始数值

### 关键决策
1. 数据源优先用`CarbonMonthlyAggregate`(预聚合)，有product筛选时回退到`CarbonFootprintDetail`
2. `proVal`(产值)暂置0，后续可通过新增Hook扩展
3. NtReport端也需注册Hook定义(apply()会验证Hook是否已注册)

## 任务清单

### ✅ 任务1: 创建Hook定义类
- 文件: `Modules/NtCarbon/Hooks/Definitions/GetTransportDetailHook.php`
- 操作: 新建，参照`GetMonthlyEmissionHook.php`
- 验证: 类能被实例化
- 状态: 已完成

### ✅ 任务2: 创建Hook参数类
- 文件: `Modules/NtCarbon/Hooks/Parameters/GetTransportDetailParameter.php`
- 操作: 新建，包含company_id/start_time/end_time/product属性
- 验证: 实例化参数对象，getter返回正确值
- 状态: 已完成

### ✅ 任务3: 创建Hook结果类
- 文件: `Modules/NtCarbon/Hooks/Results/GetTransportDetailResult.php`
- 操作: 新建，包含raw_material_value/transport_value/process_value/total_value/product_output/product_unit，实现jsonSerialize()
- 验证: success()/failure()工厂方法正常工作
- 状态: 已完成

### ✅ 任务4: 创建NtCarbon侧Hook Handler (依赖任务1/2/3)
- 文件: `Modules/NtCarbon/Hooks/Handlers/GetTransportDetailHandler.php`
- 操作: 新建，查询CarbonMonthlyAggregate汇总各阶段排放值
- 验证: Hook执行返回正确数据结构
- 状态: 已完成

### ✅ 任务5: 注册Hook到NtCarbon的HookServiceProvider (依赖任务4)
- 文件: `Modules/NtCarbon/Providers/HookServiceProvider.php`
- 操作: 修改，添加registerHook和add
- 验证: `php artisan hook:list`能看到新Hook
- 状态: 已完成

### ✅ 任务6: 创建NtReport侧Service (依赖任务1/2/3)
- 文件: `Modules/NtReport/Services/TransportDetailService.php`
- 操作: 新建，通过HookManager::apply()调用NtCarbon Hook
- 验证: 调用方法返回正确结构数组
- 状态: 已完成

### ✅ 任务7: 创建NtReport的HookServiceProvider (依赖任务6)
- 文件: `Modules/NtReport/Providers/HookServiceProvider.php`
- 操作: 新建，注册Hook定义
- 验证: ServiceProvider能正常注册
- 状态: 已完成

### ✅ 任务8: 注册HookServiceProvider到NtReportServiceProvider (依赖任务7)
- 文件: `Modules/NtReport/Providers/NtReportServiceProvider.php`
- 操作: 修改，在$providers数组中添加HookServiceProvider
- 验证: 模块正常加载
- 状态: 已完成

### ✅ 任务9: 修改TransportDetailListHandler (依赖任务6/8)
- 文件: `Modules/NtReport/ApiProto/Handlers/TransportDetailListHandler.php`
- 操作: 替换TODO骨架，调用Service+计算ratio+构建Proto Response
- 验证: API调用返回正确Proto Response
- 状态: 已完成

### 依赖关系
```
任务1/2/3(并行) → 任务4 → 任务5
                → 任务6 → 任务7 → 任务8 → 任务9
```

## 实施总结

### 已完成文件清单
1. `Modules/NtCarbon/Hooks/Definitions/GetTransportDetailHook.php` - Hook定义
2. `Modules/NtCarbon/Hooks/Parameters/GetTransportDetailParameter.php` - Hook参数
3. `Modules/NtCarbon/Hooks/Results/GetTransportDetailResult.php` - Hook结果
4. `Modules/NtCarbon/Hooks/Handlers/GetTransportDetailHandler.php` - Hook处理器
5. `Modules/NtCarbon/Providers/HookServiceProvider.php` - 注册Hook(修改)
6. `Modules/NtReport/Services/TransportDetailService.php` - NtReport Service
7. `Modules/NtReport/Providers/HookServiceProvider.php` - NtReport HookServiceProvider
8. `Modules/NtReport/Providers/NtReportServiceProvider.php` - 注册ServiceProvider(修改)
9. `Modules/NtReport/ApiProto/Handlers/TransportDetailListHandler.php` - Handler实现(修改)

### 架构实现
- **跨模块通信**: NtReport Handler → Service → HookManager → NtCarbon Handler → CarbonMonthlyAggregate
- **数据源**: 优先使用`CarbonMonthlyAggregate`月度聚合表
- **产品筛选**: 有product参数时通过Product模型查找，再查CarbonFootprintDetail
- **占比计算**: Handler层计算ratio(除零保护)
- **过滤条件回传**: start_time和end_time已回传

### 关键决策执行情况
- ✅ 数据源优先用`CarbonMonthlyAggregate`(预聚合)
- ✅ `proVal`(产值)暂置0
- ✅ NtReport端注册Hook定义(apply会验证)
- ✅ 除零保护: `$totalValue > 0 ? round($value / $totalValue, 4) : 0.0`
- ✅ company_id通过`$this->token_merchant_id`获取
- ✅ Service层静态方法设计

### 待测试验证
- API请求测试: `/api/proto/nt_report/transport_detail/list`
- Hook注册验证: `php artisan hook:list`
- 数据查询正确性
- 产品筛选功能
- ratio计算正确性
