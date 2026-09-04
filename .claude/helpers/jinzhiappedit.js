#!/usr/bin/env node

/**
 * Claude Code Hook 工具 - 模块文件保护
 *
 * 功能：不允许修改和写入非Modules*的文件（白名单目录除外）
 */

import fs from 'fs';
import path from 'path';

/**
 * 保护规则配置
 * 所有规则集中配置，便于维护和调整
 */
const protectionRules = {
    // 允许修改的文件后缀
    allowedExtensions: [
        "php",
        // "js",
        // "css",
        // "json"
    ],

    // 允许的例外目录列表（白名单）
    allowedExceptions: [
        "laravel-e2e-test-workspace",
        "extensions/dlaravel"
    ],

    // 路径匹配器规则（按优先级顺序执行，匹配即停止）
    // type: "allow" - 允许修改 | "deny" - 禁止修改
    pathMatchers: [
        // === 特殊路径拦截规则（优先级最高）===
        {
            pattern: /^Modules\/ApiProto\/config\/pathlist.php$/,
            type: "deny",
            message: "pathlist.php 是自动生成的路由配置文件，禁止修改,不要试图修改会被覆盖。请使用 'composer proto' 命令重新生成。规则是固定的准确的,无bug的,你写的东西不符合请自查,要么path错误,要么request/response类名错误,use proto-dev skill"
        },
        {
            pattern: /^Modules\/ApiProto\/Protobuf\//,
            type: "deny",
            message: "Protobuf 目录下的文件是 proto 编译产出物，禁止修改,不要试图修改会被覆盖。请使用 'composer proto' 命令重新生成。"
        },
        {
            pattern: /^Modules\/ApiProto\/protos\//,
            type: "deny",
            message: "protos 目录下的 .proto 文件是归拢后用于构建的，不是原始 proto。请修改模块内原始 proto 文件。"
        },

        // === 模块文件允许规则（优先级中等）===
        {
            pattern: /(^|[/\\])Modules[^/\\]*[/\\]/,
            type: "allow",
            message: "模块文件允许修改"
        },

        // === 默认拒绝规则（优先级最低，兜底）===
        {
            pattern: /.*/,
            type: "deny",
            message: "模块化项目,不允许修改和写入非Modules*的文件(不是模块代码)"
        }
    ]
};

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
 * 统一路径匹配器
 * 按优先级顺序匹配路径规则，匹配即停止
 * @returns {object|null} 返回匹配的规则对象 {type: "allow"/"deny", message: "..."}，未匹配返回 null
 */
function matchPath(filePath) {
    if (!filePath) return null;

    const normalizedPath = path.normalize(filePath);

    // 遍历所有路径匹配器规则
    for (const matcher of protectionRules.pathMatchers) {
        // 支持相对路径匹配：提取相对路径部分
        // 例如：/data/project/.../Modules/ApiProto/config/pathlist.php -> Modules/ApiProto/config/pathlist.php
        const relativePath = normalizedPath.includes('Modules/')
            ? normalizedPath.substring(normalizedPath.indexOf('Modules/'))
            : normalizedPath;

        if (matcher.pattern.test(relativePath)) {
            return {
                type: matcher.type,
                message: matcher.message
            };
        }
    }

    return null;
}

/**
 * 检查文件路径是否在白名单目录中
 */
function isInAllowedException(filePath) {
    if (!filePath) return false;

    const normalizedPath = path.normalize(filePath);

    // 检查是否匹配任何白名单目录
    return protectionRules.allowedExceptions.some(exception => {
        const exceptionPattern = new RegExp(`(^|/)${exception}(/|$)`);
        return exceptionPattern.test(normalizedPath);
    });
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
    const filePath = toolInput.file_path || '';

    // 只处理Edit和Write操作
    if (!['Edit', 'Write'].includes(toolName)) {
        process.exit(0);
    }

    const fileExtension = path.extname(filePath).slice(1);
    const isValidExtension = protectionRules.allowedExtensions.includes(fileExtension);

    // 1. 检查文件后缀是否在允许的列表中
    if (!isValidExtension) {
        // 文件后缀不在允许列表，放行（不拦截）
        process.exit(0);
    }

    // 2. 检查是否在白名单目录中（白名单目录允许所有操作）
    if (isInAllowedException(filePath)) {
        process.exit(0);
    }

    // 3. 使用统一路径匹配器判断
    const matchResult = matchPath(filePath);

    if (matchResult && matchResult.type === "deny") {
        // 拒绝修改
        const reason = matchResult.message + (filePath ? `: ${filePath}` : '');
        const errorOutput = {
            "hookSpecificOutput": {
                "hookEventName": "PreToolUse",
                "permissionDecision": "deny",
                "permissionDecisionReason": reason
            }
        };
        console.log(JSON.stringify(errorOutput));
        process.exit(0);
    }

    // 4. 允许修改（allow 或未匹配到规则）
    // console.log(`✓ 文件路径验证通过: ${filePath}`);
    process.exit(0);
}

// 运行主函数
main();
