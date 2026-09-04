<?php

namespace Modules\ABase\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 请求日志模型
 *
 * @property int $id 主键ID
 * @property string $unid 请求唯一ID
 * @property string $request_unid 请求UNID
 * @property string $run_unid 运行UNID
 * @property string $path 请求路径
 * @property string $method 请求方法
 * @property string $router 路由
 * @property string $module 模块
 * @property string $headers 请求头JSON
 * @property string $query Query参数JSON
 * @property string $post POST数据JSON
 * @property string $protobuf_json Protobuf JSON数据
 * @property string $files 上传文件信息JSON
 * @property string $ipaddress IP地址
 * @property string $host 主机
 * @property string $user_agent User Agent
 * @property int $user_id 用户ID
 * @property string $token Token
 * @property string $response_status 响应状态码
 * @property string $response_type 响应Content-Type
 * @property int $response_size 响应大小(字节)
 * @property string $response 响应内容
 * @property bool $response_truncated 响应是否截断
 * @property string $error 错误信息
 * @property int $run_ms 运行毫秒数
 * @property int $sql_num SQL查询次数
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SysRequestLog extends Model
{
    /**
     * 使用 dblog 数据库连接
     */
    protected $connection = 'dblog';

    protected $table = 'sys_request_logs';

    protected $fillable = [
        'unid',
        'request_unid',
        'run_unid',
        'path',
        'method',
        'router',
        'module',
        'headers',
        'query',
        'post',
        'protobuf_json',
        'files',
        'ipaddress',
        'host',
        'user_agent',
        'user_id',
        'token',
        'response_status',
        'response_type',
        'response_size',
        'response',
        'response_truncated',
        'error',
        'run_ms',
        'sql_num',
    ];

    protected $casts = [
        'response_truncated' => 'boolean',
        'response_size' => 'integer',
        'user_id' => 'integer',
        'run_ms' => 'integer',
        'sql_num' => 'integer',
    ];
}