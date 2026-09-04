<?php

namespace Modules\Demo5\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 文章评价队列服务
 *
 * 用于派发文章评价任务到RabbitMQ队列，由Rust消费者处理
 *
 * @package Modules\Demo5\Services
 */
class ArticleRatingQueueService
{
    /**
     * RabbitMQ连接配置
     *
     * @var array
     */
    protected array $config = [
        'host' => '192.168.4.107',  // RabbitMQ服务器地址
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest',
        'vhost' => '/',
        'queue' => 'article_rating',
    ];

    /**
     * 派发文章评价任务到 RabbitMQ
     *
     * @param string $title 文章标题
     * @param string $content 文章内容
     * @param string $author 作者名
     * @param int $articleId 文章ID（可选）
     * @return string 任务ID
     * @throws \Exception
     */
    public function dispatchRatingTask(string $title, string $content, string $author, int $articleId = null): string
    {
        // 生成任务ID
        $taskId = Str::uuid()->toString();

        // 构造任务数据（符合Rust消费者期望的格式）
        $taskData = [
            'task_type' => 'article_rating',
            'task_id' => $taskId,
            'data' => [
                'title' => $title,
                'content' => $content,
                'author' => $author,
                'article_id' => $articleId,
            ],
            'created_at' => time(),
            'timeout' => 300,  // 5分钟超时
            'retry_count' => 0,
        ];

        // 连接RabbitMQ
        $connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost']
        );

        $channel = $connection->channel();

        // 声明队列（持久化，带死信队列参数）
        $channel->queue_declare(
            $this->config['queue'],
            false,  // passive
            true,   // durable（持久化）
            false,  // exclusive
            false,  // auto_delete
            false,  // nowait
            [
                'x-dead-letter-exchange' => ['S', 'dlx_article_rating'],
                'x-dead-letter-routing-key' => ['S', 'article_rating'],
            ]
        );

        // 创建消息（持久化）
        $message = new AMQPMessage(
            json_encode($taskData),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json',
            ]
        );

        // 发布消息
        $channel->basic_publish($message, '', $this->config['queue']);

        // 关闭连接
        $channel->close();
        $connection->close();

        // 记录日志
        Log::info("文章评价任务已派发", [
            'task_id' => $taskId,
            'queue' => $this->config['queue'],
            'article_id' => $articleId,
            'title' => $title,
        ]);

        return $taskId;
    }
}
