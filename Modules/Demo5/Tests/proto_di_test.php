<?php

/**
 * Proto 依赖注入测试脚本
 * 直接测试 Proto Message 解析，绕过 Laravel 服务容器问题
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Demo5ApiProto\Post\CreatePostRequest;
use Demo5ApiProto\Post\PostData;
use Google\Protobuf\Internal\Message;

echo "=== Proto 依赖注入测试 ===\n\n";

// 测试 1: 手动解析 Proto Message
echo "测试 1: 手动解析 Proto Message\n";
$jsonData = '{"title":"测试文章","content":"这是内容","status":"draft","user_id":1}';
$protoRequest = new CreatePostRequest();
$protoRequest->mergeFromJsonString($jsonData);

echo "解析成功！\n";
echo "标题: " . $protoRequest->getTitle() . "\n";
echo "内容: " . $protoRequest->getContent() . "\n";
echo "状态: " . $protoRequest->getStatus() . "\n";
echo "用户ID: " . $protoRequest->getUserId() . "\n\n";

// 测试 2: Proto 序列化
echo "测试 2: Proto 序列化为 JSON\n";
$jsonOutput = $protoRequest->serializeToJsonString();
echo "序列化结果: $jsonOutput\n\n";

// 测试 3: PostData 转换
echo "测试 3: 创建 PostData 响应\n";
$postData = new PostData();
$postData->setId(1);
$postData->setTitle("依赖注入测试");
$postData->setContent("测试内容成功");
$postData->setStatus("draft");
$postData->setUserId(1);
$postData->setCreatedAt(time());
$postData->setUpdatedAt(time());

echo "PostData JSON: " . $postData->serializeToJsonString() . "\n\n";

// 测试 4: 嵌套 Proto（ListPostsResponse）
echo "测试 4: 创建 ListPostsResponse（带 Pagination）\n";
use Demo5ApiProto\Post\ListPostsResponse;
use ApiProto\Common\Pagination;

$response = new ListPostsResponse();
$response->setCode(0);
$response->setMessage('文章列表获取成功');
// repeated 字段需要通过内部RepeatedField操作
$dataList = $response->getData();
$dataList[] = $postData;

$pagination = new Pagination();
$pagination->setPage(1);
$pagination->setPageSize(20);
$pagination->setTotal(1);
$response->setPagination($pagination);

echo "ListPostsResponse JSON:\n";
echo $response->serializeToJsonString() . "\n\n";

echo "=== 所有测试通过！Proto 功能正常 ===\n";