<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Base;

use Dcat\Admin\Widgets\Box;

/**
 * 提示卡片基类
 *
 * 用于创建各种类型的提示信息卡片
 *
 * 使用场景：
 * - 提示信息（info）
 * - 警告提示（warning）
 * - 成功提示（success）
 * - 错误提示（danger）
 */
abstract class AlertCard extends Box
{
    /**
     * 提示类型
     *
     * @var string
     */
    protected $alertType = 'info';

    /**
     * 提示标题
     *
     * @var string
     */
    protected $alertTitle = '提示信息';

    /**
     * 提示内容
     *
     * @var string
     */
    protected $alertContent = '这是一个提示信息卡片';

    /**
     * 卡片高度
     *
     * @var int
     */
    protected $height = 150;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->initAlert();

        parent::__construct(
            $this->alertTitle,
            $this->buildAlertContent()
        );

        $this->setHtmlAttribute('style', "min-height: {$this->height}px;");
    }

    /**
     * 初始化提示卡片
     * 子类可重写此方法设置自定义配置
     */
    protected function initAlert()
    {
    }

    /**
     * 设置提示标题
     *
     * @param string $title
     * @return $this
     */
    public function alertTitle(string $title)
    {
        $this->alertTitle = $title;
        $this->title($title);
        return $this;
    }

    /**
     * 设置提示内容
     *
     * @param string $content
     * @return $this
     */
    public function alertContent(string $content)
    {
        $this->alertContent = $content;
        $this->content($this->buildAlertContent());
        return $this;
    }

    /**
     * 设置提示类型
     *
     * @param string $type info/warning/success/danger
     * @return $this
     */
    public function alertType(string $type)
    {
        $this->alertType = $type;
        $this->content($this->buildAlertContent());
        return $this;
    }

    /**
     * 构建提示内容 HTML
     *
     * @return string
     */
    protected function buildAlertContent(): string
    {
        $contentHeight = $this->height - 55; // 减去标题栏高度

        return <<<HTML
<div class="alert alert-{$this->alertType}" style="margin: -10px;border-radius: 0;min-height: {$contentHeight}px;">
    <p style="padding: 20px 10px;">{$this->alertContent}</p>
</div>
HTML;
    }
}
