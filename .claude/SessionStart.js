// # claude SessionStart 钩子
// #
// 输入格式如下
// {
//   "session_id": "abc123",
//   "transcript_path": "~/.claude/projects/.../00893aaf-19fa-41d2-8238-13269b9b3ca0.jsonl",
//   "permission_mode": "default",
//   "hook_event_name": "SessionStart",
//   "source": "startup"
// }
// 输出格式如下

// {
//   "hookSpecificOutput": {
//     "hookEventName": "SessionStart",
//     "additionalContext": "My additional context here"
//   }
// }
//  功能: 在会话开始时，添加额外的 上下文信息， 当前时间信息

/**
 * SessionStart钩子实现
 * 在Claude会话开始时添加额外的上下文信息
 */
function main(input) {
    try {
        // 解析输入数据
        let sessionData;
        if (!input || input.trim() === '') {
            // 如果没有输入或输入为空，使用默认值
            sessionData = {};
        } else if (typeof input === 'string') {
            try {
                sessionData = JSON.parse(input);
            } catch (e) {
                // 如果JSON解析失败，使用默认值
                sessionData = {};
            }
        } else {
            sessionData = input;
        }

        // 获取当前时间信息
        const now = new Date();
        const timeInfo = {
            timestamp: now.toISOString(),
            localTime: now.toLocaleString('zh-CN', {
                timeZone: 'Asia/Shanghai',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                weekday: 'long'
            }),
            unixTimestamp: Math.floor(now.getTime() / 1000),
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        };

        // 构建额外的上下文信息 - 只包含有效信息
        const additionalContext = {
            sessionInfo: {},
            environment: {},
            message: `会话开始于 ${timeInfo.localTime}`
        };

        // 动态添加sessionInfo字段，只包含有效值
        if (sessionData.session_id && sessionData.session_id.trim() !== '') {
            additionalContext.sessionInfo.sessionId = sessionData.session_id;
            additionalContext.message += `，Session ID: ${sessionData.session_id}`;
        }

        if (sessionData.permission_mode && sessionData.permission_mode.trim() !== '') {
            additionalContext.sessionInfo.permissionMode = sessionData.permission_mode;
        }

        if (sessionData.source && sessionData.source.trim() !== '' && sessionData.source !== 'unknown') {
            additionalContext.sessionInfo.source = sessionData.source;
        }

        // 始终包含startTime
        additionalContext.sessionInfo.startTime = timeInfo;

        // 动态添加environment字段
        if (process.platform && process.platform.trim() !== '') {
            additionalContext.environment.platform = process.platform;
        }

        if (process.cwd && typeof process.cwd === 'function') {
            const cwd = process.cwd();
            if (cwd && cwd.trim() !== '') {
                additionalContext.environment.workingDirectory = cwd;
            }
        }

        // 清理空的sessionInfo和environment对象
        if (Object.keys(additionalContext.sessionInfo).length === 0) {
            delete additionalContext.sessionInfo;
        }
        if (Object.keys(additionalContext.environment).length === 0) {
            delete additionalContext.environment;
        }

        // 获取 hookEventName，优先使用传入的参数，否则使用默认值
        const hookEventName = sessionData.hook_event_name || "SessionStart";

        // 构建输出结果 - 动态包含字段
        const outputData = {
            hookSpecificOutput: {
                hookEventName: hookEventName,
                additionalContext: JSON.stringify(additionalContext, null, 2),
                timestamp: timeInfo.timestamp
            }
        };

        // 只有当sessionInfo存在且有内容时才添加到输出
        if (additionalContext.sessionInfo && Object.keys(additionalContext.sessionInfo).length > 0) {
            outputData.hookSpecificOutput.sessionInfo = additionalContext.sessionInfo;
        }

        // 输出结果到stdout
        console.log(JSON.stringify(outputData));
        return 0;

    } catch (error) {
        // 获取 hookEventName，优先使用传入的参数，否则使用默认值
        const hookEventName = sessionData?.hook_event_name || "SessionStart";

        // 错误处理
        const errorOutput = {
            hookSpecificOutput: {
                hookEventName: hookEventName,
                error: error.message,
                additionalContext: `会话开始时发生错误: ${error.message}`
            }
        };

        console.error(JSON.stringify(errorOutput));
        return 1;
    }
}

// 如果是直接运行脚本，则执行main函数
if (require.main === module) {
    const input = process.argv.length > 2 ? process.argv[2] : '';
    process.exit(main(input));
}

module.exports = main;