# FeatureSsh 模块

SSH服务器管理与远程操作功能模块

## 核心功能

- **SSH服务器管理**: 服务器信息、分组、状态监控
- **SSH认证管理**: 密钥对、密码、证书、Agent
- **SSH连接管理**: 连接池、会话管理、断线重连
- **SSH Bash执行**: 命令执行、实时输出、后台任务
- **SFTP文件传输**: 上传下载、目录操作、权限管理

## 数据表

- `fssh_server_groups` - 服务器分组表
- `fssh_servers` - SSH服务器表
- `fssh_key_pairs` - SSH密钥对表
- `fssh_authentications` - SSH认证方式表
- `fssh_command_logs` - SSH命令执行日志表

## 安装

```bash
# 运行迁移
php artisan module:migrate FeatureSsh

# 运行填充
php artisan module:seed FeatureSsh
```

## 使用示例

```php
// 测试SSH连接
$result = \Modules\FeatureSsh\Services\SshConnectionService::testConnection($serverId);

// 执行命令
$result = \Modules\FeatureSsh\Services\SshBashService::execute($serverId, 'ls -la');

// SFTP上传
$result = \Modules\FeatureSsh\Services\SftpService::upload($serverId, $localPath, $remotePath);
```

## 后台管理

访问 `/admin/featuressh/*` 进行SSH服务器和认证管理。

## 文档

详细文档请查看 `CLAUDE.md` 和 `Docs/` 目录。