#!/usr/bin/env node

/**
 * Claude Code Hook 工具 - Bash 命令保护
 *
 * 功能：拦截危险的bash命令，防止误操作
 */

import fs from 'fs';

// 危险命令模式列表（patterns 为数组形式，支持多个正则）
const dangerousPatterns = [
    {
        patterns: [
            /rm\s+(-[rf]+\s+|-[a-z]*r[a-z]*f[a-z]*\s+|-[a-z]*f[a-z]*r[a-z]*\s+).*\//i,
            /rm\s+.*\/\s*$/i
        ],
        message: "禁止递归强制删除根目录或系统目录"
    },
    {
        patterns: [
            /git\s+push\s+.*--force/i,
            /git\s+push\s+.*-f\s/i
        ],
        message: "禁止强制推送（git push --force/-f），任何时候都不能覆盖远程仓库的提交历史,你认为需要-f的需求都是错误的"
    },
    {
        patterns: [
            /git\s+reset\s+--hard\s+origin\//i,
            /git\s+clean\s+-[fdx]+\s+--\s*\//i
        ],
        message: "禁止破坏性 Git 操作（硬重置远程分支、清理根目录），这会丢失本地提交或文件,如确定需要操作交由用户进行"
    },
    {
        patterns: [
            /chmod\s+(-R\s+)?000\s+\//i,
            /chown\s+.*\s+\//i,
            /mkfs\./i,
            /dd\s+.*of=\/dev\//i,
            />\s*\/dev\/sda/i,
            /iptables\s+/i,
            /firewall-cmd\s+/i,
            /ufw\s+/i,
            /ip6tables\s+/i,
            /nft\s+/i,
            /ebtables\s+/i
        ],
        message: "禁止对系统进行操作"
    },
    {
        patterns: [
            /\|.*sh\s*$/i,
            /curl.*\|.*sh/i,
            /wget.*\|.*sh/i
        ],
        message: "禁止从网络下载并执行脚本（存在安全风险）"
    },
    {
        patterns: [
            /:\(\)\{\s*:\|\:&\s*\}\s*;\s*:/i
        ],
        message: "禁止执行 fork bomb（炸弹病毒）"
    },
    {
        patterns: [
            /php\s+artisan\s+serve/i
        ],
        message: "项目已经通过nginx运行,不要重复启动"
    },
    {
        patterns: [
            /mysql\s+/i
        ],
        message: "mysql命令禁止使用,读取数据库使用php tinker或 dbhub mcp"
    },
    {
        patterns: [
            /cd\s+/i
        ],
        message: "禁止切换目录,工作目录禁止改变"
    },
    {
        patterns: [
            /php artisan module:make-command\s+/i
        ],
        message: "不要使用module:make-command命令,它生成的文件不符合项目规范,建议阅读Demo5模块的案例后创建文件"
    }
];

// 允许的例外（命令白名单）
const allowedExceptions = [
    // 可以在这里添加允许的命令模式
    // 例如：/^npm\s+install/
];

/**
 * 从stdin读取Hook输入数据
 */
function readHookInput() {
    try {
        const input = fs.readFileSync(0, 'utf8');
        return JSON.parse(input);
    } catch (error) {
        console.error('读取Hook输入失败:', error.message);
        process.exit(1);
    }
}

/**
 * 检查命令是否在白名单中
 */
function isInAllowedException(command) {
    if (!command) return false;

    return allowedExceptions.some(pattern => pattern.test(command));
}

/**
 * 检查命令是否匹配危险模式
 */
function checkDangerousPattern(command) {
    if (!command) return null;

    for (const rule of dangerousPatterns) {
        for (const pattern of rule.patterns) {
            if (pattern.test(command)) {
                return rule;
            }
        }
    }

    return null;
}

/**
 * 主处理函数
 */
function main() {
    // 读取Hook输入
    const hookInput = readHookInput();

    // 只处理PreToolUse事件
    if (hookInput.hook_event_name !== 'PreToolUse') {
        process.exit(0);
    }

    const toolName = hookInput.tool_name || '';
    const toolInput = hookInput.tool_input || {};
    const command = toolInput.command || '';

    // 只处理Bash操作
    if (toolName !== 'Bash') {
        process.exit(0);
    }

    // 检查是否在白名单中
    if (isInAllowedException(command)) {
        process.exit(0);
    }

    // 检查危险模式
    const dangerousRule = checkDangerousPattern(command);
    if (dangerousRule) {
        const errorOutput = {
            "hookSpecificOutput": {
                "hookEventName": "PreToolUse",
                "permissionDecision": "deny",
                "permissionDecisionReason": `危险命令被拦截: ${dangerousRule.message}\n命令: ${command}`
            }
        };
        console.log(JSON.stringify(errorOutput));
        process.exit(0);
    }

    // 命令安全，允许执行
    process.exit(0);
}

// 运行主函数
main();
