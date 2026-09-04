# Docker Compose 容器 Git 推送问题解决方案

## 问题诊断

### 根本原因
容器以 `root` 用户运行（supervisord 需要 root 权限管理服务），但：
- SSH agent socket 属于 `php` 用户
- Git 仓库目录属于 `php` 用户
- root 操作需要额外配置（safe.directory）

### 影响
- ✅ root 可以使用 SSH agent（通过配置）
- ⚠️ root 创建的文件权限不正确（影响应用）
- ⚠️ 需要额外的 known_hosts 配置

## 解决方案

### ✅ 方案1：使用 php 用户操作 Git（推荐）

```bash
# 在宿主机执行 Git 操作（以 php 用户身份）
docker compose exec -u php xiaoshuo-dev git status
docker compose exec -u php xiaoshuo-dev git add .
docker compose exec -u php xiaoshuo-dev git commit -m "message"
docker compose exec -u php xiaoshuo-dev git push

# 或进入容器后切换用户
docker compose exec xiaoshuo-dev bash
su - php  # 切换到 php 用户
cd /www/devroot/moyuan/laravel_php
git status
git push
```

**优势**：
- ✅ 权限正确（文件属于 php 用户）
- ✅ SSH agent 正常工作
- ✅ 无需额外配置

### 方案2：配置 root 用户（已解决）

如果必须用 root（已配置）：
```bash
# 已完成的配置
docker compose exec xiaoshuo-dev bash -c "
# 1. 添加 Git 服务器的 host keys
ssh-keyscan github.com gitee.com codeup.aliyun.com >> ~/.ssh/known_hosts

# 2. 解决仓库所有权问题
git config --global --add safe.directory '*'

# 3. 复制 Git 用户配置
cp /home/php/.gitconfig ~/.gitconfig
"

# Git 操作
docker compose exec xiaoshuo-dev git push
```

**缺点**：
- ⚠️ 创建的文件权限为 root，需要手动修复
- ⚠️ 每次新 Git 服务器都需要添加 host key

## VSCode 终端配置

### 自动以 php 用户打开终端

在 VSCode 附加到容器后，配置默认终端用户：

**方式1：settings.json**
```json
{
    "terminal.integrated.defaultProfile.linux": "bash-php",
    "terminal.integrated.profiles.linux": {
        "bash-php": {
            "path": "/bin/bash",
            "args": ["-c", "su - php"],
            "icon": "terminal-bash"
        }
    }
}
```

**方式2：tasks.json**
```json
{
    "version": "2.0.0",
    "tasks": [{
        "label": "git-push",
        "type": "shell",
        "command": "docker compose exec -u php xiaoshuo-dev git push",
        "problemMatcher": []
    }]
}
```

## 快速命令别名

### 在宿主机 ~/.bashrc 添加

```bash
# Docker Compose Git 别名（使用 php 用户）
alias dgit='docker compose exec -u php xiaoshuo-dev git'
alias dcomposer='docker compose exec -u php xiaoshuo-dev composer'
alias dartisan='docker compose exec -u php xiaoshuo-dev php artisan'

# 使用示例
dgit status
dgit add .
dgit commit -m "更新配置"
dgit push

dcomposer install
dartisan migrate
```

## 验证步骤

### 1. 验证 SSH Agent
```bash
# root 用户
docker compose exec xiaoshuo-dev ssh-add -l
# 输出：256 SHA256:xxx... pve-work (ED25519)

# php 用户
docker compose exec -u php xiaoshuo-dev ssh-add -l
# 输出相同 ✅
```

### 2. 验证 SSH 连接
```bash
# php 用户连接测试
docker compose exec -u php xiaoshuo-dev ssh -T git@codeup.aliyun.com
# 输出：Welcome to Codeup, dongasai! ✅
```

### 3. 验证 Git 操作
```bash
# 以 php 用户测试 push
docker compose exec -u php xiaoshuo-dev bash -c "
cd /www/devroot/moyuan/laravel_php
git fetch origin --dry-run
"
# 应无错误 ✅
```

## 权限问题预防

### 创建文件后检查权限
```bash
# 检查新创建文件权限
docker compose exec xiaoshuo-dev ls -l /www/devroot/moyuan/laravel_php/

# 如果 root 创建了文件，修复权限
docker compose exec xiaoshuo-dev chown -R php:php /www/devroot/moyuan/laravel_php/
```

## 最佳实践总结

| 操作 | 推荐用户 | 原因 |
|------|----------|------|
| Supervisord 管理 | root | 需要管理 apache/sshd |
| Git 操作 | php | 权限正确，SSH agent 匹配 |
| Composer 操作 | php | 文件权限正确 |
| Laravel artisan | php | 应用需要 php 用户权限 |
| Apache/SSH 服务 | root | 系统服务 |

## 当前配置状态

✅ 已解决：
- SSH agent 可用（php 和 root 都可使用）
- known_hosts 已添加（github.com, gitee.com, codeup.aliyun.com）
- Git 用户配置已复制
- safe.directory 已配置

⚠️ 注意：
- 使用 `docker compose exec -u php` 进行 Git 操作
- root 创建的文件需要修复权限

---
**更新时间**: 2026-05-04 14:52
**验证状态**: ✅ php 用户 Git SSH 连接正常