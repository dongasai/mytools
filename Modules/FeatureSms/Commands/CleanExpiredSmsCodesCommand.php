<?php

namespace Modules\FeatureSms\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureSms\Models\SmsCode;

/**
 * 清理过期短信验证码命令
 */
class CleanExpiredSmsCodesCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'feature-sms:clean-expired-codes {--hours=24 : 清理多少小时前的过期验证码}';

    /**
     * 命令描述
     */
    protected $description = '清理过期的短信验证码记录';

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $hours = $this->option('hours');
        $expiredTime = Carbon::now()->subHours($hours);

        $this->info("开始清理 {$hours} 小时前的过期验证码...");

        try {
            $deletedCount = SmsCode::where('created_at', '<', $expiredTime)->delete();

            $this->info("清理完成，共删除 {$deletedCount} 条记录。");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            // 记录数据库操作失败
            SystemLogService::exception('feature_sms', $e, [
                'hours' => $hours,
                'context' => 'clean_expired_sms_codes',
            ]);
            $this->error('清理失败：' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
