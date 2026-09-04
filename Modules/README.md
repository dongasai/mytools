# 核心逻辑

## 核心元素
- 企业
- 生产工艺/工艺工程
- 能耗数据
- 产量产值
- 原辅材料
- 能碳标准
- 碳资产
- 供应链
    * 供应商
    * 客户
- 环境排放
    * 废水
    * 废气
    * 工业固体废弃物

## 企业
- enterprise_enterprises EnterpriseEnterprise ,核心字段: id 企业id,industry 行业code

## 产品
- nt_production_products NtProduct 产品表

## 生产工艺/工艺工程
- **`nt_production_processes` ProcessDefinition** 工艺定义 ProcessDefinition.code
- enterprise_id, category_id, name, code 工艺代码-唯一, formula,formula_params, default_factors
- **`nt_production_process_data` ProcessData** 工艺数据模型 ,实际生产中工艺的具体数据,$enterprise_id 企业ID,$definition_id 工艺定义ID,$tco2 CO2排放量(冗余列)
- ** `enterprise_hangye_process` | **行业工艺关联表** | hangye_code, process_code |

## 产品工艺关联
> 计算生产阶段碳排放

nt_production_statis_config (主配置)
↓ HasMany
nt_production_statis_product (产品配置 产品统计项表 )
* product_id 产品ID
↓ HasMany
nt_production_statis_process (排放过程配置)
* product_id 产品ID



## 能耗数据

- electric → nt_energy_cm_ele (电力消耗)
- heat → nt_energy_cm_heat (热力消耗)
- energy → nt_energy_cm_fuel (化石燃料)
    - energy_id 关联nt_energy_factors 化石燃料排放因子 (type=1 化石燃料)
- medium → nt_energy_cm_medium (耗能工质)
    - energy_id 关联nt_energy_factors 化石燃料排放因子 (type=2 化石燃料)
- sup_electric → nt_energy_sy_ele (外供电力)
- sup_heat → nt_energy_sy_heat (外供热力)
- sup_energy → nt_energy_sy_energy (外输焦碳)

## 原辅材料
> 计算原料获取/
- nt_material_records 原副材料碳排放记录表
    * product_id 产品id
    * material_id → 原辅料类型因子   nt_factors 表
    * factor_id → 材料排放类型因子  nt_factors 表
    * transport_factor_id → 运输排放类型因子   nt_factors 表
- nt_material_monthlies 原辅料月度汇总表
    * total_material_emission 月度原料获取阶段总排放量(tCO2e)
    * total_transport_emission 月度运输总排放量(tCO2e)


## 能碳标准
- nt_standards 
  * enterprise_id: 企业ID
  * type: 标准类型（energy=能耗标准，carbon=碳排放标准）
  * data_time: 年份（YYYY格式）

## 碳足迹
- 原料获取阶段 nt_material_monthlies.total_material_emission
- 原料运输阶段 nt_material_monthlies.total_transport_emission
- 生产阶段 nt_production_product_data.tco2
    * 通过 nt_production_statis_product 获取,产品的 分配方式
    * 再通过 nt_production_statis_process 获取产品的工艺列表和分配比例
    * 查询 nt_material_monthlies 的工艺碳排放总数 * 比例
    * 三种division方式: 产量num/产值val/百分比other
    * 未分别统计(不区分工艺了),division 增加日期

## 碳资产 NtCarbon
- 配额表 nt_carbon_quota 年度配额
- 月度 nt_carbon_asset_monthly  月度碳资产数据模型 - 存储企业月度可再生能源、碳交易及捕集数据.
