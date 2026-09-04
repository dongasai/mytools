<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      title="Demo Admin API",
 *      version="1.0.0",
 *      description="基于 Laravel 12 + Dcat Admin 的演示项目 API 文档"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo Admin API Server"
 * )
 */
class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/test",
     *      tags={"Test"},
     *      summary="测试接口",
     *      description="返回一个简单的测试响应",
     *
     *      @OA\Response(
     *          response=200,
     *          description="成功响应",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Hello from Swagger API!"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="integer",
     *                  example=200
     *              )
     *          )
     *      )
     * )
     */
    public function test()
    {
        return response()->json([
            'message' => 'Hello from Swagger API!',
            'status' => 200,
        ]);
    }
}
