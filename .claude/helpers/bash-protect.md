# Bash 命令保护 Hook

## 功能说明

bash-protect.js 是一个 Claude Code PreToolUse hook，用于在执行bash命令前验证命令安全性，拦截潜在的危险操作。

## 拦截规则

当前配置会拦截以下类型的危险命令：

### 文件系统危险操作
- `rm -rf /` - 删除根目录
- `rm -rf /*` - 删除根目录下所有文件
- 递归强制删除系统目录

### Git 危险操作
- `git push --force` - 强制推送到远程（建议使用 --force-with-lease）
- `git push -f` - 强制推送简写
- `git reset --hard origin/*` - 硬重置到远程分支（会丢失本地提交）
- `git clean -fdx -- /` - 清理根目录

### 系统危险操作
- `chmod 000 /` - 移除根目录权限
- `chown ... /` - 修改根目录所有者
- `mkfs.*` - 格式化磁盘
- `dd ... of=/dev/*` - 直接写入设备文件
- `> /dev/sda` - 写入磁盘设备
- `iptables -F` - 清空防火墙规则

### 网络脚本执行
- `curl ... | sh` - 从网络下载并执行脚本
- `wget ... | sh` - 从网络下载并执行脚本
- Fork bomb（炸弹病毒）

## 配置说明

Hook 配置位于 `.claude/settings.json`：

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "node .claude/helpers/bash-protect.js"
          }
        ]
      }
    ]
  }
}
```

## 白名单配置

如需允许特定命令，可在 `bash-protect.js` 中修改 `allowedExceptions` 数组：

```javascript
const allowedExceptions = [
    /^npm\s+install/,
    /^composer\s+install/,
    // 添加更多允许的模式...
];
```

## 自定义拦截规则

如需添加新的拦截规则，在 `dangerousPatterns` 数组中添加（`patterns` 为数组形式，支持多个正则）：

```javascript
const dangerousPatterns = [
    {
        patterns: [
            /your-dangerous-pattern-1/i,
            /your-dangerous-pattern-2/i,
            // 可以添加更多正则...
        ],
        message: "拦截原因说明"
    },
    // ...
];
```

## 工作原理

1. Hook 在执行 Bash 命令前触发
2. 脚本读取命令内容并匹配危险模式
3. 如匹配到危险模式，返回 deny 决策并阻止执行
4. 如命令安全，允许执行

## 测试验证

```bash
# 验证hook配置已添加
jq '.hooks.PreToolUse | length' .claude/settings.json

# 安全命令可以正常执行
ls -la

# 危险命令会被拦截（不会真的执行）
# rm -rf /  # 会被拦截
```

## 注意事项

- 此hook仅拦截通过Claude执行的bash命令
- 不会拦截用户在终端直接执行的命令
- 拦截后会给出明确的错误提示
- 可以通过修改配置添加白名单或自定义规则

## 相关文件

- `.claude/settings.json` - Hook配置文件
- `.claude/helpers/bash-protect.js` - Bash保护脚本
- `.claude/helpers/jinzhiappedit.js` - 编辑保护脚本（参考实现）