<?php

declare(strict_types=1);

namespace Modules\Notification\Providers;

use Nwidart\Modules\Traits\PathNamespace;

/**
 * Notification模块服务提供者.
 *
 * 继承自 ABase 标准服务提供者，确保模块配置正确注册
 */
class NotificationServiceProvider extends \Modules\ABase\Support\ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Notification';

    protected string $nameLower = 'module_notification';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // 所有ServiceProvider由module.json注册，不应在此手动注册
    }

    /**
     * Register commands in the format of Command::class.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Notification\Commands\SendNotificationCommand::class,
            \Modules\Notification\Commands\TemplateNotificationCommand::class,
            \Modules\Notification\Commands\StatusNotificationCommand::class,
            \Modules\Notification\Commands\CleanNotificationCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        //
    }
}