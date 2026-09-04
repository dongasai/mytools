# Demo5模块

## 模块概述
Demo5模块演示了(仅做演示)现代Laravel模块开发的最佳实践，采用分层架构和事件驱动设计。

## 核心功能
- 表前缀 `demo5_`

### 1. 用户管理系统
- 用户注册、登录、资料管理
- 用户状态管理（激活/禁用/封禁）
- 邮箱和手机验证
- 密码重置功能

### 2. 用户认证与安全
- 多因子认证支持
- 登录失败处理
- 会话管理
- 权限控制

### 3. 登录日志系统
- 详细的登录记录
- 登录行为分析
- 异常登录检测
- 登录统计报表

## 数据库设计

### 用户表 (demo5_users)
```sql
CREATE TABLE demo5_users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL COMMENT '用户名',
    email VARCHAR(255) UNIQUE NOT NULL COMMENT '邮箱',
    phone VARCHAR(20) NULL COMMENT '手机号',
    password VARCHAR(255) NOT NULL COMMENT '密码哈希',
    avatar VARCHAR(500) NULL COMMENT '头像路径',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'inactive' COMMENT '用户状态',
    email_verified_at TIMESTAMP NULL COMMENT '邮箱验证时间',
    phone_verified_at TIMESTAMP NULL COMMENT '手机验证时间',
    last_login_at TIMESTAMP NULL COMMENT '最后登录时间',
    last_login_ip VARCHAR(45) NULL COMMENT '最后登录IP',
    login_count INT DEFAULT 0 COMMENT '登录次数',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_last_login (last_login_at)
) COMMENT '用户表';
```

**字段说明：**
- `username`: 用户登录名，唯一索引
- `email`: 用户邮箱，唯一索引，可用于登录
- `phone`: 手机号，可选，可用于登录和验证
- `password`: 使用bcrypt哈希存储
- `status`: 用户状态，支持激活、未激活、封禁三种状态
- `email_verified_at/phone_verified_at`: 验证状态记录
- `last_login_at/last_login_ip`: 最后登录信息
- `login_count`: 累计登录次数统计

### 用户登录日志表 (demo5_user_login_logs)
```sql
CREATE TABLE demo5_user_login_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL COMMENT '用户ID',
    login_type ENUM('web', 'api', 'mobile') DEFAULT 'web' COMMENT '登录类型',
    ip_address VARCHAR(45) NOT NULL COMMENT '登录IP地址',
    user_agent TEXT NULL COMMENT '用户代理信息',
    location VARCHAR(255) NULL COMMENT '登录地点（基于IP解析）',
    device_info JSON NULL COMMENT '设备信息',
    is_successful BOOLEAN DEFAULT TRUE COMMENT '是否登录成功',
    failure_reason VARCHAR(255) NULL COMMENT '登录失败原因',
    session_id VARCHAR(255) NULL COMMENT '会话ID',
    logout_at TIMESTAMP NULL COMMENT '退出登录时间',
    duration_seconds INT NULL COMMENT '登录时长（秒）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_created_at (created_at),
    INDEX idx_login_type (login_type),
    INDEX idx_successful (is_successful),
    FOREIGN KEY (user_id) REFERENCES demo5_users(id) ON DELETE CASCADE
) COMMENT '用户登录日志表';
```

**字段说明：**
- `login_type`: 登录方式（Web后台、API接口、移动端）
- `ip_address`: 客户端IP地址，支持IPv6
- `user_agent`: 浏览器/客户端信息
- `location`: 基于IP解析的地理位置
- `device_info`: JSON格式存储设备详细信息
- `is_successful`: 登录是否成功
- `failure_reason`: 失败原因（密码错误、账户禁用等）
- `session_id`: 会话标识，用于追踪会话
- `logout_at`: 主动退出登录的时间
- `duration_seconds`: 本次登录持续时间

### 文章表 (demo5_posts)
```sql
CREATE TABLE demo5_posts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL COMMENT '文章标题',
    content TEXT NOT NULL COMMENT '文章内容',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft' COMMENT '文章状态',
    user_id BIGINT NOT NULL COMMENT '作者ID',
    published_at TIMESTAMP NULL COMMENT '发布时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_published_at (published_at),
    FOREIGN KEY (user_id) REFERENCES demo5_users(id) ON DELETE CASCADE
) COMMENT '文章表';
```

**字段说明：**
- `title`: 文章标题，255字符限制
- `content`: 文章内容，支持长文本
- `status`: 文章状态（草稿、已发布、已归档）
- `user_id`: 作者ID，关联用户表
- `published_at`: 发布时间，定时发布功能

### 评论表 (demo5_comments)
```sql
CREATE TABLE demo5_comments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    content TEXT NOT NULL COMMENT '评论内容',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' COMMENT '评论状态',
    post_id BIGINT NOT NULL COMMENT '文章ID',
    user_id BIGINT NOT NULL COMMENT '评论者ID',
    parent_id BIGINT NULL COMMENT '父评论ID',
    ip_address VARCHAR(45) NULL COMMENT 'IP地址',
    user_agent TEXT NULL COMMENT '用户代理',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (post_id) REFERENCES demo5_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES demo5_users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES demo5_comments(id) ON DELETE CASCADE
) COMMENT '评论表';
```

**字段说明：**
- `content`: 评论内容
- `status`: 评论状态（待审核、已通过、已拒绝）
- `post_id`: 所属文章ID
- `user_id`: 评论者ID
- `parent_id`: 父评论ID，支持无限层级回复
- `ip_address`: 评论者IP地址，用于安全分析
- `user_agent`: 评论者浏览器信息

## 技术特性

### 1. 架构设计
- **Services (服务层)**: 协调不同组件，处理业务流程
- **Logics (逻辑层)**: 纯函数计算，无状态，高性能
- **Admin/Repositories (Dcat Admin 数据访问层)**: 后台数据访问组件，非核心架构
- **事件驱动架构**: 使用事件系统降低组件耦合

### 2. 安全设计
- 密码使用bcrypt加密存储
- 登录失败次数限制
- 异常登录检测和报警
- 会话管理和安全控制
- 详细的操作审计日志

### 3. 性能优化
- 数据库索引优化
- 查询优化和缓存策略
- 队列处理耗时操作
- 分页查询优化

### 4. 用户体验
- 响应式设计
- 友好的错误提示
- 多语言支持准备
- 无障碍访问支持

## 开发规范

### 1. 代码规范
- 遵循PSR-12编码标准
- 使用PHPDoc注释
- 严格的类型声明
- 单元测试覆盖

### 2. 数据库规范
- 使用迁移文件管理表结构
- 工厂模式生成测试数据
- 种子文件初始化基础数据
- 外键约束和索引优化

### 3. 安全规范
- 输入数据验证和过滤
- SQL注入防护
- XSS攻击防护
- CSRF保护机制

### 不实现的功能(边界)
* 复杂的组织架构管理
* 第三方SSO集成（企业版功能）
* 高级审计功能
* 实时聊天系统
