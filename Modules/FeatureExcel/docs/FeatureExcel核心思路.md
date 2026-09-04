# FeatureExcel 模块核心思路

## 对外提供
1. 约定/基类
- AbstractImportTemplate 导入模板
- AbstractExportTemplate 导出模板
2. 服务
- ModuleFeatureExcelService 对外服务类
3. 工具
- console工具execl表格和AbstractImportTemplate契合(表格模板是否符合导入ImportTemplate)
- 根据导入模板定义自动生成 Excel 模板文件的Console
-  Excel 解析命令,解析表格文件,格式化输出
