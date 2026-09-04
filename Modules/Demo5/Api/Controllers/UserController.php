<?php

namespace Modules\Demo5\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Demo5\Models\Demo5User;
use Modules\Demo5\Api\Resources\UserResource;
use Modules\Demo5\Api\Resources\UserCollection;

/**
 * API 用户控制器
 *
 * 提供 RESTful API 接口
 */
class UserController extends Controller
{
    /**
     * 用户列表
     *
     * @return UserCollection
     */
    public function index()
    {
        $users = Demo5User::with('posts')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return new UserCollection($users);
    }

    /**
     * 用户详情
     *
     * @param int $id
     * @return UserResource
     */
    public function show($id)
    {
        $user = Demo5User::with('posts')->findOrFail($id);

        return new UserResource($user);
    }

    /**
     * 创建用户
     *
     * @param Request $request
     * @return UserResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:demo5_users,email',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:500',
            'status' => 'sometimes|string|in:active,inactive,banned',
        ]);

        $user = Demo5User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'avatar' => $validated['avatar'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return new UserResource($user);
    }

    /**
     * 更新用户
     *
     * @param Request $request
     * @param int $id
     * @return UserResource
     */
    public function update(Request $request, $id)
    {
        $user = Demo5User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:demo5_users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:500',
            'status' => 'sometimes|string|in:active,inactive,banned',
        ]);

        $user->update($validated);

        return new UserResource($user);
    }

    /**
     * 删除用户
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Demo5User::findOrFail($id);
        $user->delete();

        return response()->noContent();
    }
}