<?php

namespace DLaravel\Model;

use DLaravel\Helper\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * 请求日志
 * field start
 *
 * @property int $id
 * @property string $router
 * @property string $query get参数
 * @property string $post 原始请求数据
 * @property string $protobuf_json Protobuf转换后的JSON数据
 * @property string $unid
 * @property string $path
 * @property string $error
 * @property string $headers
 * @property string $method 请求类型
 * @property string $request_unid
 * @property string $run_unid
 * @property string $response 返回消息
 * @property string $ipaddress ip地址
 * @property int $user_id 用户ID
 * @property string $host 主机
 * @property string $server
 * @property int $sale_date 日期
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $module 模块
 * @property string $token token
 * @property int $run_ms 运行毫秒数
 * @property int $sql_num sql语句数量
 *                        field end
 */
class RequestLog extends Model
{
    protected $table = 'sys_request_logs';

    public static function selectPath()
    {
        $data = Cache::cacheCall([__CLASS__, __FUNCTION__, 2], function () {
            $data = RequestLog::query()
                ->groupBy('path')
                ->distinct()
                ->whereIn('module', [
                    'oapi', 'app',
                ])
                ->pluck('path', 'path')
                ->toArray();

            return $data;
        }, [], 100);

        return $data;
    }
}
