<?php

namespace Modules\Application\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Models\ContinuousTimes;

/**
 * 数据库自查 Metric
 *
 * 用于检查数据库连接、表数量统计和 CRUD 操作测试
 *
 * @since 2026-08-23
 */
class DatabaseHealth extends Card
{
    /**
     * 测试记录类型标识
     */
    private const TEST_TYPE = 'db_health_test';

    /**
     * 初始化卡片
     *
     * @return void
     */
    protected function init(): void
    {
        parent::init();
        $this->title('数据库自查');
        $this->height(300);  // 卡片总高度 300px
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request): void
    {
        $health = $this->checkDatabaseHealth();
        $this->withContent($health);
    }

    /**
     * 检查数据库健康状态
     *
     * @return array<string, mixed> 返回健康检查结果数组，包含：
     *                              - connection: string 连接状态
     *                              - tables: int 表数量
     *                              - crud_test: string CRUD测试结果
     */
    protected function checkDatabaseHealth(): array
    {
        $result = [
            'connection' => '正常',
            'tables' => 0,
            'crud_test' => '未测试',
        ];

        // 1. 检查连接
        DB::connection()->getPdo();

        // 2. 获取表数量
        $tables = DB::select('SHOW TABLES');
        $result['tables'] = count($tables);

        // 3. CRUD 测试 - 使用 application_continuous_times 表
        DB::transaction(function () use (&$result) {
            // Create - 插入测试记录
            $record = ContinuousTimes::create([
                'user_id' => 0,
                'stype' => self::TEST_TYPE,
                'sid' => 0,
                'number' => 0,
                'last_time' => time(),
                'diff' => 0,
            ]);

            $testId = $record->id;

            // Read - 读取刚插入的记录
            $readRecord = ContinuousTimes::find($testId);
            if ($readRecord && $readRecord->stype === self::TEST_TYPE) {
                // Update - 更新记录
                $readRecord->number += 1;
                $readRecord->save();

                // Delete - 删除测试记录
                $readRecord->forceDelete();

                $result['crud_test'] = '通过';
            } else {
                // Read 失败，抛出异常让事务回滚
                throw new \RuntimeException('CRUD Read test failed');
            }
        });

        return $result;
    }

    /**
     * 设置卡片内容
     *
     * @param array<string, mixed> $content 内容数组
     * @return $this
     */
    public function withContent($content): self
    {
        $statusClass = $content['crud_test'] === '通过' ? 'text-success' : 'text-danger';
        $statusIcon = $content['crud_test'] === '通过' ? 'fa-check-circle' : 'fa-times-circle';

        // 转义输出，防止 XSS
        $connection = e($content['connection']);
        $tables = e((string) $content['tables']);
        $crudTest = e($content['crud_test']);

        $html = <<<HTML
<div style="padding: 0.5rem;">
    <div class="{$statusClass} text-center mb-2">
        <i class="fa {$statusIcon} mr-1"></i>
        <span class="font-weight-bold">数据库自查{$crudTest}</span>
    </div>
    <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-database mr-1"></i>连接状态</span>
            <span class="text-primary">{$connection}</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-table mr-1"></i>数据表</span>
            <span class="text-info">{$tables}</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-cogs mr-1"></i>CRUD测试</span>
            <span class="{$statusClass}">{$crudTest}</span>
        </div>
    </div>
</div>
HTML;

        return $this->content($html);
    }
}