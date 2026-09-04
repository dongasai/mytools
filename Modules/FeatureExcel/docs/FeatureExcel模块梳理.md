# FeatureExcel 模块梳理

**梳理时间**: 2026-08-14（已退役状态）
**梳理方式**: 设计文档 + 代码实际状态核对
**v2退役状态**: 2026-08-14 已完成退役，所有调用方已迁移至 v3

---

## 一、模块定位

**工具功能模块层**：提供 Excel/CSV 导入导出的通用引擎能力，无独立数据表（priority 5）。

对外三大能力（与核心思路一致）：

| 能力 | 组件 | 状态 |
|------|------|------|
| 约定/基类 | AbstractImportTemplate / AbstractExportTemplate | ✅ 已实现 |
| 服务 | ModuleFeatureExcelService（全静态门面） | ✅ 已实现 |
| 工具 | Console 3 命令（validate / generate-template / parse） | ✅ 已实现 |

> `docs/实现进度报告.md` 是重构前快照，声称三大能力"不存在"，**已过时**，实际均已实现。

---

## 二、架构分层

```
业务模块（NtEnergy / NtReport / Enterprise / FeatureExcelDemo）
    ↓ 继承模板基类（v3 对象化模式）
ModuleFeatureExcelService ——统一入口
    ↓
HookApplyLogic（业务钩子） + Engines 引擎层
    ├── Import（Excel/CsvImportEngine + Abstract 基类）
    ├── Export（Excel/CsvExportEngine + Abstract 基类）
    ├── File（Excel/CsvFileHandler，分块读取）
    ├── Validation（DataValidator → ValidationResult / ImportResult）
    └── Transform（Data/Type/ValueTransformer）
    ↓
核心模块层：ABase（Provider 基类）+ AFile（导出文件保存）
```

---

## 三、核心组件明细

### 1. 模板基类（Templates/Base/）

- `AbstractImportTemplate extends Engines\Template\ImportTemplate`
  - 抽象：`defineFields(): array`
  - 钩子：`validateRow(array $row, int $rowNumber): ?string`（null 通过）、`transformRow(array $row): array`
  - 属性：name / description / format / sheet / startRow(2) / headerRow(1)
- `AbstractExportTemplate extends ExportTemplate`
  - 抽象：`defineFields()`；钩子：`formatRow(array $row): array`
  - 属性：name / sheetName / headers / styles / fileNamePattern / storeName / persist / cacheTtl，内置 `generateFileName()`（变量替换 + 字符过滤）

基类不持有 Engine；由 Service 按 `getFormat()` 静态选择 CSV/Excel 引擎。

### 2. 对外服务（Services/）

**ModuleFeatureExcelService**（v3 推荐，全静态）：
- `importWithValidation(filePath, template): ImportResult` — 引擎标准验证 → 业务钩子（validateRow/transformRow）
- `import(filePath, template): array` — 跳过验证，仅 transformRow
- `export(data, template, options): string` — formatRow → 引擎生成 → 返回下载 URL
- `generateDataFingerprint(parameters, ?data): string` — 导出缓存一致性指纹

**v2 组件状态**：ImportService / ExportService / TemplateService / TemplateParser / ReportTemplateConfig 已于 2026-08-14 删除，v2 模式完全退役。

### 3. 引擎层（Engines/）

完整可用，静态工具风格：
- Import/Export 各 3 个类（Abstract + Excel + Csv），AbstractImportEngine 含 resolveFilePath/validateFilePath 路径安全，AbstractExportEngine 含缓存键/指纹
- File：ExcelFileHandler 支持分块 Generator 读取与样式
- Validation：DataValidator → ValidationResult；ImportResult（success/failed 工厂）
- Transform：Data / Type / Value 三级转换

### 4. Logics

- `HookApplyLogic`：applyImportHooks（逐行 validateRow→transformRow，错误格式"第N行 业务验证: xx"）、applyExportHooks（逐行 formatRow）
- `TemplateLogic`：getImport/ExportTemplateSummary（供 Console 展示）

### 5. Console 命令

| 命令 | 签名 | 功能 |
|------|------|------|
| ValidateTemplateCommand | `featureexcel:validate {file} {template} [--dry-run]` | 表格与模板契合验证，支持 FQCN/短类名 |
| GenerateTemplateCommand | `featureexcel:generate-template {template} [--output] [--filename]` | 按导入模板生成 Excel（表头+示例行） |
| ParseExcelCommand | `feature-excel:parse {path} [--rows=100] [--sheet=0]` | 解析本地/URL 文件，对象化输出单元格 |

均继承 DLaravel\Commands\Command，入口 handleRun。

### 6. 辅助组件

- **Events**（3 个：ExportCompleted / ImportCompleted / ImportFailed）：仅定义，**全项目无 dispatch**
- **Enums**（3 个：DataType / ExportFormat / ImportStatus）：已定义，**无任何引用**（引擎用裸字符串比较）
- **Exceptions**（4 个）：FileFormat / InvalidData / Validation（携带 errors）/ TemplateNotFound
- **config/feature_excel.php**：default_format、max_upload_size(10MB)、max_rows(10万)、chunk_threshold(5000)/chunk_size(1000)、cache_ttl、时区、CSV 编码/BOM
- **ServiceProvider**：继承 ABase\Support\ServiceProvider，仅注册 3 个命令

---

## 四、集成现状

| 模块 | 集成方式 | 状态 |
|------|---------|------|
| **FeatureExcelDemo** | v3 完整示例：2 个模板（含全部钩子）+ DemoOrderService + 4 个 Proto Handler + Model/迁移/Seeder | ✅ 完整 |
| **NtEnergy** | v3：`Templates/Import/EnergyDataHourlyImportTemplate`（含 Meter 业务验证，EnergyDataService:298 调用） | ✅ 实际使用 |
| **NtReport** | v3：`CarbonCheckExportTemplate` / `CarbonFootprintExportTemplate`（FeatureExcelTemplates 目录） | ✅ 已迁移 v3 |
| **Enterprise** | v3：`EnterpriseUserExportTemplate`（FeatureExcelTemplates 目录） | ✅ 已迁移 v3 |

---

## 五、v2 模式退役记录（2026-08-14 完成）

### 5.1 退役执行记录

**迁移完成（3 个调用方全部迁移至 v3）**：

| 调用方 | 原 v2 组件 | 新 v3 模板类 |
|--------|-----------|-------------|
| `Modules/NtReport/Services/CarbonFootprintExportService.php` | ExportService | `CarbonFootprintExportTemplate` |
| `Modules/NtReport/Services/CarbonCheckExportService.php` | ExportService | `CarbonCheckExportTemplate` |
| `Modules/Enterprise/ApiProto/Handlers/UserExportHandler.php` | ExportService | `EnterpriseUserExportTemplate` |

**已删除 v2 组件（2026-08-14）**：

- `ExportService` — 已删除
- `ImportService` — 已删除
- `TemplateService` — 已删除
- `TemplateParser` — 已删除
- `ReportTemplateConfig` — 已删除
- `TemplateNotFoundException` — 已删除

**测试清理**：

- `FeatureExcelTest`（v2，约 19 用例）— 已删除
- `TemplateBaseTest`（v3，13 用例）— 保留

### 5.2 退役验证结果

- ✅ 3 个导出接口 curl 重放全部成功
- ✅ 全局 grep v2 类名零命中（除历史文档）
- ✅ `php artisan test Modules/FeatureExcel/Tests/Unit` 仅运行 TemplateBaseTest 且全绿
- ✅ v2 模式完全退役，仅保留 v3 对象化模板模式

---

## 六、问题清单

1. **文档过时**：`docs/实现进度报告.md` 声称核心组件"不存在"，实际全部实现，需更新或归档
2. **~~v2 未废弃~~** → ✅ **已解决（2026-08-14 退役完成）**：NtReport、Enterprise 已迁移至 v3；v2 组件已删除
3. **死代码**：3 个事件类 + 3 个枚举无任何 dispatch/引用
4. **模板类重名**：NtEnergy 同时存在 `Templates/Import/EnergyDataHourlyImportTemplate`（实际使用）与 `FeatureExcelTemplates/` 下同名类（无引用示例），短类名解析有歧义风险
5. **命令扫描目录不一致**：validate 扫 ImportTemplates/ExportTemplates，generate-template 扫 FeatureExcelTemplates
6. **命名不统一**：命令前缀 `featureexcel:` 与 `feature-excel:` 混用；GenerateTemplateCommand 默认目录 `public/excel_templates` 与 CLAUDE.md 文档（`public/excel_template`）不一致
7. **FieldMapping 双套设置器**：setDefault() 与 defaultValue() 并存
8. **CLAUDE.md 示例失真**：validateRow 签名为 `(array $row): bool`，实际代码为 `(array $row, int $rowNumber): ?string`

---

## 七、测试覆盖

- `TemplateBaseTest`（v3，13 用例）：FieldMapping 流式接口 + fromArray 兼容、基类实例化、HookApplyLogic 三钩子、Service 全流程 CSV（钩子失败/引擎失败/成功/无验证）、指纹幂等
- ~~`FeatureExcelTest`（v2，约 19 用例）~~ — **已删除（2026-08-14 v2 退役）**
- 无 Tests/Feature 目录

---

## 八、后续建议

**P0（清理）**
- ✅ ~~执行 v2 退役~~ — **已完成（2026-08-14）**：NtReport、Enterprise 已迁移至 v3；v2 组件已删除
- 更新或归档过时的实现进度报告

**P1（消除歧义）**
- 处理 NtEnergy 重名模板类（删除无引用示例或改名）
- 统一命令扫描目录与命令前缀；修正 CLAUDE.md 示例签名
- FieldMapping 移除 setDefault()，保留 defaultValue()

**P2（增强）**
- 为 3 个事件补 dispatch（导入/导出完成通知）或删除；枚举接入引擎替换裸字符串
- 补充 Console 命令测试与 Feature 测试
