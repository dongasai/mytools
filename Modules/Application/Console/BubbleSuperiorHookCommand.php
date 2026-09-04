<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\BubbleSuperiorHookDefinition;
use Modules\Application\Hooks\Parameters\BubbleSuperiorHookParameter;

/**
 * 冒泡上级Hook控制台命令
 *
 * 用于测试和调试冒泡上级Hook功能
 */
class BubbleSuperiorHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:bubble-superior
                            {--source=admin : 源模块 (admin, shop, workflow)}
                            {--user-id= : 用户ID (必需)}
                            {--org-type= : 组织类型}
                            {--target-user-type= : 目标用户类型}
                            {--max-bubble-level= : 最大冒泡层级}
                            {--stop-at-level= : 停止层级}
                            {--include-same-type= : 是否包含相同用户类型 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用冒泡上级Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('🫧 执行冒泡上级Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $userId = $this->option('user-id');
        $orgType = $this->option('org-type');
        $targetUserType = $this->option('target-user-type');
        $maxBubbleLevel = $this->option('max-bubble-level') ? (int) $this->option('max-bubble-level') : null;
        $stopAtLevel = $this->option('stop-at-level') ? (int) $this->option('stop-at-level') : null;
        $includeSameType = $this->option('include-same-type') === 'true';
        $debug = $this->option('debug');

        if (!$userId) {
            $this->error('❌ 用户ID是必需的参数，请使用 --user-id= 选项指定');
            return;
        }

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = BubbleSuperiorHookParameter::create(
            source: $source,
            userId: (int) $userId,
            orgType: $orgType,
            targetUserType: $targetUserType,
            maxBubbleLevel: $maxBubbleLevel,
            stopAtLevel: $stopAtLevel,
            includeSameType: $includeSameType
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['用户ID', $parameter->user_id],
            ['组织类型', $parameter->org_type ?? 'null'],
            ['目标用户类型', $parameter->target_user_type ?? 'null'],
            ['最大冒泡层级', $parameter->max_bubble_level ?? 'null'],
            ['停止层级', $parameter->stop_at_level ?? 'null'],
            ['包含相同用户类型', $parameter->include_same_type ? 'true' : 'false'],
        ]);

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(BubbleSuperiorHookDefinition::class, $parameter);

        // 输出结果
        $this->outputResult($result, $debug);
    }

    /**
     * 输出Hook结果
     */
    private function outputResult($result, bool $debug): void
    {
        $this->newLine();
        $this->info('📊 Hook执行结果:');

        // 基本信息
        $this->table(['属性', '值'], [
            ['执行状态', $result->isSuccess() ? '✅ 成功' : '❌ 失败'],
            ['处理消息', $result->getMessage()],
            '冒泡上级数量' => $result->getCount(),
            ['是否为空', $result->isEmpty() ? '是' : '否'],
        ]);

        // 错误信息
        if ($result->hasErrors()) {
            $this->newLine();
            $this->error('⚠️ 错误信息:');
            foreach ($result->getErrors() as $error) {
                $this->line("  • $error");
            }
        }

        // 冒泡上级列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('🫧 冒泡上级列表:');

            $bubbleSuperiors = $result->getBubbleSuperiors();
            $tableData = [];

            foreach ($bubbleSuperiors as $superior) {
                $tableData[] = [
                    'ID' => $superior['id'],
                    '姓名' => $superior['name'],
                    '用户类型' => $superior['user_type'],
                    '冒泡层级' => $superior['bubble_level'],
                    '原始层级' => $superior['original_level'],
                    '匹配条件' => $superior['match_condition'],
                    '状态' => $superior['is_active'] ? '活跃' : '非活跃',
                ];
            }

            $this->table([
                'ID',
                '姓名',
                '用户类型',
                '冒泡层级',
                '原始层级',
                '匹配条件',
                '状态'
            ], $tableData);

            // 按冒泡层级分组
            $this->newLine();
            $this->info('📋 按冒泡层级分组:');

            $maxLevel = $result->getMaxBubbleLevel();
            $this->line("  📈 最大冒泡层级: $maxLevel");

            for ($level = 1; $level <= $maxLevel; $level++) {
                $levelSuperiors = $result->getSuperiorsByBubbleLevel($level);
                if (!empty($levelSuperiors)) {
                    $this->line("  🎯 层级 $level: " . count($levelSuperiors) . "个上级");
                    foreach ($levelSuperiors as $superior) {
                        $this->line("    • {$superior['name']} ({$superior['user_type']})");
                    }
                }
            }

            // 目标用户类型匹配
            if (!empty($result->getTargetUserType())) {
                $this->newLine();
                $this->info('🎯 目标用户类型匹配结果:');
                $targetMatches = $result->getSuperiorsByTargetType();
                if (!empty($targetMatches)) {
                    foreach ($targetMatches as $match) {
                        $this->line("  ✅ {$match['name']} - {$match['match_condition']}");
                    }
                } else {
                    $this->line("  ❌ 未找到匹配目标用户类型的上级");
                }
            }

            // 冒泡路径
            $this->newLine();
            $this->info('🛤️ 冒泡路径:');
            $bubblePath = $result->getBubblePath();
            if (!empty($bubblePath)) {
                foreach ($bubblePath as $index => $path) {
                    $this->line("  {$path['bubble_level']}. {$path['name']} ({$path['user_type']})");
                    if (!empty($path['match_reason'])) {
                        $this->line("     原因: {$path['match_reason']}");
                    }
                }
            }
        }

        // 元数据
        if (!empty($result->metadata)) {
            $this->newLine();
            $this->info('📋 元数据:');
            $this->line(json_encode($result->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // 调试信息
        if ($debug) {
            $this->newLine();
            $this->info('🐛 调试信息:');
            $debugInfo = Hooks::debug();

            if (!empty($debugInfo['execution_log'])) {
                $this->line('执行日志:');
                foreach ($debugInfo['execution_log'] as $log) {
                    $this->line("  • $log");
                }
            }

            if (!empty($debugInfo['handlers'])) {
                $this->line('处理器信息:');
                foreach ($debugInfo['handlers'] as $handler) {
                    $this->line("  • $handler");
                }
            }
        }
    }
}
