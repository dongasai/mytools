<?php

namespace Modules\AClean\Enums;

/**
 * 任务状态枚举
 *
 * 定义了清理任务的各种状态
 */
enum TASK_STATUS: int
{
    /**
     * 待执行 - 任务已创建但尚未开始执行
     */
    case PENDING = 1;

    /**
     * 备份中 - 正在创建数据备份
     */
    case BACKING_UP = 2;

    /**
     * 执行中 - 正在执行清理操作
     */
    case RUNNING = 3;

    /**
     * 已完成 - 任务执行成功完成
     */
    case COMPLETED = 4;

    /**
     * 已失败 - 任务执行失败
     */
    case FAILED = 5;

    /**
     * 已取消 - 任务被用户取消
     */
    case CANCELLED = 6;

    /**
     * 已暂停 - 任务被暂停执行
     */
    case PAUSED = 7;

    /**
     * 获取任务状态的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::PENDING => '待执行',
            self::BACKING_UP => '备份中',
            self::RUNNING => '执行中',
            self::COMPLETED => '已完成',
            self::FAILED => '已失败',
            self::CANCELLED => '已取消',
            self::PAUSED => '已暂停',
        };
    }

    /**
     * 获取任务状态的详细说明
     */
    public function getDetailDescription(): string
    {
        return match ($this) {
            self::PENDING => '任务已创建，等待执行',
            self::BACKING_UP => '正在创建数据备份',
            self::RUNNING => '正在执行清理操作',
            self::COMPLETED => '任务执行成功完成',
            self::FAILED => '任务执行过程中发生错误',
            self::CANCELLED => '任务被用户手动取消',
            self::PAUSED => '任务执行被暂停',
        };
    }

    /**
     * 获取状态对应的颜色
     */
    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::BACKING_UP => 'info',
            self::RUNNING => 'primary',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'warning',
            self::PAUSED => 'dark',
        };
    }

    /**
     * 获取状态对应的图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'fa-clock',
            self::BACKING_UP => 'fa-download',
            self::RUNNING => 'fa-spinner',
            self::COMPLETED => 'fa-check-circle',
            self::FAILED => 'fa-times-circle',
            self::CANCELLED => 'fa-ban',
            self::PAUSED => 'fa-pause-circle',
        };
    }

    /**
     * 判断任务是否正在运行
     */
    public function isRunning(): bool
    {
        return in_array($this, [self::BACKING_UP, self::RUNNING]);
    }

    /**
     * 判断任务是否已完成（成功或失败）
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }

    /**
     * 判断任务是否可以执行
     */
    public function canExecute(): bool
    {
        return in_array($this, [self::PENDING, self::PAUSED]);
    }

    /**
     * 判断任务是否可以取消
     */
    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::BACKING_UP, self::RUNNING, self::PAUSED]);
    }

    /**
     * 判断任务是否可以暂停
     */
    public function canPause(): bool
    {
        return in_array($this, [self::BACKING_UP, self::RUNNING]);
    }

    /**
     * 判断任务是否可以恢复
     */
    public function canResume(): bool
    {
        return $this === self::PAUSED;
    }

    /**
     * 获取所有任务状态的选项数组
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getDescription();
        }

        return $options;
    }

    /**
     * 获取带详细信息的选项数组
     */
    public static function getDetailOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = [
                'name' => $case->getDescription(),
                'description' => $case->getDetailDescription(),
                'color' => $case->getColor(),
                'icon' => $case->getIcon(),
                'is_running' => $case->isRunning(),
                'is_finished' => $case->isFinished(),
                'can_execute' => $case->canExecute(),
                'can_cancel' => $case->canCancel(),
                'can_pause' => $case->canPause(),
                'can_resume' => $case->canResume(),
            ];
        }

        return $options;
    }

    /**
     * 获取运行中的状态列表
     */
    public static function getRunningStatuses(): array
    {
        return [self::BACKING_UP->value, self::RUNNING->value];
    }

    /**
     * 获取已完成的状态列表
     */
    public static function getFinishedStatuses(): array
    {
        return [self::COMPLETED->value, self::FAILED->value, self::CANCELLED->value];
    }
}
