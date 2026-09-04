<?php

namespace Modules\ABase\Support;

use Modules\ABase\Enums\ConfigDbBackupMode;
use Modules\ABase\Enums\ConfigDbCompressionType;
use Modules\ABase\Enums\ConfigDbNotificationChannel;
use Modules\ABase\Enums\ConfigDbType;
use Modules\ABase\Enums\ConfigDbValidationRule;

/**
 * 配置表枚举辅助工具类
 */
class ConfigDbEnumHelper
{
    /**
     * 获取所有可用的配置类型
     */
    public static function getConfigTypes(): array
    {
        return ConfigDbType::getAllTypes();
    }

    /**
     * 获取所有可用的备份模式
     */
    public static function getBackupModes(): array
    {
        return ConfigDbBackupMode::getAllModes();
    }

    /**
     * 获取所有可用的压缩类型
     */
    public static function getCompressionTypes(): array
    {
        return ConfigDbCompressionType::getAllTypes();
    }

    /**
     * 获取所有可用的通知渠道
     */
    public static function getNotificationChannels(): array
    {
        return ConfigDbNotificationChannel::getAllChannels();
    }

    /**
     * 获取所有可用的验证规则
     */
    public static function getValidationRules(): array
    {
        return ConfigDbValidationRule::getAllRules();
    }

    /**
     * 验证配置类型
     */
    public static function validateConfigType(string $type): bool
    {
        return ConfigDbType::isValid($type);
    }

    /**
     * 验证备份模式
     */
    public static function validateBackupMode(string $mode): bool
    {
        return ConfigDbBackupMode::isValid($mode);
    }

    /**
     * 验证压缩类型
     */
    public static function validateCompressionType(string $type): bool
    {
        return ConfigDbCompressionType::isValid($type);
    }

    /**
     * 验证通知渠道
     */
    public static function validateNotificationChannel(string $channel): bool
    {
        return ConfigDbNotificationChannel::isValid($channel);
    }

    /**
     * 获取枚举值描述
     */
    public static function getEnumDescription(string $enumType, string $value): ?string
    {
        // 使用 tryFrom() 避免异常，无需 try-catch
        return match ($enumType) {
            'config_type' => ConfigDbType::tryFrom($value)?->getDescription(),
            'backup_mode' => ConfigDbBackupMode::tryFrom($value)?->getDescription(),
            'compression_type' => ConfigDbCompressionType::tryFrom($value)?->getDescription(),
            'notification_channel' => ConfigDbNotificationChannel::tryFrom($value)?->getDescription(),
            default => null
        };
    }

    /**
     * 获取枚举值图标
     */
    public static function getEnumIcon(string $enumType, string $value): ?string
    {
        try {
            return match ($enumType) {
                'config_type' => ConfigDbType::from($value)?->getIcon(),
                'backup_mode' => ConfigDbBackupMode::from($value)?->getIcon(),
                'compression_type' => ConfigDbCompressionType::from($value)?->getIcon(),
                'notification_channel' => ConfigDbNotificationChannel::from($value)?->getIcon(),
                default => null
            };
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * 格式化枚举信息为选择数组
     */
    public static function formatEnumForSelect(string $enumType): array
    {
        $enums = match ($enumType) {
            'config_type' => ConfigDbType::cases(),
            'backup_mode' => ConfigDbBackupMode::cases(),
            'compression_type' => ConfigDbCompressionType::cases(),
            'notification_channel' => ConfigDbNotificationChannel::cases(),
            default => []
        };

        return array_map(fn($enum) => [
            'value' => $enum->value,
            'label' => $enum->getDescription(),
            'icon' => method_exists($enum, 'getIcon') ? $enum->getIcon() : null,
        ], $enums);
    }

    /**
     * 生成枚举配置示例
     */
    public static function generateEnumConfigExamples(): array
    {
        return [
            'config_types' => [
                'description' => '配置表类型枚举示例',
                'examples' => [
                    'model_type' => [
                        'type' => ConfigDbType::MODEL->value,
                        'description' => ConfigDbType::MODEL->getDescription(),
                        'usage' => '通过模型类精确指定表',
                    ],
                    'prefix_type' => [
                        'type' => ConfigDbType::PREFIX->value,
                        'description' => ConfigDbType::PREFIX->getDescription(),
                        'usage' => '通过表前缀批量指定表',
                    ],
                ],
            ],
            'backup_modes' => [
                'description' => '备份模式枚举示例',
                'examples' => [
                    'full_backup' => [
                        'mode' => ConfigDbBackupMode::FULL->value,
                        'description' => ConfigDbBackupMode::FULL->getDescription(),
                        'includes' => '表结构 + 数据',
                    ],
                    'structure_only' => [
                        'mode' => ConfigDbBackupMode::STRUCTURE_ONLY->value,
                        'description' => ConfigDbBackupMode::STRUCTURE_ONLY->getDescription(),
                        'includes' => '仅表结构',
                    ],
                    'data_only' => [
                        'mode' => ConfigDbBackupMode::DATA_ONLY->value,
                        'description' => ConfigDbBackupMode::DATA_ONLY->getDescription(),
                        'includes' => '仅数据',
                    ],
                ],
            ],
            'compression_types' => [
                'description' => '压缩类型枚举示例',
                'examples' => [
                    'no_compression' => [
                        'type' => ConfigDbCompressionType::NONE->value,
                        'description' => ConfigDbCompressionType::NONE->getDescription(),
                        'extension' => ConfigDbCompressionType::NONE->getFileExtension(),
                    ],
                    'gzip_compression' => [
                        'type' => ConfigDbCompressionType::GZIP->value,
                        'description' => ConfigDbCompressionType::GZIP->getDescription(),
                        'extension' => ConfigDbCompressionType::GZIP->getFileExtension(),
                        'level' => ConfigDbCompressionType::GZIP->getDefaultCompressionLevel(),
                    ],
                ],
            ],
            'notification_channels' => [
                'description' => '通知渠道枚举示例',
                'examples' => [
                    'log_notification' => [
                        'channel' => ConfigDbNotificationChannel::LOG->value,
                        'description' => ConfigDbNotificationChannel::LOG->getDescription(),
                        'async' => ConfigDbNotificationChannel::LOG->isAsync() ? '是' : '否',
                    ],
                    'email_notification' => [
                        'channel' => ConfigDbNotificationChannel::EMAIL->value,
                        'description' => ConfigDbNotificationChannel::EMAIL->getDescription(),
                        'async' => ConfigDbNotificationChannel::EMAIL->isAsync() ? '是' : '否',
                    ],
                ],
            ],
        ];
    }

    /**
     * 验证配置数组中的所有枚举值
     */
    public static function validateConfigEnums(array $config): array
    {
        $errors = [];
        $warnings = [];

        // 验证表配置
        if (isset($config['tables'])) {
            foreach ($config['tables'] as $tableName => $tableConfig) {
                if (isset($tableConfig['type'])) {
                    if (! self::validateConfigType($tableConfig['type'])) {
                        $errors[] = "表 {$tableName}: 无效的类型 '{$tableConfig['type']}'";
                    }
                }

                if (isset($tableConfig['backup_mode'])) {
                    if (! self::validateBackupMode($tableConfig['backup_mode'])) {
                        $errors[] = "表 {$tableName}: 无效的备份模式 '{$tableConfig['backup_mode']}'";
                    }
                }
            }
        }

        // 验证全局设置
        if (isset($config['global_settings'])) {
            $globalSettings = $config['global_settings'];

            if (isset($globalSettings['backup_mode'])) {
                if (! self::validateBackupMode($globalSettings['backup_mode'])) {
                    $errors[] = "全局设置: 无效的备份模式 '{$globalSettings['backup_mode']}'";
                }
            }

            if (isset($globalSettings['compression']['type'])) {
                if (! self::validateCompressionType($globalSettings['compression']['type'])) {
                    $errors[] = "全局设置: 无效的压缩类型 '{$globalSettings['compression']['type']}'";
                }
            }

            if (isset($globalSettings['notification_channels'])) {
                foreach ($globalSettings['notification_channels'] as $channel) {
                    if (! self::validateNotificationChannel($channel)) {
                        $errors[] = "全局设置: 无效的通知渠道 '{$channel}'";
                    }
                }
            }
        }

        // 验证高级设置
        if (isset($config['advanced_settings'])) {
            $advancedSettings = $config['advanced_settings'];

            if (isset($advancedSettings['compression']['type'])) {
                if (! self::validateCompressionType($advancedSettings['compression']['type'])) {
                    $errors[] = "高级设置: 无效的压缩类型 '{$advancedSettings['compression']['type']}'";
                }
            }

            if (isset($advancedSettings['notifications']['channels'])) {
                foreach ($advancedSettings['notifications']['channels'] as $channel) {
                    if (! self::validateNotificationChannel($channel)) {
                        $errors[] = "高级设置: 无效的通知渠道 '{$channel}'";
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * 获取枚举统计信息
     */
    public static function getEnumStatistics(): array
    {
        return [
            'config_types' => [
                'total' => count(ConfigDbType::cases()),
                'values' => array_column(ConfigDbType::cases(), 'value'),
            ],
            'backup_modes' => [
                'total' => count(ConfigDbBackupMode::cases()),
                'values' => array_column(ConfigDbBackupMode::cases(), 'value'),
            ],
            'compression_types' => [
                'total' => count(ConfigDbCompressionType::cases()),
                'values' => array_column(ConfigDbCompressionType::cases(), 'value'),
            ],
            'notification_channels' => [
                'total' => count(ConfigDbNotificationChannel::cases()),
                'values' => array_column(ConfigDbNotificationChannel::cases(), 'value'),
            ],
            'validation_rules' => [
                'total' => count(ConfigDbValidationRule::cases()),
                'values' => array_column(ConfigDbValidationRule::cases(), 'value'),
            ],
        ];
    }
}
