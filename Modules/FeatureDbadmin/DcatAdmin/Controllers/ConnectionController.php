<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\DatabaseService;

/**
 * 数据库连接管理控制器
 *
 * 数据库连接 CRUD 管理（JSON API）
 */
class ConnectionController extends AdminController
{
    /**
     * 连接列表 JSON 接口
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $connections = DatabaseService::getConnections(false);

        return $this->success_json([
            'data' => $connections,
            'total' => count($connections),
        ]);
    }

    /**
     * 创建连接
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:feature_dbadmin_connections,name',
            'driver' => 'required|in:mysql,pgsql,sqlite',
            'host' => 'required_if:driver,mysql,pgsql|string|max:100',
            'port' => 'nullable|integer|min:1|max:65535',
            'database' => 'required|string|max:100',
            'username' => 'required_if:driver,mysql,pgsql|string|max:100',
            'password' => 'nullable|string',
            'charset' => 'nullable|string|max:20',
            'collation' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error_json($validator->errors()->first(), 422);
        }

        $data = $validator->validated();
        $data['created_by'] = Admin::user()->id;
        $data['is_active'] = $data['is_active'] ?? true;

        // 创建连接
        $connection = Connection::create($data);

        return $this->success_json($connection->toArray(), '连接创建成功');
    }

    /**
     * 更新连接
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function modify(Request $request, int $id)
    {
        $connection = Connection::find($id);

        if (!$connection) {
            return $this->error_json('连接不存在', 404);
        }

        // 检查是否只是更新状态（只有 is_active 字段）
        $input = $request->all();
        $onlyUpdateStatus = count($input) === 1 && isset($input['is_active']);

        if ($onlyUpdateStatus) {
            // 只更新状态，不需要验证其他字段
            $connection->update(['is_active' => $input['is_active']]);

            return $this->success_json($connection->toArray(), '连接状态已更新');
        }

        // 完整更新，需要验证所有字段
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:feature_dbadmin_connections,name,' . $id,
            'driver' => 'required|in:mysql,pgsql,sqlite',
            'host' => 'required_if:driver,mysql,pgsql|string|max:100',
            'port' => 'nullable|integer|min:1|max:65535',
            'database' => 'required|string|max:100',
            'username' => 'required_if:driver,mysql,pgsql|string|max:100',
            'password' => 'nullable|string',
            'charset' => 'nullable|string|max:20',
            'collation' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error_json($validator->errors()->first(), 422);
        }

        $data = $validator->validated();

        // 更新连接
        $connection->update($data);

        return $this->success_json($connection->fresh()->toArray(), '连接更新成功');
    }

    /**
     * 删除连接
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(int $id)
    {
        $connection = Connection::find($id);

        if (!$connection) {
            return $this->error_json('连接不存在', 404);
        }

        $connection->delete();

        return $this->success_json(null, '连接删除成功');
    }

    /**
     * 测试连接
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(int $id)
    {
        $result = DatabaseService::testConnection($id);

        if ($result['success']) {
            return $this->success_json($result);
        } else {
            return $this->error_json($result['message'] ?? '连接失败');
        }
    }

    /**
     * 测试连接配置（未保存的配置）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver' => 'required|in:mysql,pgsql,sqlite',
            'host' => 'required_if:driver,mysql,pgsql|string|max:100',
            'port' => 'nullable|integer|min:1|max:65535',
            'database' => 'required|string|max:100',
            'username' => 'required_if:driver,mysql,pgsql|string|max:100',
            'password' => 'nullable|string',
            'charset' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->error_json($validator->errors()->first(), 422);
        }

        $config = $validator->validated();

        // 测试连接配置
        $result = DatabaseService::testConnectionConfig($config);

        if ($result['success']) {
            return $this->success_json($result);
        } else {
            return $this->error_json($result['message'] ?? '连接失败');
        }
    }
}