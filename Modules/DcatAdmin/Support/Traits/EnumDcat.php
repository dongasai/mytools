<?php

namespace Modules\DcatAdmin\Support\Traits;

/**
 * 枚举 Dcat Admin 通用 Trait
 *
 * 使用此 Trait 的枚举必须实现以下方法：
 * - getLabel(): string - 获取标签文本
 * - getColor(): string - 获取颜色类型 (success, warning, danger, info, primary, secondary, light, dark)
 */
trait EnumDcat
{
    /**
     * 获取Badge HTML标签（用于Grid显示）
     *
     * @return string 返回 Dcat Admin 风格的 Badge HTML
     */
    public function getBadgeHtml(): string
    {
        if (!method_exists($this, 'getLabel') || !method_exists($this, 'getColor')) {
            throw new \BadMethodCallException(
                'Enum using EnumDcat trait must implement getLabel() and getColor() methods'
            );
        }

        $label = $this->getLabel();
        $color = $this->getColor();

        return "<span class=\"badge badge-{$color}\">{$label}</span>";
    }

    /**
     * 获取Badge HTML标签（用于Grid显示，包含未知状态处理）
     *
     * @param string|null $value 枚举值
     * @param string $unknownLabel 未知状态标签
     * @return string 返回 Dcat Admin 风格的 Badge HTML
     */
    public static function getBadgeHtmlByValue(?string $value, string $unknownLabel = '未知'): string
    {
        if ($value === null) {
            return "<span class=\"badge badge-secondary\">{$unknownLabel}</span>";
        }

        // 尝试根据值获取枚举实例
        $instance = null;

        // 检查是否有 fromValue 方法
        if (method_exists(static::class, 'fromValue')) {
            $instance = static::fromValue($value);
        } else {
            // 如果没有 fromValue 方法，尝试直接匹配 cases
            foreach (static::cases() as $case) {
                if ($case->value === $value) {
                    $instance = $case;
                    break;
                }
            }
        }

        if ($instance === null) {
            return "<span class=\"badge badge-secondary\">{$unknownLabel}</span>";
        }

        return $instance->getBadgeHtml();
    }

    
    /**
     * 获取Label HTML标签（用于Show页面显示）
     *
     * @return string 返回 Dcat Admin 风格的 Label HTML
     */
    public function getLabelHtml(): string
    {
        if (!method_exists($this, 'getLabel') || !method_exists($this, 'getColor')) {
            throw new \BadMethodCallException(
                'Enum using EnumDcat trait must implement getLabel() and getColor() methods'
            );
        }

        $label = $this->getLabel();
        $color = $this->getColor();

        return "<span class=\"label label-{$color}\">{$label}</span>";
    }
}
