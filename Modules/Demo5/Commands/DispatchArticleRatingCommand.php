<?php

namespace Modules\Demo5\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Demo5\Services\ArticleRatingQueueService;

/**
 * 派发文章评价任务到Rust队列
 *
 * 验证命令：延迟10秒派发任务
 */
class DispatchArticleRatingCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'demo5:article-rating';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '派发文章评价任务到Rust队列（验证双队列架构）';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('开始派发文章评价任务到Rust队列...');

        // 示例文章数据
        $title = '测试文章：双队列架构验证';
        $content = '这是一篇用于验证Rust队列消费者的测试文章内容。
本文将测试文章评价功能的完整流程，包括：
1. Laravel派发任务到RabbitMQ队列
2. Rust消费者监听并处理任务
3. Rust查询文章数据并计算评分
4. Rust保存评价结果到数据库
5. Rust发送通知给作者

测试时间：' . now()->toDateTimeString();
        $author = '测试作者';
        $articleId = 999;  // 测试ID

        $this->info("文章标题: {$title}");
        $this->info("文章ID: {$articleId}");

        try {
            $service = new ArticleRatingQueueService();

            // 派发任务（立即派发，Rust消费者处理）
            $dispatchStartTime = microtime(true);
            $taskId = $service->dispatchRatingTask(
                $title,
                $content,
                $author,
                $articleId
            );
            $dispatchElapsed = round((microtime(true) - $dispatchStartTime) * 1000, 2);

            $this->info("✅ 任务派发成功！");
            $this->info("任务ID: {$taskId}");
            $this->info("队列名: article_rating");
            $this->info("派发耗时: {$dispatchElapsed}ms");
            $this->newLine();

            // ===== Sleep验证环节 =====
            $this->info("开始Sleep验证（等待Rust消费者处理）...");
            $this->newLine();

            // 1. 检查初始队列状态
            $this->info("[1] 检查队列初始状态:");
            $initialStatus = $this->checkQueueStatus();
            $this->info("  - 消息数: {$initialStatus['messages']}");
            $this->info("  - 消费者: {$initialStatus['consumers']}");

            // 2. Sleep等待处理（每秒检查数据库）
            $sleepSeconds = 10;
            $this->info("[2] Sleep最多 {$sleepSeconds}秒，每秒检查数据库...");
            $rating = null;
            $foundInSeconds = 0;

            for ($i = 1; $i <= $sleepSeconds; $i++) {
                sleep(1);

                // 每秒检查数据库
                $rating = \DB::table('article_ratings')
                    ->where('task_id', $taskId)
                    ->first();

                if ($rating) {
                    $foundInSeconds = $i;
                    $this->info("  - 第 {$i}秒：✅ 发现数据库记录！停止等待");
                    break;
                } else {
                    $this->info("  - 第 {$i}秒：未发现记录，继续等待...");
                }
            }
            $this->newLine();

            // 3. 检查处理后队列状态
            $this->info("[3] 检查队列处理后状态:");
            $finalStatus = $this->checkQueueStatus();
            $this->info("  - 消息数: {$finalStatus['messages']}");
            $this->info("  - 消费者: {$finalStatus['consumers']}");

            // 4. 验证数据库结果
            $this->newLine();
            $this->info("[4] 验证数据库结果:");

            // 查询数据库中的评价记录
            $queryStartTime = microtime(true);
            $rating = \DB::table('article_ratings')
                ->where('task_id', $taskId)
                ->first();
            $queryElapsed = round((microtime(true) - $queryStartTime) * 1000, 2);

            $this->info("数据库查询耗时: {$queryElapsed}ms");
            $this->newLine();

            // 从Rust日志提取处理耗时
            $rustLogFile = '/tmp/rust-consumer-final-test.log';
            $processingTimeFromLog = null;

            if (file_exists($rustLogFile)) {
                $rustLogContent = file_get_contents($rustLogFile);
                if ($rustLogContent) {
                    $processingTimeFromLog = $this->extractProcessingTimeFromLog($rustLogContent, $taskId);
                } else {
                    $this->warn("⚠️  日志文件读取失败: {$rustLogFile}");
                }
            } else {
                // 尝试其他可能的日志文件
                $alternativeLogs = [
                    '/tmp/rust-consumer.log',
                    '/tmp/rust-consumer-final.log',
                    '/tmp/rust-consumer-test.log',
                ];

                foreach ($alternativeLogs as $altLog) {
                    if (file_exists($altLog)) {
                        $rustLogContent = file_get_contents($altLog);
                        if ($rustLogContent) {
                            $processingTimeFromLog = $this->extractProcessingTimeFromLog($rustLogContent, $taskId);
                            if ($processingTimeFromLog) {
                                $rustLogFile = $altLog;
                                break;
                            }
                        }
                    }
                }
            }

            if ($rating) {
                $this->info("✅ 评价结果已保存到数据库");

                $this->info("  - ID: {$rating->id}");
                $this->info("  - 任务ID: {$rating->task_id}");
                $this->info("  - 文章ID: " . ($rating->article_id ?? 'null'));
                $this->info("  - 标题: {$rating->title}");
                $this->info("  - 作者: {$rating->author}");
                $this->info("  - 标题评分: {$rating->title_score}");
                $this->info("  - 内容评分: {$rating->content_score}");
                $this->info("  - 综合评分: {$rating->overall_score}");
                $this->info("  - 评级: {$rating->rating}");
                $this->info("  - 字数: {$rating->word_count}");
                $this->info("  - 字符数: {$rating->char_count}");
                $this->info("  - 处理时间: {$rating->processed_at}");

                // 显示发现耗时
                $this->info("  - 发现耗时: {$foundInSeconds}秒 ⭐（从派发到数据库查询发现）");

                if ($processingTimeFromLog) {
                    $this->info("  - Rust处理耗时: {$processingTimeFromLog}秒 ⭐（从Rust日志：接收→处理完成）");
                } else {
                    $this->warn("  - Rust处理耗时: 无法从日志读取（日志文件可能不存在）");
                }
                $this->newLine();

                // 验证数据是否符合要求
                $this->newLine();
                $this->info("[5] 数据验证:");
                $issues = [];

                if ($rating->overall_score < 0 || $rating->overall_score > 100) {
                    $issues[] = "综合评分范围异常（应为0-100）";
                }
                if ($rating->word_count <= 0) {
                    $issues[] = "字数统计异常（应大于0）";
                }
                if ($rating->char_count <= 0) {
                    $issues[] = "字符数统计异常（应大于0）";
                }
                if (!in_array($rating->rating, ['优秀', '良好', '一般', '较差'])) {
                    $issues[] = "评级值异常（应为：优秀/良好/一般/较差）";
                }

                if (empty($issues)) {
                    $this->info("✅ 所有数据验证通过");
                } else {
                    $this->warn("⚠️  数据验证发现问题：");
                    foreach ($issues as $issue) {
                        $this->warn("  - {$issue}");
                    }
                }
            } else {
                $this->warn("⚠️  数据库中未找到评价记录（task_id: {$taskId})");
                $this->warn("可能原因：");
                $this->warn("  - Rust消费者数据库配置错误");
                $this->warn("  - Rust消费者保存失败");
                $this->warn("  - 处理时间过长，查询过早");
            }

            $this->newLine();
            $totalElapsed = round((microtime(true) - $startTime) * 1000, 2);
            $this->info("[6] 耗时统计:");
            $this->info("  - 派发耗时: {$dispatchElapsed}ms（RabbitMQ消息发送）");
            if ($processingTimeFromLog) {
                $this->info("  - 处理耗时: {$processingTimeFromLog}秒 ⭐（从Rust日志，派发→处理完成）");
            }
            $this->info("  - 等待耗时: {$sleepSeconds}s（Sleep验证）");
            $this->info("  - 查询耗时: {$queryElapsed}ms（数据库查询验证）");
            $this->info("  - 总验证耗时: {$totalElapsed}ms");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ 任务派发失败！");
            $this->error("错误信息: " . $e->getMessage());

            $this->newLine();
            $this->warn("请检查：");
            $this->warn("1. RabbitMQ是否运行（192.168.4.107:5672）");
            $this->warn("2. PhpAmqpLib是否安装");
            $this->warn("3. RabbitMQ连接配置是否正确");

            return Command::FAILURE;
        }
    }

    /**
     * 从Rust日志提取处理耗时
     *
     * @param string $logContent 日志内容
     * @param string $taskId 任务ID（用于验证）
     * @return string|null 处理耗时（秒）
     */
    private function extractProcessingTimeFromLog(string $logContent, string $taskId): ?string
    {
        // 移除ANSI颜色代码
        $logContent = preg_replace('/\x1b\[[0-9;]*m/', '', $logContent);

        // 搜索包含任务ID的日志行
        $lines = explode("\n", $logContent);
        $taskReceived = false;

        foreach ($lines as $line) {
            // 检测任务接收（精确匹配任务ID）
            if (strpos($line, "收到任务 [{$taskId}]:") !== false) {
                $taskReceived = true;
                continue;
            }

            // 检测任务完成（在收到任务之后的行）
            if ($taskReceived && preg_match('/INFO\s+consumer::consumer:\s+任务处理成功\s+\[耗时:\s+([\d.]+)s\]/', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * 检查队列状态
     *
     * @return array
     */
    private function checkQueueStatus(): array
    {
        try {
            $url = 'http://192.168.4.107:15672/api/queues/%2F/article_rating';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, 'guest:guest');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            // curl_close在PHP 8.3中已不需要，curl资源会自动释放

            $data = json_decode($response, true);

            return [
                'messages' => $data['messages'] ?? 0,
                'messages_ready' => $data['messages_ready'] ?? 0,
                'consumers' => $data['consumers'] ?? 0,
            ];
        } catch (\Exception $e) {
            // 记录错误日志（队列状态检查失败不影响主流程）
            Log::warning("队列状态检查失败", ['error' => $e->getMessage()]);

            return [
                'messages' => -1,
                'messages_ready' => -1,
                'consumers' => -1,
            ];
        }
    }
}