#!/bin/bash

# 请求日志配置检查测试脚本

echo "========== 请求日志配置检查测试 =========="
echo ""

# 测试服务类
echo "1. 测试服务类..."
php artisan tinker --execute="
\$summary = \Modules\ABase\Services\RequestLogConfigCheckService::getSummary();
print_r(\$summary);
"

echo ""
echo "2. 测试配置信息..."
php artisan tinker --execute="
\$config = \Modules\ABase\Services\RequestLogConfigCheckService::getConfigInfo();
print_r(\$config);
"

echo ""
echo "3. 测试数据库连接..."
php artisan tinker --execute="
try {
    DB::connection('dblog')->getPdo();
    echo '✅ dblog 连接成功' . PHP_EOL;
} catch (\Exception \$e) {
    echo '❌ dblog 连接失败: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "4. 检查数据表..."
php artisan tinker --execute="
try {
    \$exists = \Illuminate\Support\Facades\Schema::connection('dblog')->hasTable('sys_request_logs');
    if (\$exists) {
        \$count = DB::connection('dblog')->table('sys_request_logs')->count();
        echo '✅ sys_request_logs 表存在，记录数: ' . \$count . PHP_EOL;
    } else {
        echo '❌ sys_request_logs 表不存在' . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo '❌ 检查失败: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "========== 测试完成 =========="