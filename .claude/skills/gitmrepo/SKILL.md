---
name: gitmrepo
description: gitmrepo管理,git加入模块。
---
gitmrepo代码管理
# 核心思路:
1. 不使用submodule,重点!不使用git submodule
2. 主仓库 和 模块仓库 同时管理代码
3. 模块仓库作为主仓库的子目录(一份代码被两个仓库管理,无直接关联)

## 注意事项
- `.gitmrepo` 配置文件是gitmrepo体系的配置文件
- 如有gitlink,要移除gitlink,立即提交,防止git混乱
- 如有submodule,要移除submodule
- 要明确submodule和mrepo不是一个东西,项目使用的mrepo而不是submodule
- 如遇到模块目录始终识别为submodule,则'先将 模块的.git备份到 .git.bak ,然后加入,提交,再改回来'
