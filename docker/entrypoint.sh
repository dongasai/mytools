#!/bin/bash
set -e

# 容器启动脚本，仅用于 Dockerfile（生产环境）

# 输出版本信息
cd /var/www/html
su php -c "php artisan version"

# 启动 supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
