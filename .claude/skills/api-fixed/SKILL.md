---
name: api-fixed
description: 用于修复api问题,修复bug,修复审阅报告中的问题
---

修复问题
1. 第一步是核对问题,使用subagent 核对问题真实性
2. 修复问题
- 制定修复方案
- 审阅修复方案
- 修复
- review修复结果
- 复杂问题使用 taskflow-auto skill
- 涉及proto的执行 composer proto进行编译
3. 验证，对api请求进行复现，重放

### Api调试
```bash
# 有request_id 可以重放请求
php artisan debug:replay-request {request_id}  # 重放Api请求
# 请求日志: storage/logs/requests/{request_id}.log

php artisan enterprise:token                   # 获取可用的测试token

# 使用 Token 测试 Proto API
curl -H "Authorization: Bearer {token}" http://请求地址/api/proto/{module}/{handler}/{action}
```
