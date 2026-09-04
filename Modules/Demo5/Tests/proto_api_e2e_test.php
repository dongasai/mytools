<?php

/**
 * Proto API 独立测试脚本
 * 绕过Laravel ServiceProvider问题，直接测试API端点
 */

require __DIR__ . '/../../../vendor/autoload.php';

use GuzzleHttp\Client;

echo "=== Proto API e2e 测试 ===\n\n";

// 配置API基础URL
$baseUrl = 'http://192.168.4.107:32214/api/';
$client = new Client([
    'base_uri' => $baseUrl,
    'timeout' => 5,
    'headers' => ['Content-Type' => 'application/json'],
]);

$testResults = [];

// ========== 测试1: 文章列表（空数据） ==========

echo "测试 1: 文章列表（空数据）\n";
try {
    $response = $client->post('proto/demo5/posts/list', [
        'json' => ['page' => 1, 'page_size' => 20],
    ]);

    $body = json_decode($response->getBody(), true);
    if ($response->getStatusCode() === 200 &&
        $body['code'] === 0 &&
        isset($body['pagination'])) {
        echo "✓ 通过\n";
        $testResults['list_empty'] = true;
    } else {
        echo "✗ 失败: HTTP {$response->getStatusCode()}, code {$body['code']}\n";
        $testResults['list_empty'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['list_empty'] = false;
}

echo "\n";

// ========== 测试2: 创建文章 ==========

echo "测试 2: 创建文章\n";
$createData = [
    'title' => 'Proto API 测试文章',
    'content' => '这是通过Proto API创建的测试文章内容',
    'status' => 'draft',
    'user_id' => 1,
    'published_at' => 0,
];

try {
    $response = $client->post('proto/demo5/posts/create', ['json' => $createData]);
    $body = json_decode($response->getBody(), true);

    if ($response->getStatusCode() === 201 &&
        $body['code'] === 0 &&
        $body['data']['title'] === $createData['title']) {
        echo "✓ 通过\n";
        echo "文章ID: {$body['data']['id']}\n";
        $testResults['create'] = true;
        $createdPostId = $body['data']['id'];
    } else {
        echo "✗ 失败\n";
        $testResults['create'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['create'] = false;
}

echo "\n";

// ========== 测试3: 获取文章详情 ==========

if (isset($createdPostId)) {
    echo "测试 3: 获取文章详情\n";
    try {
        $response = $client->post('proto/demo5/posts/get', ['json' => ['id' => $createdPostId]]);
        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 &&
            $body['code'] === 0 &&
            $body['data']['id'] === $createdPostId &&
            is_int($body['data']['created_at'])) {
            echo "✓ 通过\n";
            echo "时间戳验证: created_at={$body['data']['created_at']} (整数)\n";
            $testResults['get'] = true;
        } else {
            echo "✗ 失败\n";
            $testResults['get'] = false;
        }
    } catch (Exception $e) {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['get'] = false;
    }
}

echo "\n";

// ========== 测试4: 更新文章 ==========

if (isset($createdPostId)) {
    echo "测试 4: 更新文章\n";
    $updateData = [
        'id' => $createdPostId,
        'title' => 'Proto API 更新后的标题',
        'status' => 'published',
    ];

    try {
        $response = $client->post('proto/demo5/posts/update', ['json' => $updateData]);
        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 &&
            $body['code'] === 0 &&
            $body['data']['title'] === $updateData['title'] &&
            $body['data']['status'] === 'published') {
            echo "✓ 通过\n";
            $testResults['update'] = true;
        } else {
            echo "✗ 失败\n";
            $testResults['update'] = false;
        }
    } catch (Exception $e) {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['update'] = false;
    }
}

echo "\n";

// ========== 测试5: 文章列表（有数据） ==========

echo "测试 5: 文章列表（有数据）\n";
try {
    $response = $client->post('proto/demo5/posts/list', [
        'json' => ['page' => 1, 'page_size' => 10],
    ]);

    $body = json_decode($response->getBody(), true);
    if ($response->getStatusCode() === 200 &&
        $body['code'] === 0 &&
        count($body['data']) > 0) {
        echo "✓ 通过\n";
        echo "数据条数: {$body['pagination']['total']}\n";
        $testResults['list_with_data'] = true;
    } else {
        echo "✗ 失败\n";
        $testResults['list_with_data'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['list_with_data'] = false;
}

echo "\n";

// ========== 测试6: 删除文章 ==========

if (isset($createdPostId)) {
    echo "测试 6: 删除文章\n";
    try {
        $response = $client->post('proto/demo5/posts/delete', ['json' => ['id' => $createdPostId]]);
        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 &&
            $body['code'] === 0 &&
            $body['message'] === '文章删除成功') {
            echo "✓ 通过\n";
            $testResults['delete'] = true;
        } else {
            echo "✗ 失败\n";
            $testResults['delete'] = false;
        }
    } catch (Exception $e) {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['delete'] = false;
    }
}

echo "\n";

// ========== 测试7: 获取不存在文章（404） ==========

echo "测试 7: 获取不存在的文章（404）\n";
try {
    $response = $client->post('proto/demo5/posts/get', ['json' => ['id' => 99999]]);
    $body = json_decode($response->getBody(), true);

    if ($response->getStatusCode() === 404 &&
        $body['code'] === 404 &&
        $body['message'] === '文章不存在') {
        echo "✓ 通过\n";
        $testResults['get_404'] = true;
    } else {
        echo "✗ 失败: HTTP {$response->getStatusCode()}\n";
        $testResults['get_404'] = false;
    }
} catch (GuzzleHttp\Exception\ClientException $e) {
    if ($e->getCode() === 404) {
        echo "✓ 通过（404异常）\n";
        $testResults['get_404'] = true;
    } else {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['get_404'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['get_404'] = false;
}

echo "\n";

// ========== 测试8: Content-Type验证（415） ==========

echo "测试 8: Content-Type验证（非application/json）\n";
try {
    $badClient = new Client([
        'base_uri' => $baseUrl,
        'headers' => ['Content-Type' => 'text/plain'],
    ]);

    $response = $badClient->post('proto/demo5/posts/list', ['json' => ['page' => 1]]);
    echo "✗ 失败: 应返回415但返回{$response->getStatusCode()}\n";
    $testResults['content_type'] = false;
} catch (GuzzleHttp\Exception\ClientException $e) {
    if ($e->getCode() === 415) {
        echo "✓ 通过（415异常）\n";
        $testResults['content_type'] = true;
    } else {
        echo "✗ 失败: {$e->getMessage()}\n";
        $testResults['content_type'] = false;
    }
} catch (Exception $e) {
    echo "✗ 失败: {$e->getMessage()}\n";
    $testResults['content_type'] = false;
}

echo "\n";

// ========== 测试总结 ==========

echo "=== 测试总结 ===\n";
$passed = count(array_filter($testResults));
$total = count($testResults);
$percentage = round(($passed / $total) * 100, 1);

echo "通过: {$passed}/{$total} ({ $percentage}%)\n";

if ($passed === $total) {
    echo "\n✓ 所有测试通过！Proto API 功能正常\n";
} else {
    echo "\n✗ 部分测试失败\n";
    foreach ($testResults as $name => $result) {
        if (!$result) {
            echo "- {$name} 失败\n";
        }
    }
}