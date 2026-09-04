# 命令

## 创建新模块

### 命令使用
```bash
php artisan module:create <模块名> [--namespace=<命名空间>]
```

### 参数说明
- `模块名`: 必填，要创建的新模块名称
- `--namespace`: 可选，指定命名空间（默认与模块名相同）

### 功能特性
1. **模块复制**: 基于 Modules/Demo5 模板创建新模块
2. **命名空间更新**: 自动更新所有文件中的命名空间
3. **配置文件更新**: 更新 module.json 配置文件
4. **文件重命名**: 自动重命名服务提供者文件
5. **错误处理**: 包含完善的错误处理和回滚机制

### 使用示例

#### 1. 创建标准模块（命名空间与模块名相同）
```bash
php artisan module:create MyModule
```
创建结果：
- 📁 路径: `Modules/MyModule`
- 🔧 命名空间: `Modules\MyModule`
- 📋 别名: `mymodule`

#### 2. 创建自定义命名空间模块
```bash
php artisan module:create MyModule --namespace=CustomNS
```
创建结果：
- 📁 路径: `Modules/MyModule`
- 🔧 命名空间: `Modules\CustomNS`
- 📋 别名: `mymodule`

### 实现细节
- 复制 Demo5 模块的所有文件和目录结构
- 递归替换所有 PHP 文件中的命名空间引用
- 更新服务提供者类名和文件名
- 更新模块配置文件中的相关信息
- 支持错误回滚，创建失败时自动清理