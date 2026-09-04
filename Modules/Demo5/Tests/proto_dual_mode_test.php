<?php

/**
 * Proto API 双模式传输测试脚本
 *
 * 测试JSON模式和二进制模式两种传输方式
 */

require __DIR__ . '/../../../vendor/autoload.php';

use ApiProto\Common\ResponseEnvelope;
use Demo5ApiProto\Post\CreatePostRequest;
use Demo5ApiProto\Post\CreatePostResponse;
use Demo5ApiProto\Post\GetPostRequest;
use Demo5ApiProto\Post\GetPostResponse;
use GuzzleHttp\Client;

echo "=== Proto API 双模式传输测试 ===\n\n";

// 配置API基础URL（使用artisan serve启动的端口）
$baseUrl = 'http://127.0.0.1:8000/api';

$jsonClient = new Client([
    'base_uri' => $baseUrl,
    'timeout' => 5,
]);

$binaryClient = new Client([
    'base_uri' => $baseUrl,
    'timeout' => 5,
]);

try {
    $requestData = [
        'title' => 'JSON模式测试文章',
        'content' => '这是JSON模式创建的内容',
        'status' => 'draft',
        'user_id' => 1,
        'published_at' => 0,
    ];

    $response = $jsonClient->post('demo5-proto/posts/create', [
        'json' => $requestData,
        'headers' => ['Content-Type' => 'application/json'],
    ]);

    $body = json_decode($response->getBody(), true);

    if ($response->getStatusCode() === 201 &&
        $body['code'] === 0 &&
        $body['data']['title'] === $requestData['title']) {
        echo "✓ 通过\n";
        echo "响应格式: JSON\n";
        echo "文章ID: {$body['data']['id']}\n";
        $testResults['json_create'] = true;
        $createdPostId = $body['data']['id'];
    } else {
        echo "✗ 失败: HTTP {$response->getStatusCode()}\n";
        $testResults['json_create'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['json_create'] = false;
}

echo "\n";

// ========== 测试2: 二进制模式创建文章 ==========

echo "测试 2: 二进制模式创建文章\n";
$binaryClient = new Client([
    'base_uri' => $baseUrl,
    'timeout' => 5,
]);

try {
    // 创建Proto Request对象
    $protoRequest = new CreatePostRequest();
    $protoRequest->setTitle('二进制模式测试文章');
    $protoRequest->setContent('这是二进制模式创建的内容');
    $protoRequest->setStatus('draft');
    $protoRequest->setUserId(1);
    $protoRequest->setPublishedAt(0);

    // 序列化为二进制
    $binaryData = $protoRequest->serializeToString();

    $response = $binaryClient->post('demo5-proto/posts/create', [
        'body' => $binaryData,
        'headers' => ['Content-Type' => 'application/x-protobuf'],
    ]);

    // 验证响应Content-Type
    $responseContentType = $response->getHeaderLine('Content-Type');

    if ($responseContentType === 'application/x-protobuf') {
        echo "✓ 响应Content-Type正确: application/x-protobuf\n";

        // 解析二进制响应
        $envelope = new ResponseEnvelope();
        $envelope->mergeFromString($response->getBody()->getContents());

        if ($envelope->getCode() === 0) {
            echo "✓ ResponseEnvelope解析成功\n";
            echo "Message: {$envelope->getMessage()}\n";

            // 解析data字段（CreatePostResponse）
            $postResponse = new CreatePostResponse();
            $postResponse->mergeFromString($envelope->getData());

            echo "文章ID: {$postResponse->getData()->getId()}\n";
            echo "标题: {$postResponse->getData()->getTitle()}\n";

            $testResults['binary_create'] = true;
            $binaryPostId = $postResponse->getData()->getId();
        } else {
            echo "✗ 失败: envelope code={$envelope->getCode()}\n";
            $testResults['binary_create'] = false;
        }
    } else {
        echo "✗ 失败: 响应Content-Type={$responseContentType}（期望application/x-protobuf）\n";
        $testResults['binary_create'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['binary_create'] = false;
}

echo "\n";

// ========== 测试3: JSON模式获取文章 ==========

if (isset($createdPostId)) {
    echo "测试 3: JSON模式获取文章详情\n";
    try {
        $response = $jsonClient->post('demo5-proto/posts/get', [
            'json' => ['id' => $createdPostId],
        ]);

        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 &&
            $body['code'] === 0 &&
            $body['data']['id'] === $createdPostId) {
            echo "✓ 通过\n";
            $testResults['json_get'] = true;
        } else {
            echo "✗ 失败\n";
            $testResults['json_get'] = false;
        }
    } catch (Exception $e) {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['json_get'] = false;
    }
}

echo "\n";

// ========== 测试4: 二进制模式获取文章 ==========

if (isset($binaryPostId)) {
    echo "测试 4: 二进制模式获取文章详情\n";
    try {
        // 创建Proto Request
        $protoRequest = new GetPostRequest();
        $protoRequest->setId($binaryPostId);

        $response = $binaryClient->post('demo5-proto/posts/get', [
            'body' => $protoRequest->serializeToString(),
            'headers' => ['Content-Type' => 'application/x-protobuf'],
        ]);

        $responseContentType = $response->getHeaderLine('Content-Type');

        if ($responseContentType === 'application/x-protobuf') {
            $envelope = new ResponseEnvelope();
            $envelope->mergeFromString($response->getBody()->getContents());

            if ($envelope->getCode() === 0) {
                $getResponse = new GetPostResponse();
                $getResponse->mergeFromString($envelope->getData());

                if ($getResponse->getData()->getId() === $binaryPostId) {
                    echo "✓ 通过\n";
                    echo "标题: {$getResponse->getData()->getTitle()}\n";
                    $testResults['binary_get'] = true;
                } else {
                    echo "✗ 失败: ID不匹配\n";
                    $testResults['binary_get'] = false;
                }
            } else {
                echo "✗ 失败: envelope code={$envelope->getCode()}\n";
                $testResults['binary_get'] = false;
            }
        } else {
            echo "✗ 失败: 响应格式不正确\n";
            $testResults['binary_get'] = false;
        }
    } catch (Exception $e) {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['binary_get'] = false;
    }
}

echo "\n";

// ========== 测试5: 混合模式验证 ==========

echo "测试 5: 混合模式验证（JSON请求→二进制响应）\n";
try {
    // 使用JSON请求，但指定接受二进制响应（通过Accept header）
    $response = $jsonClient->post('demo5-proto/posts/list', [
        'json' => ['page' => 1, 'page_size' => 10],
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/x-protobuf', // 这里仅做测试，实际应保持一致性
        ],
    ]);

    // 根据实际响应Content-Type判断
    $responseContentType = $response->getHeaderLine('Content-Type');
    echo "响应Content-Type: {$responseContentType}\n";

    // 当前实现会按请求Content-Type返回，所以应该是JSON
    if (str_contains($responseContentType, 'application/json')) {
        echo "✓ 请求-响应模式一致性保持（JSON请求→JSON响应）\n";
        $testResults['mixed_mode'] = true;
    } else {
        echo "⚠ 模式不一致（当前实现按请求模式返回）\n";
        $testResults['mixed_mode'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['mixed_mode'] = false;
}

echo "\n";

// ========== 测试6: 错误响应验证（JSON模式404） ==========

echo "测试 6: JSON模式错误响应（404）\n";
try {
    $response = $jsonClient->post('demo5-proto/posts/get', [
        'json' => ['id' => 99999],
    ]);

    $body = json_decode($response->getBody(), true);

    if ($response->getStatusCode() === 404 &&
        $body['code'] === 404 &&
        $body['message'] === '文章不存在') {
        echo "✓ 通过\n";
        $testResults['json_error_404'] = true;
    } else {
        echo "✗ 失败\n";
        $testResults['json_error_404'] = false;
    }
} catch (GuzzleHttp\Exception\ClientException $e) {
    if ($e->getCode() === 404) {
        echo "✓ 通过（404异常）\n";
        $testResults['json_error_404'] = true;
    } else {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['json_error_404'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['json_error_404'] = false;
}

echo "\n";

// ========== 测试7: 错误响应验证（二进制模式404） ==========

echo "测试 7: 二进制模式错误响应（404）\n";
try {
    $protoRequest = new GetPostRequest();
    $protoRequest->setId(99999);

    $response = $binaryClient->post('demo5-proto/posts/get', [
        'body' => $protoRequest->serializeToString(),
        'headers' => ['Content-Type' => 'application/x-protobuf'],
    ]);

    $responseContentType = $response->getHeaderLine('Content-Type');

    if ($responseContentType === 'application/x-protobuf') {
        $envelope = new ResponseEnvelope();
        $envelope->mergeFromString($response->getBody()->getContents());

        if ($envelope->getCode() === 404 && $envelope->getMessage() === '文章不存在') {
            echo "✓ 通过\n";
            $testResults['binary_error_404'] = true;
        } else {
            echo "✗ 失败: envelope code={$envelope->getCode()}, message={$envelope->getMessage()}\n";
            $testResults['binary_error_404'] = false;
        }
    } else {
        echo "✗ 失败: 响应格式不正确\n";
        $testResults['binary_error_404'] = false;
    }
} catch (GuzzleHttp\Exception\ClientException $e) {
    if ($e->getCode() === 404) {
        echo "✓ 通过（404异常）\n";
        $testResults['binary_error_404'] = true;
    } else {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['binary_error_404'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['binary_error_404'] = false;
}

echo "\n";

// ========== 测试总结 ==========

echo "=== 测试总结 ===\n";
$passed = count(array_filter($testResults));
$total = count($testResults);
$percentage = round(($passed / $total) * 100, 1);

echo "通过: {$passed}/{$total} ({$percentage}%)\n\n";

if ($passed === $total) {
    echo "✓ 所有测试通过！双模式传输功能正常\n";
    echo "\n支持的传输方式：\n";
    echo "- JSON模式: Content-Type: application/json\n";
    echo "- 二进制模式: Content-Type: application/x-protobuf\n";
} else {
    echo "✗ 部分测试失败\n";
    foreach ($testResults as $name => $result) {
        if (!$result) {
            echo "- {$name} 失败\n";
        }
    }
}