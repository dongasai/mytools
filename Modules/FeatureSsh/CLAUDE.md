# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## 模块定位

**FeatureSsh = SSH 功能模块**

核心定位：SSH 服务器管理与远程操作功能模块

核心能力：
- SSH 服务器管理（服务器信息、分组、状态监控）
- SSH 认证管理（密钥对、密码、证书、Agent）
- SSH 连接管理（连接池、会话管理、断线重连）
- SSH Bash 执行（命令执行、实时输出、后台任务）
- SFTP 文件传输（上传下载、目录操作、权限管理）

---

## 核心设计原则

### 1. 独立功能模块

**SSH 是通用功能**：
- NnnAgent 模块使用（远程项目）
- ASync 模块使用（远程数据同步）
- 其他模块可复用

### 2. 安全优先

**凭证安全**：
- 所有凭证加密存储（Laravel encrypt()）
- 私钥文件权限管理
- 密码字段掩码显示
- 操作日志审计

### 3. 连接复用

**连接池设计**：
- 避免重复建立连接
- 自动断线重连
- 连接健康检查
- 资源释放管理

---

## 数据表设计

### 核心表

```sql
fssh_servers (SSH服务器表)
├── id
├── name (服务器名称)
├── group_id (分组ID，nullable)
├── host
├── port (默认22)
├── system_type (enum: linux/windows/macos)
├── system_info (JSON: 系统信息)
├── status (enum: active/inactive/offline)
├── last_check_at (最后检测时间)
└── 备注

fssh_server_groups (服务器分组表)
├── id
├── name
├── parent_id (支持层级，nullable)
└── 备注

fssh_authentications (SSH认证方式表)
├── id
├── name (认证名称)
├── server_id (关联服务器)
├── auth_type (enum: key/password/certificate/agent)
├── credentials (加密存储JSON)
├── is_default (是否默认认证)
├── status
└── 备注

fssh_key_pairs (SSH密钥对表)
├── id
├── name
├── type (enum: rsa/ed25519/ecdsa)
├── public_key
├── private_key (加密存储)
├── passphrase (加密存储，nullable)
├── fingerprint
├── comment (nullable)
└── 创建时间

fssh_command_logs (SSH命令执行日志表)
├── id
├── server_id
├── user_id (执行用户，nullable)
├── command
├── exit_code
├── output (text，截断存储)
├── execution_time (毫秒)
├── status (enum: success/failed/timeout)
└── 执行时间
```

---

## 模块结构

```
Modules/FeatureSsh/
├── Commands/              # Artisan 命令
│   ├── SshKeyGenerateCommand.php
│   ├── SshTestCommand.php
│   └── SshServerCheckCommand.php
├── config/                # 配置文件
│   └── ssh.php
├── Database/              # 数据库迁移和填充
│   ├── migrations/
│   └── seeders/
├── DcatAdmin/             # 超管后台入口
│   └── Controllers/
│       ├── ServerController.php
│       ├── ServerGroupController.php
│       ├── AuthenticationController.php
│       ├── KeyPairController.php
│       └── CommandLogController.php
├── Docs/                  # 详细文档
│   ├── 架构设计.md
│   ├── 认证方式.md
│   ├── Bash执行.md
│   └── SFTP使用.md
├── Dtos/                  # 数据传输对象
│   ├── ServerInfo.php
│   ├── CommandResult.php
│   └── FileTransferResult.php
├── Enums/                 # 枚举定义
│   ├── AuthType.php
│   ├── ServerStatus.php
│   └── SystemType.php
├── Events/                # 事件类
│   ├── ServerConnected.php
│   ├── CommandExecuted.php
│   └── FileTransferred.php
├── Listeners/             # 事件监听器
├── Logics/                # 逻辑层（必须静态类）
│   ├── SshKeyLogic.php
│   └── CommandParseLogic.php
├── Models/                # 数据模型
│   ├── Server.php
│   ├── ServerGroup.php
│   ├── Authentication.php
│   ├── KeyPair.php
│   └── CommandLog.php
├── Providers/             # 服务提供者
│   └── FeatureSshServiceProvider.php
├── QueueJobs/             # 队列任务
├── routes/                # 路由定义
│   └── admin.php
├── Services/              # 业务服务层（优先静态方法）
│   ├── ServerService.php
│   ├── AuthenticationService.php
│   ├── SshConnectionService.php
│   ├── SshBashService.php
│   ├── SftpService.php
│   └── KeyPairService.php
├── Support/               # 支持类
│   ├── SshClient.php
│   ├── SftpClient.php
│   └── ConnectionPool.php
├── Tests/                 # 测试
│   ├── Unit/
│   └── Feature/
└── Validations/           # 验证类
    ├── ServerValidation.php
    └── AuthenticationValidation.php
```

---

## 核心服务设计

### 1. SshConnectionService - 连接管理

```php
class SshConnectionService
{
    /**
     * 获取连接（从连接池或新建）
     */
    public static function getConnection(int $serverId, ?int $authId = null): SshClient;

    /**
     * 测试连接
     */
    public static function testConnection(int $serverId, ?int $authId = null): array;

    /**
     * 释放连接（归还连接池）
     */
    public static function releaseConnection(SshClient $client): void;

    /**
     * 获取服务器信息
     */
    public static function getServerInfo(int $serverId): ServerInfo;
}
```

### 2. SshBashService - Bash执行

```php
class SshBashService
{
    /**
     * 执行命令
     */
    public static function execute(int $serverId, string $command, array $options = []): CommandResult;

    /**
     * 执行后台命令
     */
    public static function executeBackground(int $serverId, string $command): string;

    /**
     * 获取后台命令输出
     */
    public static function getBackgroundOutput(string $taskId): array;

    /**
     * 实时执行（流式输出）
     */
    public static function executeStreaming(int $serverId, string $command, callable $callback): CommandResult;
}
```

### 3. SftpService - 文件传输

```php
class SftpService
{
    /**
     * 上传文件
     */
    public static function upload(int $serverId, string $local, string $remote): FileTransferResult;

    /**
     * 下载文件
     */
    public static function download(int $serverId, string $remote, string $local): FileTransferResult;

    /**
     * 列出目录
     */
    public static function listDirectory(int $serverId, string $path): array;

    /**
     * 创建目录
     */
    public static function createDirectory(int $serverId, string $path): bool;

    /**
     * 删除文件/目录
     */
    public static function delete(int $serverId, string $path): bool;
}
```

### 4. KeyPairService - 密钥管理

```php
class KeyPairService
{
    /**
     * 生成密钥对
     */
    public static function generate(string $name, string $type = 'ed25519', ?string $passphrase = null): KeyPair;

    /**
     * 导入密钥对
     */
    public static function import(string $name, string $privateKey, ?string $publicKey = null, ?string $passphrase = null): KeyPair;

    /**
     * 导出公钥
     */
    public static function exportPublicKey(int $keyPairId): string;

    /**
     * 验证密钥对
     */
    public static function validate(int $keyPairId): bool;
}
```

---

## 认证方式说明

### key - 密钥认证

```json
{
    "key_pair_id": 1,
    "username": "root"
}
```

### password - 密码认证

```json
{
    "username": "root",
    "password": "encrypted_password"
}
```

### certificate - 证书认证

```json
{
    "certificate": "certificate_content",
    "key_pair_id": 1,
    "username": "root"
}
```

### agent - SSH Agent

```json
{
    "username": "root"
}
```

---

## 后台管理界面

### 菜单结构

```
SSH管理
├── 服务器管理
│   ├── 服务器列表
│   └── 服务器分组
├── 认证管理
│   ├── 认证配置
│   └── 密钥对管理
└── 操作日志
    └── 命令执行日志
```

### 服务器管理

```
/admin/featuressh/servers
├── 列表页
│   ├── 服务器名称
│   ├── 分组
│   ├── 主机:端口
│   ├── 系统类型
│   ├── 状态
│   ├── 最后检测时间
│   └── 操作: 测试连接 | 编辑 | 删除
├── 创建/编辑表单
│   ├── 基本信息
│   ├── 网络配置
│   ├── 系统信息（自动检测）
│   └── 备注
└── 测试连接功能
```

### 认证管理

```
/admin/featuressh/authentications
├── 列表页
│   ├── 认证名称
│   ├── 关联服务器
│   ├── 认证类型
│   ├── 是否默认
│   ├── 状态
│   └── 操作: 测试 | 编辑 | 删除
├── 创建/编辑表单
│   ├── 基本信息
│   ├── 选择服务器
│   ├── 认证类型选择
│   ├── 认证配置（动态显示）
│   └── 设置默认
└── 测试认证功能
```

### 密钥对管理

```
/admin/featuressh/key-pairs
├── 列表页
│   ├── 名称
│   ├── 类型
│   ├── 指纹
│   ├── 备注
│   └── 操作: 查看公钥 | 编辑 | 删除
├── 创建表单
│   ├── 名称
│   ├── 密钥类型
│   ├── 密码（可选）
│   └── 备注
└── 导入功能
```

---

## 开发规范

### 必须遵守

1. **模块化开发**：所有代码在 `Modules/FeatureSsh/` 内
2. **数据表前缀**：使用 `fssh_` 作为数据表前缀
3. **PHPDoc 规范**：所有代码必须有注释
4. **禁止 try**：非必要不要 try，会掩盖错误
5. **禁止构造函数属性提升语法**
6. **优先静态方法**：Services 层优先使用静态方法
7. **Logics 必须静态类**：Logics 层必须是静态类，不允许实例化
8. **凭证加密**：所有密码、私钥必须使用 `encrypt()` 加密存储

### 分层架构

```
Models → Services → Logics → Controllers
```

| 层级 | 职责 | 关键规则 |
|------|------|---------|
| **Models** | 数据结构、关系映射 | Eloquent ORM，数据表使用 `fssh_` 前缀 |
| **Services** | 协调组件、复杂业务 | **优先静态方法**，**禁止读取HTTP/session**，参数显性传入 |
| **Logics** | 数据组装/单一逻辑 | **必须静态类**，不允许实例化，无状态 |
| **DcatAdmin Controllers** | 超管后台HTTP处理 | Grid/Form/Action，系统级管理 |

---

## 技术栈

- **SSH 库**：phpseclib/phpseclib 或 ext-ssh2
- **加密**：Laravel `encrypt()` / `decrypt()`
- **连接池**：自定义实现（基于静态数组）
- **密钥生成**：phpseclib 或 OpenSSL

---

## 依赖关系

```
FeatureSsh (功能模块)
├── 依赖 ABase（基础设施）
└── 被其他模块使用
    ├── NnnAgent（远程项目）
    ├── ASync（远程同步）
    └── 其他需要SSH的模块
```

---

## 开发命令

### 模块管理

```bash
# 运行模块迁移
php artisan module:migrate FeatureSsh

# 运行模块填充
php artisan module:seed FeatureSsh

# 查看模块状态
php artisan module:list
```

### 测试

```bash
# 运行模块测试
./vendor/bin/phpunit Modules/FeatureSsh/Tests

# 运行单个测试
./vendor/bin/phpunit --filter=TestName
```

### 代码格式化

```bash
./vendor/bin/pint Modules/FeatureSsh/
```

---

## 模块类型

**feature** - 功能模块

---

**创建时间**: 2026-09-02
**维护者**: AI 开发团队