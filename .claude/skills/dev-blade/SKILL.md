---
name: dev-blade
description: 进行blade视图开发,必须启用
---
# blade视图开发

- 文件组织
    - 根目录:模块/resources/views
    - 页面文件目录:模块/resources/views/pages
    - 页面文件按照路由的结构进行组织:如路由 /payweb/2fa/setup 则创建 模块/resources/views/pages/payweb/2fa/setup.blade.php
    - 组件文件:模块/resources/views/components
- 不能使用cdn文件,只能使用本地文件
